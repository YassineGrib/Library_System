# Digital Library System

A comprehensive digital library management system that allows users to browse and download e-books, with an integrated administration system.

## Key Features

- **Multilingual User Interface**: Support for Arabic, English, and French
- **Registration and Login System**: With admin approval for new users
- **Book Management**: Add, view, download, and delete books
- **Subscription System**: With subscription end date and upload limits
- **Admin Dashboard**: For managing users, books, and categories
- **Responsive Interface**: Works on all devices (desktop, mobile, tablet)
- **Modern Design**: Using Bootstrap 5 and Font Awesome
- **Advanced Security**: Protection against SQL Injection and XSS attacks

## Technical Requirements

- PHP 7.4 or newer
- MySQL 5.7 or newer
- Web server (Apache/Nginx)
- XAMPP (for local development)

## Installation

1. Download or clone the project to the `htdocs` folder in XAMPP:
   ```
   git clone https://github.com/yourusername/library-system.git
   ```

2. Create a new database named `library_system`

3. Modify the configuration file in `config/xampp.php` to specify database connection information:
   ```php
   return [
       'db' => [
           'host' => 'localhost',
           'name' => 'library_system',
           'user' => 'root',
           'pass' => ''
       ],
       'app' => [
           'base_url' => '/Library_System/'
       ]
   ];
   ```

4. Visit `http://localhost/Library_System/setup.php` to set up the database and create an admin account

5. Visit `http://localhost/Library_System/update_db.php` to update the database structure (if you are upgrading from a previous version)

6. Log in using the admin account:
   - Email: `admin@library.local`
   - Password: `Admin123`

## Project Structure

```
Library_System/
├── admin/                  # Admin dashboard pages
├── config/                 # Configuration files
├── core/                   # Core classes (Database, Auth, etc.)
├── lang/                   # Translation files
├── public/                 # Public files
│   ├── assets/             # CSS, JS, images
│   └── uploads/            # Uploaded files (books, receipts)
├── views/                  # View templates
│   └── layout/             # Shared layout templates
├── .htaccess               # Redirect settings
├── index.php               # Main entry point
├── setup.php               # Setup script
└── update_db.php           # Database update script
```

## User Guide

### For Users

1. **Registration**: Create a new account and wait for admin approval
2. **Browse Books**: Browse available books by category or use search
3. **Upload Books**: You can upload books after logging in and having your account approved
4. **Profile**: View your account information, subscription end date, and books you've uploaded

### For Administrators

1. **User Management**: Approve or reject new users
2. **Book Management**: Add, edit, and delete books
3. **Category Management**: Create and edit book categories
4. **Dashboard**: View system statistics

## Security

- SQL Injection protection implemented using Prepared Statements
- XSS protection implemented using `htmlspecialchars()`
- Passwords encrypted using secure hashing algorithm
- Permission verification implemented for all operations

## Customization

### Changing the Default Language

Modify the `core/Localization.php` file:

```php
private $defaultLanguage = 'en'; // ar, en, fr
```

### Adding a New Language

1. Create a new file in the `lang/` folder (e.g., `de.php` for German)
2. Copy the contents of `en.php` and translate the values
3. Update the `getSupportedLanguages()` function in the `core/Localization.php` file

### Changing the Application Appearance

Modify the `public/assets/css/app.css` file to customize the general appearance of the application.

## License

This project is licensed under the MIT License.

---

Developed © 2023
