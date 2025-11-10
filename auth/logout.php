<?php
require_once '../config/config.php';

// Xóa tất cả session
session_destroy();

// Redirect về trang chủ
header('Location: ' . SITE_URL);
exit();
?>
