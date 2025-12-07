<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BlueWaves STORE — eFootball</title>

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
    .product:hover { transform: translateY(-2px); }
    .product.selected {
      border: 2px solid var(--blue3);
      box-shadow: 0 6px 18px rgba(0,130,255,.15);
    }

    .price { font-weight: 700; }

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
      <div style="font-size:12px;color:#777">Top-up eFootball Coins</div>
    </div>
  </div>

  <div class="icons">
    <div class="icon">👤</div>
    <div class="icon">☰</div>
  </div>
</header>

<main>

  <div class="card">
    <h3>Masukkan eFootball ID / Konami ID Anda</h3>

    <div class="user-inputs">
      <input id="userId" placeholder="Masukkan eFootball ID">
    </div>
  </div>

  <div class="card">
    <h2>Pilih Nominal</h2>
    <div class="product-list" id="products"></div>
  </div>

  <div class="card">
    <h2>Pilih Pembayaran</h2>
    <div class="payment-item">
      <strong>QRIS</strong>
      <span style="margin-left:auto">All Payment</span>
    </div>

    <div class="payment-item">
      <strong>E-Wallet</strong>

      <div onclick="toggleWallet()" style="padding:12px;border:1px solid #fff;border-radius:10px;background:#0f1624;color:#fff;cursor:pointer;margin-left:10px;">
        Pilih E-Wallet
      </div>

      <div id="walletList" style="display:none;margin-top:10px;width:100%;">
        <div class="item" style="padding:12px;background:#102031;border-radius:10px;margin-bottom:8px;color:white;">Gopay</div>
        <div class="item" style="padding:12px;background:#102031;border-radius:10px;margin-bottom:8px;color:white;">DANA</div>
        <div class="item" style="padding:12px;background:#102031;border-radius:10px;margin-bottom:8px;color:white;">ShopeePay</div>
        <div class="item" style="padding:12px;background:#102031;border-radius:10px;margin-bottom:8px;color:white;">OVO</div>
      </div>

      <script>
      function toggleWallet(){
        const box = document.getElementById('walletList');
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
      }
      </script>

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
const sample = [
  [100,'100 eFootball Coins',10000],
  [250,'250 eFootball Coins',23000],
  [500,'500 eFootball Coins',45000],
  [1000,'1000 eFootball Coins',89000],
  [2000,'2000 eFootball Coins',175000],
  [3000,'3000 eFootball Coins',259000],
  [5000,'5000 eFootball Coins',430000]
];

const productsEl = document.getElementById('products');
let selected = null;

function render(){
  productsEl.innerHTML = '';
  sample.forEach((p,i)=>{
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
  selected = sample[i];
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
  if(!uid) return alert('Masukkan eFootball ID dahulu');
  if(!selected) return alert('Pilih produk terlebih dahulu');

  alert(`Order dibuat:\nUser: ${uid}\nProduk: ${selected[1]}\nTotal: Rp ${numberWithCommas(selected[2])}\n\n(Demo saja, pembayaran belum aktif)`);
};

render();
</script>

</body>
</html>