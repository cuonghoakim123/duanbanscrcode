<?php
/**
 * Environment Variables Loader
 * Đọc file .env và load các biến môi trường
 */

class EnvLoader {
    private static $loaded = false;
    
    /**
     * Load file .env
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }
        
        if ($path === null) {
            $path = dirname(__DIR__) . '/.env';
        }
        
        if (!file_exists($path)) {
            // Nếu không có .env, sử dụng giá trị mặc định
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Bỏ qua comment
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes nếu có
                $value = trim($value, '"\'');
                
                // Set environment variable
                if (!getenv($key)) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Lấy giá trị biến môi trường
     */
    public static function get($key, $default = null) {
        self::load();
        
        $value = getenv($key);
        if ($value === false) {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }
        
        return $value;
    }
}

// Auto load khi file được include
EnvLoader::load();

