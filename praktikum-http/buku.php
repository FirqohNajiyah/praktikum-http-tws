<?php
// Header kustom untuk REST API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require "data.php";

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// 1. METHOD GET (Read Semua atau Read by ID)
if ($method === "GET") {
    if ($id !== null) {
        $foundBook = null;
        foreach ($books as $b) {
            if ($b['id'] === $id) {
                $foundBook = $b;
                break;
            }
        }

        if ($foundBook) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $foundBook]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Buku dengan ID $id tidak ditemukan"]);
        }
    } else {
        http_response_code(200);
        echo json_encode(["status" => "success", "total" => count($books), "data" => $books]);
    }
    exit();
}

// 2. METHOD POST (Create Data Baru)
if ($method === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    // Validasi gagal (422) jika atribut tidak lengkap
    if (!isset($input['isbn']) || !isset($input['title']) || !isset($input['author'])) {
        http_response_code(422);
        echo json_encode(["status" => "fail", "message" => "Atribut 'isbn', 'title', dan 'author' wajib lengkap!"]);
        exit();
    }

    $newId = count($books) > 0 ? max(array_column($books, 'id')) + 1 : 1;
    $newBook = [
        "id" => $newId,
        "isbn" => $input['isbn'],
        "title" => $input['title'],
        "author" => $input['author']
    ];

    http_response_code(201);
    echo json_encode(["status" => "success", "message" => "Buku berhasil ditambahkan", "data" => $newBook]);
    exit();
}

// 3. METHOD PUT (Update Data)
if ($method === "PUT") {
    // Validasi gagal jika ID tidak disertakan (400 Bad Request)
    if ($id === null) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Parameter ID wajib disertakan untuk update!"]);
        exit();
    }

    // Validasi apakah ID buku tersebut ada di data
    $bookExists = false;
    foreach ($books as $b) {
        if ($b['id'] === $id) {
            $bookExists = true;
            break;
        }
    }

    if (!$bookExists) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Buku dengan ID $id tidak ditemukan untuk diperbarui"]);
        exit();
    }

    $input = json_decode(file_get_contents("php://input"), true);
    
    // Validasi gagal (422) jika atribut update tidak lengkap
    if (!isset($input['isbn']) || !isset($input['title']) || !isset($input['author'])) {
        http_response_code(422);
        echo json_encode(["status" => "fail", "message" => "Atribut 'isbn', 'title', dan 'author' wajib lengkap untuk update!"]);
        exit();
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Buku dengan ID $id berhasil diperbarui",
        "data" => [
            "id" => $id,
            "isbn" => $input['isbn'],
            "title" => $input['title'],
            "author" => $input['author']
        ]
    ]);
    exit();
}

// 4. METHOD DELETE (Hapus Data)
if ($method === "DELETE") {
    // Validasi gagal jika ID tidak disertakan (400 Bad Request)
    if ($id === null) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Parameter ID wajib disertakan untuk menghapus!"]);
        exit();
    }

    // Validasi apakah ID buku tersebut ada di data
    $bookExists = false;
    foreach ($books as $b) {
        if ($b['id'] === $id) {
            $bookExists = true;
            break;
        }
    }

    if (!$bookExists) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Buku dengan ID $id tidak ditemukan untuk dihapus"]);
        exit();
    }

    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Buku dengan ID $id berhasil dihapus"]);
    exit();
}

// Penanganan Method Tidak Diizinkan (405)
http_response_code(405);
echo json_encode(["status" => "error", "message" => "HTTP Method '$method' tidak diizinkan."]);
?>