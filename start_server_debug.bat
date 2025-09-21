@echo off
echo ========================================
echo Service Tracker - Server Debug Startup
echo ========================================
echo.

echo Checking PHP installation...
php --version
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP or add it to your system PATH
    echo Download PHP from: https://windows.php.net/download/
    pause
    exit /b 1
)

echo.
echo PHP found! Starting server...
echo.
echo Project directory: %~dp0
echo Server URL: http://localhost:8000
echo.
echo IMPORTANT: Keep this window open while using the application
echo Press Ctrl+C to stop the server
echo.

cd /d "%~dp0"
echo Starting PHP development server...
php -S localhost:8000 -t .
echo.
echo Server stopped.
pause
