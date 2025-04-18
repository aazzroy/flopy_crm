# Troubleshooting Guide

This guide provides solutions for common issues you might encounter when using Flopy CRM.

## Installation Issues

### 404 Not Found Error

**Symptoms**: You get a 404 error when accessing the application or certain pages.

**Solutions**:
1. Check that mod_rewrite is enabled in Apache
2. Verify that .htaccess files are present in both the root directory and the public directory
3. Check that AllowOverride is set to All in your Apache configuration
4. Make sure your controller and method exist and are spelled correctly

### Database Connection Error

**Symptoms**: You see an error like "Connection Error: SQLSTATE[HY000] [1045] Access denied for user..."

**Solutions**:
1. Verify database credentials in `config/database.php`
2. Check that the MySQL server is running
3. Ensure the database exists
4. Make sure the MySQL user has proper permissions

### Permission Denied Error

**Symptoms**: You see "Permission denied" errors when uploading files or accessing certain directories.

**Solutions**:
1. Check file permissions on the uploads directory
2. Ensure the web server has write permissions to required directories
3. Check that the owner of the files is correct

## Application Issues

### Login Problems

**Symptoms**: Unable to log in, even with correct credentials.

**Solutions**:
1. Reset your password
2. Clear browser cookies and cache
3. Check that your account is active
4. Verify that the session configuration is correct

### Missing Contacts or Deals

**Symptoms**: Contacts or deals you created are not visible.

**Solutions**:
1. Check if you're filtering the list by any criteria
2. Verify you have the correct permissions to view the data
3. Check that the data was actually saved to the database

### Email Sending Failures

**Symptoms**: The system fails to send emails.

**Solutions**:
1. Check the SMTP configuration in settings
2. Verify your SMTP server is running
3. Check that your email credentials are valid
4. Test your SMTP server with a simple script

## Performance Issues

### Slow Page Loading

**Symptoms**: Pages take a long time to load.

**Solutions**:
1. Enable caching
2. Optimize database queries
3. Check for slow queries in the MySQL slow query log
4. Ensure your server has sufficient resources
5. Consider adding database indexes for frequently queried columns

### Memory Limit Errors

**Symptoms**: You see "Allowed memory size exhausted" errors.

**Solutions**:
1. Increase PHP memory limit in php.ini
2. Optimize memory-intensive operations
3. Implement pagination for large datasets

## API Issues

### Authentication Failures

**Symptoms**: API requests return 401 Unauthorized errors.

**Solutions**:
1. Check that your API token is valid and not expired
2. Ensure you're including the token in the request header
3. Verify that your account has API access permissions

### Rate Limiting Issues

**Symptoms**: API requests return 429 Too Many Requests errors.

**Solutions**:
1. Reduce the frequency of your API requests
2. Implement caching on your client side
3. Contact admin for increased rate limits if needed

## Still Having Issues?

If you're still experiencing problems after trying the solutions above:

1. Check the PHP error logs for more details
2. Enable detailed error reporting temporarily
3. Contact support at admin@flopy.com
4. Open an issue on the [GitHub repository](https://github.com/aazzroy/flopy_crm/issues)
