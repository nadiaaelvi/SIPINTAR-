
<?php
require_once "koneksi.php";

$pesan = "";
$jenisPesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $kode_barang = trim($_POST["kode_barang"]);
    $nama_barang = trim($_POST["nama_barang"]);
    $kategori = trim($_POST["kategori"]);
    $kondisi = trim($_POST["kondisi"]);
    $lokasi = trim($_POST["lokasi"]);

    if (
        $kode_barang == "" ||
        $nama_barang == "" ||
        $kategori == "" ||
        $kondisi == "" ||
        $lokasi == ""
    ) {
        $pesan = "Semua kolom wajib diisi.";
        $jenisPesan = "error";
    } else {

        $query = "INSERT INTO barang
        (kode_barang, nama_barang, kategori, kondisi, lokasi, status_barang)
        VALUES (?, ?, ?, ?, ?, 'Tersedia')";

        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param(
                "sssss",
                $kode_barang,
                $nama_barang,
                $kategori,
                $kondisi,
                $lokasi
            );

            if ($stmt->execute()) {
                header("Location: barang.php?pesan=berhasil");
                exit;
            } else {
                if ($conn->errno == 1062 || $stmt->errno == 1062) {
                    $pesan = "Kode barang sudah digunakan. Gunakan kode lain.";
                } else {
                    $pesan = "Data gagal disimpan. Periksa kembali data barang.";
                }
                $jenisPesan = "error";
            }

            $stmt->close();
        } else {
            $pesan = "Terjadi kesalahan saat menyiapkan penyimpanan data.";
            $jenisPesan = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang | SIPINTAR</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 35px;
            font-family: Arial, sans-serif;
            background: #f4f5fb;
            color: #17213c;
        }

        .container {
            max-width: 850px;
            margin: 20px auto;
            padding: 35px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.05);
        }

        h1 {
            margin-top: 0;
            color: #5145e5;
            font-size: 32px;
        }

        .keterangan {
            color: #71809b;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 9px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 14px;
            border: 1px solid #dce1ed;
            border-radius: 9px;
            font-size: 15px;
            background: white;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #5145e5;
        }

        .tombol {
            display: inline-block;
            padding: 13px 22px;
            border: none;
            border-radius: 9px;
            text-decoration: none;
            font-size: 15px;
            cursor: pointer;
        }

        .simpan {
            background: #5145e5;
            color: white;
        }

        .kembali {
            background: #e9eaf5;
            color: #39415b;
            margin-left: 8px;
        }

        .pesan {
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #ffe8e8;
            color: #b42318;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            .container {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Tambah Barang Inventaris</h1>

    <p class="keterangan">
        Masukkan informasi barang yang akan didaftarkan ke dalam SIPINTAR.
    </p>

    <?php if ($pesan != ""): ?>
        <div class="pesan">
            <?= htmlspecialchars($pesan); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">

        <div class="form-group">
            <label>Kode Barang</label>
            <input
                type="text"
                name="kode_barang"
                placeholder="Contoh: BRG-002"
                value="<?= htmlspecialchars($_POST['kode_barang'] ?? ''); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>Nama Barang</label>
            <input
                type="text"
                name="nama_barang"
                placeholder="Masukkan nama barang"
                value="<?= htmlspecialchars($_POST['nama_barang'] ?? ''); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>Kategori</label>
            <select name="kategori" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="Elektronik">Elektronik</option>
                <option value="Peralatan Kantor">Peralatan Kantor</option>
                <option value="Peralatan Kelas">Peralatan Kelas</option>
                <option value="Peralatan Lainnya">Peralatan Lainnya</option>
            </select>
        </div>

        <div class="form-group">
            <label>Kondisi Barang</label>
            <select name="kondisi" required>
                <option value="">-- Pilih Kondisi --</option>
                <option value="Baik">Baik</option>
                <option value="Rusak Ringan">Rusak Ringan</option>
                <option value="Rusak Berat">Rusak Berat</option>
            </select>
        </div>

        <div class="form-group">
            <label>Lokasi Barang</label>
            <input
                type="text"
                name="lokasi"
                placeholder="Contoh: Ruang Program Studi"
                value="<?= htmlspecialchars($_POST['lokasi'] ?? ''); ?>"
                required
            >
        </div>

        <button type="submit" class="tombol simpan">
            Simpan Barang
        </button>

        <a href="barang.php" class="tombol kembali">
            Kembali
        </a>

    </form>

</div>

</body>
</html>
