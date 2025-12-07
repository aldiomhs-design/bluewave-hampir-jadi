<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: web.php'); 
    exit;
}
?>


<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BlueWaves STORE — Free Fire</title>

  <style>
    :root {
      --blue1: #0a4b78;
      --blue2: #0f6fb8;
      --blue3: #4ab3ff;
      --bg: #0a4b78;
      --card: #ffffff;
      --dark: #1f2b38;
    }

    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: system-ui, Arial;
      background: linear-gradient(180deg, var(--bg), #dff1ff);
      color: var(--dark);
    }

    header {
      background: #fff;
      padding: 14px 18px;
      display: flex;
      align-items: center;
      gap: 14px;
      box-shadow: 0 2px 0 rgba(0,0,0,.05);
      position: sticky;
      top: 0;
      z-index: 50;
      border-bottom: 3px solid var(--blue3);
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .logo-mark {
      width: 42px;
      height: 42px;
      background: linear-gradient(135deg, var(--blue2), var(--blue1));
      border-radius: 10px;
    }
    .logo h1 {
      margin: 0;
      font-size: 20px;
      font-weight: 700;
    }

    .icons {
      margin-left: auto;
      display: flex;
      gap: 10px;
    }
    .icon {
      width: 36px;
      height: 36px;
      background: #f0f6ff;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    main {
      padding: 18px;
      max-width: 1080px;
      margin: auto;
    }

    .card {
      background: var(--card);
      border-radius: 14px;
      padding: 18px;
      box-shadow: 0 6px 16px rgba(0,0,0,.07);
      margin-bottom: 18px;
    }

    .muted { color: #6b6b6b; }
    .user-inputs { display: flex; gap: 10px; }
    .user-inputs input {
      flex: 1;
      padding: 12px;
      border-radius: 10px;
      border: 1px solid #ddd;
      background: #f7fbff;
    }

    .tabs {
      display: flex;
      gap: 8px;
      margin-bottom: 14px;
    }
    .tab {
      padding: 8px 14px;
      background: #edf6ff;
      border-radius: 10px;
      border: 1px solid #d9ecff;
      font-weight: 600;
      cursor: pointer;
    }

    .product-list {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
      max-height: 420px;
      overflow: auto;
    }
    .product {
      padding: 12px;
      background: #fff;
      border-radius: 12px;
      border: 1px solid #e2ecf5;
      box-shadow: 0 4px 12px rgba(0,0,0,.05);
      cursor: pointer;
      transition: .15s;
    }
    .product:hover {
      transform: translateY(-2px);
    }
    .product.selected {
      border: 2px solid var(--blue3);
      box-shadow: 0 6px 18px rgba(0,130,255,.15);
    }

    .price { font-weight: 700; }

    .right .card { margin-bottom: 14px; }
    .payment-item {
      padding: 12px;
      display: flex;
      border-radius: 10px;
      align-items: center;
      background: #f8fcff;
      border: 1px solid #d9ecff;
    }

    .bottom-bar {
      position: absolute;
      left: 0;
      background: linear-gradient(90deg, var(--blue1), var(--blue2));
      padding: 14px 18px;
      display: flex;
      justify-content: space-between;
      color: #fff;
      z-index: 100;
      width: 100%;
      text-align: center;
    }
    .btn {
      padding: 12px 20px;
      background: #fff;
      color: var(--blue1);
      border-radius: 10px;
      font-weight: 700;
      cursor: pointer;
      border: none;
    }

    @media(max-width:900px){
      .product-list { grid-template-columns: repeat(3,1fr); }
    }
    @media(max-width:480px){
      .product-list { grid-template-columns: repeat(2,1fr); }
    }
  </style>
</head>

<body>
<header>
  <div class="logo">
    <div class="logo-mark"></div>
    <div>
      <h1>BlueWaves</h1>
      <div style="font-size:12px;color:#777">Free Fire Top-up</div>
    </div>
  </div>
  <div class="icons">
    <div class="icon">👤</div>
    <div class="icon">☰</div>
  </div>
</header>

<main>
  <div class="card">
    <h3>Masukkan ID Player Anda Free Fire Anda</h3>

    <div class="user-inputs">
      <input id="userId" placeholder="Masukkan ID Player">
    </div>
  </div>

  <div class="card">
    <h2>Pilih Diamonds</h2>

    <div class="tabs">
      <div class="tab">ALL</div>
      <div class="tab">DIAMONDS</div>
      <div class="tab">MEMBERSHIP</div>
    </div>

    <div class="product-list" id="products"></div>
  </div>

  <div class="right">
    <div class="card">
      <h2>Pilih Pembayaran</h2>
      <div class="payment-item"><strong>QRIS</strong><span style="margin-left:auto">All Payment</span></div>
      <div class="payment-item"><strong>E-Wallet</strong><span style="margin-left:auto">Pilih</span></div>
      <div class="payment-item"><strong>Virtual Account</strong><span style="margin-left:auto">Bank</span></div>
    </div>
  </div>
</main>

<div class="bottom-bar">
  <div>
    <div style="font-size:13px">Total</div>
    <div id="totalRp" style="font-size:20px;font-weight:700">Rp 0</div>
  </div>
  <button id="buyNow" class="btn">BELI SEKARANG ▶</button>
</div>

<script>
const products = [
  [5,  "5 Diamonds",         900],
  [12, "12 Diamonds",        2200],
  [20, "20 Diamonds",        3500],
  [50, "50 Diamonds",        8000],
  [70, "70 Diamonds",        10500],
  [100,"100 Diamonds",       15000],
  [140,"140 Diamonds",       21000],
  [355,"355 Diamonds",       51000],
  [720,"720 Diamonds",      102000],
  [1000,"1000 Diamonds",    150000],
];

const productsEl = document.getElementById('products');
let selected = null;

function render(){
  productsEl.innerHTML = '';
  products.forEach((p,i)=>{
    const box = document.createElement('div');
    box.className = 'product';
    box.innerHTML = `
      <div style="font-size:14px;font-weight:700">${p[1]}</div>
      <div class="price">Rp ${numberWithCommas(p[2])}</div>
    `;
    box.onclick = ()=> select(i, box);
    productsEl.appendChild(box);
  });
}

function select(i,box){
  const prev = document.querySelector('.product.selected');
  if(prev) prev.classList.remove('selected');
  box.classList.add('selected');
  selected = products[i];
  updateTotal();
}

function updateTotal(){
  const totalEl = document.getElementById('totalRp');
  if(!selected) return totalEl.textContent = 'Rp 0';
  totalEl.textContent = 'Rp ' + numberWithCommas(selected[2]);
}

function numberWithCommas(x){
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g,",");
}

document.getElementById('buyNow').onclick = ()=>{
  const uid = document.getElementById('userId').value.trim();
  if(!uid) return alert('Masukkan Player ID dahulu');
  if(!selected) return alert('Pilih diamonds terlebih dahulu');

  alert(
`Order dibuat:
User: ${uid}
Produk: ${selected[1]}
Total: Rp ${numberWithCommas(selected[2])}

(Demo saja, belum terhubung ke payment)`
  );
};

render();
</script>

</body>
</html>