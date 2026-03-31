# Git Setup Guide for Vercel Deployment

This guide will help you set up Git and push your project to GitHub for Vercel deployment.

## Prerequisites

### 1. Install Git

#### Windows:
1. Download Git from [git-scm.com](https://git-scm.com/download/win)
2. Run the installer with default settings
3. Restart your terminal/command prompt

#### macOS:
```bash
# Install via Homebrew
brew install git

# Or download from git-scm.com
```

#### Linux:
```bash
# Ubuntu/Debian
sudo apt update && sudo apt install git

# CentOS/RHEL
sudo yum install git
```

### 2. Configure Git
```bash
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

## Step-by-Step Git Setup

### Step 1: Initialize Repository
Open terminal/command prompt and navigate to your project folder:
```bash
cd "c:/xampp/htdocs/suman portfolio"
git init
```

### Step 2: Add Files to Git
```bash
# Add all files
git add .

# Or add specific files
git add .gitignore vercel.json package.json composer.json
git add frontend/ api/ backend/
git add *.md *.php
```

### Step 3: Create Initial Commit
```bash
git commit -m "Initial commit: Portfolio website with Vercel setup"
```

### Step 4: Create GitHub Repository

#### Option A: Via GitHub Website
1. Go to [github.com](https://github.com)
2. Click "+" → "New repository"
3. Repository name: `portfolio-website`
4. Description: `Professional portfolio website with PHP/MySQL`
5. Make it **Public** (required for Vercel free tier)
6. **DO NOT** initialize with README (we already have files)
7. Click "Create repository"

#### Option B: Via GitHub CLI
```bash
# Install GitHub CLI first
# Windows: winget install GitHub.cli
# macOS: brew install gh

# Login to GitHub
gh auth login

# Create repository
gh repo create portfolio-website --public --source=. --remote=origin --push
```

### Step 5: Connect to GitHub
```bash
# Add remote repository (replace YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/portfolio-website.git

# Push to GitHub
git push -u origin main
```

## Complete Git Commands Summary

```bash
# Navigate to project
cd "c:/xampp/htdocs/suman portfolio"

# Initialize Git
git init

# Configure Git (first time only)
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"

# Add files
git add .

# Commit changes
git commit -m "Initial commit: Portfolio website ready for Vercel deployment"

# Add remote (replace YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/portfolio-website.git

# Push to GitHub
git push -u origin main
```

## Vercel Connection

### Method 1: GitHub Integration (Recommended)

1. **Import to Vercel**:
   - Go to [vercel.com](https://vercel.com)
   - Click "New Project"
   - Import your GitHub repository
   - Vercel will auto-detect it's a PHP project

2. **Configure Settings**:
   - Framework Preset: PHP
   - Root Directory: `./`
   - Build Command: `npm run build`
   - Output Directory: `frontend`

3. **Environment Variables**:
   ```
   DATABASE_URL=your_database_connection_string
   SITE_NAME=My Portfolio
   SITE_URL=https://your-app.vercel.app
   AUTHOR_NAME=Your Name
   AUTHOR_EMAIL=your@email.com
   ```

4. **Deploy**:
   - Click "Deploy"
   - Your site will be live at `your-app.vercel.app`

### Method 2: Vercel CLI

```bash
# Install Vercel CLI
npm i -g vercel

# Login
vercel login

# Deploy from project directory
cd "c:/xampp/htdocs/suman portfolio"
vercel

# Follow prompts to link to GitHub
```

## Common Git Commands

### Daily Workflow
```bash
# Check status
git status

# Add changes
git add .

# Commit changes
git commit -m "Updated portfolio content"

# Push to GitHub
git push
```

### Branch Management
```bash
# Create new branch
git checkout -b feature/new-page

# Switch branches
git checkout main

# Merge branch
git merge feature/new-page

# Delete branch
git branch -d feature/new-page
```

### Undo Changes
```bash
# Discard local changes
git checkout -- filename.php

# Reset to last commit
git reset --hard HEAD

# Remove file from Git
git rm filename.php
git commit -m "Remove filename.php"
```

## Troubleshooting

### Git Not Found
```bash
# Windows: Check if Git is in PATH
where git

# If not found, reinstall Git or add to PATH manually
```

### Permission Denied
```bash
# GitHub authentication issues
git config --global credential.helper store

# Or use SSH keys instead of HTTPS
ssh-keygen -t rsa -b 4096 -C "your.email@example.com"
```

### Push Rejected
```bash
# Force push (use carefully)
git push -f origin main

# Or pull first
git pull origin main --rebase
git push origin main
```

### Large Files Error
```bash
# Install Git LFS for large files
git lfs install
git lfs track "*.zip"
git lfs track "*.pdf"
git add .gitattributes
git commit -m "Track large files with LFS"
```

## Next Steps After Git Setup

1. **Verify GitHub Repository**: Check that all files are uploaded correctly
2. **Test Local Build**: Run `npm run build` to ensure no errors
3. **Deploy to Vercel**: Follow Vercel connection steps above
4. **Configure Domain**: Set up custom domain if desired
5. **Monitor Deployment**: Check Vercel dashboard for any issues

## Best Practices

- **Commit Often**: Make small, frequent commits with clear messages
- **Use Branches**: Create branches for new features
- **Pull Before Push**: Always pull latest changes before pushing
- **Review Changes**: Check `git status` and `git diff` before committing
- **Backup**: Keep important database backups separate from Git

Your portfolio is now ready for Git version control and Vercel deployment!
