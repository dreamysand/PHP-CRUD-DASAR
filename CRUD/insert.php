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
if (isset($_GET['kelas'])) {
    $getKelas = $_GET['kelas'];
}
if (isset($_GET['start_show_hal'])) {
    $hal = $_GET['start_show_hal'];
}
//INPUT
//Data dari form 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nis']) && isset($_POST['nama']) && isset($_POST['kelas']) && isset($_POST['nilaipweb']) && isset($_POST['nilaipbo']) && isset($_POST['nilaidb'])) {
    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $jk = $_POST['jk'];
    $nilaipweb = $_POST['nilaipweb'];
    $nilaipbo = $_POST['nilaipbo'];
    $nilaidb = $_POST['nilaidb'];
    $hal = $_POST['hal'];
    //Input 
    //Cek nis
    $stmt = $conn->prepare("SELECT COUNT(*) FROM nilai_mkk WHERE nis=?");//buat template sql
    $stmt->bind_param("s",$nis);
    $stmt->execute();
    $stmt->bind_result($valueNis);
    $stmt->fetch();
    $stmt->close();
    // Cek nama
    $stmt = $conn->prepare("SELECT COUNT(*) FROM nilai_mkk WHERE nama=?");//buat template sql
    $stmt->bind_param("s",$nama);
    $stmt->execute();
    $stmt->bind_result($valueNama);
    $stmt->fetch();
    $stmt->close(); 
    if ($valueNis>0) { ?>
        <script>
            alert("NIS SUDAH ADA");
            window.location.href = "index.php?start_show_hal=<?php echo $hal; ?>&kelas=<?php echo $kelas; ?>";
        </script>
        <?php
    } else if ($valueNama>0) { ?>
        <script>
            alert("NAMA SUDAH ADA");
            window.location.href = "index.php?start_show_hal=<?php echo $hal; ?>&kelas=<?php echo $kelas; ?>";
        </script>
        <?php
    } else {
        if ($kelas == '11 RPL 1') {
            $stmt = $conn->prepare("INSERT INTO nilai_mkk (nis, nama, kelas, jeniskelamin, nilaipweb, nilaipbo, nilaidb) VALUES (?, ?, ?, ?, ?, ?, ?)");//buat template sql
            $stmt->bind_param("ssssiii", $nis, $nama, $kelas, $jk, $nilaipweb, $nilaipbo, $nilaidb);//input value ke template sql
            //Cek masuk
            if ($stmt->execute()/*execute() buat jalanin stmt yang diatas*/) {
                $conn->query("SET @presence = 0");//
                $conn->query("UPDATE nilai_mkk SET presensi = @presence := @presence + 1 ORDER BY nama ASC");
                ?>
                <script>
                    alert("DATA DITAMBAHKAN");//Alert nis nya ditambahin
                    window.location.href = "index.php?start_show_hal=<?php echo $hal; ?>&kelas=<?php echo $kelas; ?>";
                </script>
               <?php
            }else { ?>
             <script>
                    alert("GAGAL");//Alert nambahinnya gagal
                    window.location.href = "index.php?start_show_hal=<?php echo $hal; ?>&kelas=<?php echo $kelas; ?>";
                </script>

            <?php
            }
            $stmt->close(); 
        } else {
            $stmt = $conn->prepare("INSERT INTO nilai_mkk2 (nis, nama, kelas, jeniskelamin, nilaipweb, nilaipbo, nilaidb) VALUES (?, ?, ?, ?, ?, ?, ?)");//buat template sql
            $stmt->bind_param("ssssiii", $nis, $nama, $kelas, $jk, $nilaipweb, $nilaipbo, $nilaidb);//input value ke template sql
            //Cek masuk
            if ($stmt->execute()/*execute() buat jalanin stmt yang diatas*/) {
                $conn->query("SET @presence = 0");//
                $conn->query("UPDATE nilai_mkk2 SET presensi = @presence := @presence + 1 ORDER BY nama ASC");
                ?>
                <script>
                    alert("DATA DITAMBAHKAN");//Alert nis nya ditambahin
                    window.location.href = "index.php?start_show_hal=<?php echo $hal; ?>&kelas=<?php echo $kelas; ?>";
                </script>
               <?php
            }else { ?>
             <script>
                    alert("GAGAL");//Alert nambahinnya gagal
                    window.location.href = "index.php?start_show_hal=<?php echo $hal; ?>&kelas=<?php echo $kelas; ?>";
                </script>

            <?php
            }
            $stmt->close(); 
        }
    $conn->close();//nutup koneksi conn
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
        <button class="btn btn-back" type="button" onclick="window.location.href='index.php?start_show_hal=<?php echo $hal; ?>&kelas=<?php echo $getKelas; ?>'">
            <span>Back</span>
        </button>
    </nav>

    <!-- Form Container -->
    <div class="container form-container">
        <h2 class="text-center mb-4">Isi Data Nilai MKK</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <!-- Hidden Input -->
            <input type="number" name="hal" hidden value="<?php echo htmlspecialchars($hal); ?>">

            <!-- NIS -->
            <div class="mb-3">
                <label for="nis" class="form-label">NIS:</label>
                <input type="number" class="form-control" name="nis" required placeholder="Isi dengan angka, contoh: 1, 2, 3, dll">
            </div>

            <!-- Nama -->
            <div class="mb-3">
                <label for="nama" class="form-label">Nama:</label>
                <input type="text" class="form-control" name="nama" required placeholder="Isi dengan nama lengkap anda, contoh: Jennifer Bros">
            </div>

            <!-- Kelas -->
            <div class="mb-3">
                <label for="kelas" class="form-label">Kelas:</label>
                <select class="form-select" name="kelas" required>
                    <option <?php if ($getKelas == '11 RPL 1') echo "selected"; ?>>11 RPL 1</option>
                    <option <?php if ($getKelas == '11 RPL 2') echo "selected"; ?>>11 RPL 2</option>
                </select>
            </div>

            <!-- Jenis Kelamin -->
            <div class="mb-3">
                <label for="jk" class="form-label">Jenis Kelamin:</label>
                <select class="form-select" name="jk" required>
                    <option>Laki-laki</option>
                    <option>Perempuan</option>
                </select>
            </div>

            <!-- Nilai -->
            <div class="mb-3">
                <label for="nilaipweb" class="form-label">Nilai PWEB:</label>
                <input type="number" class="form-control" name="nilaipweb" required max="100" min="0" placeholder="Isi dengan angka, contoh: 1, 2, 3, dll">
            </div>

            <div class="mb-3">
                <label for="nilaipbo" class="form-label">Nilai PBO:</label>
                <input type="number" class="form-control" name="nilaipbo" required placeholder="Isi dengan angka, contoh: 1, 2, 3, dll">
            </div>

            <div class="mb-3">
                <label for="nilaidb" class="form-label">Nilai BASDAT:</label>
                <input type="number" class="form-control" name="nilaidb" required placeholder="Isi dengan angka, contoh: 1, 2, 3, dll">
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
