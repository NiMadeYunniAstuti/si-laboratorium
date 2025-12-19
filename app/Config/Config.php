<?php

/**
 * Application Configuration
 */
class Config
{
    // Database Configuration
    public const DB_HOST = 'mysql'; // Docker service name
    public const DB_NAME = 'lbms_app';
    public const DB_USER = 'lbms_user';
    public const DB_PASS = 'lbms_password';
    public const DB_CHARSET = 'utf8mb4';

    // Application Configuration
    public const APP_NAME = 'LBMS - Library Book Management System';
    public const APP_VERSION = '1.0.0';
    public const APP_ENV = 'development'; // development, staging, production
    public const APP_DEBUG = true;
    public const APP_URL = 'http://localhost';

    // Session Configuration
    public const SESSION_LIFETIME = 3600; // 1 hour
    public const SESSION_NAME = 'lbms_session';

    // Security
    public const ENCRYPTION_KEY = 'your-secret-key-change-in-production';
    public const PASSWORD_MIN_LENGTH = 8;

    // Pagination
    public const ITEMS_PER_PAGE = 10;

    // File Upload
    public const MAX_FILE_SIZE = 5242880; // 5MB
    public const ALLOWED_FILE_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

    // Email Configuration (if needed)
    public const SMTP_HOST = 'localhost';
    public const SMTP_PORT = 587;
    public const SMTP_USERNAME = '';
    public const SMTP_PASSWORD = '';
    public const SMTP_FROM_EMAIL = 'noreply@lbms.com';
    public const SMTP_FROM_NAME = 'LBMS';
}