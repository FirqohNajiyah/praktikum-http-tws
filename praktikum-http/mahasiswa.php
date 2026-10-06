<?php
header("Content-Type: application/json");
require "data.php";

$method = $_SERVER['REQUEST_METHOD'];

// ----------------------------------------------------
// GET: Mengambil semua data atau berdasarkan ID
// ----------------------------------------------------
if ($method === "GET") {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $found = null;
        foreach ($students as $student) {
            if ($student['id'] === $id) {
                $found = $student;
                break;
            }
        }
        
        if ($found) {
            http_response_code(200);
            echo json_encode($found);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Mahasiswa dengan ID tersebut tidak ditemukan"]);
        }
    } else {
        http_response_code(200);
        echo json_encode($students);
    }
    exit();
}

// ----------------------------------------------------
// POST: Menambah data mahasiswa baru
// ----------------------------------------------------
if ($method === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input['nim']) || !isset($input['name']) || !isset($input['major'])) {
        http_response_code(400);
        echo json_encode(["error" => "Field nim, name, dan major wajib diisi"]);
        exit();
    }
    
    $newId = count($students) > 0 ? max(array_column($students, 'id')) + 1 : 1;
    $newstudent = [
        "id" => $newId,
        "nim" => $input['nim'],
        "name" => $input['name'],
        "major" => $input['major']
    ];
    
    // Catatan: Karena ini berbasis file/array runtime, data baru ini 
    // akan tersimpan selama request Postman berjalan.
    http_response_code(201);
    header("Location: /mahasiswa.php?id=$newId");
    echo json_encode($newstudent);
    exit();
}

// ----------------------------------------------------
// PUT: Mengubah data mahasiswa berdasarkan ID (Tugas 1)
// ----------------------------------------------------
if ($method === "PUT") {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Parameter ID wajib disertakan di URL"]);
        exit();
    }

    $id = intval($_GET['id']);
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['nim']) || !isset($input['name']) || !isset($input['major'])) {
        http_response_code(400);
        echo json_encode(["error" => "Field nim, name, dan major wajib diisi untuk update"]);
        exit();
    }

    $foundIndex = -1;
    foreach ($students as $index => $student) {
        if ($student['id'] === $id) {
            $foundIndex = $index;
            break;
        }
    }

    if ($foundIndex === -1) {
        http_response_code(404);
        echo json_encode(["error" => "ID tidak ditemukan"]);
        exit();
    }

    // Update data
    $students[$foundIndex]['nim'] = $input['nim'];
    $students[$foundIndex]['name'] = $input['name'];
    $students[$foundIndex]['major'] = $input['major'];

    http_response_code(200);
    echo json_encode([
        "message" => "Data mahasiswa berhasil diubah",
        "data" => $students[$foundIndex]
    ]);
    exit();
}

// ----------------------------------------------------
// DELETE: Menghapus data mahasiswa berdasarkan ID (Tugas 1)
// ----------------------------------------------------
if ($method === "DELETE") {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Parameter ID wajib disertakan di URL"]);
        exit();
    }

    $id = intval($_GET['id']);
    $foundIndex = -1;
    foreach ($students as $index => $student) {
        if ($student['id'] === $id) {
            $foundIndex = $index;
            break;
        }
    }

    if ($foundIndex === -1) {
        http_response_code(404);
        echo json_encode(["error" => "ID tidak ditemukan"]);
        exit();
    }

    // Hapus dari array
    array_splice($students, $foundIndex, 1);

    http_response_code(200); // Bisa juga menggunakan 204 No Content
    echo json_encode(["message" => "Data mahasiswa berhasil dihapus"]);
    exit();
}

// Jika method selain di atas
http_response_code(405);
echo json_encode(["error" => "Method tidak didukung"]);
?>