<?php
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

//Simpan Halaman
if (isset($_GET['start_show_hal'])) {
    $hal = $_GET['start_show_hal'];
}
//INPUT
//Data dari form 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nama']) && isset($_POST['jk']) && isset($_POST['jabatan']) && isset($_POST['mapel'])) {
    $nama = $_POST['nama'];
    $jk = $_POST['jk'];
    $jabatan = $_POST['jabatan'];
    $mapel = $_POST['mapel'];
    $gambar = $_POST['gambar'];
    $hal = $_POST['hal'];
    //Input 
    // Cek nama
    $stmt = $conn->prepare("SELECT COUNT(*) FROM guru WHERE namaguru=?");//buat template sql
    $stmt->bind_param("s",$nama);
    $stmt->execute();
    $stmt->bind_result($valueNama);
    $stmt->fetch();
    $stmt->close(); 
    if ($valueNama>0) { ?>
        <script>
            alert("NAMA SUDAH ADA");
            window.location.href = "index.php?guru&start_show_hal=<?php echo $hal; ?>";
        </script>
        <?php
    } else {
        $stmt = $conn->prepare("INSERT INTO guru (namaguru, jk, jabatan, mapel, gambar) VALUES (?, ?, ?, ?, ?)");//buat template sql
        $stmt->bind_param("sssss", $nama, $jk, $jabatan, $mapel, $gambar);//input value ke template sql
            
        //Cek masuk
        if ($stmt->execute()/*execute() buat jalanin stmt yang diatas*/) {
            ?>
            <script>
                alert("DATA DITAMBAHKAN");//Alert nis nya ditambahin
                window.location.href = "index.php?guru&start_show_hal=<?php echo $hal; ?>";
            </script>
            <?php
        }else { ?>
             <script>
                alert("GAGAL");//Alert nambahinnya gagal
                window.location.href = "index.php?guru&start_show_hal=<?php echo $hal; ?>";
            </script>

        <?php
        }
        $stmt->close(); 
    } 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai</title>
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
    <!-- Navbar -->
    <nav class="navbar bg-primary">
        <a class="navbar-brand text-white" href="#">DATA KELAS 11 RPL</a>
        <button class="btn btn-back" type="button" onclick="window.location.href='index.php?guru?start_show_hal=<?php echo $hal; ?>'">
            <span>Back</span>
        </button>
    </nav>

    <!-- Form Container -->
    <div class="container form-container">
        <h2 class="text-center mb-4">Isi Guru</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <!-- Hidden Input -->
            <input type="number" name="hal" hidden value="<?php echo htmlspecialchars($hal); ?>">

            <!-- Nama -->
            <div class="mb-3">
                <label for="nama" class="form-label">Nama:</label>
                <input type="text" class="form-control" name="nama" required placeholder="Isi dengan nama lengkap anda, contoh: Jennifer Bros">
            </div>

            <!-- Jenis Kelamin -->
            <div class="mb-3">
                <label for="jk" class="form-label">Jenis Kelamin:</label>
                <select class="form-select" name="jk" required>
                    <option>Laki-laki</option>
                    <option>Perempuan</option>
                </select>
            </div>

            <!-- Jabatan -->
            <div class="mb-3">
                <label for="jabatan" class="form-label">Jabatan:</label>
                <input type="text" class="form-control" name="jabatan" required placeholder="Isi dengan jabatan anda">
            </div>

            <!-- Mapel -->
            <div class="mb-3">
                <label for="mapel" class="form-label">Mapel:</label>
                <input type="text" class="form-control" name="mapel" required placeholder="Isi dengan mapel anda">
            </div>

            <!-- Gambar -->
            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar:</label>
                <input type="text" class="form-control" name="gambar" required placeholder="Isi dengan url gambar anda">
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" name="kirim" class="btn btn-custom">Kirim</button>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="footer-custom">
        &copy; 2024 Dean. Ya Udah Lah.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
