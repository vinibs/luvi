<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

define('SYSROOT', '/'); // Default: '/'
// Complete path to application root:
define('BASEPATH', realpath($_SERVER["DOCUMENT_ROOT"]).SYSROOT);
define('ENVIRONMENT', 'dev'); // 'dev' or 'production'
define('REDIR_HTTPS', false); // Define if HTTPS or not (default: false)

// Session settings
define('SESSION_NAME', 'LuviSession'); // Name of session cookie
define('SESSION_HTTP_ONLY', 1); // HTTP_ONLY property of session cookies
// Set the time of inactivity before the user is logged out - 0 for disable:
define('SESSION_ACTIVITY_TIME', 2);

// Database settings - Uses PDO
define('DB_DRIVER', 'mysql'); // Options: mysql, pgsql, sqlite
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'teste');


// Email settings
define('MAIL_FROM_NAME', 'Fulano'); // Shortcut to the name to be shown as 'from'

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', '465');
define('SMTP_USER', 'Username');
define('SMTP_PASS', 'Password');
define('SMTP_SECURE', 'ssl');
define('SMTP_AUTH', TRUE);
