<?php
// api.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = '/' . trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$route = substr($path, strlen($base));
$route = '/' . trim($route, '/');

$parts = array_values(array_filter(explode('/', $route)));

if (isset($parts[0]) && $parts[0] === basename($_SERVER['SCRIPT_NAME'])) {
    array_shift($parts);
}

$resource = isset($parts[0]) ? $parts[0] : '';
$id = isset($parts[1]) ? $parts[1] : null;

// CORS 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($method === 'OPTIONS') exit;

try {
    if ($resource !== 'books') {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        exit;
    }

    $pdo = getPDO();

    if ($method === 'POST' && !$id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $title = $data['title'] ?? null;
        $author = $data['author'] ?? null;
        $availability = isset($data['availability']) ? (int)$data['availability'] : 1;
        $genres = isset($data['genres']) && is_array($data['genres']) ? $data['genres'] : [];

        if (!$title || !$author) {
            http_response_code(400);
            echo json_encode(['error' => 'title and author required']);
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO books (title, author, availability, genres, createdAt) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)');
        $stmt->execute([$title, $author, $availability, json_encode(array_values(array_filter(array_map('trim', $genres))))]);
        $newId = $pdo->lastInsertId();

        foreach ($genres as $gname) {
            $gname = trim($gname);
            if ($gname === '') continue;
            $stmt = $pdo->prepare('INSERT IGNORE INTO genres (name) VALUES (?)');
            $stmt->execute([$gname]);
        }

        $stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
        $stmt->execute([$newId]);
        $row = $stmt->fetch();
        if ($row) $row['genres'] = json_decode($row['genres']);
        echo json_encode($row);
        exit;
    }

    if ($method === 'GET') {
        // GET /books or /books/{id} or /books?genre=xyz
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) { http_response_code(404); echo json_encode(['error' => 'Not found']); exit; }
            $row['genres'] = json_decode($row['genres']);
            echo json_encode($row);
            exit;
        }

        $genre = isset($_GET['genre']) ? $_GET['genre'] : null;
        if ($genre) {
            // try JSON_CONTAINS first (MySQL 5.7+). fallback to LIKE
            try {
                $stmt = $pdo->prepare('SELECT * FROM books WHERE JSON_CONTAINS(genres, JSON_QUOTE(?)) ORDER BY createdAt DESC');
                $stmt->execute([$genre]);
                $rows = $stmt->fetchAll();
            } catch (Exception $e) {
                $stmt = $pdo->prepare('SELECT * FROM books WHERE genres LIKE ? ORDER BY createdAt DESC');
                $stmt->execute(['%"' . $genre . '"%']);
                $rows = $stmt->fetchAll();
            }
        } else {
            $stmt = $pdo->query('SELECT * FROM books ORDER BY createdAt DESC');
            $rows = $stmt->fetchAll();
        }
        foreach ($rows as &$r) { $r['genres'] = json_decode($r['genres']); }
        echo json_encode($rows);
        exit;
    }

    if ($method === 'PUT' && $id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $fields = [];
        $params = [];
        if (isset($data['title'])) { $fields[] = 'title = ?'; $params[] = $data['title']; }
        if (isset($data['author'])) { $fields[] = 'author = ?'; $params[] = $data['author']; }
        if (isset($data['availability'])) { $fields[] = 'availability = ?'; $params[] = (int)$data['availability']; }

        if (empty($fields) && !isset($data['genres'])) { http_response_code(400); echo json_encode(['error' => 'no fields to update']); exit; }

        if (!empty($fields)) {
            $params[] = $id;
            $sql = 'UPDATE books SET ' . implode(', ', $fields) . ' WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        if (isset($data['genres']) && is_array($data['genres'])) {
            // write genres JSON into books.genres and ensure registry
            $clean = array_values(array_filter(array_map('trim', $data['genres'])));
            $stmt = $pdo->prepare('UPDATE books SET genres = ? WHERE id = ?');
            $stmt->execute([json_encode($clean), $id]);
            foreach ($clean as $gname) {
                $stmt = $pdo->prepare('INSERT IGNORE INTO genres (name) VALUES (?)');
                $stmt->execute([$gname]);
            }
        }

    // return the updated book record
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { http_response_code(404); echo json_encode(['error' => 'Not found']); exit; }
    $row['genres'] = json_decode($row['genres']);
    echo json_encode($row);
        exit;
    }

    if ($method === 'DELETE' && $id) {
        $stmt = $pdo->prepare('DELETE FROM books WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['deleted' => (int)$stmt->rowCount()]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}

?>
