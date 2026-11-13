<?php
require_once "koneksi.php";

// Mengambil data inventaris dari database
$query = "SELECT * FROM barang ORDER BY id_barang DESC";
$result = $conn->query($query);

if (!$result) {
    die("Gagal mengambil data inventaris.");
}

$totalBarang = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Inventaris | SIPINTAR</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f5fb;
            color: #17213c;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.04);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        h1 {
            margin: 0 0 8px;
            color: #5145e5;
        }

        .keterangan {
            color: #77819a;
        }

        .tombol {
            display: inline-block;
            padding: 12px 18px;
            background: #5145e5;
            color: white;
            text-decoration: none;
            border-radius: 9px;
        }

        .jumlah {
            color: #5145e5;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th {
            background: #f1f0ff;
            color: #5145e5;
            padding: 16px;
            text-align: left;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #edf0f5;
        }

        .status {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .tersedia {
            background: #e1f8ed;
            color: #16834d;
        }

        .dipinjam {
            background: #e1f0ff;
            color: #2369c8;
        }

        .diperiksa {
            background: #fff2d9;
            color: #a66b00;
        }

        .kosong {
            text-align: center;
            color: #77819a;
            padding: 30px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <div>
            <h1>Data Inventaris</h1>
            <p class="keterangan">
                Daftar barang inventaris yang terdaftar di SIPINTAR.
            </p>
        </div>

        <a href="index.php" class="tombol">
            Kembali ke Dashboard
        </a><a href="tambah_barang.php" class="tombol">
    + Tambah Barang
</a>
    </div>

    <div class="jumlah">
        Total Inventaris: <?= $totalBarang; ?> barang
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($totalBarang > 0): ?>

                <?php $no = 1; ?>

                <?php while ($barang = $result->fetch_assoc()): ?>

                    <?php
                    $status = $barang['status_barang'];

                    $kelasStatus = 'tersedia';

                    if ($status == 'Sedang Dipinjam') {
                        $kelasStatus = 'dipinjam';
                    } elseif ($status == 'Perlu Diperiksa') {
                        $kelasStatus = 'diperiksa';
                    }
                    ?>

                    <tr>
                        <td><?= $no++; ?></td>

                        <td>
                            <?= htmlspecialchars($barang['kode_barang']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($barang['nama_barang']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($barang['kategori']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($barang['lokasi']); ?>
                        </td>

                        <td>
                            <span class="status <?= $kelasStatus; ?>">
                                <?= htmlspecialchars($status); ?>
                            </span>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="6" class="kosong">
                        Belum ada data inventaris.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>