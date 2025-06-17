<?php
session_start();
include("config/db.php");

$error = "";

// Saat tombol login ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Validasi sederhana
    if ($username === "" || $password === "") {
        $error = "Harap isi semua field!";
    } else {
        // Cek username dan password langsung (tanpa hash)
        $stmt = $koneksi->prepare("SELECT * FROM kasir WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $data = $result->fetch_assoc();
            $_SESSION['id_kasir'] = $data['id_kasir'];
            $_SESSION['nama_kasir'] = $data['nama_kasir'];
            header("Location: home.php");
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login Kasir - Jayaraga Garut</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 320px;
        }
        h2 {
            text-align: center;
        }
        .form-group {
            margin-top: 15px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        button {
            width: 100%;
            margin-top: 20px;
            padding: 10px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Login Kasir</h2>
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" required />
        </div>
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required />
        </div>
        <button type="submit">Masuk</button>
    </form>
</div>

</body>
</html>
