FINEDICA PROJECT STRUCTURE AND DEPLOYMENT INSTRUCTIONS
=======================================================
This guide provides a concise, step-by-step reference for deploying the Finedica project on Google Cloud VM or a similar environment. It covers project structure, deployment, database setup, service management, and troubleshooting.

Project Structure
-----------------
1. .git/: Git version control folder (not needed for deployment).
2. avatars/: Stores user avatar images generated or uploaded by users.
3. chatbot/: Chatbot backend (Python and PHP), model files, requirements.txt for dependencies.
4. css/: Stylesheets for the web app.
5. data/: Data files used by the chatbot RAG.
6. debug_test.txt: For debugging or test output (not required in production).
7. deploy_to_gcp.ps1: PowerShell script to zip the finedica folder for deployment (Windows only).
8. expenditure/: Expenditure tracking app (Python, PHP, static files).
   - expenditure_venv/: Python virtual environment for local development (do NOT deploy this folder).
   - expenditure_static/: Static assets (images, JS, CSS) for the expenditure app.
   - expenditure_templates/: HTML or template files for the expenditure app.
9. future_self/: Future self feature (PHP).
10. generate_avatar/: Avatar generation scripts and logs.
11. js/: JavaScript files for frontend logic.
12. php/: Main PHP backend, including authentication, database config, and upload scripts.
13. psychometric_test/: Psychometric test feature.
14. python/: Additional Python scripts.
15. README.txt: This file.
16. requirements.txt: Python dependencies for the whole project.
17. start_services.bat / start_services.sh: Scripts to start all Python services.
18. uploads/: Stores user-uploaded files. Must be writable by the web server.

I. Preparing for Deployment
--------------------------
1. Ensure all your main project folders (php, chatbot, css, js, data, generate_avatar, expenditure, future_self, avatars, uploads, etc.) are inside a folder named 'finedica'.
2. Do NOT move or change your local files. This folder is for deployment packaging only.
3. Do NOT include any local virtual environment folders (e.g., expenditure_venv) in your deployment package.

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
     `email` VARCHAR(255) NOT NULL UNIQUE,
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


# Finedica Deployment Guide (GitHub + Google Cloud VM)

## 1. Prerequisites
- A Google Cloud account and a running VM (Ubuntu recommended).
- Your code pushed to a GitHub repository.
- Your MySQL database export (from XAMPP/phpMyAdmin).
- Your VM’s external IP address.

## 2. Step-by-Step Deployment

### A. Prepare Your Google Cloud VM
1. **SSH into your VM**  
   Use the Google Cloud Console or your SSH client.
2. **Update and install required packages**  
   ```sh
   sudo apt update
   sudo apt upgrade -y
   sudo apt install apache2 php libapache2-mod-php python3 python3-pip git mysql-server unzip -y
   ```
3. **(Optional) Install phpMyAdmin**  
   ```sh
   sudo apt install phpmyadmin php-mbstring php-zip php-gd php-json php-curl
   sudo phpenmod mbstring
   sudo systemctl restart apache2
   sudo ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin
   ```
   Access at: `http://YOUR_VM_EXTERNAL_IP/phpmyadmin`

### B. Clone Your GitHub Repository
```sh
cd /var/www/
sudo git clone https://github.com/yourusername/finedica.git
sudo chown -R www-data:www-data finedica
```
If you see a "dubious ownership" error, run:
```sh
sudo git config --global --add safe.directory /var/www/finedica
```

### C. Set Up Apache
1. **Set DocumentRoot**  
   Edit `/etc/apache2/sites-available/000-default.conf` and set:
   ```
   DocumentRoot /var/www/finedica
   ```
2. **Restart Apache**  
   ```sh
   sudo systemctl restart apache2
   ```

### D. Set Up MySQL Database
1. **Log in to MySQL**  
   ```sh
   sudo mysql -u root -p
   ```
2. **Create database and user**  
   ```sql
   CREATE DATABASE user_reg_db;
   ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'finedica';
   FLUSH PRIVILEGES;
   ```
3. **Import your .sql file**  
   - Upload your SQL file to the VM (use the Google Cloud Console SSH window, click the three-dot menu, and "Upload file").
   - Import:
     ```sh
     mysql -u root -p user_reg_db < ~/user_reg_db.sql
     ```

### E. Update PHP Config
Edit `/var/www/finedica/php/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', 'finedica');
define('DB_NAME', 'user_reg_db');
define('DB_PORT', 3306);
```

### F. Install Python Dependencies
```sh
cd /var/www/finedica
pip3 install -r requirements.txt
pip3 install -r chatbot/requirements.txt
pip3 install sentence-transformers
```

### G. Set Up and Run the Chatbot
1. **Open port 5002 in Google Cloud firewall**  
   - Go to VPC network > Firewall > Create rule.
   - Allow TCP:5002 from 0.0.0.0/0.
2. **Start the chatbot**  
   ```sh
   cd /var/www/finedica/chatbot
   python3 chatbot.py
   ```
   (For production, use systemd or another process manager.)

### H. Set Permissions
```sh
sudo chown -R www-data:www-data /var/www/finedica
sudo chmod -R 755 /var/www/finedica
```

### I. Access Your Site
- Main site: `http://YOUR_VM_EXTERNAL_IP/`
- Chatbot: Ensure your frontend fetches from `http://YOUR_VM_EXTERNAL_IP:5002/chat`

## 3. Important Notes from Previous Queries
- **MySQL port:** Always use 3306 on the VM.
- **Chatbot backend:** Must use the VM’s public IP and port 5002 in frontend fetch calls.
- **LangChain/Ollama:** Ensure Ollama is installed and running, and the phi3 model is pulled.
- **Chroma vector store:** Fix permissions for `langchain_chroma_db` if you see "readonly database" errors.
- **Psychometric test results:** Are stored in the MySQL table `psychometric_test_responses`, not in SQLite.
- **Firewall:** Open ports 80 (HTTP), 443 (HTTPS), and 5002 (chatbot).
- **phpMyAdmin:** Install and access at `/phpmyadmin` for database management.

## 4. Updating the Website from GitHub
To update your deployed website with the latest changes from your GitHub repository:

```sh
cd /var/www/finedica
# Pull latest changes from GitHub to your VM
git pull origin main
# Push your local changes from VM to GitHub (if any)
git push origin main
# If you updated dependencies:
sudo pip3 install -r requirements.txt
sudo pip3 install -r chatbot/requirements.txt
# Restart services if needed:
sudo systemctl restart apache2
sudo systemctl restart finedica_chatbot  # if using systemd
```

**Explanation:**
- Use `git pull origin main` to update your VM with the latest code from GitHub.
- Use `git push origin main` to push any local changes you made on the VM back to GitHub.
- Only use `sudo` with git if you have permission issues (ideally, fix permissions so you don’t need sudo).
- Always update dependencies and restart services after pulling new code.

### Resolving Git Pull Conflicts Due to Local Changes
If you see an error like:

```
error: Your local changes to the following files would be overwritten by merge:
  <file list>
Please commit your changes or stash them before you merge.
Aborting
```

This means you have local changes that would be overwritten by a `git pull`. To resolve:

**If you want to keep your local changes:**
```sh
git add .
git commit -m "Save local changes before pull"
git pull origin main
```

**If you want to discard your local changes (danger: this cannot be undone):**
```sh
git reset --hard
git pull origin main
```

**If you want to temporarily save your changes and re-apply them after pulling:**
```sh
git stash
git pull origin main
git stash pop
```

Choose the method that fits your needs. Committing or stashing is safest if you want to keep your work.

## 5. Troubleshooting
- **Database errors:** Check config.php and MySQL port.
- **Chatbot fallback responses:** Ensure Ollama and all Python dependencies are installed and running.
- **Permission errors:** Fix with `sudo chown` and `sudo chmod` as above.
- **Deprecation warnings:** Update LangChain classes as needed in the future.

---


VI. Python Services and Dependencies
------------------------------------
1. Install Python 3.x if not already installed.
2. Install dependencies: pip install -r requirements.txt (from the finedica or chatbot folder).
3. Start Python backend services as described below.


### Running Both Chatbot Modes (Quick & Long)
To enable both quick and long-form chatbot responses, you must run both chatbot.py and chatbotquick.py at the same time:

On Windows:
1. Open two terminal windows (or Command Prompts).
2. In the first terminal, run:
   ```sh
   cd c:\xampp\htdocs\finedica\chatbot
   python chatbot.py
   ```
3. In the second terminal, run:
   ```sh
   cd c:\xampp\htdocs\finedica\chatbot
   python chatbotquick.py
   ```

On Linux/VM:
1. Open two terminal sessions (or use tmux/screen).
2. In the first session, run:
   ```sh
   cd /var/www/finedica/chatbot
   python3 chatbot.py
   ```
3. In the second session, run:
   ```sh
   cd /var/www/finedica/chatbot
   python3 chatbotquick.py
   ```

- Ensure ports 5002 (chatbot.py) and 5003 (chatbotquick.py) are open in your VM firewall and cloud provider firewall settings.
- The frontend will automatically send requests to the correct backend based on the user’s chat mode selection (short-quick or long-late).
- You can use a process manager (like NSSM on Windows or systemd/supervisor on Linux) to keep both services running in the background.

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

## Project Structure (Windows/XAMPP Example)

```
finedica/
├── avatars/
├── chatbot/
│   ├── chatbot_model.pth
│   ├── chatbot.php
│   ├── chatbot.py
│   ├── ...
├── css/
├── data/
├── expenditure/
├── future_self/
├── generate_avatar/
├── js/
├── php/
├── psychometric_test/
├── python/
├── uploads/
├── README.md
├── requirements.txt
├── start_services.bat
├── start_services.sh
├── deploy_to_gcp.ps1
```

- All main features (PHP, Python, chatbot, etc.) are organized in subfolders.
- Place your project root (finedica) in your web server directory (e.g., c:\xampp\htdocs\finedica on Windows or /var/www/finedica on Linux/VM).

### Ensuring Ports 5002 and 5003 Are Open (Windows/Cloud VM)
To allow external access to both chatbot services, you must open ports 5002 and 5003 in your firewall and (if using a cloud provider) in your VM's network settings:

**On Google Cloud VM:**
1. Go to Google Cloud Console > VPC network > Firewall.
2. Click "Create firewall rule".
3. Set the following:
   - Name: allow-chatbot-ports
   - Targets: All instances in the network (or specify your VM)
   - Source IP ranges: 0.0.0.0/0 (or restrict as needed)
   - Protocols and ports: Check "Specified protocols and ports", then enter `tcp:5002,5003`
4. Click "Create" to apply the rule.

**On Windows (local firewall):**
1. Open Windows Defender Firewall > Advanced settings.
2. Click "Inbound Rules" > "New Rule..."
3. Select "Port", click Next.
4. Select "TCP", enter `5002,5003` in "Specific local ports".
5. Allow the connection, click Next.
6. Choose when the rule applies (Domain, Private, Public), click Next.
7. Name the rule (e.g., "Finedica Chatbot Ports") and click Finish.

- After opening the ports, you can test from another machine:
  ```sh
  curl -X POST http://YOUR_VM_IP:5002/chat -H "Content-Type: application/json" -d "{\"message\": \"hi\"}"
  curl -X POST http://YOUR_VM_IP:5003/chat -H "Content-Type: application/json" -d "{\"message\": \"hi\"}"
  ```
- You should receive a JSON response from each chatbot service.
