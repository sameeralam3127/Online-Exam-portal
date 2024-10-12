# Online Exam Portal

Welcome to the Online Exam Portal! This web application allows students to register, take exams, and view their results. Administrators can manage questions and view student records.

## Features

### User Interface
- **Registration and Login**: Students can register and log in to their accounts.
- **Dashboard**: Students have access to their personal dashboard.
- **Exam Taking**: Students can take exams.
- **Results**: Students can view their exam results and scores.

### Admin Interface
- **Secure Admin Login**: Administrators can log in to their accounts using credentials.
- **Dashboard**: Administrators have access to manage user lists.
- **Manage Questions**: Administrators can add, edit, or delete questions for exams.
- **View Student Records**: Administrators can access student profiles and exam results.

## Technologies Used
- **Frontend**: HTML, CSS, JavaScript, Bootstrap (for responsive design)
- **Backend**: PHP, MySQL (for database management)

## Requirements
- Apache server (XAMPP for Windows, LAMP for Linux)
- PHP (version 7.4 or higher)
- MySQL database

## Getting Started

### Cloning the Repository

To clone this repository, you need to have Git installed. Run the following command in your terminal:

```bash
git clone https://github.com/sameeralam3127/Online-Exam-portal.git
```

### Setting Up the Environment

1. **Place the Project in XAMPP**:
   - Move the cloned folder (`Online-Exam-portal`) into your XAMPP `htdocs` directory:
     - **Windows**: `C:\xampp\htdocs\`
     - **Linux**: `/var/www/html/`

2. **Create the Database**:
   Open your web browser and navigate to `http://localhost/phpmyadmin`. Create a new database named `online_exam_portal_db`. You can use the SQL script below to create the necessary tables.

   ### Database Creation SQL

   ```sql
   CREATE DATABASE online_exam_portal_db;

   USE online_exam_portal_db;

   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(50) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL,
       role ENUM('student', 'admin') NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE questions (
       id INT AUTO_INCREMENT PRIMARY KEY,
       question TEXT NOT NULL,
       option_a VARCHAR(255) NOT NULL,
       option_b VARCHAR(255) NOT NULL,
       option_c VARCHAR(255) NOT NULL,
       option_d VARCHAR(255) NOT NULL,
       correct_option CHAR(1) NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE exams (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       score INT NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (user_id) REFERENCES users(id)
   );

   CREATE TABLE results (
       id INT AUTO_INCREMENT PRIMARY KEY,
       exam_id INT NOT NULL,
       question_id INT NOT NULL,
       selected_option CHAR(1) NOT NULL,
       FOREIGN KEY (exam_id) REFERENCES exams(id),
       FOREIGN KEY (question_id) REFERENCES questions(id)
   );

   INSERT INTO users (username, password, role) VALUES
   ('admin', 'YOUR_HASHED_PASSWORD', 'admin'); -- Use the hashed password generated below
   ```

3. **Generate a Hashed Password**:
   - You can generate a hashed password using the `hash_password.php` file provided in the project. To do this, navigate to the following URL in your web browser:
     ```
     http://localhost/online-exam-portal/student/hash_password.php
     ```
   - This file will display a hashed password that you can use in the SQL script above. Copy the generated password and replace `YOUR_HASHED_PASSWORD` in the SQL script with the hashed value.

4. **Configure Database Connection**:
   - Open the `db.php` file located in your project directory.
   - Update the database connection settings if needed (usually no changes are necessary):
     ```php
     $servername = "localhost";
     $username = "root"; // default username
     $password = ""; // default password
     $dbname = "online_exam_portal_db";
     ```

5. **Access the Application**:
   - Start the Apache server using the XAMPP control panel.
   - In your web browser, navigate to:
     ```
     http://localhost/online-exam-portal/
     ```

## Docker Setup

To run the Online Exam Portal using Docker, you can utilize the provided `Dockerfile` and `docker-compose.yml` files. Follow these steps:

### Prerequisites

- Install Docker and Docker Compose on your machine. Refer to the [official documentation](https://docs.docker.com/get-docker/) for installation instructions.

### Setting Up Docker Environment

1. **Clone the Repository**:
   Ensure you have cloned the repository as mentioned earlier. If you haven't done this yet, run:
   ```bash
   git clone https://github.com/sameeralam3127/Online-Exam-portal.git
   ```

2. **Navigate to the Project Directory**:
   Change to the directory containing the cloned repository:
   ```bash
   cd Online-Exam-portal
   ```

3. **Build and Run the Docker Containers**:
   Use Docker Compose to build and run the application. Execute the following command in the terminal:
   ```bash
   docker-compose up --build
   ```

4. **Access the Application**:
   - The web application will be available at: 
     ```
     http://localhost:8080
     ```
   - You can access phpMyAdmin at:
     ```
     http://localhost:8081
     ```
   Use the following credentials to log in:
   - **Username**: `root`
   - **Password**: `root`

### Database Initialization

The `docker-compose.yml` file includes an `init.sql` file that will automatically create the database and necessary tables when the MySQL container starts. This includes:

- **Users Table**: For storing user credentials and roles.
- **Exams Table**: For managing exam information.
- **Questions Table**: For adding questions related to exams.
- **Results Table**: For recording exam results associated with users.

You don't need to manually create the database as it will be initialized automatically when you run the application.

### Stopping the Application

To stop the running containers, press `CTRL+C` in the terminal where the Docker containers are running. If you want to remove the containers after stopping, run:
```bash
docker-compose down
```

### Modifying the Application

Any changes you make to the application files will be reflected immediately in the running Docker containers due to the volume mapping specified in `docker-compose.yml`.

## Contributing
Contributions are welcome! If you have suggestions or improvements, please fork the repository and create a pull request.

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

