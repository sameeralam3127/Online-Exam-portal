# Online Exam Portal

A Flask-based online examination portal with SQLite database.

## Features

- User authentication (login/logout)
- Admin and student roles
- Dashboard for exam management
- Secure password handling
- Responsive design using Bootstrap 5

## Setup Instructions

1. Create a virtual environment:
```bash
python -m venv venv
source venv/bin/activate  # On macOS/Linux
```

2. Install dependencies:
```bash
pip install -r requirements.txt
```

3. Initialize the database:
```bash
python
>>> from app import app, db
>>> with app.app_context():
...     db.create_all()
>>> exit()
```

4. Run the application:
```bash
python app.py
```

The application will be available at `http://localhost:5000`

## Database

The application uses SQLite as the database, which is stored in `exam_portal.db`. The database file will be created automatically when you first run the application.

## Project Structure

```
.
├── app.py              # Main application file
├── requirements.txt    # Python dependencies
├── static/            # Static files (CSS, JS, images)
│   └── css/
│       └── style.css
├── templates/         # HTML templates
│   ├── base.html
│   ├── index.html
│   ├── login.html
│   └── dashboard.html
└── exam_portal.db     # SQLite database file
```

## Security Features

- Password hashing using Werkzeug
- Session management with Flask-Login
- CSRF protection
- Secure password storage

## Contributing

Feel free to submit issues and enhancement requests! 