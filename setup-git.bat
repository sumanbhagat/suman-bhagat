@echo off
echo Setting up Git repository for Vercel deployment...
echo.

REM Check if Git is installed
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Git is not installed! Please install Git first:
    echo 1. Download from: https://git-scm.com/download/win
    echo 2. Run installer with default settings
    echo 3. Restart this script after installation
    pause
    exit /b 1
)

echo Git found! Initializing repository...
echo.

REM Initialize Git repository
git init
if %errorlevel% neq 0 (
    echo Failed to initialize Git repository
    pause
    exit /b 1
)

echo Git repository initialized successfully!
echo.

REM Configure Git (if not already configured)
git config --global user.name >nul 2>&1
if %errorlevel% neq 0 (
    echo Please configure Git with your name and email:
    set /p git_name="Enter your name: "
    set /p git_email="Enter your email: "
    git config --global user.name "%git_name%"
    git config --global user.email "%git_email%"
    echo Git configured!
    echo.
)

REM Add all files
echo Adding files to Git...
git add .
if %errorlevel% neq 0 (
    echo Failed to add files to Git
    pause
    exit /b 1
)

echo Files added successfully!
echo.

REM Create initial commit
echo Creating initial commit...
git commit -m "Initial commit: Portfolio website with Vercel setup"
if %errorlevel% neq 0 (
    echo Failed to create commit
    pause
    exit /b 1
)

echo Initial commit created successfully!
echo.

echo ==========================================
echo Git setup completed!
echo ==========================================
echo.
echo Next steps:
echo 1. Create a repository on GitHub.com
echo 2. Run these commands (replace YOUR_USERNAME):
echo    git remote add origin https://github.com/YOUR_USERNAME/portfolio-website.git
echo    git push -u origin main
echo.
echo 3. Import your repository to Vercel
echo.
echo For detailed instructions, see GIT_SETUP.md
echo.
pause
