# Vercel Deployment Guide

This guide will help you deploy your PHP portfolio website to Vercel with frontend/backend separation.

## Prerequisites

1. **Vercel Account**: Sign up at [vercel.com](https://vercel.com)
2. **GitHub Account**: Connect your repository to Vercel
3. **Database**: External database service (PlanetScale, Supabase, etc.)
4. **Domain** (optional): Custom domain for your portfolio

## Setup Instructions

### 1. Database Setup

Since Vercel is serverless, you need an external database:

#### Option A: PlanetScale (Recommended)
```bash
# Install PlanetScale CLI
brew install planetscale/tap/pscale

# Create database
pscale database create portfolio

# Get connection string
pscale connection-string portfolio main
```

#### Option B: Supabase
1. Create account at [supabase.com](https://supabase.com)
2. Create new project
3. Get connection string from Settings > Database

#### Option C: MongoDB Atlas
1. Create account at [mongodb.com/atlas](https://mongodb.com/atlas)
2. Create free cluster
3. Get connection string

### 2. Environment Variables

In Vercel dashboard, set these environment variables:

```bash
DATABASE_URL=mysql://user:pass@host:port/database
SITE_NAME=My Portfolio
SITE_URL=https://your-app.vercel.app
AUTHOR_NAME=Your Name
AUTHOR_EMAIL=your@email.com
```

### 3. Deploy to Vercel

#### Method A: GitHub Integration (Recommended)
1. Push your code to GitHub
2. Import project in Vercel dashboard
3. Connect your GitHub repository
4. Configure environment variables
5. Deploy!

#### Method B: Vercel CLI
```bash
# Install Vercel CLI
npm i -g vercel

# Login to Vercel
vercel login

# Deploy project
vercel

# Production deployment
vercel --prod
```

### 4. Database Migration

Run these SQL commands on your external database:

```sql
-- Create tables (use the SQL files from backend/admin/database/)
-- Import: backend/admin/database/schema.sql
-- Import: backend/admin/database/about_resume_schema.sql
```

### 5. Email Configuration (Optional)

For contact forms to work:

1. **Resend** (Recommended):
   - Sign up at [resend.com](https://resend.com)
   - Get API key
   - Set `RESEND_API_KEY` environment variable
   - Set `ADMIN_EMAIL` to your email

2. **SendGrid**:
   - Sign up at [sendgrid.com](https://sendgrid.com)
   - Get API key
   - Configure in API files

## File Structure After Deployment

```
your-app.vercel.app/
├── frontend/           # Static files (served directly)
│   ├── index.php
│   ├── about.php
│   ├── assets/
│   └── includes/
├── api/               # Serverless functions
│   ├── database.php
│   ├── contact.php
│   ├── blog.php
│   ├── portfolio.php
│   └── admin/
└── uploads/           # File uploads (if enabled)
```

## API Endpoints

Your backend functionality is now available via API:

- `GET /api/database/site-settings` - Get site settings
- `GET /api/database/hero-slides` - Get hero slides
- `POST /api/contact` - Submit contact form
- `GET /api/blog` - Get blog posts
- `GET /api/portfolio` - Get portfolio projects
- `/admin/*` - Admin panel (routes to backend)

## Custom Domain

1. In Vercel dashboard, go to Project Settings > Domains
2. Add your custom domain
3. Configure DNS records as instructed
4. Update `SITE_URL` environment variable

## Performance Optimization

### Enable Edge Functions
```json
// In vercel.json
{
  "functions": {
    "api/**/*.php": {
      "runtime": "php",
      "regions": ["iad1"]
    }
  }
}
```

### Static Asset Optimization
- Images are automatically optimized by Vercel
- CSS/JS are minified during build
- Enable caching headers in .htaccess

### Database Optimization
- Use connection pooling
- Enable query caching
- Optimize indexes

## Monitoring

### Vercel Analytics
1. Enable in Project Settings > Analytics
2. Track page views and performance

### Error Tracking
- Check Vercel Function Logs
- Monitor API response times
- Set up alerting for errors

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check `DATABASE_URL` format
   - Verify database credentials
   - Ensure database allows external connections

2. **API Returns 500 Error**
   - Check Function Logs in Vercel dashboard
   - Verify PHP syntax with `npm run validate-php`
   - Check environment variables

3. **Static Assets Not Loading**
   - Verify paths in CSS/JS files
   - Check .htasset routing in vercel.json
   - Clear browser cache

4. **Contact Form Not Working**
   - Verify email service API keys
   - Check email configuration
   - Review spam filters

### Debug Mode

Enable debugging by setting:
```bash
DEBUG=true
NODE_ENV=development
```

## Security Considerations

1. **Environment Variables**: Never commit sensitive data
2. **Database**: Use SSL connections
3. **API**: Implement rate limiting
4. **Uploads**: Validate file types and sizes
5. **Admin**: Add authentication middleware

## Scaling

### When to Scale
- High traffic volumes
- Multiple regions needed
- Complex database operations

### Scaling Options
1. **Vercel Pro**: Higher limits and analytics
2. **Database Upgrades**: Larger plans for PlanetScale/Supabase
3. **CDN**: Additional CDN layers for static assets
4. **Load Balancing**: Multiple function regions

## Maintenance

### Regular Tasks
- Update dependencies
- Monitor database performance
- Review logs for errors
- Backup database regularly
- Update SSL certificates

### Updates
```bash
# Update dependencies
npm update
composer update

# Redeploy
vercel --prod
```

## Support

- **Vercel Docs**: [vercel.com/docs](https://vercel.com/docs)
- **PHP on Vercel**: [vercel.com/docs/concepts/functions/serverless-functions](https://vercel.com/docs/concepts/functions/serverless-functions)
- **Database Docs**: PlanetScale/Supabase documentation
- **Community**: Vercel Discord community
