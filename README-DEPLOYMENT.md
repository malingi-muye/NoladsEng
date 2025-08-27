# Nolads Engineering - PHP cPanel Deployment

## Installation Instructions

1. **Database Setup:**
   - Create a new MySQL database in cPanel
   - Import the SQL file: `public_html/api/database/production-init.mysql.sql`
   - Note your database credentials

2. **File Upload:**
   - Upload all files to your cPanel public_html directory
   - Or upload to a subdirectory if installing in a subfolder

3. **Environment Configuration:**
   - Copy or rename your environment file as needed (e.g., .env.example to .env)
   - Update the database credentials in your config

4. **File Permissions:**
   - Set proper permissions for upload directories (e.g., uploads/)
   - Ensure the database file (if using SQLite) is writable

## Important Notes

- Change default admin password after first login
- Update all secret keys in production
- Configure HTTPS for security
- Set up regular database backups

## Support

For technical support, contact: admin@noladseng.com

## Database Schema

See `public_html/api/database/production-init.mysql.sql` for the complete schema.

## Default Admin Login

- Email: admin@noladseng.com  
- Password: admin123 (CHANGE THIS IMMEDIATELY!)
