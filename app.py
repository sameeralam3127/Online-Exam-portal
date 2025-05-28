from flask import Flask, render_template, request, redirect, url_for, flash, jsonify
from flask_sqlalchemy import SQLAlchemy
from flask_login import LoginManager, UserMixin, login_user, login_required, logout_user, current_user
from werkzeug.security import generate_password_hash, check_password_hash
from datetime import datetime, timedelta
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
    questions = db.relationship('Question', backref='exam', lazy=True, cascade='all, delete-orphan')
    results = db.relationship('ExamResult', backref='exam', lazy=True, cascade='all, delete-orphan')

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
    exam_id = db.Column(db.Integer, db.ForeignKey('exam.id', ondelete='CASCADE'), nullable=False)
    user_id = db.Column(db.Integer, db.ForeignKey('user.id', ondelete='CASCADE'), nullable=False)
    score = db.Column(db.Integer)
    total_marks = db.Column(db.Integer)
    start_time = db.Column(db.DateTime, default=datetime.utcnow)
    end_time = db.Column(db.DateTime)
    is_passed = db.Column(db.Boolean)
    answers = db.relationship('UserAnswer', backref='result', lazy=True, cascade='all, delete-orphan')

# User Answer Model
class UserAnswer(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    result_id = db.Column(db.Integer, db.ForeignKey('exam_result.id', ondelete='CASCADE'), nullable=False)
    question_id = db.Column(db.Integer, db.ForeignKey('question.id', ondelete='CASCADE'), nullable=False)
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

@app.route('/admin/users', methods=['GET', 'POST'])
@login_required
def admin_users():
    if not current_user.is_admin:
        flash('Access denied')
        return redirect(url_for('dashboard'))

    if request.method == 'POST':
        username = request.form.get('username')
        email = request.form.get('email')
        password = request.form.get('password')
        is_admin = 'is_admin' in request.form
        
        # Check if username already exists
        if User.query.filter_by(username=username).first():
            flash('Username already exists', 'danger')
            return redirect(url_for('admin_users'))
        
        # Check if email already exists
        if User.query.filter_by(email=email).first():
            flash('Email already registered', 'danger')
            return redirect(url_for('admin_users'))
        
        # Create new user
        user = User(username=username, email=email, is_admin=is_admin)
        user.set_password(password)
        db.session.add(user)
        db.session.commit()
        
        flash('User created successfully!', 'success')
        return redirect(url_for('admin_users'))

    users = User.query.filter_by(is_admin=False).all()
    return render_template('admin/users.html', users=users)

@app.route('/admin/reports')
@login_required
def admin_reports():
    if not current_user.is_admin:
        flash('Access denied. Admin privileges required.', 'danger')
        return redirect(url_for('dashboard'))
    
    # Get filter parameters
    exam_id = request.args.get('exam_id', type=int)
    date_from = request.args.get('date_from')
    date_to = request.args.get('date_to')
    status = request.args.get('status')
    
    # Debug logging
    print("Starting admin_reports route")
    print(f"Filter parameters: exam_id={exam_id}, date_from={date_from}, date_to={date_to}, status={status}")
    
    # Base query with eager loading of relationships
    query = ExamResult.query.join(User).join(Exam).options(
        db.joinedload(ExamResult.student),
        db.joinedload(ExamResult.exam),
        db.joinedload(ExamResult.answers)
    ).order_by(ExamResult.start_time.desc())
    
    # Apply filters
    if exam_id:
        query = query.filter(ExamResult.exam_id == exam_id)
    
    if date_from:
        try:
            date_from = datetime.strptime(date_from, '%Y-%m-%d')
            query = query.filter(ExamResult.start_time >= date_from)
        except ValueError:
            flash('Invalid date format for Date From', 'warning')
    
    if date_to:
        try:
            date_to = datetime.strptime(date_to, '%Y-%m-%d')
            # Add one day to include the entire day
            date_to = date_to + timedelta(days=1)
            query = query.filter(ExamResult.start_time < date_to)
        except ValueError:
            flash('Invalid date format for Date To', 'warning')
    
    if status:
        if status == 'passed':
            query = query.filter(ExamResult.is_passed == True)
        elif status == 'failed':
            query = query.filter(ExamResult.is_passed == False)
    
    # Get all exams for the filter dropdown
    exams = Exam.query.order_by(Exam.title).all()
    print(f"Found {len(exams)} exams")
    
    # Execute the query and get results
    results = query.all()
    print(f"Found {len(results)} results")
    
    # Debug: Print each result
    for result in results:
        print(f"Result: Student={result.student.username}, Exam={result.exam.title}, Score={result.score}")
    
    # Calculate statistics
    total_results = len(results)
    if total_results > 0:
        passed_results = sum(1 for r in results if r.is_passed)
        pass_rate = (passed_results / total_results) * 100
        
        total_score_percentage = sum((r.score / r.total_marks) * 100 for r in results)
        avg_score = total_score_percentage / total_results
        
        unique_students = len(set(r.user_id for r in results))
    else:
        pass_rate = 0
        avg_score = 0
        unique_students = 0
    
    stats = {
        'total_results': total_results,
        'pass_rate': round(pass_rate, 1),
        'avg_score': round(avg_score, 1),
        'unique_students': unique_students
    }
    
    print("Stats:", stats)
    
    return render_template('admin/reports.html',
                         exams=exams,
                         results=results,
                         stats=stats,
                         filters={
                             'exam_id': exam_id,
                             'date_from': date_from,
                             'date_to': date_to,
                             'status': status
                         })

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
    
    try:
        # This will automatically delete related questions and results due to cascade
        db.session.delete(exam)
        db.session.commit()
        flash('Exam deleted successfully!', 'success')
    except Exception as e:
        db.session.rollback()
        flash('Error deleting exam. Please try again.', 'danger')
        print(f"Error deleting exam: {str(e)}")
    
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

# Create sample data function
def create_sample_data():
    print("Creating admin user...")
    # Create admin user if not exists
    admin = User.query.filter_by(username='admin').first()
    if not admin:
        admin = User(
            username='admin',
            email='admin@example.com',
            is_admin=True
        )
        admin.set_password('admin123')
        db.session.add(admin)
        db.session.commit()
        print("Admin user created successfully!")
    else:
        print("Admin user already exists")

    print("Creating sample exam...")
    # Create a sample exam if it doesn't exist
    exam = Exam.query.filter_by(title="Computer Science Fundamentals").first()
    if not exam:
        exam = Exam(
            title="Computer Science Fundamentals",
            description="A comprehensive test covering fundamental computer science concepts.",
            duration=60,
            total_marks=100,
            passing_marks=60,
            created_by=admin.id,
            is_active=True
        )
        db.session.add(exam)
        db.session.commit()
        print("Sample exam created successfully!")

        # Add sample questions
        questions = [
            Question(
                exam_id=exam.id,
                question_text="What is the time complexity of binary search?",
                option_a="O(1)",
                option_b="O(log n)",
                option_c="O(n)",
                option_d="O(n^2)",
                correct_answer="B",
                marks=10
            ),
            Question(
                exam_id=exam.id,
                question_text="Which data structure uses LIFO principle?",
                option_a="Queue",
                option_b="Stack",
                option_c="Tree",
                option_d="Graph",
                correct_answer="B",
                marks=10
            )
        ]
        db.session.add_all(questions)
        db.session.commit()
        print("Sample questions added successfully!")
    else:
        print("Sample exam already exists")

    print("Creating sample students...")
    # Create sample students
    students = []
    for i in range(1, 4):  # Create 3 sample students
        username = f"student{i}"
        student = User.query.filter_by(username=username).first()
        if not student:
            student = User(
                username=username,
                email=f"{username}@example.com",
                is_admin=False
            )
            student.set_password('student123')
            students.append(student)
            print(f"Created student: {username}")
    
    if students:
        db.session.add_all(students)
        db.session.commit()
        print("Sample students created successfully!")

    print("Creating sample exam results...")
    # Create sample exam results
    current_time = datetime.utcnow()
    
    # Get all students (including existing ones)
    all_students = User.query.filter_by(is_admin=False).all()
    
    for student in all_students:
        # Create two results for each student
        results = []
        
        # Passed attempt
        if not ExamResult.query.filter_by(user_id=student.id, score=80).first():
            results.append(ExamResult(
                exam_id=exam.id,
                user_id=student.id,
                score=80,
                total_marks=100,
                start_time=current_time - timedelta(days=1),
                end_time=current_time - timedelta(days=1, minutes=45),
                is_passed=True
            ))
        
        # Failed attempt
        if not ExamResult.query.filter_by(user_id=student.id, score=50).first():
            results.append(ExamResult(
                exam_id=exam.id,
                user_id=student.id,
                score=50,
                total_marks=100,
                start_time=current_time - timedelta(hours=2),
                end_time=current_time - timedelta(hours=1),
                is_passed=False
            ))
        
        if results:
            db.session.add_all(results)
            db.session.commit()
            print(f"Created exam results for {student.username}")
    
    print("Sample data creation completed!")

    # Print summary of created data
    print("\nData Creation Summary:")
    print(f"Total Users: {User.query.count()}")
    print(f"Total Exams: {Exam.query.count()}")
    print(f"Total Questions: {Question.query.count()}")
    print(f"Total Results: {ExamResult.query.count()}")

if __name__ == '__main__':
    # Delete the existing database file if it exists
    if os.path.exists('exam_portal.db'):
        os.remove('exam_portal.db')
        print("Existing database removed.")
    
    with app.app_context():
        print("Creating database tables...")
        db.create_all()
        print("Database tables created successfully!")
        
        print("Creating sample data...")
        create_sample_data()
        print("Sample data creation completed!")
    
    # Try different ports if 5001 is in use
    ports = [5001, 5002, 5003, 5004, 5005]
    
    for port in ports:
        try:
            print(f"Attempting to start server on port {port}")
            app.run(debug=True, port=port)
            break
        except OSError as e:
            print(f"Port {port} is in use, trying next port...")
            if port == ports[-1]:
                print("All ports are in use. Please free up a port and try again.")
                raise e 