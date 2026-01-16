# DarLa Application

This is the DarLa application deployed on Vercel with PHP support using the community runtime.

## Deployment to Vercel

1. Make sure you have the `vercel-php@0.9.0` runtime configured in your `vercel.json`
2. Set up your environment variables in Vercel dashboard:
   - DB_HOST: Your database host
   - DB_NAME: Your database name
   - DB_USER: Your database username
   - DB_PASS: Your database password
3. Push your code to a Git repository connected to Vercel

## Important Notes

- This application uses PHP through the community `vercel-php` runtime
- Database connections use environment variables for security
- All routes are handled through the API endpoint at `/api/index.php`
- PHP version 8.5.x is used (supported by vercel-php@0.9.0)
- The application works with popular PHP frameworks and includes common extensions

## Routes

The application handles the following routes:
- `/` - Main page
- `/activity_log` - Activity log page
- `/employees` - Employee management
- `/admin_profile` - Admin profile page
- `/health` - Health check endpoint
- And other PHP pages as defined in the router