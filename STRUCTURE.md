# Project Structure

This portfolio website has been reorganized into a clear frontend/backend separation.

## Directory Structure

```
suman portfolio/
├── frontend/           # Frontend application files
│   ├── index.php       # Main homepage
│   ├── about.php       # About page
│   ├── blog.php        # Blog page
│   ├── contact.php     # Contact page
│   ├── gallery.php     # Gallery page
│   ├── portfolio.php   # Portfolio page
│   ├── resume.php      # Resume page
│   ├── assets/         # CSS, JS, images
│   └── includes/       # Frontend includes (header, footer, etc.)
├── backend/            # Backend application files
│   ├── admin/          # Admin panel
│   │   ├── dashboard.php
│   │   ├── settings.php
│   │   ├── database/   # Database connection and files
│   │   └── includes/   # Backend includes and functions
│   └── config.php      # Configuration file
├── uploads/            # User uploaded files
├── .htaccess          # Apache configuration with routing rules
└── index.php          # Root redirect to frontend
```

## Access Points

- **Frontend**: Access pages directly or via clean URLs
  - Homepage: `/` or `/frontend/index.php`
  - About: `/about` or `/frontend/about.php`
  - Admin: `/admin` → Routes to `/backend/admin/`

## Key Changes

1. **Frontend/Backend Separation**: Clear separation of concerns
2. **Updated Paths**: All includes and requires updated to use relative paths
3. **Routing**: `.htaccess` configured to route requests appropriately
4. **Security**: Admin folders protected with rewrite rules

## Development Notes

- Frontend files access backend via `../backend/` relative paths
- Admin panel accessible via `/admin` URL (routes to backend)
- Static assets remain in frontend/assets/
- Database connections centralized in backend/admin/database/
