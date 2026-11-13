
<?php
require_once "koneksi.php";

$pesan = "";
$data_peminjaman = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_peminjaman = (int) ($_POST["id_peminjaman"] ?? 0);

    $cek = $conn->prepare(
        "SELECT p.id_peminjaman, p.id_mahasiswa,
                p.id_barang, p.status_transaksi,
                m.nama_mahasiswa, m.nim,
                b.nama_barang, b.kode_barang
         FROM peminjaman p
         JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
         JOIN barang b ON p.id_barang = b.id_barang
         WHERE p.id_peminjaman = ?
         AND p.status_transaksi = 'Dipinjam'"
    );

    $cek->bind_param("i", $id_peminjaman);
    $cek->execute();
    $hasil = $cek->get_result();

    if ($hasil->num_rows === 1) {
        $data_peminjaman = $hasil->fetch_assoc();

        $conn->begin_transaction();

        try {
            $ubah = $conn->prepare(
                "UPDATE peminjaman
                 SET waktu_kembali = NOW(),
                     foto_barang_sesudah = '',
                     status_transaksi = 'Dikembalikan'
                 WHERE id_peminjaman = ?
                 AND status_transaksi = 'Dipinjam'"
            );

            $ubah->bind_param("i", $id_peminjaman);
            $ubah->execute();

            if ($ubah->affected_rows !== 1) {
                throw new Exception("Transaksi tidak dapat diperbarui.");
            }

            $barang = $conn->prepare(
                "UPDATE barang
                 SET status_barang = 'Tersedia'
                 WHERE id_barang = ?
                 AND status_barang = 'Sedang Dipinjam'"
            );

            $barang->bind_param("i", $data_peminjaman["id_barang"]);
            $barang->execute();

            if ($barang->affected_rows !== 1) {
                throw new Exception("Status barang tidak dapat diperbarui.");
            }

            $conn->commit();
            $pesan = "Pengembalian berhasil dicatat.";
            $data_peminjaman = null;

        } catch (Throwable $e) {
            $conn->rollback();
            $pesan = "Pengembalian gagal: " . $e->getMessage();
        }

    } else {
        $pesan = "Nomor peminjaman tidak ditemukan atau sudah dikembalikan.";
    }
}

$query = "SELECT p.id_peminjaman, m.nama_mahasiswa,
                 m.nim, b.nama_barang, b.kode_barang
          FROM peminjaman p
          JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
          JOIN barang b ON p.id_barang = b.id_barang
          WHERE p.status_transaksi = 'Dipinjam'
          ORDER BY p.id_peminjaman DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Barang - SIPINTAR</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f6fb;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 650px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
        }

        h2 {
            color: #173b70;
        }

        label {
            display: block;
            margin-top: 20px;
            font-weight: bold;
        }

        select, button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border-radius: 7px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            background: #173b70;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 25px;
        }

        .pesan {
            background: #e8f5e9;
            padding: 12px;
            border-radius: 7px;
        }

        .kembali {
            display: inline-block;
            margin-top: 20px;
            color: #173b70;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>Form Pengembalian Barang</h2>
    <p>Sistem Informasi Peminjaman Inventaris Terintegrasi</p>

    <?php if ($pesan !== ""): ?>
        <p class="pesan"><?= htmlspecialchars($pesan) ?></p>
    <?php endif; ?>

    <form method="POST">

        <label>Pilih Transaksi Peminjaman Aktif</label>

        <select name="id_peminjaman" required>
            <option value="">-- Pilih Peminjaman --</option>

            <?php while ($p = $result->fetch_assoc()): ?>
                <option value="<?= (int)$p["id_peminjaman"] ?>">
                    <?= htmlspecialchars($p["nama_mahasiswa"]) ?>
                    - <?= htmlspecialchars($p["nama_barang"]) ?>
                    - <?= htmlspecialchars($p["kode_barang"]) ?>
                </option>
            <?php endwhile; ?>

        </select>

        <button type="submit">
            Konfirmasi Pengembalian
        </button>

    </form>

    <a href="layanan.php" class="kembali">
        ← Kembali ke pilihan layanan
    </a>

</div>

</body>
</html>
