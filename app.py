from flask import Flask, render_template, request, redirect, url_for, flash, jsonify
from flask_sqlalchemy import SQLAlchemy
from flask_login import LoginManager, UserMixin, login_user, login_required, logout_user, current_user
from werkzeug.security import generate_password_hash, check_password_hash
from datetime import datetime
import os

app = Flask(__name__)
app.config['SECRET_KEY'] = os.urandom(24)
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///exam_portal.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

db = SQLAlchemy(app)
login_manager = LoginManager()
login_manager.init_app(app)
login_manager.login_view = 'login'

# User Model
class User(UserMixin, db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    password_hash = db.Column(db.String(128))
    is_admin = db.Column(db.Boolean, default=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    exams_taken = db.relationship('ExamResult', backref='student', lazy=True)

    def set_password(self, password):
        self.password_hash = generate_password_hash(password)

    def check_password(self, password):
        return check_password_hash(self.password_hash, password)

# Exam Model
class Exam(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(200), nullable=False)
    description = db.Column(db.Text)
    duration = db.Column(db.Integer)  # Duration in minutes
    total_marks = db.Column(db.Integer)
    passing_marks = db.Column(db.Integer)
    created_by = db.Column(db.Integer, db.ForeignKey('user.id'))
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    is_active = db.Column(db.Boolean, default=True)
    questions = db.relationship('Question', backref='exam', lazy=True)
    results = db.relationship('ExamResult', backref='exam', lazy=True)

# Question Model
class Question(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    exam_id = db.Column(db.Integer, db.ForeignKey('exam.id'), nullable=False)
    question_text = db.Column(db.Text, nullable=False)
    option_a = db.Column(db.String(200), nullable=False)
    option_b = db.Column(db.String(200), nullable=False)
    option_c = db.Column(db.String(200), nullable=False)
    option_d = db.Column(db.String(200), nullable=False)
    correct_answer = db.Column(db.String(1), nullable=False)  # 'A', 'B', 'C', or 'D'
    marks = db.Column(db.Integer, default=1)

# Exam Result Model
class ExamResult(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    exam_id = db.Column(db.Integer, db.ForeignKey('exam.id'), nullable=False)
    user_id = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)
    score = db.Column(db.Integer)
    total_marks = db.Column(db.Integer)
    start_time = db.Column(db.DateTime, default=datetime.utcnow)
    end_time = db.Column(db.DateTime)
    is_passed = db.Column(db.Boolean)
    answers = db.relationship('UserAnswer', backref='result', lazy=True)

# User Answer Model
class UserAnswer(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    result_id = db.Column(db.Integer, db.ForeignKey('exam_result.id'), nullable=False)
    question_id = db.Column(db.Integer, db.ForeignKey('question.id'), nullable=False)
    selected_answer = db.Column(db.String(1))  # 'A', 'B', 'C', or 'D'
    is_correct = db.Column(db.Boolean)
    question = db.relationship('Question', backref='user_answers')

@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))

# Routes
@app.route('/')
def index():
    return render_template('index.html')

@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        username = request.form.get('username')
        email = request.form.get('email')
        password = request.form.get('password')
        
        if User.query.filter_by(username=username).first():
            flash('Username already exists')
            return redirect(url_for('register'))
        
        if User.query.filter_by(email=email).first():
            flash('Email already registered')
            return redirect(url_for('register'))
        
        user = User(username=username, email=email)
        user.set_password(password)
        db.session.add(user)
        db.session.commit()
        
        flash('Registration successful! Please login.')
        return redirect(url_for('login'))
    
    return render_template('register.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')
        user = User.query.filter_by(username=username).first()
        
        if user and user.check_password(password):
            login_user(user)
            return redirect(url_for('dashboard'))
        flash('Invalid username or password')
    return render_template('login.html')

@app.route('/dashboard')
@login_required
def dashboard():
    if current_user.is_admin:
        # Get all exams, users, and results for admin dashboard
        exams = Exam.query.all()
        users = User.query.filter_by(is_admin=False).all()
        results = ExamResult.query.order_by(ExamResult.start_time.desc()).all()
        return render_template('admin/dashboard.html',
                             exams=exams,
                             users=users,
                             results=results)
    else:
        # Get available and past exams for student dashboard
        available_exams = Exam.query.filter_by(is_active=True).all()
        past_exams = ExamResult.query.filter_by(user_id=current_user.id).all()
        return render_template('student/dashboard.html',
                             available_exams=available_exams,
                             past_exams=past_exams)

# Admin Routes
@app.route('/admin/exams')
@login_required
def admin_exams():
    if not current_user.is_admin:
        flash('Access denied')
        return redirect(url_for('dashboard'))
    exams = Exam.query.all()
    return render_template('admin/exams.html', exams=exams)

@app.route('/admin/exam/create', methods=['GET', 'POST'])
@login_required
def create_exam():
    if not current_user.is_admin:
        flash('Access denied')
        return redirect(url_for('dashboard'))
    
    if request.method == 'POST':
        exam = Exam(
            title=request.form.get('title'),
            description=request.form.get('description'),
            duration=int(request.form.get('duration')),
            total_marks=int(request.form.get('total_marks')),
            passing_marks=int(request.form.get('passing_marks')),
            created_by=current_user.id
        )
        db.session.add(exam)
        db.session.commit()
        flash('Exam created successfully!')
        return redirect(url_for('admin_exams'))
    
    return render_template('admin/create_exam.html')

@app.route('/admin/users')
@login_required
def admin_users():
    if not current_user.is_admin:
        flash('Access denied')
        return redirect(url_for('dashboard'))
    users = User.query.filter_by(is_admin=False).all()
    return render_template('admin/users.html', users=users)

@app.route('/admin/reports')
@login_required
def admin_reports():
    if not current_user.is_admin:
        flash('Access denied. Admin privileges required.', 'danger')
        return redirect(url_for('dashboard'))
    
    # Get all exams for the filter dropdown
    exams = Exam.query.all()
    
    # Get all results with related data
    results = ExamResult.query.join(User).join(Exam).all()
    
    return render_template('admin/reports.html', 
                         exams=exams,
                         results=results)

@app.route('/logout')
@login_required
def logout():
    logout_user()
    return redirect(url_for('index'))

@app.route('/debug/users')
def debug_users():
    users = User.query.all()
    return '<br>'.join([f"{u.id}: {u.username} ({u.email}) - Admin: {u.is_admin}" for u in users])

@app.route('/admin/exam/<int:exam_id>/edit', methods=['GET', 'POST'])
@login_required
def edit_exam(exam_id):
    if not current_user.is_admin:
        flash('Access denied. Admin privileges required.', 'danger')
        return redirect(url_for('dashboard'))
    
    exam = Exam.query.get_or_404(exam_id)
    
    if request.method == 'POST':
        exam.title = request.form.get('title')
        exam.description = request.form.get('description')
        exam.duration = int(request.form.get('duration'))
        exam.total_marks = int(request.form.get('total_marks'))
        exam.passing_marks = int(request.form.get('passing_marks'))
        db.session.commit()
        flash('Exam updated successfully!', 'success')
        return redirect(url_for('admin_exams'))
    
    return render_template('admin/edit_exam.html', exam=exam)

@app.route('/admin/exam/<int:exam_id>/delete', methods=['POST'])
@login_required
def delete_exam(exam_id):
    if not current_user.is_admin:
        flash('Access denied. Admin privileges required.', 'danger')
        return redirect(url_for('dashboard'))
    
    exam = Exam.query.get_or_404(exam_id)
    db.session.delete(exam)
    db.session.commit()
    flash('Exam deleted successfully!', 'success')
    return redirect(url_for('admin_exams'))

@app.route('/exam/<int:exam_id>/start', methods=['GET', 'POST'])
@login_required
def start_exam(exam_id):
    exam = Exam.query.get_or_404(exam_id)
    
    # Check if exam is active
    if not exam.is_active:
        flash('This exam is not currently available.', 'danger')
        return redirect(url_for('dashboard'))
    
    # Check if user has already taken this exam
    existing_result = ExamResult.query.filter_by(
        exam_id=exam_id,
        user_id=current_user.id
    ).first()
    
    if existing_result:
        flash('You have already taken this exam.', 'warning')
        return redirect(url_for('dashboard'))
    
    if request.method == 'POST':
        # Process exam submission
        score = 0
        answers = []
        
        for question in exam.questions:
            selected_answer = request.form.get(f'question_{question.id}')
            is_correct = selected_answer == question.correct_answer
            
            if is_correct:
                score += question.marks
            
            answer = UserAnswer(
                question_id=question.id,
                selected_answer=selected_answer,
                is_correct=is_correct
            )
            answers.append(answer)
        
        # Create exam result
        result = ExamResult(
            exam_id=exam_id,
            user_id=current_user.id,
            score=score,
            total_marks=exam.total_marks,
            end_time=datetime.utcnow(),
            is_passed=score >= exam.passing_marks,
            answers=answers
        )
        
        db.session.add(result)
        db.session.commit()
        
        flash('Exam submitted successfully!', 'success')
        return redirect(url_for('dashboard'))
    
    return render_template('student/take_exam.html', exam=exam)

@app.route('/admin/exam/<int:exam_id>/questions', methods=['GET', 'POST'])
@login_required
def manage_questions(exam_id):
    if not current_user.is_admin:
        flash('Access denied. Admin privileges required.', 'danger')
        return redirect(url_for('dashboard'))
    
    exam = Exam.query.get_or_404(exam_id)
    
    if request.method == 'POST':
        question = Question(
            exam_id=exam_id,
            question_text=request.form.get('question_text'),
            option_a=request.form.get('option_a'),
            option_b=request.form.get('option_b'),
            option_c=request.form.get('option_c'),
            option_d=request.form.get('option_d'),
            correct_answer=request.form.get('correct_answer'),
            marks=int(request.form.get('marks', 1))
        )
        
        db.session.add(question)
        db.session.commit()
        flash('Question added successfully!', 'success')
        return redirect(url_for('manage_questions', exam_id=exam_id))
    
    return render_template('admin/manage_questions.html', exam=exam)

@app.route('/admin/question/<int:question_id>/delete', methods=['POST'])
@login_required
def delete_question(question_id):
    if not current_user.is_admin:
        flash('Access denied. Admin privileges required.', 'danger')
        return redirect(url_for('dashboard'))
    
    question = Question.query.get_or_404(question_id)
    exam_id = question.exam_id
    
    db.session.delete(question)
    db.session.commit()
    
    flash('Question deleted successfully!', 'success')
    return redirect(url_for('manage_questions', exam_id=exam_id))

@app.route('/admin/result/<int:result_id>/delete', methods=['POST'])
@login_required
def delete_result(result_id):
    if not current_user.is_admin:
        flash('Access denied. Admin privileges required.', 'danger')
        return redirect(url_for('dashboard'))
    
    result = ExamResult.query.get_or_404(result_id)
    db.session.delete(result)
    db.session.commit()
    
    flash('Exam result deleted successfully!', 'success')
    return redirect(url_for('admin_reports'))

@app.route('/admin/user/<int:user_id>/edit', methods=['GET', 'POST'])
@login_required
def edit_user(user_id):
    if not current_user.is_admin:
        flash('Access denied. Admin privileges required.', 'danger')
        return redirect(url_for('dashboard'))
    
    user = User.query.get_or_404(user_id)
    
    if request.method == 'POST':
        user.username = request.form.get('username')
        user.email = request.form.get('email')
        
        # Update password if provided
        new_password = request.form.get('password')
        if new_password:
            user.set_password(new_password)
        
        # Update admin status
        user.is_admin = 'is_admin' in request.form
        
        db.session.commit()
        flash('User updated successfully!', 'success')
        return redirect(url_for('admin_users'))
    
    return render_template('admin/edit_user.html', user=user)

@app.route('/admin/user/<int:user_id>/assign-exam', methods=['GET', 'POST'])
@login_required
def assign_exam(user_id):
    if not current_user.is_admin:
        flash('Access denied. Admin privileges required.', 'danger')
        return redirect(url_for('dashboard'))
    
    user = User.query.get_or_404(user_id)
    
    if request.method == 'POST':
        exam_id = request.form.get('exam_id')
        exam = Exam.query.get_or_404(exam_id)
        
        # Delete any existing results for this exam
        existing_result = ExamResult.query.filter_by(
            exam_id=exam_id,
            user_id=user_id
        ).first()
        
        if existing_result:
            db.session.delete(existing_result)
        
        db.session.commit()
        flash(f'Exam "{exam.title}" has been assigned to {user.username}', 'success')
        return redirect(url_for('admin_users'))
    
    # Get all exams
    exams = Exam.query.all()
    # Get exams already taken by the user
    taken_exams = [result.exam_id for result in user.exams_taken]
    
    return render_template('admin/assign_exam.html', 
                         user=user, 
                         exams=exams, 
                         taken_exams=taken_exams)

@app.route('/exam/result/<int:result_id>')
@login_required
def view_result(result_id):
    result = ExamResult.query.get_or_404(result_id)
    
    # Ensure the user can only view their own results
    if result.user_id != current_user.id and not current_user.is_admin:
        flash('Access denied', 'danger')
        return redirect(url_for('dashboard'))
    
    # Load the questions for each answer
    for answer in result.answers:
        answer.question = Question.query.get(answer.question_id)
    
    return render_template('student/view_result.html', result=result)

def create_admin_user():
    with app.app_context():
        if not User.query.filter_by(username='admin').first():
            admin = User(username='admin', email='admin@example.com', is_admin=True)
            admin.set_password('admin123')
            db.session.add(admin)
            db.session.commit()
            print("Admin user created successfully!")

def create_sample_questions():
    # Create a sample exam if it doesn't exist
    exam = Exam.query.filter_by(title="Computer Science Fundamentals").first()
    if not exam:
        exam = Exam(
            title="Computer Science Fundamentals",
            description="A comprehensive test covering fundamental computer science concepts including algorithms, data structures, programming, and computer architecture.",
            duration=60,  # 60 minutes
            total_marks=100,
            passing_marks=60,
            created_by=1,  # Assuming admin user has ID 1
            is_active=True
        )
        db.session.add(exam)
        db.session.commit()

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
        create_admin_user()
        create_sample_questions()
    app.run(debug=True) 