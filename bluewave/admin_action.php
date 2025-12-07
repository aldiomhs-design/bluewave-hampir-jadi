<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$action = $_GET['action'] ?? '';
$order_id = $_GET['id'] ?? 0;

if($action == 'confirm') {
    $status = 'success';
    $message = 'Pesanan berhasil dikonfirmasi!';
} elseif($action == 'reject') {
    $status = 'failed';
    $message = 'Pesanan ditolak!';
} else {
    header("Location: admin_dashboard.php");
    exit();
}

// Update status
$sql = "UPDATE topup SET status = '$status' WHERE id = $order_id";
if($conn->query($sql)) {
    echo "<script>
        alert('$message');
        window.location.href = 'admin_dashboard.php';
    </script>";
} else {
    echo "Error: " . $conn->error;
}
?>