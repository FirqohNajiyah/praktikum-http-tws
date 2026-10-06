<?php
// Header kustom sesuai Tugas 2
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

require "data.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "GET") {
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "total_courses" => count($courses),
        "data" => $courses
    ]);
    exit();
}

if ($method === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    
    // Validasi input khusus matakuliah
    if (!isset($input['code']) || !isset($input['name']) || !isset($input['sks'])) {
        http_response_code(422); // Unprocessable Entity / Bad Request
        echo json_encode([
            "status" => "fail",
            "message" => "Atribut 'code', 'name', dan 'sks' wajib lengkap!"
        ]);
        exit();
    }

    $newId = count($courses) > 0 ? max(array_column($courses, 'id')) + 1 : 1;
    $newCourse = [
        "id" => $newId,
        "code" => $input['code'],
        "name" => $input['name'],
        "sks" => intval($input['sks'])
    ];

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Mata kuliah berhasil ditambahkan",
        "data" => $newCourse
    ]);
    exit();
}

// Penanganan error method tidak diizinkan
http_response_code(405);
echo json_encode([
    "status" => "error",
    "message" => "HTTP Method '$method' tidak diizinkan untuk endpoint Matakuliah."
]);
?>