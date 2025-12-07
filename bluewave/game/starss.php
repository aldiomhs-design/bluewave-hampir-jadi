<?php
require_once "database.php";
session_start();

$idAkun = $_SESSION['user_id'];

if (!isset($_SESSION["username"])) {
  echo "<script>
  alert('Kamu Harus Login Untuk Akses Halaman Ini!');
  window.location.href='index.php';
  </script>";
}

if (isset($_POST["pesan"])) {
  $game = $_POST["game"];
  $userId = $_POST['userId'];
  $serverId = $_POST['serverId'];
  $namaProduk = $_POST['selectedProductName'];
  $hargaProduk = $_POST['selectedProductPrice'];
  $metode = $_POST['selectedPayment'];

  // Handle file upload
  $buktiPembayaran = null;
  if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'bukti_pembayaran/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $fileExtension = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $uploadFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $uploadFile)) {
      $buktiPembayaran = $uploadFile;
    }
  }

  if (empty($game) || empty($userId) || empty($serverId)) {
    echo "<script>alert('Semua Kolom Wajib Di isi!');</script>";
  } else {
    if ($buktiPembayaran) {
      $query = "INSERT INTO topup (game, user_id, id_akun, server_id, produk, harga, pembayaran, bukti_bayar) VALUES ('$game','$userId','$idAkun','$serverId','$namaProduk','$hargaProduk','$metode','$buktiPembayaran')";
    } else {
      $query = "INSERT INTO topup (game, user_id, id_akun, server_id, produk, harga, pembayaran) VALUES ('$game','$userId','$idAkun','$serverId','$namaProduk','$hargaProduk','$metode')";
    }

    $result = $conn->query($query);

    if ($conn->affected_rows > 0) {
      echo "<script>alert('Pesanan Berhasil Dibuat');</script>";
    } else {
      echo "<script>alert('Pesanan Gagal Dibuat');</script>";
    }
  }
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BlueWaves STORE — Super Sus</title>

  <!-- Google Fonts -->
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

    /* HEADER */
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

    .logo h1 {
      margin: 0;
      font-size: 1.25rem;
      font-weight: 700;
    }

    .logo div {
      font-size: 0.75rem;
      opacity: 0.9;
    }

    .icons {
      margin-left: auto;
      display: flex;
      gap: 0.75rem;
      align-items: center;
    }

    .icon {
      width: 36px;
      height: 36px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      transition: var(--transition);
      cursor: pointer;
    }

    .icon:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: translateY(-2px);
    }

    .back-link {
      color: white;
      text-decoration: none;
      font-weight: 500;
      padding: 0.5rem 1rem;
      border-radius: var(--radius);
      transition: var(--transition);
    }

    .back-link:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    /* MAIN CONTENT */
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

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
    }

    .card h2 {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: var(--dark);
    }

    .card h3 {
      font-size: 1.125rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--dark);
    }

    /* USER INPUTS */
    .user-inputs {
      display: flex;
      gap: 1rem;
    }

    .input-group {
      flex: 1;
    }

    .input-label {
      display: block;
      font-weight: 500;
      margin-bottom: 0.5rem;
      color: var(--dark);
    }

    .input-field {
      width: 100%;
      padding: 0.875rem 1rem;
      border: 1.5px solid var(--gray-light);
      border-radius: var(--radius);
      font-size: 1rem;
      transition: var(--transition);
      background: white;
    }

    .input-field:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    /* PRODUCT LIST */
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

    .product::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: var(--primary);
      transform: scaleX(0);
      transition: var(--transition);
    }

    .product:hover {
      transform: translateY(-5px);
      border-color: var(--primary-light);
      box-shadow: var(--shadow);
    }

    .product:hover::before {
      transform: scaleX(1);
    }

    .product.selected {
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .product.selected::before {
      transform: scaleX(1);
      background: var(--primary);
    }

    .product-name {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--dark);
    }

    .product-price {
      color: var(--primary-dark);
      font-weight: 700;
      font-size: 1rem;
    }

    /* PAYMENT */
    .payment-item {
      padding: 1rem;
      display: flex;
      align-items: center;
      border-radius: var(--radius);
      background: #f8fcff;
      border: 1.5px solid var(--gray-light);
      cursor: pointer;
      margin-bottom: 0.75rem;
      transition: var(--transition);
      position: relative;
    }

    .payment-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: var(--primary);
      transform: scaleX(0);
      transition: var(--transition);
    }

    .payment-item:hover {
      transform: translateY(-2px);
      border-color: var(--primary-light);
      box-shadow: var(--shadow);
    }

    .payment-item:hover::before {
      transform: scaleX(1);
    }

    .payment-item.selected {
      border-color: var(--primary);
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .payment-item.selected::before {
      transform: scaleX(1);
      background: var(--primary);
    }

    .payment-name {
      font-weight: 600;
    }

    .payment-desc {
      margin-left: auto;
      color: var(--gray);
      font-size: 0.875rem;
    }

    /* WALLET LIST */
    #walletList {
      margin-top: 0.5rem;
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      display: none;
    }

    /* BOTTOM BAR */
    .bottom-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: white;
      padding: 1rem 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
      z-index: 100;
    }

    .total-section {
      display: flex;
      flex-direction: column;
    }

    .total-label {
      font-size: 0.875rem;
      color: var(--gray);
    }

    .total-amount {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary-dark);
    }

    .btn {
      padding: 1rem 2rem;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: white;
      border-radius: var(--radius);
      font-weight: 700;
      cursor: pointer;
      border: none;
      transition: var(--transition);
      box-shadow: var(--shadow);
      display: flex;
      align-items: center;
      gap: 0.5rem;
      position: relative;
      overflow: hidden;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: 0.5s;
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 20px -5px rgba(37, 99, 235, 0.3);
    }

    .btn:active {
      transform: translateY(0);
    }

    .btn-secondary {
      background: var(--gray);
    }

    .btn-secondary:hover {
      box-shadow: 0 15px 20px -5px rgba(100, 116, 139, 0.3);
    }

    /* POPUP STYLES */
    .popup-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.7);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      padding: 1rem;
      backdrop-filter: blur(5px);
      animation: fadeIn 0.3s ease;
    }

    .popup-content {
      background: white;
      border-radius: 20px;
      width: 100%;
      max-width: 450px;
      box-shadow: var(--shadow-lg);
      animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
      position: relative;
      overflow: hidden;
    }

    .popup-content::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
    }

    .popup-header {
      padding: 1.5rem 1.5rem 0;
    }

    .popup-body {
      padding: 1.5rem;
    }

    .popup-footer {
      padding: 0 1.5rem 1.5rem;
      display: flex;
      gap: 0.75rem;
    }

    .popup-title {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: var(--dark);
    }

    .order-detail {
      margin-bottom: 1rem;
      padding: 0.75rem 1rem;
      background: #f8fafc;
      border-radius: var(--radius);
      border-left: 3px solid var(--primary);
    }

    .qr-code {
      width: 100%;
      max-width: 260px;
      margin: 1.5rem auto;
      padding: 1rem;
      background: white;
      border-radius: 12px;
      border: 1px solid var(--gray-light);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #666;
      font-size: 0.9rem;
    }

    /* FILE UPLOAD STYLES */
    .file-upload {
      border: 2px dashed var(--gray-light);
      border-radius: var(--radius);
      padding: 2rem;
      text-align: center;
      margin: 1.5rem 0;
      transition: var(--transition);
      cursor: pointer;
    }

    .file-upload:hover {
      border-color: var(--primary);
    }

    .file-upload.dragover {
      border-color: var(--primary);
      background: rgba(37, 99, 235, 0.05);
    }

    .file-upload-icon {
      font-size: 2.5rem;
      color: var(--gray);
      margin-bottom: 1rem;
    }

    .file-input {
      display: none;
    }

    .file-info {
      margin-top: 1rem;
      font-size: 0.875rem;
      color: var(--gray);
    }

    .preview-image {
      max-width: 100%;
      max-height: 200px;
      border-radius: var(--radius);
      margin-top: 1rem;
      display: none;
    }

    /* ANIMATIONS */
    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      header {
        padding: 1rem;
      }

      .logo h1 {
        font-size: 1.125rem;
      }

      .back-link span {
        display: none;
      }

      .user-inputs {
        flex-direction: column;
        gap: 1rem;
      }

      .product-list {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      }

      .bottom-bar {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
      }

      .btn {
        width: 100%;
        justify-content: center;
      }

      .popup-footer {
        flex-direction: column;
      }
    }

    @media (max-width: 480px) {
      main {
        padding: 1rem;
      }

      .card {
        padding: 1.25rem;
      }

      .product-list {
        grid-template-columns: repeat(2, 1fr);
      }
    }
  </style>
</head>

<body>

  <header>
    <div class="logo">
      <div class="logo-mark">BW</div>
      <div>
        <h1>BlueWaves</h1>
        <div>Top-up Super Sus</div>
      </div>
    </div>
    <div class="icons">
      <div class="icon">👤</div>
      <div class="icon">☰</div>
      <a href="web.php" class="back-link">← <span>Kembali Ke Beranda</span></a>
    </div>
  </header>

  <main>
    <form action="" method="post" id="orderForm" enctype="multipart/form-data">
      <!-- USER ID -->
      <input type="hidden" value="Super Sus" name="game">
      <div class="card">
        <h3>Masukkan User ID Super Sus Anda</h3>
        <div class="user-inputs">
          <div class="input-group">
            <label class="input-label">User ID</label>
            <input id="userId" class="input-field" placeholder="Masukkan User ID" name="userId">
          </div>
        </div>
      </div>

      <!-- PRODUK -->
      <div class="card">
        <h2>Pilih Nominal</h2>
        <div class="product-list" id="products" name="produk"></div>
      </div>

      <!-- PAYMENT -->
      <div class="card">
        <h2>Pilih Pembayaran</h2>

        <!-- QRIS -->
        <div class="payment-item" onclick="selectPayment('QRIS', this)">
          <div class="payment-name">QRIS</div>
          <div class="payment-desc">All Payment</div>
        </div>

        <!-- EWALLET -->
        <div class="payment-item" onclick="toggleWalletList()">
          <div class="payment-name">E-Wallet</div>
          <div class="payment-desc">Pilih</div>
        </div>

        <div id="walletList">
          <div class="payment-item" onclick="selectPayment('Gopay', this)">
            <div class="payment-name">Gopay</div>
          </div>
          <div class="payment-item" onclick="selectPayment('DANA', this)">
            <div class="payment-name">DANA</div>
          </div>
          <div class="payment-item" onclick="selectPayment('ShopeePay', this)">
            <div class="payment-name">ShopeePay</div>
          </div>
          <div class="payment-item" onclick="selectPayment('OVO', this)">
            <div class="payment-name">OVO</div>
          </div>
        </div>
      </div>

      <input type="hidden" name="selectedProductName" id="selectedProductName">
      <input type="hidden" name="selectedProductPrice" id="selectedProductPrice">
      <input type="hidden" name="selectedPayment" id="selectedPayment">
      <input type="file" name="bukti_pembayaran" id="buktiPembayaran" class="file-input" accept="image/*">

  </main>

  <!-- BOTTOM BAR -->
  <div class="bottom-bar">
    <div class="total-section">
      <div class="total-label">Total</div>
      <div id="totalRp" class="total-amount">Rp 0</div>
    </div>
    <button type="button" id="buyNow" class="btn" name="pesan">
      BELI SEKARANG <span style="font-size: 1.2rem;">▶</span>
    </button>
  </div>
  </form>

  <!-- POPUP KONFIRMASI PESANAN -->
  <div id="orderConfirmationPopup" class="popup-overlay" style="display: none;">
    <div class="popup-content">
      <div class="popup-header">
        <h3 class="popup-title">Konfirmasi Pesanan</h3>
      </div>
      <div class="popup-body">
        <div class="order-detail">
          <p><strong>User:</strong> <span id="confirmUserId"></span> (<span id="confirmServerId"></span>)</p>
          <p><strong>Produk:</strong> <span id="confirmProductName"></span></p>
          <p><strong>Harga:</strong> <span id="confirmProductPrice"></span></p>
          <p><strong>Pembayaran:</strong> <span id="confirmPayment"></span></p>
        </div>
      </div>
      <div class="popup-footer">
        <button type="button" class="btn" onclick="showQRCodePopup()" style="flex: 1;">Konfirmasi & Bayar</button>
        <button type="button" class="btn btn-secondary" onclick="closeOrderConfirmationPopup()"
          style="flex: 1;">Batal</button>
      </div>
    </div>
  </div>

  <!-- POPUP QR CODE -->
  <div id="qrCodePopup" class="popup-overlay" style="display: none;">
    <div class="popup-content">
      <div class="popup-header">
        <h3 class="popup-title">Scan QRIS</h3>
      </div>
      <div class="popup-body">
        <div class="qr-code">
          [QR Code Placeholder]
        </div>
        <p style="text-align: center; color: var(--gray);">QRIS Demo — belum terhubung gateway</p>
      </div>
      <div class="popup-footer">
        <button type="button" class="btn" onclick="showUploadPopup()" style="flex: 1;">Saya Sudah Bayar</button>
        <button type="button" class="btn btn-secondary" onclick="closeQRCodePopup()" style="flex: 1;">Kembali</button>
      </div>
    </div>
  </div>

  <!-- POPUP UPLOAD BUKTI PEMBAYARAN -->
  <div id="uploadPopup" class="popup-overlay" style="display: none;">
    <div class="popup-content">
      <div class="popup-header">
        <h3 class="popup-title">Upload Bukti Pembayaran</h3>
      </div>
      <div class="popup-body">
        <div class="order-detail">
          <p><strong>User:</strong> <span id="uploadUserId"></span> (<span id="uploadServerId"></span>)</p>
          <p><strong>Produk:</strong> <span id="uploadProductName"></span></p>
          <p><strong>Harga:</strong> <span id="uploadProductPrice"></span></p>
          <p><strong>Pembayaran:</strong> <span id="uploadPayment"></span></p>
        </div>

        <div class="file-upload" id="fileUploadArea" onclick="document.getElementById('buktiPembayaran').click()">
          <div class="file-upload-icon">📁</div>
          <p>Klik untuk upload bukti pembayaran</p>
          <p class="file-info">Format: JPG, PNG (Maks. 5MB)</p>
          <img id="previewImage" class="preview-image" alt="Preview bukti pembayaran">
        </div>

        <div id="fileError" style="color: var(--danger); margin-top: 0.5rem; display: none;">
          File harus berupa gambar (JPG, PNG) dan maksimal 5MB
        </div>
      </div>
      <div class="popup-footer">
        <button type="button" class="btn" onclick="submitOrder()" style="flex: 1;">Kirim Bukti</button>
        <button type="button" class="btn btn-secondary" onclick="closeUploadPopup()" style="flex: 1;">Kembali</button>
      </div>
    </div>
  </div>

  <script>
    /* ===== PRODUK ===== */
    const sample = [
      [40, "40 Star", 6000],
      [90, "90 Star", 12000],
      [200, "200 Star", 24000],
      [500, "500 Star", 54000],
      [1200, "1200 Star", 120000],
      [2500, "2500 Star", 240000],
    ];

    let selectedProduct = null;
    let selectedPayment = null;

    const productsEl = document.getElementById('products');

    function render() {
      productsEl.innerHTML = '';
      sample.forEach((p, i) => {
        const box = document.createElement('div');
        box.className = 'product';
        box.innerHTML = `
          <div class="product-name">${p[1]}</div>
          <div class="product-price">Rp ${numberWithCommas(p[2])}</div>
        `;
        box.onclick = () => {
          document.querySelectorAll('.product').forEach(x => x.classList.remove('selected'));
          box.classList.add('selected');

          selectedProduct = p;

          // isi ke input hidden
          document.getElementById('selectedProductName').value = p[1];
          document.getElementById('selectedProductPrice').value = p[2];

          updateTotal();
        };

        productsEl.appendChild(box);
      });
    }
    render();

    function numberWithCommas(x) {
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function updateTotal() {
      document.getElementById('totalRp').textContent =
        selectedProduct ? "Rp " + numberWithCommas(selectedProduct[2]) : "Rp 0";
    }

    /* ===== PAYMENT ===== */

    function clearPaymentSelection() {
      document.querySelectorAll('.payment-item').forEach(i => {
        i.classList.remove('selected');
      });
    }

    function selectPayment(name, element) {
      clearPaymentSelection();
      element.classList.add('selected');

      selectedPayment = name;

      // isi ke hidden input
      document.getElementById('selectedPayment').value = name;
    }

    function toggleWalletList() {
      const list = document.getElementById('walletList');
      list.style.display = list.style.display === 'none' ? 'block' : 'none';
    }

    /* ===== POPUP FUNCTIONS ===== */

    function showOrderConfirmationPopup() {
      // Isi data ke popup konfirmasi
      document.getElementById('confirmUserId').textContent = document.getElementById('userId').value;
      document.getElementById('confirmServerId').textContent = document.getElementById('serverId').value;
      document.getElementById('confirmProductName').textContent = selectedProduct[1];
      document.getElementById('confirmProductPrice').textContent = 'Rp ' + numberWithCommas(selectedProduct[2]);
      document.getElementById('confirmPayment').textContent = selectedPayment;

      // Tampilkan popup
      document.getElementById('orderConfirmationPopup').style.display = 'flex';
    }

    function closeOrderConfirmationPopup() {
      document.getElementById('orderConfirmationPopup').style.display = 'none';
    }

    function showQRCodePopup() {
      // Tutup popup konfirmasi
      closeOrderConfirmationPopup();

      // Tampilkan popup QR code
      document.getElementById('qrCodePopup').style.display = 'flex';
    }

    function closeQRCodePopup() {
      document.getElementById('qrCodePopup').style.display = 'none';
    }

    function showUploadPopup() {
      // Tutup popup QR code
      closeQRCodePopup();

      // Isi data ke popup upload
      document.getElementById('uploadUserId').textContent = document.getElementById('userId').value;
      document.getElementById('uploadServerId').textContent = document.getElementById('serverId').value;
      document.getElementById('uploadProductName').textContent = selectedProduct[1];
      document.getElementById('uploadProductPrice').textContent = 'Rp ' + numberWithCommas(selectedProduct[2]);
      document.getElementById('uploadPayment').textContent = selectedPayment;

      // Tampilkan popup upload
      document.getElementById('uploadPopup').style.display = 'flex';
    }

    function closeUploadPopup() {
      document.getElementById('uploadPopup').style.display = 'none';
    }

    function submitOrder() {
      // Submit form
      document.getElementById('orderForm').submit();
    }

    /* ===== FILE UPLOAD HANDLING ===== */

    const fileInput = document.getElementById('buktiPembayaran');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const previewImage = document.getElementById('previewImage');
    const fileError = document.getElementById('fileError');

    fileInput.addEventListener('change', function (e) {
      handleFileSelection(this.files[0]);
    });

    // Drag and drop functionality
    fileUploadArea.addEventListener('dragover', function (e) {
      e.preventDefault();
      this.classList.add('dragover');
    });

    fileUploadArea.addEventListener('dragleave', function (e) {
      e.preventDefault();
      this.classList.remove('dragover');
    });

    fileUploadArea.addEventListener('drop', function (e) {
      e.preventDefault();
      this.classList.remove('dragover');

      if (e.dataTransfer.files.length) {
        handleFileSelection(e.dataTransfer.files[0]);
        fileInput.files = e.dataTransfer.files;
      }
    });

    function handleFileSelection(file) {
      fileError.style.display = 'none';

      // Validasi file
      if (!file) return;

      const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
      const maxSize = 5 * 1024 * 1024; // 5MB

      if (!validTypes.includes(file.type)) {
        fileError.textContent = 'Format file tidak didukung. Harus JPG atau PNG.';
        fileError.style.display = 'block';
        return;
      }

      if (file.size > maxSize) {
        fileError.textContent = 'Ukuran file terlalu besar. Maksimal 5MB.';
        fileError.style.display = 'block';
        return;
      }

      // Tampilkan preview
      const reader = new FileReader();
      reader.onload = function (e) {
        previewImage.src = e.target.result;
        previewImage.style.display = 'block';

        // Update text area upload
        fileUploadArea.querySelector('p').textContent = 'File terpilih: ' + file.name;
        fileUploadArea.querySelector('.file-info').textContent = 'Klik untuk mengganti file';
      };
      reader.readAsDataURL(file);
    }

    /* ===== BUY NOW ===== */

    document.getElementById('buyNow').onclick = () => {
      const uid = document.getElementById('userId').value.trim();
      const sid = document.getElementById('serverId').value.trim();

      if (!uid) return alert('Masukkan UserID dahulu');
      if (!sid) return alert('Masukkan Server ID dahulu');
      if (!selectedProduct) return alert('Pilih produk terlebih dahulu');
      if (!selectedPayment) return alert('Pilih metode pembayaran');

      // Tampilkan popup konfirmasi
      showOrderConfirmationPopup();
    };
  </script>

</body>

</html>