<?php
require_once __DIR__ . '/config.php';

function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('sistem_akses');
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    if (!is_array($roles)) $roles = [$roles];
    if (!in_array($_SESSION['role'], $roles)) {
        header('Location: login.php');
        exit;
    }
}

function redirectByRole() {
    $role = $_SESSION['role'] === 'admin' ? 'admin_it' : $_SESSION['role'];
    $map = [
        'pemohon'          => 'dashboard_pemohon.php',
        'pengarah_jab'     => 'dashboard_pengarah_jab.php',
        'pengarah_jtik'    => 'dashboard_pengarah_jtik.php',
        'admin_it'         => 'dashboard_admin_it.php',
        'admin'            => 'dashboard_admin_it.php',
    ];
    $dest = $map[$role] ?? 'login.php';
    header("Location: $dest");
    exit;
}
