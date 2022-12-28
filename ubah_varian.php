<?php
    session_start();
    /**
     * Simple example of extending the SQLite3 class and changing the __construct
     * parameters, then using the open method to initialize the DB.
     */
    class MyDB extends SQLite3
    {
        function __construct()
        {
            $this->open('database_dorayaki.db');
        }
    }

    $db = new MyDB();

    $username = $_SESSION["username"];
    $is_admin = $_SESSION["is_admin"];

    $id = $_GET["id"];

    if (!isset($_SESSION["login"])) {
        header("Location: login.php");
        exit;
    }
    if (!$is_admin) {
        header("Location: dashboardUser.php");
        exit;
    }

    // Login expired jika tidak aktif selama 30 menit
    if (isset($_SESSION["last_active"])) {
        $seconds_inactive = time() - $_SESSION["last_active"];

        if ($seconds_inactive >= 1800) {
            header("Location: logout.php");
        }
    }
    $_SESSION["last_active"] = time();

    $statement = $db->prepare("SELECT rowid, * FROM dorayaki WHERE rowid = :id;");
    $statement->bindValue(":id", $id);
    $result = $statement->execute();
    
    $row = $result->fetchArray(SQLITE3_ASSOC);

    $nama = $row['nama'];
    $deskripsi = $row['deskripsi'];
    $harga = $row['harga'];
    $gambar = $row['gambar'];

    if(isset($_POST["search"])){
        $key = $_POST["search"];

        setcookie("searchKey", $key);

        header("Location: pencarianAdmin.php");
        exit();
  
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>AnakAyam - Ubah Varian</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="css/tambah_varian.css">
        <link rel="stylesheet" href="css/all.min.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Lobster&display=swap" rel="stylesheet">
    </head>
    <body>
        <div class="wrapper">
            <nav>
                <input type="checkbox" id="check">
                <label for="check" class="checkbtn"><i class="fas fa-bars"></i></label>
                <span><a href="">AnakAyam</a></span>
                <ul>
                    <li><a href="dashboardAdmin.php">Home<i class="fas fa-home"></i></a></li>
                    <li><a href="riwayatadmin.php">Riwayat<i class="fas fa-history"></i></i></a></li>
                    <li><a href="tambah_varian.php">Tambah Varian<i class="fas fa-plus"></i></a></li>
                    <li><a href="logout.php">Logout<i class="fas fa-sign-out-alt"></i></a></li>
                    <li><a href=""><?= $username; ?><i class="fas fa-user-alt"></i></a></li>
                </ul>
            </nav>
            <div class="search-container">
                <div class="search-bar">
                    <form action="" method="POST">
                        <input type="text" name="search" placeholder="Cari varian...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>

            <h1>Ubah Varian</h1>
            <div class="container">
                <div class="add">
                    <form enctype="multipart/form-data" action="" method="POST">
                        <label for="nama">Nama</label>
                        <br>
                        <textarea name="nama" cols="50" rows="1" required><?= $nama; ?></textarea>
                        <br>
                        <label for="deskripsi">Deskripsi</label>
                        <br>
                        <textarea name="deskripsi" id="deskripsi" cols="50" rows="4" required><?= $deskripsi; ?></textarea>
                        <br>
                        <label for="harga">Harga</label>
                        <br>
                        <input type="number" name="harga" value=<?= $harga; ?> required>
                        <br>
                        <label for="gambar">Gambar</label>
                        <img src="img/flavor/<?= $gambar; ?>" alt="" id="image">
                        <br>
                        <input type="file" name="gambar" accept="image/*">
                        <input type="submit" name="submit" value="Tambah">
                    </form>
                </div>
            </div>

            <div class="push"></div>
        </div>
        <footer>
            <p>AnakAyam&trade;</p>
        </footer>
        <script>
            function notify() {
                alert("Detail dorayaki berhasil diubah!");
            }
        </script>
    </body>
</html>

<?php
    if (isset($_POST['submit'])) {
        $nama = $_POST["nama"];
        $deskripsi = $_POST["deskripsi"];
        $harga = $_POST["harga"];

        $db->exec("UPDATE dorayaki SET nama = '$nama', deskripsi = '$deskripsi', harga = '$harga' WHERE rowid = '$id';");

        if ($_FILES["gambar"]["size"] != 0) {
            $filename = $_FILES["gambar"]["name"];
            $tempname = $_FILES["gambar"]["tmp_name"];

            $path_parts = pathinfo($_FILES["gambar"]["name"]);
            $extension = $path_parts["extension"];
            $folder = "img/flavor/" . $filename;

            $db->exec("UPDATE dorayaki SET gambar = '$filename' WHERE rowid = '$id';");

            if (move_uploaded_file($tempname, $folder))  {
                $msg = "Image uploaded successfully";
            }
            else {
                $msg = "Failed to upload image";
            }
        }

        echo "<script> notify(); </script>";
    }
?>