<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Defensive check: ensure DB connection exists
if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not established. Check database.php');
}

$order_id = (int)($_GET['order_id'] ?? $_SESSION['current_order'] ?? 0);

// Get order details using plain SQL (safe-cast integers)
$order_id = (int)$order_id;
$user_session_id = (int)($_SESSION['user_id'] ?? 0);
$sql = "SELECT t.*, u.username 
         FROM topup t 
        JOIN users u ON t.id_akun = u.id 
        WHERE t.id = $order_id AND t.id_akun = $user_session_id
        LIMIT 1";
$result = $conn->query($sql);
if (!$result) {
    die('Database error: ' . $conn->error);
}
$order = $result->fetch_assoc();

if (!$order) {
    echo "<script>
        alert('Pesanan tidak ditemukan!');
        window.location.href='tokenhok.php';
    </script>";
    exit();
}

// Process payment proof upload
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_bayar'])) {
    $upload_dir = "uploads/bukti_bayar/";
    
    // Create directory if not exists
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate filename
    $file_ext = strtolower(pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION));
    $file_name = $order['order_id'] . '_' . time() . '.' . $file_ext;
    $target_file = $upload_dir . $file_name;
    
    // Validate file
    $check = getimagesize($_FILES['bukti_bayar']['tmp_name']);
    if ($check === false) {
        $message = "File yang diupload bukan gambar!";
        $message_type = 'error';
    } elseif ($_FILES['bukti_bayar']['size'] > 5000000) {
        $message = "Ukuran file terlalu besar! Maksimal 5MB";
        $message_type = 'error';
    } elseif (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        $message = "Hanya format JPG, JPEG, PNG & GIF yang diizinkan!";
        $message_type = 'error';
    } elseif (move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $target_file)) {
        // Update database
        $update_stmt = $conn->prepare("UPDATE topup SET bukti_bayar = ?, status = 'pending' WHERE id = ?");
        $update_stmt->bind_param("si", $target_file, $order_id);
        
        if ($update_stmt->execute()) {
            $message = "✅ Bukti pembayaran berhasil diupload! Admin akan memverifikasi dalam 1x24 jam.";
            $message_type = 'success';
            $order['bukti_bayar'] = $target_file;
            $order['status'] = 'pending';
        } else {
            $message = "❌ Gagal menyimpan bukti pembayaran!";
            $message_type = 'error';
        }
        $update_stmt->close();
    } else {
        $message = "❌ Terjadi kesalahan saat upload file!";
        $message_type = 'error';
    }
}

// Calculate remaining time
$expired_time = new DateTime($order['expired_time']);
$current_time = new DateTime();
$time_diff = $current_time->diff($expired_time);
$minutes_remaining = ($time_diff->h * 60) + $time_diff->i;
$seconds_remaining = $time_diff->s;

// Auto expire if time is up
if ($minutes_remaining <= 0 && $order['status'] == 'waiting') {
    $update_stmt = $conn->prepare("UPDATE topup SET status = 'expired' WHERE id = ?");
    $update_stmt->bind_param("i", $order_id);
    $update_stmt->execute();
    $update_stmt->close();
    $order['status'] = 'expired';
}

// Get status color
$status_colors = [
    'waiting' => '#f39c12',
    'pending' => '#3498db',
    'processing' => '#9b59b6',
    'success' => '#27ae60',
    'failed' => '#e74c3c',
    'expired' => '#95a5a6'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - BlueWave Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e3c72;
            --secondary: #2a5298;
            --accent: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --radius: 12px;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f0f5ff 0%, #e3f2fd 100%);
            min-height: 100vh;
            color: var(--dark);
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .page-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-title h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .payment-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 992px) {
            .payment-container {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .amount-display {
            text-align: center;
            padding: 1.5rem;
            margin: 1rem 0;
            background: linear-gradient(135deg, #f0f7ff 0%, #e3f2fd 100%);
            border-radius: var(--radius);
            border: 2px dashed var(--accent);
        }

        .amount {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            margin: 0.5rem 0;
        }

        .countdown-timer {
            text-align: center;
            padding: 1rem;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-radius: var(--radius);
            margin: 1rem 0;
            border: 2px solid var(--warning);
        }

        .countdown-timer h3 {
            color: #856404;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .timer {
            font-size: 2rem;
            font-weight: 700;
            color: #e74c3c;
            font-family: 'Courier New', monospace;
        }

        .qris-container {
            text-align: center;
            padding: 2rem 0;
        }

        .qris-code {
            width: 250px;
            height: 250px;
            margin: 0 auto 1.5rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .qris-code::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 48%, #e2e8f0 50%, transparent 52%);
            background-size: 20px 20px;
        }

        .qris-code-inner {
            width: 200px;
            height: 200px;
            background: #1e3c72;
            border-radius: 10px;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .instructions {
            background: #f8f9fa;
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .instructions h4 {
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .instructions ol {
            padding-left: 1.5rem;
        }

        .instructions li {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        .order-info {
            background: #f8f9fa;
            border-radius: var(--radius);
            padding: 1.5rem;
            margin: 1.5rem 0;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6c757d;
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
            color: var(--dark);
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .upload-section {
            margin-top: 2rem;
        }

        .upload-area {
            border: 3px dashed #cbd5e1;
            border-radius: var(--radius);
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            background: white;
            margin-bottom: 1.5rem;
        }

        .upload-area:hover {
            border-color: var(--accent);
            background: #f0f7ff;
        }

        .upload-area.dragover {
            border-color: var(--accent);
            background: #e3f2fd;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }

        .upload-text {
            color: var(--dark);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .upload-hint {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .file-input {
            display: none;
        }

        .preview-container {
            display: none;
            margin-top: 1.5rem;
        }

        .preview-container.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: var(--radius);
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--radius);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            margin-top: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 60, 114, 0.4);
        }

        .btn-primary:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .completed-section {
            text-align: center;
            padding: 3rem 2rem;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-radius: var(--radius);
            border: 2px solid #10b981;
        }

        .completed-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 1.5rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-buttons .btn {
            flex: 1;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .amount {
                font-size: 2rem;
            }
            
            .timer {
                font-size: 1.5rem;
            }
            
            .qris-code {
                width: 200px;
                height: 200px;
            }
            
            .qris-code-inner {
                width: 160px;
                height: 160px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-water"></i>
            BlueWave Store
        </div>
        
        <div class="user-menu">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>">
                <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="page-title">
            <h1><i class="fas fa-credit-card"></i> Pembayaran BlueWave</h1>
            <p>Selesaikan pembayaran Anda dengan scan QRIS</p>
            <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #6c757d;">
                Order ID: <strong><?= htmlspecialchars($order['order_id']) ?></strong>
            </p>
        </div>

        <?php if ($order['status'] != 'waiting' && $order['status'] != 'pending'): ?>
            <div class="completed-section">
                <div class="completed-icon">
                    <i class="fas fa-<?= $order['status'] == 'success' ? 'check-circle' : 'times-circle' ?>"></i>
                </div>
                <h2 style="color: #065f46; margin-bottom: 1rem;">
                    <?= $order['status'] == 'success' ? 'Pembayaran Berhasil!' : 
                       ($order['status'] == 'failed' ? 'Pembayaran Gagal' : 'Pembayaran Expired') ?>
                </h2>
                <p style="color: #047857; margin-bottom: 1.5rem;">
                    <?= $order['status'] == 'success' ? 
                        'Pesanan Anda telah dikonfirmasi dan sedang diproses.' : 
                        'Silakan buat pesanan baru atau hubungi admin.' ?>
                </p>
                <div class="action-buttons">
                    <a href="rp.php" class="btn btn-primary">
                        <i class="fas fa-history"></i> Lihat Riwayat
                    </a>
                    <a href="tokenhok.php" class="btn btn-success">
                        <i class="fas fa-shopping-cart"></i> Pesan Lagi
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div class="payment-container">
            <!-- QRIS Payment -->
            <div class="card">
                <h2 class="card-title"><i class="fas fa-qrcode"></i> QRIS Payment</h2>
                
                <div class="amount-display">
                    <div style="font-size: 1rem; color: #6c757d;">Total Pembayaran</div>
                    <div class="amount">Rp <?= number_format($order['harga'], 0, ',', '.') ?></div>
                    <div style="font-size: 0.9rem; color: #6c757d;">Transfer sesuai nominal</div>
                </div>

                <?php if ($order['status'] == 'waiting'): ?>
                    <div class="countdown-timer">
                        <h3><i class="fas fa-clock"></i> Selesaikan dalam:</h3>
                        <div class="timer" id="countdown-timer">
                            <?= sprintf('%02d:%02d', $minutes_remaining, $seconds_remaining) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="qris-container">
                    <div class="qris-code">
                        <div class="qris-code-inner">
                            QRIS<br>Rp <?= number_format($order['harga'], 0, ',', '.') ?>
                        </div>
                    </div>
                    
                    <div class="instructions">
                        <h4><i class="fas fa-list-ol"></i> Cara Pembayaran:</h4>
                        <ol>
                            <li>Buka aplikasi e-wallet atau mobile banking</li>
                            <li>Pilih fitur scan QRIS</li>
                            <li>Arahkan kamera ke kode QR di atas</li>
                            <li>Pastikan nominal: <strong>Rp <?= number_format($order['harga'], 0, ',', '.') ?></strong></li>
                            <li>Konfirmasi pembayaran</li>
                            <li>Simpan bukti transfer</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Payment Confirmation -->
            <div class="card">
                <h2 class="card-title"><i class="fas fa-check-circle"></i> Konfirmasi Pembayaran</h2>
                
                <div class="order-info">
                    <div class="info-item">
                        <span class="info-label">Order ID:</span>
                        <span class="info-value"><?= htmlspecialchars($order['order_id']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Game:</span>
                        <span class="info-value"><?= htmlspecialchars($order['game']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">User ID:</span>
                        <span class="info-value"><?= htmlspecialchars($order['user_id']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Server:</span>
                        <span class="info-value"><?= htmlspecialchars($order['server_id']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Produk:</span>
                        <span class="info-value"><?= htmlspecialchars($order['produk']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value">
                            <span class="status-badge" style="background: <?= $status_colors[$order['status']] ?>; color: white;">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </span>
                    </div>
                </div>

                <?php if (!empty($order['bukti_bayar'])): ?>
                    <div class="upload-section">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">
                            <i class="fas fa-file-image"></i> Bukti Pembayaran
                        </h3>
                        
                        <?php if (file_exists($order['bukti_bayar'])): ?>
                            <img src="<?= htmlspecialchars($order['bukti_bayar']) ?>" 
                                 alt="Bukti Pembayaran" 
                                 class="preview-image"
                                 style="cursor: pointer;"
                                 onclick="window.open('<?= htmlspecialchars($order['bukti_bayar']) ?>', '_blank')">
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: var(--radius);">
                                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #f39c12; margin-bottom: 1rem;"></i>
                                <p>File bukti bayar tidak ditemukan</p>
                            </div>
                        <?php endif; ?>
                        
                        <div style="margin-top: 1.5rem; padding: 1rem; background: #e3f2fd; border-radius: var(--radius);">
                            <p style="color: var(--primary); margin: 0;">
                                <i class="fas fa-info-circle"></i> 
                                Status: <strong><?= ucfirst($order['status']) ?></strong> - 
                                <?= $order['status'] == 'pending' ? 'Menunggu verifikasi admin' : 'Telah diverifikasi' ?>
                            </p>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="rp.php" class="btn btn-primary">
                                <i class="fas fa-history"></i> Lihat Riwayat
                            </a>
                        </div>
                    </div>
                <?php elseif ($order['status'] == 'waiting'): ?>
                    <div class="upload-section">
                        <p style="margin-bottom: 1rem; color: var(--dark);">
                            Setelah melakukan pembayaran, upload bukti transfer Anda:
                        </p>
                        
                        <form id="uploadForm" method="POST" enctype="multipart/form-data">
                            <div id="upload-area" class="upload-area">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="upload-text">
                                    Klik atau drag & drop file di sini
                                </div>
                                <div class="upload-hint">
                                    Format: JPG, PNG (Maks. 5MB)
                                </div>
                                <input type="file" id="file-input" name="bukti_bayar" class="file-input" accept="image/*" required>
                            </div>
                            
                            <div id="preview-container" class="preview-container">
                                <img id="preview-image" class="preview-image" src="" alt="Preview">
                                <div id="file-name" style="text-align: center; color: #6c757d; margin-bottom: 1rem;"></div>
                                <button type="button" id="remove-file" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Hapus File
                                </button>
                            </div>
                            
                            <button type="submit" id="submit-btn" class="btn btn-primary" disabled>
                                <i class="fas fa-upload"></i> Upload Bukti Pembayaran
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const uploadArea = document.getElementById('upload-area');
            const fileInput = document.getElementById('file-input');
            const previewContainer = document.getElementById('preview-container');
            const previewImage = document.getElementById('preview-image');
            const fileName = document.getElementById('file-name');
            const removeFileBtn = document.getElementById('remove-file');
            const submitBtn = document.getElementById('submit-btn');
            const uploadForm = document.getElementById('upload-form');
            const countdownTimer = document.getElementById('countdown-timer');
            
            let uploadedFile = null;
            let countdownTime = <?= $minutes_remaining * 60 + $seconds_remaining ?>;
            
            // Countdown timer
            function updateCountdown() {
                if (countdownTime <= 0) {
                    clearInterval(countdownInterval);
                    countdownTimer.innerHTML = '<span style="color: #e74c3c;">WAKTU HABIS</span>';
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-clock"></i> Waktu Habis';
                    
                    // Reload page after 3 seconds to update status
                    setTimeout(() => location.reload(), 3000);
                    return;
                }
                
                const minutes = Math.floor(countdownTime / 60);
                const seconds = countdownTime % 60;
                
                countdownTimer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                countdownTime--;
            }
            
            const countdownInterval = setInterval(updateCountdown, 1000);
            
            // File upload handling
            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });
            
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', function() {
                uploadArea.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                
                if (e.dataTransfer.files.length) {
                    handleFile(e.dataTransfer.files[0]);
                }
            });
            
            fileInput.addEventListener('change', function() {
                if (this.files.length) {
                    handleFile(this.files[0]);
                }
            });
            
            function handleFile(file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Harap unggah file JPG, PNG, atau GIF.');
                    return;
                }
                
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 5MB.');
                    return;
                }
                
                uploadedFile = file;
                fileName.textContent = file.name;
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.classList.add('active');
                };
                reader.readAsDataURL(file);
                
                // Enable submit button
                submitBtn.disabled = false;
            }
            
            // Remove file
            removeFileBtn.addEventListener('click', function() {
                uploadedFile = null;
                fileInput.value = '';
                previewContainer.classList.remove('active');
                submitBtn.disabled = true;
            });
            
            // Form submission
            const uploadFormElement = document.getElementById('uploadForm');
            if (uploadFormElement) {
                uploadFormElement.addEventListener('submit', function(e) {
                    if (!uploadedFile) {
                        e.preventDefault();
                        alert('Harap pilih file bukti pembayaran terlebih dahulu.');
                        return;
                    }
                    
                    // Show loading
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupload...';
                });
            }
            
            // Auto refresh if time is critical
            if (countdownTime < 300) { // Less than 5 minutes
                setTimeout(() => location.reload(), 60000); // Refresh every minute
            }
        });
    </script>
</body>
</html>