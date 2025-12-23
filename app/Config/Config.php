<?php

/**
 * Application Configuration
 */
class Config
{
    public const DB_HOST = 'mysql';
    public const DB_NAME = 'lbms_app';
    public const DB_USER = 'lbms_user';
    public const DB_PASS = 'lbms_password';
    public const DB_CHARSET = 'utf8mb4';

    public const APP_NAME = 'LBMS - Library Book Management System';
    public const APP_VERSION = '1.0.0';
    public const APP_ENV = 'development'; 

    public const APP_DEBUG = true;
    public const APP_URL = 'http://localhost';

    public const SESSION_LIFETIME = 3600;
    public const SESSION_NAME = 'lbms_session';

    public const ENCRYPTION_KEY = '31ef46b38f878c75e6af1ba9630ef150a82707eff49a896c1ae7510fd891e9c75cd00576286d357d9fb9d4f65c47c978';
    public const PASSWORD_MIN_LENGTH = 8;

    public const ITEMS_PER_PAGE = 10;

    public const MAX_FILE_SIZE = 5242880; 
    public const ALLOWED_FILE_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

    public const SMTP_HOST = 'localhost';
    public const SMTP_PORT = 587;
    public const SMTP_USERNAME = '';
    public const SMTP_PASSWORD = '';
    public const SMTP_FROM_EMAIL = 'noreply@lbms.com';
    public const SMTP_FROM_NAME = 'LBMS';
}