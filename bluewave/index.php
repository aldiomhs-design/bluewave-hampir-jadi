<?php
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BlueWaves STORE</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      --primary: #1a8fff;
      --secondary: #004080;
      --accent: #ff8c00;
      --light: #f8f9fa;
      --dark: #212529;
      --gradient: linear-gradient(135deg, #1a8fff, #004080, #0072ff);
      --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      --hover-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: var(--gradient);
      color: var(--dark);
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    /* Navigation with Search Bar */
    nav {
      background-color: rgba(0, 51, 102, 0.95);
      padding: 15px 0;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
      backdrop-filter: blur(10px);
    }

    .nav-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
    }

    .logo {
      display: flex;
      align-items: center;
      font-size: 1.5rem;
      font-weight: 700;
      color: white;
    }

    .logo i {
      margin-right: 10px;
      font-size: 1.8rem;
    }

    .nav-menu {
      display: flex;
      list-style: none;
      gap: 30px;
    }

    .nav-menu li a {
      color: white;
      text-decoration: none;
      font-weight: 500;
      font-size: 1rem;
      transition: all 0.3s;
      position: relative;
      padding: 5px 0;
    }

    .nav-menu li a:hover {
      color: #ffb700;
    }

    .nav-menu li a::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background-color: #ffb700;
      transition: width 0.3s;
    }

    .nav-menu li a:hover::after {
      width: 100%;
    }

    /* Search Bar in Navigation */
    .nav-search {
      display: flex;
      align-items: center;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50px;
      padding: 5px 15px;
      transition: all 0.3s;
      border: 1px solid transparent;
    }

    .nav-search:focus-within {
      background: rgba(255, 255, 255, 0.15);
      border-color: rgba(255, 255, 255, 0.3);
    }

    .nav-search input {
      background: transparent;
      border: none;
      color: white;
      padding: 8px 12px;
      width: 180px;
      font-size: 0.9rem;
      outline: none;
    }

    .nav-search input::placeholder {
      color: rgba(255, 255, 255, 0.7);
    }

    .nav-search button {
      background: transparent;
      border: none;
      color: white;
      cursor: pointer;
      padding: 5px;
      transition: all 0.3s;
      border-radius: 50%;
    }

    .nav-search button:hover {
      color: #ffb700;
      transform: scale(1.1);
    }

    /* SLIDER HEADER - Pengganti Header Lama */
    .slider {
      position: relative;
      width: 100%;
      max-width: 1200px;
      height: 350px;
      margin: 120px auto 40px;
      overflow: hidden;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
      animation: fadeIn 1s ease-in;
    }

    .slides {
      display: flex;
      transition: transform 0.5s ease;
      height: 100%;
    }

    .slide {
      min-width: 100%;
      position: relative;
    }

    .slide img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 20px;
    }

    /* Overlay untuk teks pada slide */
    .slide-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      color: white;
      z-index: 2;
      width: 80%;
    }

    .slide-content h1 {
      font-size: 3rem;
      margin-bottom: 20px;
      text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
      font-weight: 700;
    }

    .slide-content p {
      font-size: 1.2rem;
      margin-bottom: 30px;
      opacity: 0.9;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }

    /* Gradient overlay pada gambar */
    .slide::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 51, 102, 0.7));
      border-radius: 20px;
    }

    /* Tombol navigasi slider */
    .slider-nav {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(0, 0, 0, 0.4);
      border: none;
      color: white;
      padding: 12px 18px;
      font-size: 24px;
      cursor: pointer;
      border-radius: 50%;
      z-index: 10;
      transition: all 0.3s;
    }

    .slider-nav:hover {
      background: rgba(0, 0, 0, 0.7);
      transform: translateY(-50%) scale(1.1);
    }

    .slider-prev {
      left: 20px;
    }

    .slider-next {
      right: 20px;
    }

    /* Indikator dots */
    .slider-dots {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 10px;
      z-index: 10;
    }

    .dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.5);
      cursor: pointer;
      transition: all 0.3s;
    }

    .dot.active {
      background: var(--accent);
      transform: scale(1.2);
    }

    /* Main Content */
    .container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .section {
      margin-bottom: 60px;
      background-color: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      padding: 30px;
      box-shadow: var(--card-shadow);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .section:hover {
      box-shadow: var(--hover-shadow);
      transform: translateY(-5px);
    }

    .section-header {
      margin-bottom: 30px;
    }

    .section h2 {
      font-size: 2rem;
      color: var(--secondary);
      position: relative;
      display: inline-block;
    }

    .section h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 60px;
      height: 4px;
      background: var(--accent);
      border-radius: 2px;
    }

    /* Product & Game Grid */
    .product-grid, .game-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 25px;
    }

    .product-card, .game-card {
      background-color: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      transition: all 0.3s;
      position: relative;
    }

    .product-card:hover, .game-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }

    .card-image {
      position: relative;
      width: 100%;
      height: 180px;
      overflow: hidden;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }

    .card-image a {
      display: block;
      width: 100%;
      height: 100%;
    }

    .card-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      display: block;
      position: relative;
      z-index: 1;
      transition: transform 0.5s;
    }

    .card-image a:hover img {
      transform: scale(1.05);
    }

    .card-label {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      text-align: center;
      padding: 12px 0;
      background: linear-gradient(to right, 
        rgba(0, 64, 128, 0.9), 
        rgba(26, 143, 255, 0.9));
      color: white;
      font-weight: 600;
      font-size: 0.9rem;
      z-index: 2;
      backdrop-filter: blur(5px);
    }

    .card-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: var(--accent);
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.7rem;
      font-weight: 600;
      z-index: 3;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Footer */
    footer {
      background-color: var(--secondary);
      color: white;
      padding: 40px 0 20px;
      margin-top: 60px;
    }

    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 40px;
      padding: 0 20px;
    }

    .footer-section h3 {
      font-size: 1.3rem;
      margin-bottom: 20px;
      position: relative;
      display: inline-block;
    }

    .footer-section h3::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
      width: 40px;
      height: 3px;
      background: var(--accent);
    }

    .footer-section p {
      line-height: 1.6;
      margin-bottom: 15px;
    }

    .footer-links {
      list-style: none;
    }

    .footer-links li {
      margin-bottom: 10px;
    }

    .footer-links a {
      color: #ddd;
      text-decoration: none;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .footer-links a:hover {
      color: var(--accent);
      transform: translateX(5px);
    }

    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }

    .social-links a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      color: white;
      transition: all 0.3s;
    }

    .social-links a:hover {
      background: var(--accent);
      transform: translateY(-5px);
    }

    .footer-bottom {
      text-align: center;
      padding-top: 30px;
      margin-top: 30px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .product-grid, .game-grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      }
      
      .nav-container {
        flex-wrap: wrap;
        gap: 15px;
      }
      
      .nav-search {
        order: 3;
        width: 100%;
        justify-content: center;
      }
      
      .nav-search input {
        width: 100%;
      }
    }

    @media (max-width: 768px) {
      .slider {
        height: 300px;
        margin: 100px auto 30px;
        border-radius: 15px;
      }
      
      .slide-content h1 {
        font-size: 2.2rem;
      }
      
      .slide-content p {
        font-size: 1rem;
      }
      
      .nav-menu {
        gap: 15px;
        flex-wrap: wrap;
        justify-content: center;
      }
      
      .product-grid, .game-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      }
      
      .card-image {
        height: 160px;
      }
    }

    @media (max-width: 576px) {
      .slider {
        height: 250px;
        margin: 80px auto 20px;
        border-radius: 10px;
      }
      
      .slide-content h1 {
        font-size: 1.8rem;
      }
      
      .slide-content p {
        font-size: 0.9rem;
      }
      
      .slider-nav {
        padding: 8px 12px;
        font-size: 18px;
      }
      
      .product-grid, .game-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .card-image {
        height: 150px;
      }
      
      .nav-menu {
        flex-direction: column;
        gap: 10px;
        width: 100%;
        text-align: center;
      }
    }
  </style>
</head>

<body>
  <!-- Navigation -->
  <nav>
    <div class="nav-container">
      <div class="logo">
        <i class="fas fa-water"></i>
        <span>BlueWaves</span>
      </div>
      
      <ul class="nav-menu">
        <li><a href="#popular">Produk Populer</a></li>
        <li><a href="#games">Games</a></li>
        <li><a href="dashboard.php">Riwayat Pesanan</a></li>
        <li><a href="logout.php">keluar</a></li>
        <li><a href="login.php">masuk</a></li>
      </ul>
      
      <!-- Search Bar in Navigation -->
      <div class="nav-search">
        <input type="text" placeholder="Cari produk atau game...">
        <button><i class="fas fa-search"></i></button>
      </div>
    </div>
  </nav>

  <!-- SLIDER HEADER - Pengganti Header Lama -->
  <div class="slider">
    <div class="slides">
      <!-- Slide 1 -->
      <div class="slide">
        <img src="bn/mlbb.png" alt="">
        <div class="slide-content">
        </div>
      </div>
      
      <!-- Slide 2 -->
      <div class="slide">
        <img src="banner2.jpg" alt="BlueWaves Store Banner 2" onerror="this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'">
        <div class="slide-content">
          <h1>Top Up Game Terpercaya</h1>
          <p>Proses instan, aman, dan garansi 100% uang kembali!</p>
        </div>
      </div>
      
      <!-- Slide 3 -->
      <div class="slide">
        <img src="banner3.jpg" alt="BlueWaves Store Banner 3" onerror="this.src='https://images.unsplash.com/photo-1542751371-adc38448a05e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'">
        <div class="slide-content">
          <h1>Berbagai Game Populer</h1>
          <p>Mobile Legends, PUBG, Free Fire, dan banyak game lainnya!</p>
        </div>
      </div>
    </div>

    <button class="slider-nav slider-prev">&#10094;</button>
    <button class="slider-nav slider-next">&#10095;</button>
    
    <!-- Dots Indicator -->
    <div class="slider-dots">
      <div class="dot active" data-slide="0"></div>
      <div class="dot" data-slide="1"></div>
      <div class="dot" data-slide="2"></div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <!-- Produk Populer -->
    <section id="popular" class="section">
      <div class="section-header">
        <h2><i class="fas fa-fire"></i> Produk Populer</h2>
      </div>
      
      <div class="product-grid">
        <div class="product-card">
          <div class="card-image">
            <a href="game/dmml.php">
              <img src="img/ml.jpg" alt="Mobile Legends">
            </a>
            <div class="card-badge">Hot</div>
            <div class="card-label">Mobile Legends</div>
          </div>
        </div>
        
        <div class="product-card">
          <div class="card-image">
            <a href="hok.php">
              <img src="img/pubg.webp" alt="PUBG">
            </a>
            <div class="card-badge">Trending</div>
            <div class="card-label">PUBG Mobile</div>
          </div>
        </div>
        
        <div class="product-card">
          <div class="card-image">
            <a href="dmmc.php">
              <img src="img/mcgg.webp" alt="Magic Chess: Go Go">
            </a>
            <div class="card-badge">New</div>
            <div class="card-label">Magic Chess: Go Go</div>
          </div>
        </div>
        
        <div class="product-card">
          <div class="card-image">
            <img src="img/bs.webp" alt="BLOOD STRIKE">
            <div class="card-badge">Hot</div> 
            <div class="card-label">Blood Strike</div>
          </div>
        </div>
        
        <div class="product-card">
          <div class="card-image">
            <a href="game/tokenhok.php">
              <img src="img/hok.webp" alt="HONOR OF KINGS">
            </a>
            <div class="card-badge">Hot</div>
            <div class="card-label">Honor Of Kings</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Games -->
    <section id="games" class="section">
      <div class="section-header">
        <h2><i class="fas fa-gamepad"></i> Games</h2>
      </div>
      
      <div class="game-grid">
        <div class="game-card">
          <div class="card-image">
            <a href="dmmc.php">
              <img src="img/mcgg.webp" alt="MAGIC CHESS: GO GO">
            </a>
            <div class="card-label">Magic Chess: Go Go</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <a href="dmml.php">
              <img src="img/ml.jpg" alt="MOBILE LEGENDS">
            </a>
            <div class="card-label">Mobile Legends</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <img src="img/bs.webp" alt="BLOOD STRIKE">
            <div class="card-label">Blood Strike</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <img src="img/ef.webp" alt="EFOOTBALL">
            <div class="card-label">eFootball™</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <a href="starss.php">
              <img src="img/ss.webp" alt="SUPER SUS">
            </a>
            <div class="card-label">Super Sus</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <img src="img/gi.webp" alt="GENSHIN IMPACT">
            <div class="card-label">Genshin Impact</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <a href="tokenhok.php">
              <img src="img/hok.webp" alt="HONOR OF KINGS">
            </a>
            <div class="card-label">Honor Of Kings</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <img src="https://hiddengame.id/assets/images/games/1695641461_1f056ea75bf4dac69752.webp" alt="u" loading="lazy">
            <div class="card-label">Honkai: Star Rail</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <img src="img/ff.webp" alt="FREE FIRE">
            <div class="card-label">Free Fire</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <a href="ucpubg.php">
              <img src="img/pubg.webp" alt="PUBG">
            </a>
            <div class="card-label">PUBG Mobile</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <a href="dcdf.php">
              <img src="img/df.webp" alt="DELTA FORCE">
            </a>
            <div class="card-label">Garena Delta Force</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <img src="img/coc.webp" alt="CLASH OF CLANS">
            <div class="card-label">Clash OF Clans</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <img src="img/sm.webp" alt="SAUSAGE MAN">
            <div class="card-label">Sausage Man</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <img src="img/opbr.webp" alt="ONE PIECE BOUNTY RUSH">
            <div class="card-label">ONE PIECE Bounty Rush</div>
          </div>
        </div>
        
        <div class="game-card">
          <div class="card-image">
            <img src="https://hiddengame.id/assets/images/games/1702461008_05473cd7631bb305c99f.webp" alt="CALL OF DUTY">
            <div class="card-label">Call Of Duty: Mobile</div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h3>BlueWaves STORE</h3>
        <p>Tempat terbaik untuk top-up game favorit Anda dengan harga terbaik dan proses cepat!</p>
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <li><a href="https://wa.me//6289504780060"><i class="fab fa-whatsapp"></i></a></li>
        </div>
      </div>
      
      <div class="footer-section">
        <h3>Layanan</h3>
        <ul class="footer-links">
          <li><a href="#"><i class="fas fa-chevron-right"></i> Top Up Game</a></li>
          <li><a href="#"><i class="fas fa-chevron-right"></i> Voucher Game</a></li>
          <li><a href="#"><i class="fas fa-chevron-right"></i> Item Game</a></li>
          <li><a href="#"><i class="fas fa-chevron-right"></i> Layanan Lainnya</a></li>
        </ul>
      </div>
      
      <div class="footer-section">
        <h3>Bantuan</h3>
        <ul class="footer-links">
          <li><a href="#"><i class="fas fa-chevron-right"></i> Cara Order</a></li>
          <li><a href="#"><i class="fas fa-chevron-right"></i> FAQ</a></li>
          <li><a href="#"><i class="fas fa-chevron-right"></i> Kontak Kami</a></li>
          <li><a href="#"><i class="fas fa-chevron-right"></i> Kebijakan Privasi</a></li>
        </ul>
      </div>
    </div>
    
    <div class="footer-bottom">
      <p>&copy; 2025 BlueWaves STORE. All rights reserved. Hubungi kami untuk top-up! 📞</p>
    </div>
  </footer>

  <script>
    // Slider functionality
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const totalSlides = slides.length;

    // Fungsi untuk menampilkan slide tertentu
    function showSlide(index) {
      if (index >= totalSlides) currentSlide = 0;
      else if (index < 0) currentSlide = totalSlides - 1;
      else currentSlide = index;

      // Update posisi slides
      const slidesContainer = document.querySelector('.slides');
      slidesContainer.style.transform = `translateX(-${currentSlide * 100}%)`;

      // Update dots
      dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === currentSlide);
      });
    }

    // Event listeners untuk tombol next/prev
    document.querySelector('.slider-next').addEventListener('click', () => {
      showSlide(currentSlide + 1);
    });

    document.querySelector('.slider-prev').addEventListener('click', () => {
      showSlide(currentSlide - 1);
    });

    // Event listeners untuk dots
    dots.forEach((dot, index) => {
      dot.addEventListener('click', () => {
        showSlide(index);
      });
    });

    // Auto slide setiap 4.5 detik
    let slideInterval = setInterval(() => {
      showSlide(currentSlide + 1);
    }, 4500);

    // Hentikan auto slide saat hover
    const slider = document.querySelector('.slider');
    slider.addEventListener('mouseenter', () => {
      clearInterval(slideInterval);
    });

    slider.addEventListener('mouseleave', () => {
      slideInterval = setInterval(() => {
        showSlide(currentSlide + 1);
      }, 4500);
    });

    // Touch support untuk mobile
    let touchStartX = 0;
    let touchEndX = 0;

    slider.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    });

    slider.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    });

    function handleSwipe() {
      const swipeThreshold = 50;
      const diff = touchStartX - touchEndX;

      if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
          // Swipe kiri - next slide
          showSlide(currentSlide + 1);
        } else {
          // Swipe kanan - previous slide
          showSlide(currentSlide - 1);
        }
      }
    }
  </script>

</body>

</html>