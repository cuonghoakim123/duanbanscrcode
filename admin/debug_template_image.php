<?php
require_once '../config/config.php';
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    die('Unauthorized');
}

$database = new Database();
$db = $database->getConnection();

// Lấy tất cả templates
$query = "SELECT id, name, image, updated_at FROM templates ORDER BY updated_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Debug Template Images</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Name</th><th>Image Value (DB)</th><th>Built URL</th><th>File Exists</th><th>Updated At</th></tr>";

foreach ($templates as $template) {
    $image_value = $template['image'] ?? 'NULL';
    $image_url = '';
    
    if (!empty($image_value)) {
        $temp = trim($image_value);
        $temp = str_replace('/admin/uploads/templates/', '/uploads/templates/', $temp);
        $temp = str_replace('admin/uploads/templates/', 'uploads/templates/', $temp);
        
        if (preg_match('/^https?:\/\//', $temp)) {
            $image_url = $temp;
        } elseif (strpos($temp, SITE_URL) === 0) {
            $temp2 = str_replace(SITE_URL, '', $temp);
            $temp2 = ltrim($temp2, '/');
            if (strpos($temp2, 'uploads/templates/') === 0) {
                $filename = basename(str_replace('uploads/templates/', '', $temp2));
            } else {
                $filename = basename($temp2);
            }
            $image_url = SITE_URL . '/uploads/templates/' . $filename;
        } else {
            $temp2 = $temp;
            if (strpos($temp2, 'uploads/templates/') === 0) {
                $temp2 = str_replace('uploads/templates/', '', $temp2);
            }
            if (strpos($temp2, '/uploads/templates/') === 0) {
                $temp2 = str_replace('/uploads/templates/', '', $temp2);
            }
            $filename = basename($temp2);
            $image_url = SITE_URL . '/uploads/templates/' . $filename;
        }
    }
    
    $file_exists = 'N/A';
    if ($image_url) {
        $file_path = dirname(__DIR__) . '/uploads/templates/' . basename($image_url);
        $file_exists = file_exists($file_path) ? 'YES' : 'NO';
    }
    
    echo "<tr>";
    echo "<td>{$template['id']}</td>";
    echo "<td>" . htmlspecialchars($template['name']) . "</td>";
    echo "<td>" . htmlspecialchars($image_value) . "</td>";
    echo "<td>" . htmlspecialchars($image_url) . "</td>";
    echo "<td>$file_exists</td>";
    echo "<td>{$template['updated_at']}</td>";
    echo "</tr>";
}

echo "</table>";
echo "<br><a href='templates.php'>Back to Templates</a>";
?>

