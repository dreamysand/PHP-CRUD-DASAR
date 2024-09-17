<?php
session_start(); // Mulai sesi

// Cek apakah user sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Jika belum login, redirect ke halaman login
    header("Location: login.php");
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

//DELETE
if (isset($_GET['delete'])) {
    $nis = $_GET['delete'];
    $hal = $_GET['start_show_hal'];
    $kelas = $_GET['kelas'];

    if ($kelas=='11 RPL 1') {
        $stmt = $conn->prepare("DELETE FROM nilai_mkk WHERE nis = ?");
        $stmt->bind_param("s", $nis);
        if ($stmt->execute()/*execute() buat jalanin stmt yang diatas*/) {
            $conn->query("SET @presence = 0");//Ngeset variabel buat si absennya
            $conn->query("UPDATE nilai_mkk SET presensi = @presence := @presence + 1 ORDER BY nama ASC");//Ngeupdate nilai presensi dengan nambahin 1 di presensi sebelumnya
            ?>
                <script>
                    alert("DATA DIHAPUS");//Alert nis nya ditambahin
                    window.location.href = "index.php?start_show_hal=<?php echo $hal; ?>&kelas=<?php echo $kelas; ?>";
                </script>
            <?php
        }else {
            ?>
                <script>
                    alert("GAGAL");//Alert hapusnya gagal
                    window.location.href = "index.php?start_show_hal=<?php echo $hal; ?>&kelas=<?php echo $kelas; ?>";
                </script>
            <?php
        }
        $stmt->close();
        $conn->close();//nutup koneksi conn
    } else {
        $stmt = $conn->prepare("DELETE FROM nilai_mkk2 WHERE nis = ?");
        $stmt->bind_param("s", $nis);
        if ($stmt->execute()/*execute() buat jalanin stmt yang diatas*/) {
            $conn->query("SET @presence = 0");//Ngeset variabel buat si absennya
            $conn->query("UPDATE nilai_mkk2 SET presensi = @presence := @presence + 1 ORDER BY nama ASC");//Ngeupdate nilai presensi dengan nambahin 1 di presensi sebelumnya
            ?>
                <script>
                    alert("DATA DIHAPUS");//Alert nis nya ditambahin
                    window.location.href = "index.php?start_show_hal=<?php echo $hal; ?>&kelas=<?php echo $kelas; ?>";
                </script>
            <?php
        }else {
            ?>
                <script>
                    alert("GAGAL");//Alert hapusnya gagal
                    window.location.href = "index.php?start_show_hal=<?php echo $hal; ?>&kelas=<?php echo $kelas; ?>";
                </script>
            <?php
        }
        $stmt->close();
        $conn->close();//nutup koneksi conn
    }
    
}
//DROP ALL
if (isset($_GET['dropall'])) {
    $nis = $_GET['dropall'];
    $kelas = $_GET['kelas'];

    if ($kelas == '11 RPL 1') {
        $stmt = $conn->prepare("SELECT COUNT(*) AS baris FROM nilai_mkk");
        $stmt->execute();
        $stmt->bind_result($totalBaris);
        $stmt->fetch();
        $stmt->close();

        if ($totalBaris>0) {
            $stmt = $conn->prepare("DELETE FROM nilai_mkk");
            if ($stmt->execute()/*execute() buat jalanin stmt yang diatas*/) {
                ?>
                    <script>
                        alert("DATA DIHAPUS");//Alert nis nya ditambahin
                        window.location.href = "index.php";
                    </script>
                <?php
            }else {
                ?>
                    <script>
                        alert("GAGAL");//Alert hapusnya gagal
                        window.location.href = "index.php";
                    </script>
                <?php
            }
            $stmt->close();
        } else {
            ?>
                <script>
                    alert("DATA TIDAK ADA");//Alert hapusnya gagal
                    window.location.href = "index.php";
                </script>
            <?php
        }
        
        $conn->close();//nutup koneksi conn
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) AS baris FROM nilai_mkk2");
        $stmt->execute();
        $stmt->bind_result($totalBaris);
        $stmt->fetch();
        $stmt->close();

        if ($totalBaris>0) {
            $stmt = $conn->prepare("DELETE FROM nilai_mkk2");
            if ($stmt->execute()/*execute() buat jalanin stmt yang diatas*/) {
                ?>
                    <script>
                        alert("DATA DIHAPUS");//Alert nis nya ditambahin
                        window.location.href = "index.php";
                    </script>
                <?php
            }else {
                ?>
                    <script>
                        alert("GAGAL");//Alert hapusnya gagal
                        window.location.href = "index.php";
                    </script>
                <?php
            }
            $stmt->close();
        } else {
            ?>
                <script>
                    alert("DATA TIDAK ADA");//Alert hapusnya gagal
                    window.location.href = "index.php";
                </script>
            <?php
        }
        
        $conn->close();//nutup koneksi conn
    }

    
}
//Pagination & Search

if (isset($_GET['kelas'])) { 
    $pilihanKelas = $_GET['kelas'];

    function NormalizeString($normalForm){
        // Hapus semua karakter alfanumerik kecuali spasi
        $normalForm = preg_replace('/[^a-zA-Z0-9\s]/', '', $normalForm);
        // ngubah spasi jadi wildcard buat nyocokin
        $normalForm = str_replace(' ', '%', $normalForm);
        return $normalForm;
    }
    if ($pilihanKelas == '11 RPL 1') {
        $data_per_hal = 5;//Jumlah data per halnya
        if (isset($_GET['start_show_hal'])) {
            $start_show_hal = (int)$_GET['start_show_hal'];
        } else {
            $start_show_hal = 1; 
        }//Halaman berapa intinya

        if ($start_show_hal > 1) {
            $pos_awal_hal = ($start_show_hal * $data_per_hal) - $data_per_hal;
        } else {
            $pos_awal_hal = 0;
        }//Nentuin data di hal
    
        $prev = $start_show_hal - 1;//sebelumnya
        $next = $start_show_hal + 1;//sesudahnya

        $sql = "SELECT * FROM nilai_mkk WHERE 1=1";
        $sql_total_baris = "SELECT COUNT(*) AS total FROM nilai_mkk WHERE 1=1";
        if (isset($_GET['search']) && isset($_GET['jk'])) {
            $search = $_GET['search'];
            $search = trim($search);
            $search = normalizeString($search); // Normalisasi input

            $jkFilter = $_GET['jk'];

            if (!empty($search)) {
                $sql .= " AND nama LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            if (!empty($jkFilter)) {
                $sql .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
        } elseif (isset($_GET['search'])) {
            $search = $_GET['search'];
            $search = trim($search);
            $search = normalizeString($search); // Normalisasi input
            if (!empty($search)) {
                $sql .= " AND nama LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
        } elseif (isset($_GET['jk'])) {
            $jkFilter = $_GET['jk'];
            if (!empty($jkFilter)) {
                $sql .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
        } else {
            $search="";
            $jkFilter = "";
            $sql = "SELECT * FROM nilai_mkk ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal"; // select data + nambah limit di query SQL buat ngambil datanya
            $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
        }
        if (isset($_GET['search']) && isset($_GET['jk'])) {
            $search = $_GET['search'];
            $search = trim($search);
            $search = normalizeString($search); // Normalisasi input


            $jkFilter = $_GET['jk'];

            if (!empty($search)) {
                $sql_total_baris .= " AND nama LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            if (!empty($jkFilter)) {
                $sql_total_baris .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql_total_baris .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
        } elseif (isset($_GET['search'])) {
            $search = $_GET['search'];
            $search = trim($search);
            $search = normalizeString($search); // Normalisasi input
            if (!empty($search)) {
                $sql_total_baris .= " AND nama LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql_total_baris .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
        } elseif (isset($_GET['jk'])) {
            $jkFilter = $_GET['jk'];
            if (!empty($jkFilter)) {
                $sql_total_baris .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql_total_baris .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
        } else {
            $search="";
            $jkFilter = "";
            $sql_total_baris = "SELECT COUNT(*) AS total FROM nilai_mkk"; // Hitung baris
            $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
        }
        $row_total_data = $result_total_data->fetch_assoc();//ngubah hasilnya jadi array tujuannya biar datanya lebih enak di atur
        $total_baris = $row_total_data['total'];
        $total_halaman = ceil($total_baris / $data_per_hal);//rumus total halaman
   } else {
        $data_per_hal = 5;//Jumlah data per halnya
        if (isset($_GET['start_show_hal'])) {
            $start_show_hal = (int)$_GET['start_show_hal'];
        } else {
            $start_show_hal = 1;
        }//Halaman berapa intinya

        if ($start_show_hal > 1) {
            $pos_awal_hal = ($start_show_hal * $data_per_hal) - $data_per_hal;
        } else {
            $pos_awal_hal = 0;
        }//Nentuin data di hal
    
        $prev = $start_show_hal - 1;//sebelumnya
        $next = $start_show_hal + 1;//sesudahnya

        $sql = "SELECT * FROM nilai_mkk2 WHERE 1=1";
        $sql_total_baris = "SELECT COUNT(*) AS total FROM nilai_mkk2 WHERE 1=1";
        if (isset($_GET['search']) && isset($_GET['jk'])) {
            $search = $_GET['search'];
            $search = trim($search);
            $search = normalizeString($search); // Normalisasi input

            $jkFilter = $_GET['jk'];

            if (!empty($search)) {
                $sql .= " AND nama LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            if (!empty($jkFilter)) {
                $sql .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
        } elseif (isset($_GET['search'])) {
            $search = $_GET['search'];
            $search = trim($search);
            $search = normalizeString($search); // Normalisasi input
            if (!empty($search)) {
                $sql .= " AND nama LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
        } elseif (isset($_GET['jk'])) {
            $jkFilter = $_GET['jk'];
            if (!empty($jkFilter)) {
                $sql .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
        } else {
            $search="";
            $jkFilter = "";
            $sql = "SELECT * FROM nilai_mkk2 ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal"; // select data + nambah limit di query SQL buat ngambil datanya
            $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
        }
        if (isset($_GET['search']) && isset($_GET['jk'])) {
            $search = $_GET['search'];
            $search = trim($search);
            $search = normalizeString($search); // Normalisasi input


            $jkFilter = $_GET['jk'];

            if (!empty($search)) {
                $sql_total_baris .= " AND nama LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            if (!empty($jkFilter)) {
                $sql_total_baris .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql_total_baris .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
        } elseif (isset($_GET['search'])) {
            $search = $_GET['search'];
            $search = trim($search);
            $search = normalizeString($search); // Normalisasi input
            if (!empty($search)) {
                $sql_total_baris .= " AND nama LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql_total_baris .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
        } elseif (isset($_GET['jk'])) {
            $jkFilter = $_GET['jk'];
            if (!empty($jkFilter)) {
                $sql_total_baris .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql_total_baris .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
        } else {
            $search="";
            $jkFilter = "";
            $sql_total_baris = "SELECT COUNT(*) AS total FROM nilai_mkk2"; // Hitung baris
            $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
        }
        $row_total_data = $result_total_data->fetch_assoc();//ngubah hasilnya jadi array tujuannya biar datanya lebih enak di atur
        $total_baris = $row_total_data['total'];
        $total_halaman = ceil($total_baris / $data_per_hal);//rumus total halaman
   }

} else {
    $pilihanKelas = '11 RPL 1';
    function NormalizeString($normalForm){
        // Hapus semua karakter alfanumerik kecuali spasi
        $normalForm = preg_replace('/[^a-zA-Z0-9\s]/', '', $normalForm);
        // ngubah spasi jadi wildcard buat nyocokin
        $normalForm = str_replace(' ', '%', $normalForm);
        return $normalForm;
    }
    $data_per_hal = 5;//Jumlah data per halnya
        if (isset($_GET['start_show_hal'])) {
            $start_show_hal = (int)$_GET['start_show_hal'];
        } else {
            $start_show_hal = 1;
        }//Halaman berapa intinya

        if ($start_show_hal > 1) {
            $pos_awal_hal = ($start_show_hal * $data_per_hal) - $data_per_hal;
        } else {
            $pos_awal_hal = 0;
        }//Nentuin data di hal
    
        $prev = $start_show_hal - 1;//sebelumnya
        $next = $start_show_hal + 1;//sesudahnya
        
        
        $sql = "SELECT * FROM nilai_mkk WHERE 1=1";
        $sql_total_baris = "SELECT COUNT(*) AS total FROM nilai_mkk WHERE 1=1";
        if (isset($_GET['search']) && isset($_GET['jk'])) {
            $search = $_GET['search'];
            $search = trim($search);
            $search = normalizeString($search); // Normalisasi input

            $jkFilter = $_GET['jk'];

            if (!empty($search)) {
                $sql .= " AND nama LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            if (!empty($jkFilter)) {
                $sql .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
        } elseif (isset($_GET['search'])) {
            $search = $_GET['search'];
            $search = trim($search);
            $search = normalizeString($search); // Normalisasi input
            if (!empty($search)) {
                $sql .= " AND nama LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
        } elseif (isset($_GET['jk'])) {
            $jkFilter = $_GET['jk'];
            if (!empty($jkFilter)) {
                $sql .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
        } else {
            $search="";
            $jkFilter = "";
            $sql = "SELECT * FROM nilai_mkk ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal"; // select data + nambah limit di query SQL buat ngambil datanya
            $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
        }
        if (isset($_GET['search']) && isset($_GET['jk'])) {
            $search = $_GET['search'];
            $search = trim($search);
            $search = normalizeString($search); // Normalisasi input


            $jkFilter = $_GET['jk'];

            if (!empty($search)) {
                $sql_total_baris .= " AND nama LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            if (!empty($jkFilter)) {
                $sql_total_baris .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql_total_baris .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
        } elseif (isset($_GET['search'])) {
            $search = $_GET['search'];
            $search = trim($search);
            $search = normalizeString($search); // Normalisasi input
            if (!empty($search)) {
                $sql_total_baris .= " AND nama LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql_total_baris .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
        } elseif (isset($_GET['jk'])) {
            $jkFilter = $_GET['jk'];
            if (!empty($jkFilter)) {
                $sql_total_baris .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
            }
            $sql_total_baris .= " ORDER BY nama LIMIT $pos_awal_hal, $data_per_hal";
            $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
        } else {
            $search="";
            $jkFilter = "";
            $sql_total_baris = "SELECT COUNT(*) AS total FROM nilai_mkk"; // Hitung baris
            $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
        }
        $row_total_data = $result_total_data->fetch_assoc();//ngubah hasilnya jadi array tujuannya biar datanya lebih enak di atur
        $total_baris = $row_total_data['total'];
        $total_halaman = ceil($total_baris / $data_per_hal);//rumus total halaman

}

if (isset($_GET['guru'])) {
        $data_per_hal = 5;//Jumlah data per halnya
            if (isset($_GET['start_show_hal'])) {
                $start_show_hal = (int)$_GET['start_show_hal'];
            } else {
                $start_show_hal = 1;
            }//Halaman berapa intinya

            if ($start_show_hal > 1) {
                $pos_awal_hal = ($start_show_hal * $data_per_hal) - $data_per_hal;
            } else {
                $pos_awal_hal = 0;
            }//Nentuin data di hal
        
            $prev = $start_show_hal - 1;//sebelumnya
            $next = $start_show_hal + 1;//sesudahnya
            
            
            $sql = "SELECT * FROM guru WHERE 1=1";
            $sql_total_baris = "SELECT COUNT(*) AS total FROM guru WHERE 1=1";
            if (isset($_GET['search']) && isset($_GET['jk'])) {
                $search = $_GET['search'];
                $search = trim($search);
                $search = normalizeString($search); // Normalisasi input

                $jkFilter = $_GET['jk'];

                if (!empty($search)) {
                    $sql .= " AND namaguru LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
                }
                if (!empty($jkFilter)) {
                    $sql .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
                }
                $sql .= " ORDER BY namaguru LIMIT $pos_awal_hal, $data_per_hal";
                $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
            } elseif (isset($_GET['search'])) {
                $search = $_GET['search'];
                $search = trim($search);
                $search = normalizeString($search); // Normalisasi input
                if (!empty($search)) {
                    $sql .= " AND namaguru LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
                }
                $sql .= " ORDER BY namaguru LIMIT $pos_awal_hal, $data_per_hal";
                $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
            } elseif (isset($_GET['jk'])) {
                $jkFilter = $_GET['jk'];
                if (!empty($jkFilter)) {
                    $sql .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
                }
                $sql .= " ORDER BY namaguru LIMIT $pos_awal_hal, $data_per_hal";
                $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
            } else {
                $search="";
                $jkFilter = "";
                $sql = "SELECT * FROM guru ORDER BY id LIMIT $pos_awal_hal, $data_per_hal"; // select data + nambah limit di query SQL buat ngambil datanya
                $result = $conn->query($sql);//nampilin hasil menurut isi dari sqlnya
            }
            if (isset($_GET['search']) && isset($_GET['jk'])) {
                $search = $_GET['search'];
                $search = trim($search);
                $search = normalizeString($search); // Normalisasi input


                $jkFilter = $_GET['jk'];

                if (!empty($search)) {
                    $sql_total_baris .= " AND namaguru LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
                }
                if (!empty($jkFilter)) {
                    $sql_total_baris .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
                }
                $sql_total_baris .= " ORDER BY namaguru LIMIT $pos_awal_hal, $data_per_hal";
                $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
            } elseif (isset($_GET['search'])) {
                $search = $_GET['search'];
                $search = trim($search);
                $search = normalizeString($search); // Normalisasi input
                if (!empty($search)) {
                    $sql_total_baris .= " AND namaguru LIKE '%$search%'"; // select data + nambah limit di query SQL buat ngambil datanya
                }
                $sql_total_baris .= " ORDER BY namaguru LIMIT $pos_awal_hal, $data_per_hal";
                $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
            } elseif (isset($_GET['jk'])) {
                $jkFilter = $_GET['jk'];
                if (!empty($jkFilter)) {
                    $sql_total_baris .= " AND jeniskelamin = '$jkFilter'"; // select data + nambah limit di query SQL buat ngambil datanya
                }
                $sql_total_baris .= " ORDER BY namaguru LIMIT $pos_awal_hal, $data_per_hal";
                $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
            } else {
                $search="";
                $jkFilter = "";
                $sql_total_baris = "SELECT COUNT(*) AS total FROM guru"; // Hitung baris
                $result_total_data = $conn->query($sql_total_baris);//ngambil hasil SQL nya
            }
            $row_total_data = $result_total_data->fetch_assoc();//ngubah hasilnya jadi array tujuannya biar datanya lebih enak di atur
            $total_baris = $row_total_data['total'];
            $total_halaman = ceil($total_baris / $data_per_hal);//rumus total halaman
} 
        
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nilai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            position: relative;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            position: fixed;
            top: 0;
            bottom: 0;
            left: -250px;
            transition: left 0.3s ease;
            z-index: 999;
        }

        .sidebar.active {
            left: 0;
        }

        .main-content {
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }

        .main-content.shifted {
            margin-left: 250px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
        }
        .overlay.active {
            display: block;
        }
        .table-custom {
            color: white;
            border-color: black;
            text-align: center;
        }
        .table-custom th {
            background-color: red;
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
        .main-content {
            width: 100%;
        }

        @media (max-width: 600px) {
            table {
                font-size: 70%;
            }
            select {
                width: 50%;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar Atas  -->
    <nav class="navbar bg-primary">
    <a class="navbar-brand text-white" href="#">DATA KELAS 11 RPL</a>
    <button class="navbar-toggler text-white" type="button" aria-controls="navbarFunc" aria-expanded="false" aria-label="Toggle navigation" id="sidebarToggle">
        <span class="navbar-toggler-icon"></span>
    </button>
</nav>

<div class="overlay" id="overlay"></div>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar p-3" id="sidebar">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="#" class="nav-link text-white active">Home</a>
            </li>
            <li class="nav-item">
                <a href="#ordersCollapse" class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="ordersCollapse">
                    Kelas <span id="ordersIcon" class="ms-auto">&gt;</span>
                </a>
                <div class="collapse" id="ordersCollapse">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="index.php?kelas=11 RPL 1">11 RPL 1</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="index.php?kelas=11 RPL 2">11 RPL 2</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a href="#toolsCollapse" class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="toolsCollapse">
                    Tools <span id="toolsIcon" class="ms-auto">&gt;</span>
                </a>
                <div class="collapse" id="toolsCollapse">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="insert.php?start_show_hal=<?php echo $start_show_hal; ?>&kelas=<?php echo $pilihanKelas ?>">Insert to Table</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="index.php?kelas=11 RPL 2">Drop All Record</a>
                        </li>
                    </ul>
                </div>
            </li>
             <li class="nav-item">
                <a href="#guruCollapse" class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="guruCollapse">
                    Guru <span id="guruIcon" class="ms-auto">&gt;</span>
                </a>
                <div class="collapse" id="guruCollapse">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="?guru">Tabel Guru</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="insertguru.php?start_show_hal=<?php echo $start_show_hal; ?>">Insert Guru</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <a href="#" class="nav-link text-white">Profile</a>
            </li>
            <li>
                <a href="logout.php" class="nav-link text-white">Log Out</a>
            </li>
        </ul>
    </div>

    <!-- Main content area -->
    <div class="main-content p-4">
        <!-- Search bar dan Filter-->
        <div class="d-flex justify-content-between mb-3">
            <div class="input-group" style="max-width: 300px;">
                <form method="get" action="" class="d-flex">
                    <input type="text" name="kelas" hidden value="<?php echo $pilihanKelas; ?>">
                    <input type="text" name="jk" hidden value="<?php if (isset($_GET['jk'])) {
                        echo $jkFilter;
                    } else {
                        echo '';
                    } ?>">
                    <input type="text" class="form-control" placeholder="Search" aria-label="Search" name="search">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div>
                <form action="" method="get" class="d-flex">
                    <input type="text" name="kelas" hidden value="<?php echo $pilihanKelas; ?>">
                    <input type="text" name="search" hidden value="<?php if (isset($_GET['search'])) {
                        echo $search;
                    } else {
                        echo '';
                    } ?>">
                    <select class="form-select" style="background-color: #6f42c1; color: white; max-width: 150px;" name="jk">
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                        <option value="">Clear</option>
                    </select>
                    <button class="btn" style="background-color: #6f42c1; color: white;" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        <!-- Table -->
        <?php
if (isset($_GET['guru'])) {
    if ($result->num_rows > 0) { 
        ?> 
        <table border="1" class="table table-bordered table-custom">
            <th colspan="10" style="border: none;">
                TABEL GURU
            </th>
            <tr>
                <th>Id</th>
                <th>Nama</th>
                <th>JK</th>
                <th>Jabatan</th>
                <th>Mapel</th>
                <th>Drop</th>
                <th>Show</th>
                <th>Edit</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr style="height: 2rem;">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['namaguru']; ?></td>
                    <td><?php echo $row['jk']; ?></td>
                    <td><?php echo $row['jabatan']; ?></td>
                    <td><?php echo $row['mapel']; ?></td>
                    <td><a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Apakah anda yakin untuk menghapus data ini ?')"><i class="fa-solid fa-trash"></i></a></td>
                    <td><a href="show.php?show=<?php echo $row['id']; ?>"><i class="fa-solid fa-eye"></i></i></a></td>
                    <td><a href="edit.php?edit=<?php echo $row['id']; ?>"><i class="fa-solid fa-pen-to-square"></i></a></td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <table border="1" class="table table-bordered table-custom">
            <th colspan="10" style="border: none;">
                <?php if (isset($_GET['kelas'])) { ?>
                    TABEL NILAI <?php echo $pilihanKelas; ?>
                <?php } else { ?>
                    TABEL NILAI 11 RPL 1
                <?php } ?>
            </th>
            <tr>
                <th>No.Absen</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>JK</th>
                <th>Nilai PWEB</th>
                <th>Nilai PBO</th>
                <th>Nilai BASDAT</th>
                <th colspan="2">Opsi</th>
            </tr>
            <tr style="height: 2rem;">
                <td colspan="10">TIDAK ADA DATA</td>
            </tr>
        </table>
    <?php }
} else {
    if ($result->num_rows > 0) { 
        ?> 
        <table border="1" class="table table-bordered table-custom">
            <th colspan="10" style="border: none;">
                <?php if (isset($_GET['kelas'])) { ?>
                    TABEL NILAI <?php echo $pilihanKelas; ?>
                <?php } else { ?>
                    TABEL NILAI 11 RPL 1
                <?php } ?>
            </th>
            <tr>
                <th>No.Absen</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>JK</th>
                <th>Nilai PWEB</th>
                <th>Nilai PBO</th>
                <th>Nilai BASDAT</th>
                <th>Drop</th>
                <th>Edit</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr style="height: 2rem;">
                    <td><?php echo $row['presensi']; ?></td>
                    <td><?php echo $row['nis']; ?></td>
                    <td><?php echo $row['nama']; ?></td>
                    <td><?php echo $row['kelas']; ?></td>
                    <td><?php echo $row['jeniskelamin']; ?></td>
                    <td <?php if ($row['nilaipweb'] <= 78) { ?>style="color: #F50C0C;"<?php } ?>><?php echo $row['nilaipweb']; ?></td>
                    <td <?php if ($row['nilaipbo'] <= 78) { ?>style="color: #F50C0C;"<?php } ?>><?php echo $row['nilaipbo']; ?></td>
                    <td <?php if ($row['nilaidb'] <= 78) { ?>style="color: #F50C0C;"<?php } ?>><?php echo $row['nilaidb']; ?></td>
                    <td><a href="?delete=<?php echo $row['nis']; ?>&start_show_hal=<?php echo $start_show_hal; ?>&kelas=<?php echo $row['kelas']; ?>" onclick="return confirm('Apakah anda yakin untuk menghapus data ini ?')"><i class="fa-solid fa-trash"></i></a></td>
                    <td><a href="edit.php?edit=<?php echo $row['nis']; ?>&start_show_hal=<?php echo $start_show_hal; ?>&kelas=<?php echo $row['kelas']; ?>"><i class="fa-solid fa-pen-to-square"></i></a></td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <table border="1" class="table table-bordered table-custom">
            <th colspan="10" style="border: none;">
                <?php if (isset($_GET['kelas'])) { ?>
                    TABEL NILAI <?php echo $pilihanKelas; ?>
                <?php } else { ?>
                    TABEL NILAI 11 RPL 1
                <?php } ?>
            </th>
            <tr>
                <th>No.Absen</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>JK</th>
                <th>Nilai PWEB</th>
                <th>Nilai PBO</th>
                <th>Nilai BASDAT</th>
                <th colspan="2">Opsi</th>
            </tr>
            <tr style="height: 2rem;">
                <td colspan="10">TIDAK ADA DATA</td>
            </tr>
        </table>
    <?php }
}
?>


        <!-- Pagination (pink) -->
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center" style="background-color: #f8d7da;">
                <?php if ($start_show_hal > 1) { ?>
                    <li class="page-item"><a class="page-link" href="?start_show_hal=<?php echo $prev; ?>&search=<?php echo $search; ?>">Prev</a></li>
                <?php } ?>
                
                <?php for ($i = 1; $i <= $total_halaman; $i++) { ?>
                    <li class="page-item"><a class="page-link" href="?start_show_hal=<?php echo $i; ?>&search=<?php echo $search; ?>" class="<?php if ($start_show_hal == $i) echo 'active'; ?>"><?php echo $i; ?></a></li>
                <?php } ?>
                
                <?php if ($start_show_hal < $total_halaman) { ?>
                    <li class="page-item"><a class="page-link" href="?start_show_hal=<?php echo $next; ?>&search=<?php echo $search; ?>">Next</a></li>
                <?php } ?>
            </ul>
        </nav>
    </div>
</div>

<!-- Footer -->
<footer class="footer-custom mt-auto">
    &copy; 2024 Dean. Ya Udah Lah.
</footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Script buat ngilangin teks ketika ukuran layar berubah -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        function cekBesarLayar() {
            console.log('cekBesarLayar function called');
            const conditionals = document.querySelectorAll('.conditional');
            conditionals.forEach(text => {
                if (window.innerWidth > 600) {
                    text.style.display = 'inline';
                } else {
                    text.style.display = 'none';
                }
            });
        }
        window.addEventListener('resize', cekBesarLayar);
        cekBesarLayar();
    });

    </script>
    <script>
    document.querySelector('#ordersCollapse').addEventListener('show.bs.collapse', function () {
        document.querySelector('#ordersIcon').textContent = 'v';
    });

    document.querySelector('#ordersCollapse').addEventListener('hide.bs.collapse', function () {
        document.querySelector('#ordersIcon').textContent = '>';
    });
    document.querySelector('#guruCollapse').addEventListener('show.bs.collapse', function () {
        document.querySelector('#guruIcon').textContent = 'v';
    });

    document.querySelector('#guruCollapse').addEventListener('hide.bs.collapse', function () {
        document.querySelector('#guruIcon').textContent = '>';
    });
    document.querySelector('#toolsCollapse').addEventListener('show.bs.collapse', function () {
        document.querySelector('#toolsIcon').textContent = 'v';
    });

    document.querySelector('#toolsCollapse').addEventListener('hide.bs.collapse', function () {
        document.querySelector('#toolsIcon').textContent = '>';
    });
    </script>
    <script>
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const mainContent = document.getElementById('mainContent');
        
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        mainContent.classList.toggle('shifted');
    });

    document.getElementById('overlay').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const mainContent = document.getElementById('mainContent');
        
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        mainContent.classList.remove('shifted');
    });
</script>
    <!-- Script Icon -->
    <script src="https://kit.fontawesome.com/c2a393db2e.js" crossorigin="anonymous"></script>

</body>
</html>
