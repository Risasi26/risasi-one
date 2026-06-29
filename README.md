# risasi_one

A lightweight PHP + MySQL tenant utility notification system for admin use.

## Features

- Admin login and protected dashboard
- Tenant management (add, edit, delete)
- Electricity rotation reminders with simulated messaging
- Monthly water bill generation and message logging
- Announcements to all active tenants
- Message logs with WhatsApp links and SMS/USSD simulation
- Responsive modern UI with neomorphism, glassmorphism, and soft card design

## Requirements

- PHP 8+
- MySQL
- Laragon / XAMPP / similar local PHP server

## Setup Instructions

1. Place the `risasi_one` folder inside your web server root folder.
   - For Laragon: `D:\laragon\www\risasi_one`
   - For XAMPP: `C:\xampp\htdocs\risasi_one`

2. Import the database:
   - Open phpMyAdmin or MySQL CLI
   - Run the SQL file at `database/risasi_one.sql`
   - This creates the `risasi_one` database and seeds a default admin and sample tenants.

3. Update database connection if needed:
   - Edit `config/database.php`
   - Set your DB host, username, password, and database name

4. Open the app in your browser:
   - `http://localhost/risasi_one`

5. Login with default admin:
   - Email: `admin@risasi.one`
   - Password: `admin123`

## Notes

- The system uses PHP PDO and prepared statements for security.
- All tenant notifications are simulated. WhatsApp links are generated but not sent.
- The app is admin-only and does not include tenant login.
