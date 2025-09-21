# Service Tracker - Server Setup Guide

## The Problem
If you're seeing file download prompts or broken navigation, it's because you're accessing PHP files directly through the file system (file:// protocol) instead of through a web server.

## Solution: Use PHP Development Server

### Method 1: Use the Batch File (Easiest)
1. Double-click `start_server.bat` in your project folder
2. Wait for the server to start
3. Open your browser and go to: `http://localhost:8000`
4. Click Login or Register - everything will work properly!

### Method 2: Manual Command Line
1. Open Command Prompt or PowerShell
2. Navigate to your project folder:
   ```
   cd "d:\Users\ADMIN\Downloads\htdocs\service_tracker"
   ```
3. Start the PHP server:
   ```
   php -S localhost:8000
   ```
4. Open browser: `http://localhost:8000`

## Important Notes

### ✅ CORRECT Way to Access:
- `http://localhost:8000` (through web server)
- `http://localhost:8000/login.php`
- `http://localhost:8000/register.php`

### ❌ WRONG Way to Access:
- `file:///d:/Users/ADMIN/Downloads/htdocs/service_tracker/index.html`
- Double-clicking HTML files directly
- Opening files through file explorer

## Quick Test Credentials
- **Admin**: admin@college.edu / admin123
- **Library**: library@college.edu / library123
- **Hostel**: hostel@college.edu / hostel123

## Troubleshooting
- If port 8000 is busy, try: `php -S localhost:8001`
- Make sure PHP is installed and in your system PATH
- Always access through `http://localhost:PORT` not `file://`

## For Demonstrations
1. Run `start_server.bat`
2. Open `http://localhost:8000` in browser
3. Show the complete login/register flow
4. All backend functionality will work perfectly!
