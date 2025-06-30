# Finedica - Financial Goal Achievement Platform

The Finedica App is designed to empower users on their financial journey by providing a personalised and guided experience towards achieving their monetary goals. It leverages a unique approach that combines self-reflection, behavioural analysis, and future visualisation to help users make informed financial decisions.

## Table of Contents

- [Overview](#overview)
- [The Four Phases of Finedica](#the-four-phases-of-finedica)
  - [1. Data Gathering to Build the Future Self Avatar](#1-data-gathering-to-build-the-future-self-avatar)
  - [2. Analysing Previous Behaviour](#2-analysing-previous-behaviour)
  - [3. Deciding Money Philosophy](#3-deciding-money-philosophy)
  - [4. Specific Goal Setting and Implementation](#4-specific-goal-setting-and-implementation)
- [Technologies Used](#technologies-used)
  - [Backend](#backend)
  - [Frontend](#frontend)
  - [Database](#database)
  - [Cloud Services](#cloud-services)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
  - [Core Tables](#core-tables)
  - [Users Table](#users-table)
  - [Expenditure Table](#expenditure-table)
  - [Additional Tables](#additional-tables)
- [Installation and Deployment](#installation-and-deployment)
  - [Prerequisites](#prerequisites)
  - [Local Development (XAMPP/WAMP)](#local-development-xamppwamp)
  - [Google Cloud VM Deployment](#google-cloud-vm-deployment)
  - [Running Both Chatbot Modes](#running-both-chatbot-modes)
- [Usage](#usage)
- [API Endpoints](#api-endpoints)
  - [Chatbot Services](#chatbot-services)
  - [File Uploads](#file-uploads)
- [Configuration](#configuration)
  - [Environment Variables](#environment-variables)
  - [Important Directories](#important-directories)
- [Troubleshooting](#troubleshooting)
  - [Common Issues](#common-issues)
  - [Updating from GitHub](#updating-from-github)
- [Contributing](#contributing)
- [Licence](#licence)
- [Contact](#contact)

## Overview

Finedica provides a comprehensive platform for financial planning through four distinct and interconnected phases that guide users from self-discovery to goal implementation.

### Key Features

- **Personalised Future Self Avatar**: Visual representation of financial goals and future aspirations
- **Behavioural Analysis**: In-depth analysis of past financial activities and spending patterns
- **Psychometric Testing**: Guided exercises to define personal money philosophy
- **Goal Setting & Tracking**: Concrete, actionable financial goals with progress monitoring
- **AI-Powered Chatbot**: Two-mode chatbot (quick responses and detailed analysis)
- **Expenditure Tracking**: Comprehensive income and expense management

## The Four Phases of Finedica

### 1. Data Gathering to Build the Future Self Avatar
This initial phase focuses on collecting essential user data, including financial habits, aspirations, and personal information. This data forms the foundation for creating a personalised "Future Self Avatar," a visual representation of the user's financial goals and the future they envision.

### 2. Analysing Previous Behaviour
Finedica delves into a user's past financial activities. By understanding historical spending patterns, saving habits, and investment choices, the app provides insights into current behaviours that may impact future financial success. This analysis is crucial for identifying areas for improvement and reinforcing positive habits.

### 3. Deciding Money Philosophy
This phase encourages users to define their core values and beliefs regarding money. Through guided exercises and psychometric tests, Finedica helps users articulate their personal "money philosophy," which serves as a guiding principle for all subsequent financial decisions and goal setting.

### 4. Specific Goal Setting and Implementation
With a clear understanding of their financial past and a defined money philosophy, users can then set concrete and achievable financial goals. Whether it's buying your first home, saving for retirement, or any other specific financial objective, Finedica assists in breaking down these goals into actionable steps and provides tools for tracking progress and facilitating implementation.

## Technologies Used

### Backend
- **PHP**: Main backend logic, authentication, and database interactions
- **Python**: Chatbot backend, data analysis, and machine learning components
  - PyTorch for chatbot model
  - Flask for web applications
  - Ollama for NLP
  - LangChain for AI integration

### Frontend
- **HTML5**: Structure and semantic markup
- **CSS3**: Styling and responsive design
- **JavaScript**: Interactive functionality and API communication

### Database
- **MySQL**: Primary database for user management and data storage
- **SQLite**: Used for specific components like expenditure tracking
- **Vector Database**: Chroma for chatbot knowledge base

### Cloud Services
- **Google Cloud Platform**: Deployment, authentication, and storage
- **Apache**: Web server
- **systemd/NSSM**: Process management for Python services

## Project Structure

```
finedica/
├── avatars/                    # User avatar images
├── chatbot/                    # Chatbot backend (Python)
│   ├── chatbot.py             # Main chatbot service (port 5002)
│   ├── chatbotquick.py        # Quick response chatbot (port 5003)
│   ├── requirements.txt       # Python dependencies
│   └── ...
├── css/                       # Stylesheets
├── data/                      # Data files for chatbot RAG
├── expenditure/               # Expenditure tracking application
│   ├── expenditure_static/    # Static assets
│   ├── expenditure_templates/ # HTML templates
│   └── ...
├── future_self/               # Future self feature (PHP)
├── generate_avatar/           # Avatar generation scripts
├── js/                        # JavaScript files
├── php/                       # Main PHP backend
│   ├── config.php            # Database configuration
│   ├── db_config.php         # Database connection
│   └── ...
├── psychometric_test/         # Psychometric testing feature
├── python/                    # Additional Python scripts
├── uploads/                   # User-uploaded files (must be writable)
|__ chatbot_mode_select.php    # Chatbot mode change configuration
├── requirements.txt           # Main Python dependencies
├── start_services.bat/.sh     # Service startup scripts
└── README.md                  # This file
```

## Database Schema

### Core Tables

#### Users Table
```sql
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
    last_name VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
    date_of_birth DATE NOT NULL,
    employment VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
    email VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL UNIQUE,
    gender VARCHAR(20) COLLATE utf8mb4_general_ci NOT NULL,
    password VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

#### Expenditure Table
```sql
   CREATE TABLE expenditure (
       id INT(11) NOT NULL AUTO_INCREMENT,
       user_id INT(11) NOT NULL,
       email VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
       salary DECIMAL(10,2) DEFAULT NULL,
       dividends DECIMAL(10,2) DEFAULT NULL,
       state_pension DECIMAL(10,2) DEFAULT NULL,
       pension DECIMAL(10,2) DEFAULT NULL,
       benefits DECIMAL(10,2) DEFAULT NULL,
       other_income DECIMAL(10,2) DEFAULT NULL,
       gas DECIMAL(10,2) DEFAULT NULL,
       electric DECIMAL(10,2) DEFAULT NULL,
       water DECIMAL(10,2) DEFAULT NULL,
       council_tax DECIMAL(10,2) DEFAULT NULL,
       phone DECIMAL(10,2) DEFAULT NULL,
       internet DECIMAL(10,2) DEFAULT NULL,
       mobile_phone DECIMAL(10,2) DEFAULT NULL,
       food DECIMAL(10,2) DEFAULT NULL,
       other_home DECIMAL(10,2) DEFAULT NULL,
       petrol DECIMAL(10,2) DEFAULT NULL,
       car_tax DECIMAL(10,2) DEFAULT NULL,
       car_insurance DECIMAL(10,2) DEFAULT NULL,
       maintenance DECIMAL(10,2) DEFAULT NULL,
       public_transport DECIMAL(10,2) DEFAULT NULL,
       other_travel DECIMAL(10,2) DEFAULT NULL,
       social DECIMAL(10,2) DEFAULT NULL,
       holidays DECIMAL(10,2) DEFAULT NULL,
       gym DECIMAL(10,2) DEFAULT NULL,
       clothing DECIMAL(10,2) DEFAULT NULL,
       other_misc DECIMAL(10,2) DEFAULT NULL,
       nursery DECIMAL(10,2) DEFAULT NULL,
       childcare DECIMAL(10,2) DEFAULT NULL,
       school_fees DECIMAL(10,2) DEFAULT NULL,
       uni_costs DECIMAL(10,2) DEFAULT NULL,
       child_maintenance DECIMAL(10,2) DEFAULT NULL,
       other_children DECIMAL(10,2) DEFAULT NULL,
       life DECIMAL(10,2) DEFAULT NULL,
       critical_illness DECIMAL(10,2) DEFAULT NULL,
       income_protection DECIMAL(10,2) DEFAULT NULL,
       buildings DECIMAL(10,2) DEFAULT NULL,
       contents DECIMAL(10,2) DEFAULT NULL,
       other_insurance DECIMAL(10,2) DEFAULT NULL,
       pension_ded DECIMAL(10,2) DEFAULT NULL,
       student_loan DECIMAL(10,2) DEFAULT NULL,
       childcare_ded DECIMAL(10,2) DEFAULT NULL,
       travel_ded DECIMAL(10,2) DEFAULT NULL,
       sharesave DECIMAL(10,2) DEFAULT NULL,
       other_deductions DECIMAL(10,2) DEFAULT NULL,
       created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (id),
       INDEX (user_id),
       INDEX (email)
   );
```
- #### `avatars`: Stores avatar image paths
   CREATE TABLE avatars (
       email VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
       image_path VARCHAR(512) COLLATE utf8mb4_general_ci NOT NULL,
       created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (email)
   );
  
- #### `psychometric_test_responses`: Psychometric test results
   CREATE TABLE psychometric_test_responses (
       id INT(11) NOT NULL AUTO_INCREMENT,
       email VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
       dominant_belief VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
       money_resentment INT(11) NOT NULL,
       financial_fantasists INT(11) NOT NULL,
       money_prestige INT(11) NOT NULL,
       money_anxiety INT(11) NOT NULL,
       responses TEXT COLLATE utf8mb4_general_ci NOT NULL,
       created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       PRIMARY KEY (id)
   );
  
- #### `future_self_responses`: Future self questionnaire data
   CREATE TABLE future_self_responses (
       id INT(11) NOT NULL AUTO_INCREMENT,
       email VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
       stage VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
       category VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
       question VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
       response TEXT COLLATE utf8mb4_general_ci NOT NULL,
       created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (id)
   );
  
- #### `face_image_responses`: Face image uploads for avatar generation
   CREATE TABLE face_image_responses (
       id INT(11) NOT NULL AUTO_INCREMENT,
       email VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
       face_image_url VARCHAR(500) COLLATE utf8mb4_general_ci NOT NULL,
       created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (id)
   );

## Installation and Deployment

### Prerequisites
- Apache web server
- PHP 7.4 or higher
- Python 3.8 or higher
- MySQL/MariaDB
- Git

### Local Development (XAMPP/WAMP)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/finedica.git
   cd finedica
   ```

2. **Install Python dependencies:**
   ```bash
   pip install -r requirements.txt
   pip install -r chatbot/requirements.txt
   ```

3. **Configure database:**
   - Edit `php/config.php` with your database credentials
   - Import the database schema using phpMyAdmin or MySQL command line

4. **Set permissions:**
   ```bash
   chmod 777 uploads/
   ```

5. **Start services:**
   - **Windows:** Run `start_services.bat`
   - **Linux/Mac:** Run `./start_services.sh`

### Google Cloud VM Deployment

#### 1. Prepare Your VM
```bash
sudo apt update
sudo apt upgrade -y
sudo apt install apache2 php libapache2-mod-php python3 python3-pip git mysql-server unzip -y
```

#### 2. Clone Repository
```bash
cd /var/www/
sudo git clone https://github.com/yourusername/finedica.git
sudo chown -R www-data:www-data finedica
```

#### 3. Configure Apache
Edit `/etc/apache2/sites-available/000-default.conf`:
```apache
DocumentRoot /var/www/finedica
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

#### 4. Set Up Database
```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE user_reg_db;
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'finedica';
FLUSH PRIVILEGES;
```

#### 5. Configure PHP
Edit `/var/www/finedica/php/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', 'finedica');
define('DB_NAME', 'user_reg_db');
define('DB_PORT', 3306);
```

#### 6. Install Python Dependencies
```bash
cd /var/www/finedica
pip3 install -r requirements.txt
pip3 install -r chatbot/requirements.txt
```

#### 7. Configure Firewall
Open ports 80 (HTTP) and 5002-5003 (chatbot services) in Google Cloud Console:
- Go to VPC network > Firewall
- Create rule allowing TCP:80,5002,5003

#### 8. Start Chatbot Services
```bash
cd /var/www/finedica/chatbot
python3 chatbot.py &      # Long-form responses (port 5002)
python3 chatbotquick.py & # Quick responses (port 5003)
```

For production, use systemd or supervisor to manage services.

### Running Both Chatbot Modes

To enable both quick and detailed chatbot responses, run both services simultaneously:

**Development:**
```bash
# Terminal 1
cd chatbot && python3 chatbot.py

# Terminal 2  
cd chatbot && python3 chatbotquick.py
```

**Production (systemd example):**
```ini
# /etc/systemd/system/finedica-chatbot.service
[Unit]
Description=Finedica Chatbot Service
After=network.target

[Service]
Type=simple
WorkingDirectory=/var/www/finedica
ExecStart=/usr/bin/python3 chatbot/chatbot.py
Restart=always
User=www-data

[Install]
WantedBy=multi-user.target
```

## Usage

1. **Access the application:** Navigate to your domain or server IP
2. **Register/Login:** Create an account or sign in
3. **Complete the four phases:**
   - Upload face image and answer initial questions
   - Review expenditure analysis
   - Complete psychometric test
   - Set specific financial goals
4. **Use the chatbot:** Get personalised financial advice in quick or detailed mode
5. **Track progress:** Monitor your financial journey through the dashboard

## API Endpoints

### Chatbot Services
- **Detailed Chat:** `POST http://your-server:5002/chat`
- **Quick Chat:** `POST http://your-server:5003/chat`

Example request:
```json
{
  "message": "How can I save for a house deposit?",
  "user_email": "user@example.com"
}
```

### File Uploads
- **Avatar Upload:** `POST /php/upload_face_image.php`
- **General Uploads:** `POST /php/upload.php`

## Configuration

### Environment Variables
- `DB_HOST`: Database host (default: localhost)
- `DB_USER`: Database username
- `DB_PASSWORD`: Database password
- `DB_NAME`: Database name

### Important Directories
- **uploads/**: Must be writable by web server
- **avatars/**: Stores generated avatar images
- **data/**: Contains chatbot knowledge base

## Troubleshooting

### Common Issues

**Database Connection Errors:**
- Verify credentials in `php/config.php`
- Ensure MySQL service is running
- Check firewall settings

**Chatbot Not Responding:**
- Verify Python services are running on ports 5002/5003
- Check firewall allows traffic on these ports
- Review chatbot logs for errors

**File Upload Failures:**
- Ensure `uploads/` directory has write permissions
- Check PHP upload limits in `php.ini`

**PHP Warnings Breaking JSON Responses:**
```php
// Add to config.php for production
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
```

### Updating from GitHub

```bash
cd /var/www/finedica
git pull origin main
pip3 install -r requirements.txt  # If dependencies changed
sudo systemctl restart apache2
sudo systemctl restart finedica-chatbot  # If using systemd
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Create a Pull Request

## Licence

This project is licensed under the MIT Licence - see the [LICENCE](LICENCE) file for details.

## Contact

For support or questions, please contact the development team or create an issue in this repository.

---

**Note:** This application handles sensitive financial data. Ensure proper security measures are implemented in production environments, including HTTPS, input validation, and secure database configurations.
