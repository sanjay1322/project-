@echo off
echo Starting Service Tracker Development Server...
echo.
echo Open your browser and go to: http://localhost:8000
echo Press Ctrl+C to stop the server
echo.
cd /d "%~dp0"
php -S localhost:8000
pause
