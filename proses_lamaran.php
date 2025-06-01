<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "gawe_yuk";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $lowongan_id = intval($_POST['lowongan_id']);
    $surat_lamaran = trim($_POST['surat_lamaran']);
    
    // Handle CV file upload
    $target_dir = "uploads/cv/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $cv_file = $_FILES['cv'];
    $cv_filename = $user_id . '_' . time() . '_' . basename($cv_file['name']);
    $target_file = $target_dir . $cv_filename;
    
    // Check if file is a PDF
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if ($file_type != "pdf") {
        $_SESSION['error'] = "Hanya file PDF yang diperbolehkan untuk CV.";
        header("Location: detail_lowongan.php?id=" . $lowongan_id);
        exit();
    }
    
    // Move uploaded file
    if (move_uploaded_file($cv_file["tmp_name"], $target_file)) {
        // Insert application into database
        $sql = "INSERT INTO lamaran (user_id, lowongan_id, cv, surat_lamaran) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $user_id, $lowongan_id, $cv_filename, $surat_lamaran);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Lamaran berhasil dikirim!";
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat mengirim lamaran.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Terjadi kesalahan saat mengunggah CV.";
    }
    
    header("Location: detail_lowongan.php?id=" . $lowongan_id);
    exit();
}

$conn->close();
?> 
