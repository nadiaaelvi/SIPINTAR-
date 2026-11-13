
<?php
require_once "koneksi.php";

// Mengambil seluruh data mahasiswa
$query = "SELECT * FROM mahasiswa ORDER BY id_mahasiswa DESC";
$result = $conn->query($query);

// Menghitung jumlah mahasiswa
$totalMahasiswa = $result ? $result->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa | SIPINTAR</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f5fb;
            color: #202744;
        }

        .container {
            max-width: 1200px;
            margin: auto;
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
            font-size: 28px;
        }

        .subtitle {
            color: #78829b;
            margin: 0;
        }

        .button {
            display: inline-block;
            padding: 12px 18px;
            background: #5146e5;
            color: white;
            text-decoration: none;
            border-radius: 9px;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(30, 40, 80, 0.05);
        }

        .total {
            margin-bottom: 20px;
            color: #5146e5;
            font-weight: bold;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #edf0f6;
        }

        th {
            background: #f3f2ff;
            color: #5146e5;
        }

        tr:hover {
            background: #fafaff;
        }

        .badge {
            background: #e9f8ef;
            color: #21864b;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #78829b;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <div class="header">
        <div>
            <h1>Data Mahasiswa</h1>
            <p class="subtitle">
                Daftar mahasiswa yang terdaftar di sistem SIPINTAR.
            </p>
        </div>

        <a href="index.php" class="button">
            Kembali ke Dashboard
        </a>
    </div>

    <div class="card">

        <p class="total">
            Total Mahasiswa: <?php echo $totalMahasiswa; ?> orang
        </p>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Program Studi</th>
                        <th>Kelas</th>
                        <th>Kode Barcode KTM</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>

                    <?php $no = 1; ?>

                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>

                            <td>
                                <?php echo htmlspecialchars($row['nim']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['nama_mahasiswa']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['program_studi']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['kelas']); ?>
                            </td>

                            <td>
                                <span class="badge">
                                    <?php echo htmlspecialchars($row['kode_barcode_ktm']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="empty">
                            Belum ada data mahasiswa.
                        </td>
                    </tr>

                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
</body>
</html>

