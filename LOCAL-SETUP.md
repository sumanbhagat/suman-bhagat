# Local Setup Guide

Your portfolio has been restored to the original PHP structure for local development.

## 🚀 Quick Start

### 1. Start XAMPP/WAMP
1. Open XAMPP Control Panel
2. Start Apache and MySQL services
3. Make sure both are running (green status)

### 2. Access Your Portfolio
- **Main Site**: `http://localhost/suman%20portfolio/`
- **Admin Panel**: `http://localhost/suman%20portfolio/admin/`

### 3. Database Setup (First Time Only)
1. Go to: `http://localhost/phpmyadmin`
2. Create database: `portfolio`
3. Import SQL files from `admin/database/`:
   - `schema.sql`
   - `about_resume_schema.sql`

## 📁 Current Structure

```
suman portfolio/
├── index.php              # Homepage
├── about.php               # About page
├── blog.php                # Blog page
├── contact.php             # Contact page
├── gallery.php             # Gallery page
├── portfolio.php           # Portfolio page
├── resume.php              # Resume page
├── config.php              # Configuration
├── admin/                  # Admin panel
│   ├── dashboard.php
│   ├── login.php
│   ├── settings.php
│   ├── database/
│   └── includes/
├── includes/               # Shared files
│   ├── header.php
│   ├── footer.php
│   ├── settings.php
│   └── router.php
├── assets/                 # Static files
│   ├── css/
│   ├── js/
│   └── images/
└── uploads/                # User uploads
```

## 🔧 Admin Panel Access

### Default Login
- **URL**: `http://localhost/suman%20portfolio/admin/login.php`
- **Username**: Check database `users` table
- **Password**: Check database `users` table

### Admin Features
- ✅ Dashboard with statistics
- ✅ Manage hero slides
- ✅ Blog management
- ✅ Portfolio projects
- ✅ Gallery images
- ✅ Resume content
- ✅ Site settings
- ✅ Contact messages
- ✅ User management

## 🌐 Site Features

### Frontend
- ✅ Dynamic hero slider
- ✅ About section with skills
- ✅ Portfolio showcase
- ✅ Blog with categories
- ✅ Photo gallery
- ✅ Resume display
- ✅ Contact form
- ✅ Mobile responsive

### Backend
- ✅ Full CRUD operations
- ✅ Database management
- ✅ File uploads
- ✅ Security features
- ✅ User authentication
- ✅ Content management

## 📝 Configuration

### Database Settings
Edit `config.php`:
```php
// Database connection
$db_host = 'localhost';
$db_name = 'portfolio';
$db_user = 'root';
$db_pass = '';
```

### Site Settings
Edit via admin panel: `/admin/settings.php`

## 🛠️ Troubleshooting

### Common Issues

#### 1. White Screen/500 Error
- Check XAMPP services are running
- Enable PHP error display in `php.ini`
- Check Apache error logs

#### 2. Database Connection Failed
- Verify MySQL is running
- Check database credentials in `config.php`
- Ensure database `portfolio` exists

#### 3. Admin Panel Not Loading
- Check `.htaccess` file
- Verify mod_rewrite is enabled
- Check file permissions

#### 4. Images Not Loading
- Check `assets/` folder permissions
- Verify image paths in CSS
- Check image file existence

### Debug Mode
Add to `config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 🚀 Development Workflow

### Making Changes
1. Edit PHP files in your code editor
2. Refresh browser to see changes
3. Check admin panel for content updates

### Adding New Features
1. Create database tables in phpMyAdmin
2. Add PHP logic in appropriate files
3. Update admin panel if needed
4. Test thoroughly

### File Uploads
- Uploads go to `uploads/` directory
- Check folder permissions (755)
- Monitor file size limits

## 📱 Mobile Testing

### Local Mobile Testing
1. Find your computer's IP address:
   ```bash
   ipconfig
   ```
2. Access from mobile: `http://YOUR_IP/suman%20portfolio/`
3. Ensure mobile devices are on same network

## 🔒 Security

### Local Development
- Use strong admin passwords
- Keep XAMPP updated
- Backup database regularly

### Production Considerations
- Change default passwords
- Enable HTTPS
- Use environment variables
- Regular security updates

## 📊 Performance

### Optimization Tips
- Optimize images in `assets/images/`
- Enable gzip compression
- Use browser caching
- Minify CSS/JS files

### Monitoring
- Check Apache access logs
- Monitor database queries
- Profile slow pages

## 🎯 Next Steps

1. **Set up database** with SQL imports
2. **Configure admin** credentials
3. **Customize content** via admin panel
4. **Test all features** locally
5. **Deploy to production** when ready

## 📞 Support

If you encounter issues:
1. Check XAMPP error logs
2. Verify database connection
3. Test with simple PHP file
4. Check file permissions

Your portfolio is now fully functional locally! 🎉
