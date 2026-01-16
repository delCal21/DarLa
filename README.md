# DarLa Application

This is the DarLa application deployed on Vercel with PHP support.

## Deployment to Vercel

1. Make sure you have the `vercel-php` runtime configured in your `vercel.json`
2. Set up your environment variables in Vercel dashboard:
   - DB_HOST: Your database host
   - DB_NAME: Your database name
   - DB_USER: Your database username
   - DB_PASS: Your database password
3. Push your code to a Git repository connected to Vercel

## Important Notes

- This application uses PHP and requires the `vercel-php` runtime
- Database connections use environment variables for security
- All routes are handled through the API endpoint at `/api/index.php`
- PHP version is set to 8.1 in composer.json

## Routes

The application handles the following routes:
- `/` - Main page
- `/activity_log` - Activity log page
- `/employees` - Employee management
- `/admin_profile` - Admin profile page
- `/health` - Health check endpoint
- And other PHP pages as defined in the router