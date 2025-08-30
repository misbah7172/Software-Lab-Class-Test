<?php
// db.php - simple PDO helper for MySQL
// Update DB credentials if needed (default XAMPP: user=root, no password)
function getPDO()
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $host = '127.0.0.1';
    $db   = 'library';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $opts);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'DB connection failed', 'message' => $e->getMessage()]);
        exit;
    }
}

?>
