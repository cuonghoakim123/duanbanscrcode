<?php
require_once '../config/config.php';

// Xóa tất cả biến session
$_SESSION = array();

// Xóa session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Hủy session
session_destroy();

// Redirect về trang chủ
header('Location: ../index.php');
exit();
?>
