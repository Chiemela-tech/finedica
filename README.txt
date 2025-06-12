FINEDICA PROJECT STRUCTURE AND DEPLOYMENT INSTRUCTIONS
=======================================================

Project Structure
-----------------
1. avatars/: Stores user avatar images generated or uploaded by users.
2. chatbot/: Chatbot backend (Python and PHP), model files, requirements.txt for dependencies.
3. css/: Stylesheets for the web app.
4. data/: Data files used by the chatbot RAG.
5. deploy_to_gcp.ps1: PowerShell script to zip the finedica folder for deployment (Windows only).
6. expenditure/: Expenditure tracking app (Python, PHP, static files).
7. future_self/: Future self feature (PHP).
8. generate_avatar/: Avatar generation scripts and logs.
9. js/: JavaScript files for frontend logic.
10. php/: Main PHP backend, including authentication, database config, and upload scripts.
11. psychometric_test/: Psychometric test feature.
12. python/: Additional Python scripts.
13. requirements.txt: Python dependencies for the whole project.
14. start_services.bat / start_services.sh: Scripts to start all Python services.
15. uploads/: Stores user-uploaded files. Must be writable by the web server.

I. Preparing for Deployment
--------------------------
1. Ensure all your main project folders (php, chatbot, css, js, data, generate_avatar, expenditure, future_self, avatars, uploads, etc.) are inside a folder named 'finedica'.
2. Do NOT move or change your local files. This folder is for deployment packaging only.

II. Packaging for Google Cloud VM
---------------------------------
1. On Windows:
   a. Use the provided 'deploy_to_gcp.ps1' (PowerShell) script to zip the finedica folder for upload.
   b. Alternatively, manually zip the folder.
2. On Linux/Mac:
   a. Use your preferred method to create a .zip or .tar.gz archive of the finedica folder.

III. Deploying to Google Cloud VM
---------------------------------
1. Upload the zipped finedica folder to your Google Cloud VM (use SCP, SFTP, or the Google Cloud Console file upload).
2. SSH into your VM and unzip the folder in your desired location (e.g., /var/www/html/ for Apache).
3. Set your web server's document root to the 'finedica' folder.
4. Ensure the server allows traffic on ports 80 (web) and 5002 (Python services):
   a. In Google Cloud Console, go to VPC network > Firewall and allow TCP traffic on ports 80 and 5002.
5. Set permissions for the uploads directory:
   a. On Linux: sudo chmod 777 finedica/uploads
   b. On Windows: Ensure the IIS/Apache user has write permissions to finedica\uploads
6. Edit php/db_config.php to match your database credentials (default password: finedica).
7. Start Apache and MySQL/MariaDB on your VM.
8. Access phpMyAdmin (if installed) at http://your-server-ip/phpmyadmin to manage your database.
9. Import your database schema (see section VI below for table structure).
10. Install Python 3.x if not already installed.
11. Install Python dependencies:
    a. pip install -r requirements.txt (from the finedica or chatbot folder).
12. Start Python backend services:
    a. On Windows: Double-click start_services.bat or run it in a terminal.
    b. On Linux: Run ./start_services.sh in a terminal.
13. (Recommended) Use a process manager to keep Python services running after logout or server restarts:
    a. On Linux: Use systemd, supervisor, or nohup (see example below).
    b. On Windows: Use Task Scheduler or NSSM (Non-Sucking Service Manager).

IV. Database Configuration
-------------------------
1. Edit php/db_config.php to set DB_HOST, DB_USER, DB_PASS, and DB_NAME as needed.
2. Default password: finedica
3. Ensure the database and user exist and have the correct permissions.

V. Database Tables Structure (MySQL)
------------------------------------
1. users
   CREATE TABLE `users` (
     `id` INT NOT NULL AUTO_INCREMENT,
     `first_name` VARCHAR(100) NOT NULL,
     `last_name` VARCHAR(100) NOT NULL,
     `dob` DATE NOT NULL,
     `occupation` VARCHAR(100),
     `email` VARCHAR(255) NOT NULL UNIQUE,
     `password` VARCHAR(255) NOT NULL,
     `gender` VARCHAR(10),
     PRIMARY KEY (`id`)
   );
2. expenditures
   CREATE TABLE `expenditures` (
     `id` INT NOT NULL AUTO_INCREMENT,
     `user_id` INT NOT NULL,
     `category` VARCHAR(100) NOT NULL,
     `amount` DECIMAL(10,2) NOT NULL,
     `date` DATE NOT NULL,
     PRIMARY KEY (`id`),
     FOREIGN KEY (`user_id`) REFERENCES users(`id`)
   );
3. avatars
   CREATE TABLE `avatars` (
     `id` INT NOT NULL AUTO_INCREMENT,
     `user_id` INT NOT NULL,
     `avatar_path` VARCHAR(255) NOT NULL,
     `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (`id`),
     FOREIGN KEY (`user_id`) REFERENCES users(`id`)
   );
4. psychometric_results
   CREATE TABLE `psychometric_results` (
     `id` INT NOT NULL AUTO_INCREMENT,
     `user_id` INT NOT NULL,
     `result_json` TEXT NOT NULL,
     `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (`id`),
     FOREIGN KEY (`user_id`) REFERENCES users(`id`)
   );
- Add or modify tables as required by your application.

VI. Python Services and Dependencies
------------------------------------
1. Install Python 3.x if not already installed.
2. Install dependencies: pip install -r requirements.txt (from the finedica or chatbot folder).
3. Start Python backend services as described above.

VII. File Uploads
-----------------
1. The uploads/ directory must be writable by the web server for file uploads to work.
2. On Linux: sudo chmod 777 finedica/uploads
3. On Windows: Set write permissions for the IIS/Apache user.

VIII. Troubleshooting
---------------------
1. If you see file permission errors, check uploads/ directory permissions.
2. For database errors, verify db_config.php and that the database server is running.
3. For Python service errors, check that all dependencies are installed and the service is running (see logs or terminal output).
4. For port issues, ensure ports 80 (web) and 5002 (Python services) are open and not blocked by a firewall.

IX. Ensuring Uninterrupted Execution of Python Services
------------------------------------------------------
1. For production or cloud deployment, use a process manager to keep Python services running even after logout or server restarts.
2. Recommended options:
   a. On Linux: Use systemd, supervisor, or nohup with & (e.g., nohup python chatbot/chatbot.py &)
   b. On Windows: Use Task Scheduler to run start_services.bat at startup, or use a third-party service manager like NSSM (Non-Sucking Service Manager).
3. Always check that required ports (e.g., 5002 for chatbot) are open and not blocked by firewalls.
4. Regularly monitor logs for errors (see chatbot/logs or relevant service logs).

X. Example systemd Service File (Linux)
---------------------------------------
[Unit]
Description=Finedica Chatbot Service
After=network.target

[Service]
Type=simple
WorkingDirectory=/path/to/finedica
ExecStart=/usr/bin/python3 chatbot/chatbot.py
Restart=always
User=www-data

[Install]
WantedBy=multi-user.target

- Save as /etc/systemd/system/finedica-chatbot.service and run:
  sudo systemctl daemon-reload
  sudo systemctl enable --now finedica-chatbot

XI. Example NSSM Setup (Windows)
--------------------------------
1. Download NSSM (https://nssm.cc/).
2. Run: nssm install FinedicaChatbot
3. Set Application path to python.exe and Arguments to chatbot/chatbot.py
4. Set Startup directory to your finedica folder.
5. Start the service from the NSSM UI or Windows Services panel.

XII. General Recommendations
---------------------------
1. Always use absolute paths in production scripts for reliability.
2. Ensure all environment variables (if any) are set in your process manager or service configuration.
3. For persistent logs, redirect output to log files (e.g., python chatbot.py > chatbot.log 2>&1).
