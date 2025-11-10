<?php
/**
 * Language Management System
 * Hệ thống quản lý ngôn ngữ
 */

// Lấy ngôn ngữ từ session, cookie hoặc mặc định
if (isset($_GET['lang'])) {
    $lang = in_array($_GET['lang'], ['vi', 'en']) ? $_GET['lang'] : 'vi';
    $_SESSION['lang'] = $lang;
    setcookie('lang', $lang, time() + (86400 * 30), '/'); // 30 ngày
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], ['vi', 'en'])) {
    $lang = $_COOKIE['lang'];
    $_SESSION['lang'] = $lang;
} else {
    $lang = 'vi'; // Mặc định tiếng Việt
    $_SESSION['lang'] = $lang;
}

// Định nghĩa hàm tải ngôn ngữ
function loadLanguage($lang) {
    $lang_file = __DIR__ . '/../lang/' . $lang . '.php';
    if (file_exists($lang_file)) {
        return require $lang_file;
    }
    // Fallback về tiếng Việt nếu file không tồn tại
    return require __DIR__ . '/../lang/vi.php';
}

// Hàm dịch text
function __($key, $default = '') {
    global $translations;
    return isset($translations[$key]) ? $translations[$key] : ($default ? $default : $key);
}

// Tải translations
$translations = loadLanguage($lang);

// Định nghĩa hàm helper
function lang($key, $default = '') {
    return __($key, $default);
}
?>

