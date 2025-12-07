<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['order_id'])) {
        $order_id = (int)$_POST['order_id'];
        $action = $_POST['action'];
        $admin_notes = $conn->real_escape_string($_POST['admin_notes'] ?? '');
        
        if ($action === 'confirm') {
            $status = 'success';
            $message = "✅ Pesanan #$order_id berhasil dikonfirmasi!";
        } elseif ($action === 'reject') {
            $status = 'failed';
            $message = "❌ Pesanan #$order_id telah ditolak!";
        } elseif ($action === 'processing') {
            $status = 'processing';
            $message = "🔄 Pesanan #$order_id diproses!";
        }
        
        // Update order
        $update_sql = "UPDATE topup SET status = '$status', admin_notes = '$admin_notes', processed_by = $admin_id, processed_at = NOW() WHERE id = $order_id";
        
        if ($conn->query($update_sql)) {
            $success = $message;
        } else {
            $error = "Gagal update status: " . $conn->error;
        }
    }
}

// Get all orders with user info
$sql = "SELECT t.*, u.username 
    FROM topup t 
    LEFT JOIN users u ON t.id_akun = u.id 
    ORDER BY t.created_at DESC";
$result = $conn->query($sql);

// Statistics
$total_orders = $result->num_rows;
$waiting_orders = $conn->query("SELECT COUNT(*) as total FROM topup WHERE status = 'waiting'")->fetch_assoc()['total'];
$pending_orders = $conn->query("SELECT COUNT(*) as total FROM topup WHERE status = 'pending'")->fetch_assoc()['total'];
$success_orders = $conn->query("SELECT COUNT(*) as total FROM topup WHERE status = 'success'")->fetch_assoc()['total'];
$processing_orders = $conn->query("SELECT COUNT(*) as total FROM topup WHERE status = 'processing'")->fetch_assoc()['total'];
$failed_orders = $conn->query("SELECT COUNT(*) as total FROM topup WHERE status IN ('failed', 'expired')")->fetch_assoc()['total'];

// Total revenue
$revenue_result = $conn->query("SELECT SUM(harga) as total FROM topup WHERE status = 'success'");
$total_revenue = $revenue_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BlueWave Store</title>
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

        .admin-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .admin-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .container {
            max-width: 1600px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-title {
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
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card.primary {
            border-left: 4px solid var(--primary);
        }

        .stat-card.success {
            border-left: 4px solid var(--success);
        }

        .stat-card.warning {
            border-left: 4px solid var(--warning);
        }

        .stat-card.danger {
            border-left: 4px solid var(--danger);
        }

        .stat-card.accent {
            border-left: 4px solid var(--accent);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-card.primary .stat-number { color: var(--primary); }
        .stat-card.success .stat-number { color: var(--success); }
        .stat-card.warning .stat-number { color: var(--warning); }
        .stat-card.danger .stat-number { color: var(--danger); }
        .stat-card.accent .stat-number { color: var(--accent); }

        .stat-label {
            color: #6c757d;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            padding: 1.5rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .filter-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 1rem;
            transition: all 0.3s;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--accent);
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

        .status-processing {
            background: #e0ccff;
            color: #5b2d86;
            border: 1px solid #d4b5ff;
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

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 0.5rem 0.75rem;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-confirm {
            background: var(--success);
            color: white;
        }

        .btn-confirm:hover {
            background: #219653;
            transform: translateY(-1px);
        }

        .btn-reject {
            background: var(--danger);
            color: white;
        }

        .btn-reject:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }

        .btn-processing {
            background: var(--accent);
            color: white;
        }

        .btn-processing:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        .btn-view {
            background: var(--primary);
            color: white;
        }

        .btn-view:hover {
            background: var(--secondary);
            transform: translateY(-1px);
        }

        .bukti-bayar {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid #e2e8f0;
        }

        .bukti-bayar:hover {
            transform: scale(1.5);
            border-color: var(--accent);
            z-index: 100;
            position: relative;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .modal-image {
            max-width: 90%;
            max-height: 90%;
            border-radius: var(--radius);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 3rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .close-modal:hover {
            color: var(--accent);
        }

        .action-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1500;
            align-items: center;
            justify-content: center;
        }

        .action-modal-content {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .action-modal-title {
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

        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 1rem;
            min-height: 100px;
            resize: vertical;
        }

        .form-textarea:focus {
            outline: none;
            border-color: var(--accent);
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

        @media (max-width: 1200px) {
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 1200px;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .filters {
                flex-direction: column;
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
            BlueWave Admin
        </div>
        
        <div class="admin-menu">
            <div class="admin-info">
                <i class="fas fa-user-shield"></i>
                <span><?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
            
            <a href="index.php" style="color: white; text-decoration: none; padding: 8px 15px; background: rgba(255,255,255,0.2); border-radius: 5px;">
                <i class="fas fa-home"></i> User View
            </a>
            
            <a href="logout.php" style="color: white; text-decoration: none; padding: 8px 15px; background: rgba(255,255,255,0.2); border-radius: 5px;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="page-title">
            <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
            <p>Kelola semua transaksi dan pesanan</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-number"><?= $total_orders ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-number"><?= $pending_orders ?></div>
                <div class="stat-label">Menunggu Verifikasi</div>
            </div>
            
            <div class="stat-card accent">
                <div class="stat-number"><?= $processing_orders ?></div>
                <div class="stat-label">Diproses</div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-number"><?= $success_orders ?></div>
                <div class="stat-label">Berhasil</div>
            </div>
            
            <div class="stat-card danger">
                <div class="stat-number"><?= $failed_orders + $waiting_orders ?></div>
                <div class="stat-label">Gagal + Waiting</div>
            </div>
            
            <div class="stat-card" style="border-left: 4px solid #9b59b6;">
                <div class="stat-number">Rp <?= number_format($total_revenue, 0, ',', '.') ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <div class="filter-group">
                <label class="filter-label">Status:</label>
                <select class="filter-select" id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="waiting">Waiting</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Game:</label>
                <select class="filter-select" id="gameFilter">
                    <option value="">Semua Game</option>
                    <?php
                    $games = $conn->query("SELECT DISTINCT game FROM topup ORDER BY game");
                    while ($game = $games->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($game['game']) ?>">
                            <?= htmlspecialchars($game['game']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Tanggal:</label>
                <input type="date" class="filter-select" id="dateFilter">
            </div>
        </div>

        <!-- Orders Table -->
        <div class="table-container">
            <?php if ($result->num_rows > 0): ?>
                <table id="ordersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Game</th>
                            <th>User ID</th>
                            <th>Server</th>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Bukti Bayar</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr data-status="<?= $row['status'] ?>" 
                                data-game="<?= htmlspecialchars($row['game']) ?>"
                                data-date="<?= date('Y-m-d', strtotime($row['tanggal'])) ?>">
                                <td><?= $counter++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['order_id']) ?></strong>
                                    <?php if ($row['admin_notes']): ?>
                                        <br><small style="color: #6c757d; font-size: 0.8rem;">
                                            <i class="fas fa-sticky-note"></i> <?= htmlspecialchars(substr($row['admin_notes'], 0, 30)) ?>...
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($row['username']) ?></div>
                                    <?php if (!empty($row['email'])): ?>
                                        <small style="color: #6c757d; font-size: 0.8rem;">
                                            <?= htmlspecialchars($row['email']) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['game']) ?></td>
                                <td><?= htmlspecialchars($row['user_id']) ?></td>
                                <td><?= htmlspecialchars($row['server_id']) ?></td>
                                <td><?= htmlspecialchars($row['produk']) ?></td>
                                <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if (!empty($row['bukti_bayar']) && file_exists($row['bukti_bayar'])): ?>
                                        <img src="<?= htmlspecialchars($row['bukti_bayar']) ?>" 
                                             alt="Bukti Bayar" 
                                             class="bukti-bayar"
                                             onclick="viewImage('<?= htmlspecialchars($row['bukti_bayar']) ?>')">
                                    <?php else: ?>
                                        <span style="color: #6c757d;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = 'status-' . $row['status'];
                                    $status_text = ucfirst($row['status']);
                                    ?>
                                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <button class="action-btn btn-confirm" 
                                                    onclick="showActionModal('confirm', <?= $row['id'] ?>)">
                                                <i class="fas fa-check"></i> Confirm
                                            </button>
                                            <button class="action-btn btn-processing" 
                                                    onclick="showActionModal('processing', <?= $row['id'] ?>)">
                                                <i class="fas fa-cog"></i> Process
                                            </button>
                                            <button class="action-btn btn-reject" 
                                                    onclick="showActionModal('reject', <?= $row['id'] ?>)">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        <?php elseif ($row['status'] == 'processing'): ?>
                                            <button class="action-btn btn-confirm" 
                                                    onclick="showActionModal('confirm', <?= $row['id'] ?>)">
                                                <i class="fas fa-check"></i> Complete
                                            </button>
                                        <?php else: ?>
                                            <button class="action-btn btn-view" 
                                                    onclick="viewOrder(<?= $row['id'] ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem 2rem; color: #6c757d;">
                    <i class="fas fa-database" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                    <h3>Belum ada data pesanan</h3>
                    <p>Tidak ada transaksi yang ditemukan</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <span class="close-modal" onclick="closeImageModal()">&times;</span>
        <img id="modalImage" class="modal-image">
    </div>

    <!-- Action Modal -->
    <div id="actionModal" class="action-modal">
        <div class="action-modal-content">
            <div class="action-modal-title" id="actionModalTitle"></div>
            
            <form id="actionForm" method="POST">
                <input type="hidden" name="order_id" id="actionOrderId">
                <input type="hidden" name="action" id="actionType">
                
                <div class="form-group">
                    <label class="form-label">Catatan Admin (opsional):</label>
                    <textarea name="admin_notes" class="form-textarea" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeActionModal()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="action-btn" id="actionSubmitBtn">
                        <i class="fas fa-check"></i> Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Image modal functions
        function viewImage(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').style.display = 'flex';
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Action modal functions
        function showActionModal(action, orderId) {
            const modalTitle = document.getElementById('actionModalTitle');
            const actionOrderId = document.getElementById('actionOrderId');
            const actionType = document.getElementById('actionType');
            const actionSubmitBtn = document.getElementById('actionSubmitBtn');
            
            let title = '';
            let btnText = '';
            let btnClass = '';
            
            switch(action) {
                case 'confirm':
                    title = 'Konfirmasi Pesanan';
                    btnText = 'Konfirmasi';
                    btnClass = 'btn-confirm';
                    break;
                case 'processing':
                    title = 'Proses Pesanan';
                    btnText = 'Proses';
                    btnClass = 'btn-processing';
                    break;
                case 'reject':
                    title = 'Tolak Pesanan';
                    btnText = 'Tolak';
                    btnClass = 'btn-reject';
                    break;
            }
            
            modalTitle.innerHTML = `<i class="fas fa-${action === 'confirm' ? 'check' : action === 'processing' ? 'cog' : 'times'}"></i> ${title} #${orderId}`;
            actionOrderId.value = orderId;
            actionType.value = action;
            
            actionSubmitBtn.className = 'action-btn ' + btnClass;
            actionSubmitBtn.innerHTML = `<i class="fas fa-${action === 'confirm' ? 'check' : action === 'processing' ? 'cog' : 'times'}"></i> ${btnText}`;
            
            document.getElementById('actionModal').style.display = 'flex';
        }

        function closeActionModal() {
            document.getElementById('actionModal').style.display = 'none';
        }

        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('gameFilter').addEventListener('change', filterTable);
        document.getElementById('dateFilter').addEventListener('change', filterTable);

        function filterTable() {
            const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
            const gameFilter = document.getElementById('gameFilter').value.toLowerCase();
            const dateFilter = document.getElementById('dateFilter').value;
            
            const rows = document.querySelectorAll('#ordersTable tbody tr');
            
            rows.forEach(row => {
                const status = row.getAttribute('data-status').toLowerCase();
                const game = row.getAttribute('data-game').toLowerCase();
                const date = row.getAttribute('data-date');
                
                let show = true;
                
                if (statusFilter && status !== statusFilter) {
                    show = false;
                }
                
                if (gameFilter && game !== gameFilter) {
                    show = false;
                }
                
                if (dateFilter && date !== dateFilter) {
                    show = false;
                }
                
                row.style.display = show ? '' : 'none';
            });
        }

        // View order details
        function viewOrder(orderId) {
            // In a real implementation, this would open a detail modal
            alert('Detail untuk pesanan #' + orderId + ' akan ditampilkan di sini.');
        }

        // Close modals on outside click
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        document.getElementById('actionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeActionModal();
            }
        });

        // Close modals on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
                closeActionModal();
            }
        });

        // Auto refresh every 60 seconds
        setTimeout(() => location.reload(), 60000);
    </script>
</body>
</html>