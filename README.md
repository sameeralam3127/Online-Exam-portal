# Online Exam Portal

Welcome to the Online Exam Portal! This web application allows students to register, take exams, and view their results. Administrators can manage questions and view student records.
![image](https://github.com/user-attachments/assets/7e671d57-e9c5-431e-95cd-a89039fa856f)
![image](https://github.com/user-attachments/assets/de55604c-f213-4aa5-a7cb-a723427b0883)
![image](https://github.com/user-attachments/assets/91406313-0890-4af1-bbcc-fdde3e0f3594)
![image](https://github.com/user-attachments/assets/dfad6612-b389-4610-ba84-e7db9c885173)
![image](https://github.com/user-attachments/assets/737fbe41-c00b-4276-81b6-1e2363e94c65)
![image](https://github.com/user-attachments/assets/9b9cebcf-8658-4584-a239-d313818864e3)

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

### Running on a Linux System

1. **Install Apache, PHP, and MySQL**:
   Make sure you have Apache, PHP, and MySQL installed. You can install them using:
   ```bash
   sudo apt update
   sudo apt install apache2 php libapache2-mod-php mysql-server php-mysql
   ```

2. **Configure Apache**:
   Place the project directory in the Apache root folder:
   ```bash
   sudo mv Online-Exam-portal /var/www/html/
   ```

3. **Set Permissions**:
   Ensure that the Apache user can read the files:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/Online-Exam-portal
   sudo chmod -R 755 /var/www/html/Online-Exam-portal
   ```

4. **Create the Database**:
   Follow the same steps as mentioned above in the "Create the Database" section.

5. **Access the Application**:
   Open your web browser and navigate to:
   ```
   http://localhost/Online-Exam-portal/
   ```

## Contributing
Contributions are welcome! If you have suggestions or improvements, please fork the repository and create a pull request.

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---
