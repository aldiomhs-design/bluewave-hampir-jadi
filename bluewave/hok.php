<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$idAkun = $_SESSION['user_id'];

// Proses form jika ada POST
if (isset($_POST['pesan'])) {
    $game = $_POST['game'] ?? '';
    $userId = $_POST['userId'] ?? '';
    $serverId = $_POST['serverId'] ?? '';
    $namaProduk = $_POST['selectedProductName'] ?? '';
    $hargaProduk = (int)($_POST['selectedProductPrice'] ?? 0);
    $metode = $_POST['selectedPayment'] ?? '';

    // Generate order_id
    $order_id = 'BW' . date('YmdHis') . rand(100, 999);

    // Hitung expired time
    $expired_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Handle file upload (optional)
    $buktiPembayaran = null;
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/bukti_pembayaran/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileExt = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('bukti_') . '.' . $fileExt;
        $uploadPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $uploadPath)) {
            $buktiPembayaran = 'uploads/bukti_pembayaran/' . $fileName;
        }
    }

    // Insert into DB
    $gameEsc = $conn->real_escape_string($game);
    $userIdEsc = $conn->real_escape_string($userId);
    $serverIdEsc = $conn->real_escape_string($serverId);
    $produkEsc = $conn->real_escape_string($namaProduk);
    $metodeEsc = $conn->real_escape_string($metode);

    if ($buktiPembayaran) {
        $buktiEsc = $conn->real_escape_string($buktiPembayaran);
        $query = "INSERT INTO topup (order_id, game, user_id, id_akun, server_id, produk, harga, pembayaran, bukti_bayar, status, expired_time, tanggal) 
                            VALUES ('$order_id','$gameEsc','$userIdEsc','$idAkun','$serverIdEsc','$produkEsc',$hargaProduk,'$metodeEsc','$buktiEsc','waiting','$expired_time', NOW())";
    } else {
        $query = "INSERT INTO topup (order_id, game, user_id, id_akun, server_id, produk, harga, pembayaran, status, expired_time, tanggal) 
                            VALUES ('$order_id','$gameEsc','$userIdEsc','$idAkun','$serverIdEsc','$produkEsc',$hargaProduk,'$metodeEsc','waiting','$expired_time', NOW())";
    }

    if ($conn->query($query)) {
        $newId = $conn->insert_id;
        header("Location: bayar.php?order_id=$newId");
        exit();
    } else {
        $error = 'Gagal membuat pesanan: ' . $conn->error;
    }
}
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>BlueWaves STORE — Honor of Kings</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --secondary: #06b6d4;
            --dark: #0f172a;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --radius: 12px;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a4b78 0%, #4ab3ff 100%);
            background-attachment: fixed;
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        header {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-mark {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary-dark));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            box-shadow: var(--shadow);
        }

        main {
            padding: 1.5rem;
            max-width: 1000px;
            margin: 0 auto;
            padding-bottom: 120px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
            max-height: 420px;
            overflow: auto;
            padding: 0.25rem;
        }

        .product {
            padding: 1.25rem 1rem;
            background: white;
            border-radius: var(--radius);
            border: 2px solid var(--gray-light);
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .product.selected { box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); border-color: var(--primary); }

        .product-name { font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--dark); }
        .product-price { color: var(--primary-dark); font-weight: 700; font-size: 1rem; }

        .payment-item { padding: 1rem; display: flex; align-items: center; border-radius: var(--radius); background: #f8fcff; border: 1.5px solid var(--gray-light); cursor: pointer; margin-bottom: 0.75rem; }
        .payment-item.selected { border-color: var(--primary); background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); }

        .bottom-bar { position: fixed; bottom: 0; left: 0; right: 0; background: white; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1); z-index: 100; }

        .total-section { display: flex; flex-direction: column; }
        .total-label { font-size: 0.875rem; color: var(--gray); }
        .total-amount { font-size: 1.5rem; font-weight: 700; color: var(--primary-dark); }

        .btn { padding: 1rem 2rem; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border-radius: var(--radius); font-weight: 700; cursor: pointer; border: none; transition: var(--transition); box-shadow: var(--shadow); display: flex; align-items: center; gap: 0.5rem; }
        .btn-secondary { background: var(--gray); }

        @media (max-width: 768px) { .product-list { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); } .bottom-bar { flex-direction: column; gap: 1rem; align-items: stretch; } }
    </style>
</head>

<body>

    <header>
        <div class="logo">
            <div class="logo-mark">BW</div>
            <div>
                <h1 style="color:white; margin:0; font-size:1.125rem;">BlueWaves</h1>
                <div style="color:rgba(255,255,255,0.85); font-size:0.85rem;">Top-up Honor of Kings</div>
            </div>
        </div>
        <div style="margin-left:auto; display:flex; gap:0.75rem; align-items:center;">
            <a href="index.php" style="color:white; text-decoration:none; font-weight:600;">← Kembali Ke Beranda</a>
        </div>
    </header>

    <main>
        <form action="" method="post" id="orderForm" enctype="multipart/form-data">
            <input type="hidden" name="selectedProductName" id="selectedProductName">
            <input type="hidden" name="selectedProductPrice" id="selectedProductPrice">
            <input type="hidden" name="selectedPayment" id="selectedPayment">
            <input type="hidden" name="pesan" id="pesanInput" value="0">

            <div class="card">
                <h3>Panduan & Cara Top-up</h3>
                <div style="display:flex; gap:1rem; align-items:center; margin-top:1rem;">
                    <div style="flex:0 0 180px;">
                        <!-- sample image: replace `img/topup_example.png` with your actual image -->
                        <img src="img/topup_example.png" alt="Panduan Top-up" style="width:100%; border-radius:8px; border:1px solid #e2e8f0;" onerror="this.style.display='none'">
                    </div>
                    <div style="flex:1;">
                        <h4 style="margin:0 0 8px 0;">Cara Melakukan Top-up</h4>
                        <ol style="margin:0 0 0 18px; color:var(--gray);">
                            <li>Pilih produk yang ingin Anda beli (contoh: 50 Tokens).</li>
                            <li>Pilih metode pembayaran (QRIS / E-Wallet / Bank Transfer).</li>
                            <li>Masukkan User ID dan Server ID game Anda pada kolom berikut.</li>
                            <li>Klik <strong>BELI SEKARANG</strong> dan unggah bukti pembayaran jika diminta.</li>
                            <li>Tunggu verifikasi admin (1x24 jam).</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3>Masukkan User ID & Server ID HOK Anda</h3>
                <div style="display:flex; gap:1rem; margin-top:1rem;">
                    <div style="flex:1;">
                        <label style="display:block; font-weight:600; margin-bottom:0.5rem;">User ID</label>
                        <input id="userId" name="userId" class="input-field" placeholder="Masukkan User ID" style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid #e2e8f0;">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block; font-weight:600; margin-bottom:0.5rem;">Server ID</label>
                        <input id="serverId" name="serverId" class="input-field" placeholder="Masukkan Server ID" style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid #e2e8f0;">
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Pilih Nominal</h2>
                <div class="product-list" id="products"></div>
            </div>

            <div class="card">
                <h2>Pilih Pembayaran</h2>
                <div class="payment-item" onclick="selectPayment('QRIS', this)">
                    <div class="payment-name">QRIS</div>
                    <div class="payment-desc" style="margin-left:auto;color:var(--gray);">All Payment</div>
                </div>
                <div class="payment-item" onclick="toggleWalletList()">
                    <div class="payment-name">E-Wallet</div>
                    <div class="payment-desc" style="margin-left:auto;color:var(--gray);">Pilih</div>
                </div>

                <div id="walletList" style="display:none; margin-top:0.5rem;">
                    <div class="payment-item" onclick="selectPayment('Gopay', this)"><div class="payment-name">Gopay</div></div>
                    <div class="payment-item" onclick="selectPayment('DANA', this)"><div class="payment-name">DANA</div></div>
                    <div class="payment-item" onclick="selectPayment('ShopeePay', this)"><div class="payment-name">ShopeePay</div></div>
                    <div class="payment-item" onclick="selectPayment('OVO', this)"><div class="payment-name">OVO</div></div>
                </div>
            </div>

            <input type="file" name="bukti_pembayaran" id="buktiPembayaran" class="file-input" accept="image/*" style="display:none;">

            <div style="height:120px"></div>

            <div class="bottom-bar">
                <div class="total-section">
                    <div class="total-label">Total</div>
                    <div id="totalRp" class="total-amount">Rp 0</div>
                </div>
                <button type="button" id="buyNow" class="btn">BELI SEKARANG <span style="font-size: 1.2rem; margin-left:8px;">▶</span></button>
            </div>
        </form>
    </main>

    <script>
        const sample = [
            [50, '50 Tokens', 9000],
            [100, '100 Tokens', 17500],
            [250, '250 Tokens', 42000],
            [500, '500 Tokens', 82000],
            [1000, '1000 Tokens', 160000],
            [2000, '2000 Tokens', 315000],
            [5000, '5000 Tokens', 760000]
        ];

        let selectedProduct = null;
        let selectedPayment = null;

        const productsEl = document.getElementById('products');

        function render() {
            productsEl.innerHTML = '';
            sample.forEach((p) => {
                const box = document.createElement('div');
                box.className = 'product';
                box.innerHTML = `\n          <div class="product-name">${p[1]}</div>\n          <div class="product-price">Rp ${numberWithCommas(p[2])}</div>\n        `;
                box.onclick = () => {
                    document.querySelectorAll('.product').forEach(x => x.classList.remove('selected'));
                    box.classList.add('selected');
                    selectedProduct = p;
                    document.getElementById('selectedProductName').value = p[1];
                    document.getElementById('selectedProductPrice').value = p[2];
                    updateTotal();
                };
                productsEl.appendChild(box);
            });
        }
        render();

        function numberWithCommas(x) { return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); }
        function updateTotal() { document.getElementById('totalRp').textContent = selectedProduct ? "Rp " + numberWithCommas(selectedProduct[2]) : "Rp 0"; }
        function clearPaymentSelection() { document.querySelectorAll('.payment-item').forEach(i => i.classList.remove('selected')); }
        function selectPayment(name, element) { clearPaymentSelection(); element.classList.add('selected'); selectedPayment = name; document.getElementById('selectedPayment').value = name; }
        function toggleWalletList() { const list = document.getElementById('walletList'); list.style.display = list.style.display === 'none' ? 'block' : 'none'; }

        function submitOrder() {
            // set pesan flag then submit
            document.getElementById('pesanInput').value = '1';
            document.getElementById('orderForm').submit();
        }

        document.getElementById('buyNow').onclick = () => {
            const uid = document.getElementById('userId').value.trim();
            const sid = document.getElementById('serverId').value.trim();

            if (!uid) return alert('Masukkan UserID dahulu');
            if (!sid) return alert('Masukkan Server ID dahulu');
            if (!selectedProduct) return alert('Pilih produk terlebih dahulu');
            if (!selectedPayment) return alert('Pilih metode pembayaran');

            submitOrder();
        };
    </script>

</body>
</html>