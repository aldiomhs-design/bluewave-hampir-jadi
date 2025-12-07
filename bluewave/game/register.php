<?php
require_once '../database.php';

$msg = "";
if (isset($_POST["register"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $password2 = $_POST["password2"];

    if (empty($username) || empty($password) || empty($password2)) {
        $msg = "Semua Kolom harus di isi!";
    } elseif ($password !== $password2) {
        $msg = "Konfirmasi password tidak sesuai!";
    }elseif(strlen($password) < 6) {
        $msg = "Password Minimal 6 Karakter!";
    } else {
        $hashpassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = $conn->prepare("INSERT INTO users (username, password) VALUES (?,?)");
        $sql->bind_param("ss", $username, $hashpassword, );
        $sql->execute();

        if ($conn->affected_rows > 0) {
            $msg = "Akun Berhasil dibuat";
        } else {
            $msg = "Akun Tidak Berhasil Dibuat Silahkan Coba Lagi!";
        }
    }
}
?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login - BlueWaves</title>
  <style>
    :root {
      --bg-gradient: linear-gradient(135deg, #ffffffff 0%, #ffffffff 100%);
      --card-bg: #ffffff;
      --text-color: #222;
      --accent: #1a4fff;
      --accent-hover: #0037d1;
      --error-color: #d9534f;
      --success-color: #28a745;
      --shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: "Poppins", "Segoe UI", sans-serif;
    }

    body {
      background: var(--bg-gradient);
      color: var(--text-color);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .bg-video {
      position: fixed;
      right: 0;
      bottom: 0;
      min-width: 100%;
      min-height: 100%;
      z-index: -1;
      object-fit: cover;
      filter: brightness(60%);
    }

    .container {
      width: 100%;
      max-width: 400px;
      padding: 20px;
    }

    h1 {
      text-align: center;
      color: #fff;
      margin-bottom: 25px;
      letter-spacing: 0.5px;
      font-size: 1.8rem;
    }

    .card {
      background: var(--card-bg);
      border-radius: 14px;
      box-shadow: var(--shadow);
      padding: 30px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 22px rgba(0, 0, 0, 0.15);
    }

    form {
      display: flex;
      flex-direction: column;
    }

    h2 {
      text-align: center;
      color: var(--accent);
      margin-bottom: 20px;
    }

    input {
      padding: 12px 14px;
      margin-bottom: 14px;
      border: 1px solid #ffffffff;
      border-radius: 8px;
      font-size: 15px;
      transition: all 0.2s ease-in-out;
    }

    input:focus {
      border-color: var(--accent);
      outline: none;
      box-shadow: 0 0 0 3px rgba(26, 79, 255, 0.2);
    }

    button {
      background: var(--accent);
      color: #fff;
      border: none;
      padding: 12px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: background 0.2s ease-in-out;
    }

    button:hover {
      background: var(--accent-hover);
    }

    p {
      text-align: center;
      font-size: 14px;
      margin-top: 10px;
      color: #444;
    }

    a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 500;
    }

    a:hover {
      text-decoration: underline;
    }

    .msg {
      font-size: 14px;
      text-align: center;
      margin-top: 8px;
    }

    .msg.error {
      color: var(--error-color);
    }

    .msg.success {
      color: var(--success-color);
    }

    @media (max-width: 480px) {
      body {
        padding: 20px;
        height: auto;
      }

      .card {
        padding: 25px 20px;
      }
    }
  </style>
</head>

<body>
  <main class="container">
    <h1>BlueWaves</h1>

    <video autoplay loop muted playsinline class="bg-video">
      <source src="../ombak.mp4" type="video/mp4">
    </video>

    <section id="auth">
      <div class="card">
        <div id="forms">

          <form action="" method="post" id="registerForm"> 
           <h2>Register</h2>
        <p style="font-style:italic; color: red;"><?= $msg ?></p>
        <input type="text" placeholder="username" name="username">
        <input type="password" placeholder="password" name="password">
        <input type="password" placeholder="konfirmasi password" name="password2">
        <button type="submit" name="register">Daftar</button>
            <div id="regrisMsg" class="msg"></div>
          </form>
      </div>
    </section>
  </main>

  <script src="/js/app.js"></script>

</body>

</html>