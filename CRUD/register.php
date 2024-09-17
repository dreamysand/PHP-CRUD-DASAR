<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: index.php"); // Redirect ke halaman index jika sudah login
    exit();
}
$servername = "localhost";
$username = "root"; //username db
$password = ""; //password db
$dbname = "nilai";//nama db

// Bikin koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//INPUT
//Data dari form 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($valueEmail);
    $stmt->fetch();
    $stmt->close();
    if ($valueEmail>0) {
        ?>
        <script>
            alert("Email Sudah Ada");
        </script>
        <?php
    } else {
        $passwordhash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admins (email, username, password) VALUES (?, ?, ?)");//buat template sql
        $stmt->bind_param("sss", $email, $username, $passwordhash);//input value ke template sql
        //Cek masuk
        if ($stmt->execute()/*execute() buat jalanin stmt yang diatas*/) {
            ?>
            <script>
                alert("<?php echo $username ?> Berhasil Terdaftar");
                window.location.href = "index.php";
            </script>
            <?php
        }else { 
            ?>
            <script>
                alert("Gagal Mendaftar");
            </script>
            <?php
        }
        $stmt->close(); 
    }
    $conn->close();//nutup koneksi conn
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: bold;
        }
        .btn-custom {
            background-color: #6f42c1;
            color: white;
            width: 100%;
        }
        .btn-custom:hover {
            background-color: #652CCD;
            color: white;
        }
        .btn-back {
            background-color: #6f42c1;
            margin-right: 0.2rem;
            color: white;
        }
        .btn-back:hover {
            background-color: #652CCD;
            color: white;
        }
        .footer-custom {
            background-color: orange;
            color: white;
            text-align: center;
            padding: 10px;
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
        }
    </style>
</head>
<body>
    <!-- Form Container -->
    <div class="container form-container">
        <h2 class="text-center mb-4">Register Data Nilai MKK</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <!-- email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" name="email" required placeholder="Isi dengan email, contoh: example@example.com">
            </div> 

            <!-- username -->
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" class="form-control" name="username" required placeholder="Isi dengan nama anda, contoh: John">
            </div>

            <!-- password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" name="login" class="btn btn-custom">Register</button>
            </div>
        </form>
        <p>Already Have Account? <a href="login.php">Login Here</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
