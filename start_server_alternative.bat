@echo off
echo Starting Service Tracker Server...
echo.

cd /d "%~dp0"

echo Trying port 8000...
php -S localhost:8000 -t . 2>nul
if %errorlevel% neq 0 (
    echo Port 8000 failed, trying port 8001...
    php -S localhost:8001 -t . 2>nul
    if %errorlevel% neq 0 (
        echo Port 8001 failed, trying port 3000...
        php -S localhost:3000 -t . 2>nul
        if %errorlevel% neq 0 (
            echo.
            echo ERROR: Could not start PHP server
            echo.
            echo Possible solutions:
            echo 1. Install PHP: https://windows.php.net/download/
            echo 2. Add PHP to system PATH
            echo 3. Use XAMPP instead: https://www.apachefriends.org/
            echo.
            pause
            exit /b 1
        ) else (
            echo Server started on http://localhost:3000
            echo Open this URL in your browser
            start http://localhost:3000
        )
    ) else (
        echo Server started on http://localhost:8001
        echo Open this URL in your browser
        start http://localhost:8001
    )
) else (
    echo Server started on http://localhost:8000
    echo Open this URL in your browser
    start http://localhost:8000
)

echo.
echo Keep this window open while using the application
pause
