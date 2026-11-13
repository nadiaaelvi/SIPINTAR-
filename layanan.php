
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Layanan SIPINTAR</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f6fb;
            color: #243247;
        }

        .header {
            background: #173b70;
            color: white;
            padding: 25px 8%;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .header p {
            margin-bottom: 0;
            color: #dce7f7;
        }

        .container {
            max-width: 950px;
            margin: 55px auto;
            padding: 20px;
            text-align: center;
        }

        .container h2 {
            color: #173b70;
            font-size: 27px;
        }

        .container .intro {
            color: #667085;
            margin-bottom: 35px;
        }

        .layanan {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .card {
            background: white;
            padding: 35px 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        .ikon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .card h3 {
            color: #173b70;
            margin-bottom: 12px;
        }

        .card p {
            color: #667085;
            line-height: 1.6;
            min-height: 50px;
        }

        .tombol {
            display: inline-block;
            width: 100%;
            padding: 14px;
            margin-top: 15px;
            background: #173b70;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .tombol:hover {
            background: #24549a;
        }

        .footer {
            text-align: center;
            color: #7b8493;
            padding: 25px;
            font-size: 13px;
        }

        @media (max-width: 650px) {
            .layanan {
                grid-template-columns: 1fr;
            }

            .container {
                margin: 25px auto;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>SIPINTAR</h1>
        <p>Sistem Informasi Peminjaman Inventaris Terintegrasi</p>
    </div>

    <div class="container">

        <h2>Selamat Datang di SIPINTAR</h2>

        <p class="intro">
            Silakan pilih layanan yang ingin kamu gunakan.
        </p>

        <div class="layanan">

            <div class="card">
                <div class="ikon">📦</div>

                <h3>Peminjaman Barang</h3>

                <p>
                    Gunakan layanan ini untuk melakukan
                    peminjaman barang inventaris program studi.
                </p>

                <a href="peminjaman.php" class="tombol">
                    Ajukan Peminjaman
                </a>
            </div>

            <div class="card">
                <div class="ikon">↩️</div>

                <h3>Pengembalian Barang</h3>

                <p>
                    Gunakan layanan ini untuk mengembalikan
                    barang inventaris yang telah dipinjam.
                </p>

                <a href="pengembalian.php" class="tombol">
                    Pengembalian Barang
                </a>
            </div>

        </div>

    </div>

    <div class="footer">
        SIPINTAR &copy; 2026
    </div>

</body>
</html>
