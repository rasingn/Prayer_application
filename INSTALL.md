# Prayer Group Management Website - Installation Instructions

This document provides instructions for installing and setting up the Prayer Group Management Website on a shared web hosting environment.

## Requirements

- PHP 7.4 or higher with SQLite3 support
- Web server (Apache, Nginx, etc.)
- Write permissions for the web server user on the `db` directory

## Installation Steps

1. **Upload Files**
   - Upload all files and directories to your web hosting environment
   - Ensure you maintain the directory structure as provided

2. **Set Permissions**
   - Make sure the `db` directory is writable by the web server
   - Run the following command if you have SSH access:
     ```
     chmod 755 db
     chmod 644 db/schema.sql
     chmod 644 db/init_db.php
     ```

3. **Initialize Database**
   - Navigate to `http://your-domain.com/db/init_db.php` in your browser
   - You should see a message indicating the database was initialized successfully
   - If you encounter any errors, check that PHP has SQLite3 support enabled and the `db` directory is writable

4. **Access the Website**
   - Navigate to `http://your-domain.com` in your browser
   - You should see the login page
   - Use the default admin credentials to log in:
     - Username: `admin`
     - Password: `admin123`
   - **Important**: Change the admin password immediately after first login

5. **Configuration (Optional)**
   - The application uses relative paths and should work without additional configuration
   - If you need to place the application in a subdirectory, all links should continue to work correctly

## Directory Structure

- `/css` - Contains stylesheets
- `/js` - Contains JavaScript files
- `/includes` - Contains PHP class files and utility functions
- `/db` - Contains database files and initialization scripts
- `/assets` - Contains images and other static assets

## Security Considerations

- Change the default admin password immediately after installation
- Consider implementing HTTPS if your hosting provider supports it
- Regularly backup the SQLite database file located in the `db` directory

## Troubleshooting

- If you encounter a "Database connection error", check that the `db` directory is writable
- If you see PHP errors, ensure your hosting environment meets the requirements
- For any other issues, check the PHP error logs on your server

## Support

For any questions or issues, please contact the developer.
