<?php

session_start();

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

date_default_timezone_set('UTC');

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/BaseController.php';
require_once __DIR__ . '/../app/Core/BaseModel.php';

$controllerFiles = glob(__DIR__ . '/../app/Controllers/*.php');
foreach ($controllerFiles as $file) {
    require_once $file;
}

$modelFiles = glob(__DIR__ . '/../app/Models/*.php');
foreach ($modelFiles as $file) {
    require_once $file;
}

$router = new Router();

$router->get('/', function() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        header('Location: /dashboard');
    } else {
        header('Location: /login');
    }
    exit;
});

$router->get('/test', 'AdminController', 'dashboard');

$router->get('/login', 'AuthController', 'login');
$router->post('/login', 'AuthController', 'doLogin');
$router->get('/logout', 'AuthController', 'logout');
$router->get('/register', 'AuthController', 'register');
$router->post('/register', 'AuthController', 'doRegister');

$router->get('/dashboard', 'AdminController', 'dashboard', ['auth']);
$router->get('/profile', 'AdminController', 'profile', ['auth']);
$router->post('/profile', 'AdminController', 'updateProfile', ['auth']);
$router->post('/change-password', 'AdminController', 'changePassword', ['auth']);

$router->get('/users', 'AdminController', 'dataUsers', ['auth']);
$router->get('/users/new', 'AdminController', 'newUser', ['auth']);
$router->get('/users/{id}/edit', 'AdminController', 'editUser', ['auth']);
$router->get('/users/{id}', 'AdminController', 'detailUser', ['auth']);
$router->get('/alat', 'AdminController', 'manajemenAlat', ['auth']);
$router->get('/alat/new', 'AdminController', 'newAlat', ['auth']);
$router->get('/alat/{id}/edit', 'AdminController', 'editAlat', ['auth']);
$router->get('/alat/{id}/detail', 'AdminController', 'detailAlat', ['auth']);
$router->get('/peminjaman', 'AdminController', 'peminjaman', ['auth']);
$router->get('/peminjaman/new', 'AdminController', 'newPeminjaman', ['auth']);
$router->get('/peminjaman/{id}/detail', 'AdminController', 'detailPeminjaman', ['auth']);
$router->get('/settings', 'AdminController', 'settings', ['auth']);
$router->get('/settings/profile', 'AdminController', 'settingsProfile', ['auth']);
$router->get('/settings/privacy-security', 'AdminController', 'settingsPrivacySecurity', ['auth']);

$router->get('/notifications', 'AdminController', 'notifications', ['auth']);
$router->post('/notifications/mark-read/{id}', 'AdminController', 'markNotificationRead', ['auth']);
$router->delete('/notifications/delete/{id}', 'AdminController', 'deleteNotification', ['auth']);

$router->get('/admin/users', 'AdminController', 'users');
$router->post('/admin/users', 'AdminController', 'createUser');
$router->get('/admin/users/toggle/{id}', 'AdminController', 'toggleUserStatus');

$router->post('/alat/create', 'AdminController', 'createAlat', ['auth']);
$router->post('/alat/{id}/update', 'AdminController', 'updateAlat', ['auth']);
$router->post('/alat/{id}/update/status', 'AdminController', 'updateAlatStatus', ['auth']);
$router->get('/alat/delete/{id}', 'AdminController', 'deleteAlat', ['auth']);
$router->get('/alat/change-status/{id}/{status}', 'AdminController', 'changeAlatStatus', ['auth']);

$router->post('/users/create', 'AdminController', 'createUser', ['auth']);
$router->post('/users/{id}/update', 'AdminController', 'updateUser', ['auth']);
$router->post('/users/{id}/update/status', 'AdminController', 'updateUserStatus', ['auth']);
$router->get('/users/toggle-status/{id}', 'AdminController', 'toggleUserStatus', ['auth']);

$router->post('/peminjaman/create', 'AdminController', 'createPeminjaman', ['auth']);
$router->post('/peminjaman/update-status/{id}', 'AdminController', 'updatePeminjamanStatus', ['auth']);
$router->get('/peminjaman/kembalikan/{id}', 'AdminController', 'kembalikanPeminjaman', ['auth']);
$router->get('/peminjaman/batalkan/{id}', 'AdminController', 'batalkanPeminjaman', ['auth']);

$router->post('/peminjaman/{id}/proses', 'AdminController', 'prosesPeminjaman', ['auth']);
$router->post('/peminjaman/{id}/tolak', 'AdminController', 'tolakPeminjaman', ['auth']);
$router->post('/peminjaman/{id}/selesaikan', 'AdminController', 'selesaikanPeminjaman', ['auth']);

$router->dispatch();
