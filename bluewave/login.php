<?php
require_once 'database.php';
session_start();

$msg = "";

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        if (password_verify($password, $data["password"])) {
            $_SESSION["username"] = $data["username"];
            $_SESSION["user_id"] = $data["id"];
            $_SESSION["role"] = $data["role"];
            $_SESSION["is_login"] = true;
            
            // Redirect berdasarkan role
            if ($_SESSION["role"] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $msg = "Password salah!";
        }
    } else {
        $msg = "Akun tidak ditemukan!";
    }
}
?>

?>

<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login - BlueWaves</title>
  <form action="index.php" method="POST"></form>
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
      <source src="img/ombak.mp4" type="video/mp4">
    </video>

    <section id="auth">
      <div class="card">
        <div id="forms">

          <form action="" method="post" id="loginForm">
            <h2>Login</h2>
            <p style="font-style:italic; color: red;"><?= $msg ?></p>
            <input name="username" placeholder="username" />
            <input name="password" type="password" placeholder="Password" />
            <button type="submit" name="login">Login</button>
            <p>
              Belum punya akun? <a href="game/register.php" id="showRegister">Daftar</a>
            </p>
            <div id="loginMsg" class="msg"></div>
          </form>
    </section>
  </main>

  <script src="/js/app.js"></script>

</body>

</html>