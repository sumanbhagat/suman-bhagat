# Personal Portfolio Website

A modern, responsive PHP portfolio website showcasing professional work, skills, and experiences.

## Features

### Pages
- **Homepage** - Introduction, photo, and highlights with call-to-action
- **About Me** - Background, education, work experience, and personal interests
- **Portfolio** - Projects showcase with filtering and project details
- **Blog** - Articles and knowledge sharing with categories
- **Gallery** - Photo and video gallery with lightbox functionality
- **Resume/CV** - Professional qualifications and downloadable PDF
- **Contact** - Contact form with validation, FAQ section, and social links

### Technical Features
- **Responsive Design** - Works perfectly on mobile, tablet, and desktop
- **Modern UI/UX** - Clean, professional design with smooth animations
- **Mobile Navigation** - Hamburger menu for mobile devices
- **Form Validation** - Client-side and server-side validation
- **Lightbox Gallery** - Click to view images in full size
- **Smooth Scrolling** - Enhanced navigation experience
- **Font Awesome Icons** - Professional iconography throughout

## File Structure

```
portfolio/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   └── main.js            # JavaScript functionality
│   ├── images/                # Image assets (add your own)
│   └── files/
│       └── resume.pdf         # Downloadable resume
├── includes/
│   ├── header.php             # Navigation header
│   └── footer.php             # Footer with social links
├── config.php                 # Site configuration
├── index.php                  # Homepage
├── about.php                  # About Me page
├── portfolio.php              # Portfolio/Work page
├── blog.php                   # Blog/Articles page
├── gallery.php                # Photo/Media Gallery
├── resume.php                 # Resume/CV page
├── contact.php                # Contact page with form
└── README.md                  # This file
```

## Setup Instructions

### Requirements
- PHP 7.0 or higher
- Web server (Apache, Nginx, or XAMPP)

### Installation

1. **Copy files to web server**
   ```
   Copy the portfolio folder to your web server directory
   For XAMPP: c:\xampp\htdocs\school\portfolio\
   ```

2. **Configure the site**
   - Open `config.php`
   - Update the site name, author name, email, and social media links
   - Update `SITE_URL` to match your server path

3. **Add your images**
   - Add your profile photo to `assets/images/profile.jpg`
   - Add project images to `assets/images/project1.jpg` through `project6.jpg`
   - Add blog images to `assets/images/blog1.jpg` through `blog6.jpg`
   - Add gallery images to `assets/images/gallery1.jpg` through `gallery12.jpg`
   - Add an about page image to `assets/images/about.jpg`

4. **Add your resume PDF**
   - Place your resume PDF in `assets/files/resume.pdf`

5. **Access the website**
   - Open your browser
   - Navigate to: `http://localhost/school/portfolio/`

## Customization

### Changing Colors
Edit the CSS variables in `assets/css/style.css`:
```css
:root {
    --primary-color: #6366f1;    /* Change to your preferred color */
    --secondary-color: #ec4899;   /* Change to your preferred color */
    --accent-color: #06b6d4;      /* Change to your preferred color */
}
```

### Updating Content
All content is stored in PHP variables for easy editing:
- Site info: `config.php`
- Resume data: `resume.php`
- Blog posts: `blog.php`
- Portfolio items: `portfolio.php`
- Gallery items: `gallery.php`

### Adding Database Support
To enable database features for blog and contact form:
1. Uncomment database configuration in `config.php`
2. Create a MySQL database
3. Update the contact form in `contact.php` to store submissions
4. Update blog to fetch from database instead of array

## Browser Compatibility
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Credits
- Font Awesome 6.4.0 for icons
- Google Fonts (system fonts used as fallback)

## License
This project is open source and free to use for personal or commercial projects.

## Support
For issues or questions, please contact through the website's contact form.
