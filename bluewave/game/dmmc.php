<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Magic Chess GO GO — BlueWaves STORE</title>

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
      min-height: 100vh;
      position: relative;
      padding-bottom: 86px; /* ruang untuk bottom-bar */
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
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-weight:800;
      font-size:18px;
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
    .user-inputs { display: flex; gap: 10px; flex-wrap:wrap; }
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
      flex-wrap:wrap;
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
      gap:10px;
    }

    .payment-box {
      flex:1;
    }

    .bottom-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: linear-gradient(90deg, var(--blue1), var(--blue2));
      padding: 14px 18px;
      display: flex;
      justify-content: space-between;
      color: #fff;
      z-index: 100;
      width: 100%;
      text-align: center;
      align-items:center;
      box-shadow: 0 -6px 24px rgba(10,75,120,.12);
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
      .user-inputs { flex-direction:column; }
      .tabs { overflow:auto; }
    }
  </style>
</head>

<body>
<header>
  <div class="logo">
    <div class="logo-mark">MC</div>
    <div>
      <h1>BlueWaves</h1>
      <div style="font-size:12px;color:#777">Magic Chess GO GO — Top-up</div>
    </div>
  </div>
  <div class="icons">
    <div class="icon">👤</div>
    <div class="icon">☰</div>
  </div>
</header>

<main>
  <div class="card">
    <h3>Masukkan Player ID dan Server ID Magic Chess GO GO Anda</h3>

    <div class="user-inputs">
      <input id="playerId" placeholder="Masukkan Player ID">
      <input id="serverId" placeholder="Masukkan Server ID">
    </div>
  </div>

  <div class="card">
    <h2>Pilih Nominal Diamonds</h2>
    <div class="tabs">
      <div class="tab" onclick="filterAll()">ALL</div>
      <div class="tab">DIAMONDS</div>
      <div class="tab">BONUS</div>
    </div>
    <div class="product-list" id="products"></div>
  </div>

  <div class="right">
    <div class="card">
      <h2>Pilih Pembayaran</h2>
      <div class="payment-item">
        <strong>QRIS</strong>
        <span style="margin-left:auto">All Payment</span>
      </div>

      <div style="height:10px"></div>

      <div class="payment-item">
        <strong>E-Wallet</strong>
      <div onclick="toggleWallet()" style="padding:12px;border:1px solid #fff;border-radius:10px;background:#0f1624;color:#fff;cursor:pointer;margin-left:10px;">
        Pilih E-Wallet
      </div>
        <span style="margin-left:auto">Pilih</span>
      </div>

      <div id="walletList" style="display:none;margin-top:10px;">
        <div class="item" style="padding:12px;background:#102031;border-radius:10px;margin-bottom:8px;cursor:pointer;color:#fff;">Gopay</div>
        <div class="item" style="padding:12px;background:#102031;border-radius:10px;margin-bottom:8px;cursor:pointer;color:#fff;">DANA</div>
        <div class="item" style="padding:12px;background:#102031;border-radius:10px;margin-bottom:8px;cursor:pointer;color:#fff;">ShopeePay</div>
        <div class="item" style="padding:12px;background:#102031;border-radius:10px;margin-bottom:8px;cursor:pointer;color:#fff;">OVO</div>
      </div>
    </div>
  </div>
</main>

<div class="bottom-bar">
  <div style="display:flex;flex-direction:column;align-items:flex-start;">
    <div style="font-size:13px">Total</div>
    <div id="totalRp" style="font-size:20px;font-weight:700">Rp 0</div>
  </div>
  <button id="buyNow" class="btn">BELI SEKARANG ▶
  </button>
</div>

<script>
/*
  Data format: [id, label, note, price]
  - id: numeric id (unique)
  - label: string shown on card
  - note: optional small text (kept null here)
  - price: integer (in Rupiah)
*/
const sample = [
  [10,'10 Diamonds (9+1 Bonus)', null, 3000],
  [20,'20 Diamonds (18+2 Bonus)', null, 5500],
  [50,'50 Diamonds (45+5 Bonus)', null, 13200],
  [75,'75 Diamonds (68+7 Bonus)', null, 19800],
  [140,'140 Diamonds (126+14 Bonus)', null, 35400],
  [280,'280 Diamonds (252+28 Bonus)', null, 69000],
  [560,'560 Diamonds (504+56 Bonus)', null, 135000]
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
      <div class="muted" style="margin-top:6px">${p[2] ? p[2] : ''}</div>
      <div class="price" style="margin-top:8px">Rp ${numberWithCommas(p[3])}</div>
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
  totalEl.textContent = 'Rp ' + numberWithCommas(selected[3]);
}

function numberWithCommas(x){
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g,",");
}

document.getElementById('buyNow').onclick = ()=>{
  const pid = document.getElementById('playerId').value.trim();
  const sid = document.getElementById('serverId').value.trim();
  if(!pid) return alert('Masukkan Player ID dahulu');
  if(!sid) return alert('Masukkan Server ID dahulu');
  if(!selected) return alert('Pilih produk terlebih dahulu');

  alert(`Order dibuat (Demo):\nGame: Magic Chess GO GO\nPlayer ID: ${pid}\nServer ID: ${sid}\nProduk: ${selected[1]}\nTotal: Rp ${numberWithCommas(selected[3])}\n\n(Pembayaran belum terhubung — ini hanya demo)`);
};

function toggleWallet(){
  const box = document.getElementById('walletList');
  box.style.display = box.style.display === 'none' ? 'block' : 'none';
}

function filterAll(){
  // placeholder kalau mau tambah filter nanti
  render();
}

render();
</script>

</body>
</html>