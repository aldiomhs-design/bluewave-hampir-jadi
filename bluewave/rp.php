<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Process edit request
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_transaksi'])) {
    $order_id = (int)$_POST['order_id'];
    $new_user_id = $conn->real_escape_string($_POST['user_id']);
    $new_server_id = $conn->real_escape_string($_POST['server_id']);
    $mark_paid = isset($_POST['mark_paid']) ? 1 : 0;
    
    // Check if order belongs to user and status is waiting
    $check_sql = "SELECT * FROM topup WHERE id = $order_id AND id_akun = $user_id AND status = 'waiting'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $set_parts = [];
        $set_parts[] = "user_id = '$new_user_id'";
        $set_parts[] = "server_id = '$new_server_id'";
        // Do not change status here. User will be redirected to bayar.php to upload/confirm payment.

        $update_sql = "UPDATE topup SET " . implode(', ', $set_parts) . " WHERE id = $order_id";
        if ($conn->query($update_sql)) {
            // Setelah update berhasil, kembali ke halaman pembayaran untuk pesanan ini
            header("Location: bayar.php?order_id=$order_id");
            exit();
        } else {
            $message = "❌ Gagal memperbarui data!";
            $message_type = 'error';
        }
    }
    }

// Get user's orders
$sql = "SELECT * FROM topup WHERE id_akun = $user_id ORDER BY created_at DESC";
$result = $conn->query($sql);

// Statistics
$total_orders = $result->num_rows;
$waiting_orders = $conn->query("SELECT COUNT(*) as total FROM topup WHERE id_akun = $user_id AND status = 'waiting'")->fetch_assoc()['total'];
$success_orders = $conn->query("SELECT COUNT(*) as total FROM topup WHERE id_akun = $user_id AND status = 'success'")->fetch_assoc()['total'];
$failed_orders = $conn->query("SELECT COUNT(*) as total FROM topup WHERE id_akun = $user_id AND status IN ('failed', 'expired')")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - BlueWave Store</title>
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
            --radius: 10px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
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
            gap: 1.5rem;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(30, 60, 114, 0.4);
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background: #f0f7ff;
        }

        .table-container {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, #f0f7ff 0%, #e3f2fd 100%);
        }

        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            color: var(--primary);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            transition: all 0.3s;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            text-align: center;
            min-width: 100px;
        }

        .status-waiting {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-pending {
            background: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }

        .status-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .status-expired {
            background: #e2e8f0;
            color: #4a5568;
            border: 1px solid #cbd5e0;
        }

        .edit-btn {
            background: var(--warning);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .edit-btn:hover {
            background: #e67e22;
            transform: translateY(-1px);
        }

        .edit-btn:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            transform: none;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-title {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .modal-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 600;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        @media (max-width: 992px) {
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
            
            .navbar {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            th, td {
                padding: 0.75rem 1rem;
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
                <span><?= htmlspecialchars($username) ?></span>
            </div>
            
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
                <a href="tokenhok.php"><i class="fas fa-shopping-cart"></i> Pesan Baru</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="page-title">
            <h1><i class="fas fa-history"></i> Riwayat Pesanan</h1>
            <p>Lihat dan kelola semua pesanan Anda</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>">
                <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_orders ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $waiting_orders ?></div>
                <div class="stat-label">Menunggu Bayar</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $success_orders ?></div>
                <div class="stat-label">Berhasil</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $failed_orders ?></div>
                <div class="stat-label">Gagal/Expired</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="tokenhok.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Buat Pesanan Baru
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Kembali ke Beranda
            </a>
        </div>

        <!-- Orders Table -->
        <div class="table-container">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Order ID</th>
                            <th>Game</th>
                            <th>User ID</th>
                            <th>Server</th>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $counter++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['order_id']) ?></strong>
                                    <?php if ($row['status'] == 'waiting'): ?>
                                        <br><small style="color: #f39c12; font-size: 0.8rem;">
                                            <i class="fas fa-clock"></i> 
                                            <?php 
                                                $expired = new DateTime($row['expired_time']);
                                                $now = new DateTime();
                                                $diff = $now->diff($expired);
                                                echo $diff->h * 60 + $diff->i . ' menit';
                                            ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['game']) ?></td>
                                <td>
                                    <span class="user-id-display"><?= htmlspecialchars($row['user_id']) ?></span>
                                </td>
                                <td>
                                    <span class="server-display"><?= htmlspecialchars($row['server_id']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['produk']) ?></td>
                                <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <?php
                                    $status_class = 'status-' . $row['status'];
                                    $status_text = ucfirst($row['status']);
                                    ?>
                                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                                <td>
                                    <button class="edit-btn" 
                                            onclick="openEditModal(
                                                <?= $row['id'] ?>, 
                                                '<?= htmlspecialchars($row['user_id']) ?>', 
                                                '<?= htmlspecialchars($row['server_id']) ?>',
                                                '<?= $row['status'] ?>'
                                            )"
                                            <?= $row['status'] !== 'waiting' ? 'disabled' : '' ?>
                                            title="<?= $row['status'] !== 'waiting' ? 'Hanya status waiting yang bisa diedit' : 'Edit data' ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h3>Belum ada riwayat pesanan</h3>
                    <p>Mulai dengan membuat pesanan pertama Anda!</p>
                    <a href="tokenhok.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-shopping-cart"></i> Buat Pesanan
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">
                <i class="fas fa-edit"></i> Edit Data Transaksi
            </div>
            
            <form id="editForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_transaksi" value="1">
                <input type="hidden" id="editOrderId" name="order_id">
                
                <div class="form-group">
                    <label class="form-label">User ID Game:</label>
                    <input type="text" id="editUserId" name="user_id" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Server ID:</label>
                    <input type="text" id="editServerId" name="server_id" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" id="editMarkPaid" name="mark_paid" value="1">
                        &nbsp;Tandai sebagai sudah bayar (kirim ke proses/verifikasi)
                    </label>
                </div>
                <!-- Upload removed: bukti pembayaran is handled on pembayaran page -->

                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Store original data
        let originalUserId = '';
        let originalServerId = '';
        
        // Open edit modal
        function openEditModal(orderId, userId, serverId, status) {
            if (status !== 'waiting') {
                alert('Hanya transaksi dengan status "waiting" yang dapat diedit.');
                return;
            }
            
            // Save original data
            originalUserId = userId;
            originalServerId = serverId;
            
            // Fill form with current data
            document.getElementById('editOrderId').value = orderId;
            document.getElementById('editUserId').value = userId;
            document.getElementById('editServerId').value = serverId;
                // reset mark paid checkbox
                if (document.getElementById('editMarkPaid')) {
                    document.getElementById('editMarkPaid').checked = false;
                }
            
            // Show modal
            document.getElementById('editModal').style.display = 'flex';
        }
        
        // Close edit modal
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Handle form submission
        document.getElementById('editForm').addEventListener('submit', function(e) {
            const userId = document.getElementById('editUserId').value.trim();
            const serverId = document.getElementById('editServerId').value.trim();
            
            if (!userId || !serverId) {
                e.preventDefault();
                alert('User ID dan Server ID tidak boleh kosong!');
                return;
            }
            
                // Check if data actually changed or user marked as paid
                const markPaid = document.getElementById('editMarkPaid').checked;
                if (userId === originalUserId && serverId === originalServerId && !markPaid) {
                    e.preventDefault();
                    alert('Tidak ada perubahan data!');
                    return;
                }
            
            // Confirm changes
            if (!confirm('Apakah Anda yakin ingin mengubah data ini?')) {
                e.preventDefault();
            }
        });
        
        // Close modal on outside click or ESC
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });
    </script>
</body>
</html>