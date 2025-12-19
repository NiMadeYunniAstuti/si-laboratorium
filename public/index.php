<?php

// Start session
session_start();

// Error reporting based on environment
if (file_exists(__DIR__ . '/../app/Config/Config.php')) {
    require_once __DIR__ . '/../app/Config/Config.php';

    if (Config::APP_DEBUG) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
    }
}

// Set timezone
date_default_timezone_set('UTC');

// Load core classes
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/BaseController.php';
require_once __DIR__ . '/../app/Core/BaseModel.php';

// Load controllers
$controllerFiles = glob(__DIR__ . '/../app/Controllers/*.php');
foreach ($controllerFiles as $file) {
    require_once $file;
}

// Load models
$modelFiles = glob(__DIR__ . '/../app/Models/*.php');
foreach ($modelFiles as $file) {
    require_once $file;
}

// Create router instance
$router = new Router();

// Define routes
$router->get('/', 'AdminController', 'dashboard');
$router->get('/test', 'AdminController', 'dashboard');

// Authentication routes
$router->get('/login', 'AuthController', 'login');
$router->post('/login', 'AuthController', 'doLogin');
$router->get('/logout', 'AuthController', 'logout');
$router->get('/register', 'AuthController', 'register');
$router->post('/register', 'AuthController', 'doRegister');

// Dashboard routes
$router->get('/dashboard', 'AdminController', 'dashboard');
$router->get('/profile', 'AdminController', 'profile');
$router->post('/profile', 'AdminController', 'updateProfile');
$router->post('/change-password', 'AdminController', 'changePassword');

// New page routes
$router->get('/users', 'AdminController', 'dataUsers');
$router->get('/users/new', 'AdminController', 'newUser');
$router->get('/users/{id}', 'AdminController', 'detailUser');
$router->get('/alat', 'AdminController', 'manajemenAlat');
$router->get('/alat/new', 'AdminController', 'newAlat');
$router->get('/alat/{id}/detail', 'AdminController', 'detailAlat');
$router->get('/peminjaman', 'AdminController', 'peminjaman');
$router->get('/peminjaman/new', 'AdminController', 'newPeminjaman');
$router->get('/peminjaman/{id}/detail', 'AdminController', 'detailPeminjaman');
$router->get('/settings', 'AdminController', 'settings');
$router->get('/settings/profile', 'AdminController', 'settingsProfile');
$router->get('/settings/privacy-security', 'AdminController', 'settingsPrivacySecurity');

// Notifications routes
$router->get('/notifications', 'AdminController', 'notifications');

// Admin routes
$router->get('/admin/users', 'AdminController', 'users');
$router->post('/admin/users', 'AdminController', 'createUser');
$router->get('/admin/users/toggle/{id}', 'AdminController', 'toggleUserStatus');

// Alat POST routes
$router->post('/alat/create', 'AdminController', 'createAlat');
$router->post('/alat/update/{id}', 'AdminController', 'updateAlat');
$router->get('/alat/delete/{id}', 'AdminController', 'deleteAlat');
$router->get('/alat/change-status/{id}/{status}', 'AdminController', 'changeAlatStatus');

// Users POST routes
$router->post('/users/create', 'AdminController', 'createUser');
$router->post('/users/update/{id}', 'AdminController', 'updateUser');
$router->get('/users/toggle-status/{id}', 'AdminController', 'toggleUserStatus');

// Peminjaman POST routes
$router->post('/peminjaman/create', 'AdminController', 'createPeminjaman');
$router->post('/peminjaman/update-status/{id}', 'AdminController', 'updatePeminjamanStatus');
$router->get('/peminjaman/kembalikan/{id}', 'AdminController', 'kembalikanPeminjaman');
$router->get('/peminjaman/batalkan/{id}', 'AdminController', 'batalkanPeminjaman');

// Peminjaman action routes
$router->post('/peminjaman/{id}/proses', 'AdminController', 'prosesPeminjaman');
$router->post('/peminjaman/{id}/tolak', 'AdminController', 'tolakPeminjaman');
$router->post('/peminjaman/{id}/selesaikan', 'AdminController', 'selesaikanPeminjaman');

// Dispatch the request
$router->dispatch();