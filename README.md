Library Users Statistics Web App
A complete web application to replace manual library logbooks with a digital system for San Pedro College of Business Administration.

Features
Student check-in/check-out system

First-time student registration

Staff authentication and authorization

Dashboard with statistics

Visit records management

Data export functionality (CSV/XLSX)

Mobile-friendly interface

Requirements
PHP 8.0 or higher

MySQL 8.0 or higher

Web server (Apache recommended)

Composer (for PHP dependencies)

Installation
1. Install XAMPP on Windows
Download XAMPP from https://www.apachefriends.org/

Run the installer and follow the instructions

Install to the default directory (C:\xampp)

Start Apache and MySQL from the XAMPP Control Panel

2. Set up the project
Extract the project files to C:\xampp\htdocs\library-app

Open a command prompt and navigate to the project directory:

text
cd C:\xampp\htdocs\library-app
Install PHP dependencies (if any) using Composer

3. Create the database
Open phpMyAdmin by visiting http://localhost/phpmyadmin

Create a new database called library_stats

Import the SQL schema:

Click on the library_stats database

Go to the "Import" tab

Click "Choose File" and select migrations/schema.sql

Click "Go" to import the schema

Import sample data:

Repeat the process for migrations/sample_data.sql

4. Configure the application
Edit the database configuration in inc/config.php if needed:

php
define('DB_HOST', 'localhost');
define('DB_NAME', 'library_stats');
define('DB_USER', 'root');
define('DB_PASS', '');
5. Test the application
Open your web browser and go to http://localhost/library-app/public/

Test the student interface by entering a student number from the sample data (e.g., "PM100TL")

Test the staff interface by going to http://localhost/library-app/staff/login.php

Username: admin

Password: admin123

Staff Accounts
The installation includes two staff accounts:

Username: admin / Password: admin123 (Admin privileges)

Username: librarian / Password: admin123 (Librarian privileges)

Security Checklist for Deployment
Change default passwords for staff accounts

Update database credentials in inc/config.php

Set display_errors to 0 in inc/config.php for production

Implement HTTPS on the server

Restrict access to the staff directory via .htaccess

Regularly update the server and PHP version

Implement a backup strategy for the database

Set appropriate file permissions

Manual Testing
Student Registration:

Enter a new student number in the public interface

Complete the registration form

Verify the student can now check in

Time In/Time Out:

Check in with a student number

Verify the Time Out option appears

Complete the Time Out process

Staff Login:

Try logging in with incorrect credentials

Login with correct credentials

Test session timeout (30 minutes)

Data Export:

Filter visits by date range

Export to CSV and XLSX formats

Verify the exported file opens correctly in Excel

Troubleshooting
If pages don't load, check that Apache is running in XAMPP

If database errors occur, verify MySQL is running and credentials are correct

For permission issues, check file permissions in the project directory

Support
For technical support, please contact the system administrator.

This application was developed for San Pedro College of Business Administration to digitize library user statistics tracking.