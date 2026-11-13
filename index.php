
<?php
require_once "koneksi.php";

// Mengambil jumlah data dari database
$totalBarang = 0;
$barangTersedia = 0;
$barangDipinjam = 0;
$totalMahasiswa = 0;

$hasil = $conn->query("SELECT COUNT(*) AS total FROM barang");
if ($hasil) {
    $totalBarang = (int) $hasil->fetch_assoc()["total"];
}

$hasil = $conn->query(
    "SELECT COUNT(*) AS total FROM barang
     WHERE status_barang = 'Tersedia'"
);
if ($hasil) {
    $barangTersedia = (int) $hasil->fetch_assoc()["total"];
}

$hasil = $conn->query(
    "SELECT COUNT(*) AS total FROM barang
     WHERE status_barang = 'Sedang Dipinjam'"
);
if ($hasil) {
    $barangDipinjam = (int) $hasil->fetch_assoc()["total"];
}

$hasil = $conn->query("SELECT COUNT(*) AS total FROM mahasiswa");
if ($hasil) {
    $totalMahasiswa = (int) $hasil->fetch_assoc()["total"];
}

// Mengambil daftar barang terbaru
$daftarBarang = $conn->query(
    "SELECT kode_barang, nama_barang, kategori, lokasi, status_barang
     FROM barang
     ORDER BY id_barang DESC
     LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPINTAR | Dashboard Inventaris</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="app">

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-logo">S</div>
            <div>
                <h2>SIPINTAR</h2>
                <p>Sistem Inventaris Kampus</p>
            </div>
        </div>

        <div class="menu-label">MENU UTAMA</div>

        <nav class="menu">
            <a href="index.php" class="menu-item active">
                <span class="menu-icon">▦</span>
                Dashboard
            </a>
            <a  href="barang.php" class="menu-item">
                <span class="menu-icon">▣</span>
                Data Inventaris
            </a>
            <a href="mahasiswa.php" class="menu-item">
                <span class="menu-icon">♙</span>
                Data Mahasiswa
            </a>
            <a href="#peminjaman" class="menu-item">
                <span class="menu-icon">⇄</span>
                Peminjaman
            </a>
            <a href="#pengembalian" class="menu-item">
                <span class="menu-icon">↩</span>
                Pengembalian
            </a>
            <a href="#riwayat" class="menu-item">
                <span class="menu-icon">◷</span>
                Riwayat Transaksi
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="admin-avatar">A</div>
            <div>
                <strong>Administrator</strong>
                <p>Pengelola Inventaris</p>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <p class="eyebrow">SISTEM INFORMASI INVENTARIS</p>
                <h1>Dashboard</h1>
                <p class="welcome">Selamat datang di SIPINTAR.</p>
            </div>
            <div class="topbar-right">
                <span class="online-dot"></span>
                Sistem lokal aktif
            </div>
        </header>

        <section class="welcome-banner">
            <div>
                <span class="banner-label">PUSAT INFORMASI INVENTARIS</span>
                <h2>Kelola inventaris kampus dengan lebih mudah.</h2>
                <p>
                    Pantau ketersediaan barang dan kelola informasi
                    peminjaman dalam satu sistem.
                </p>
            </div>
            <div class="banner-symbol">S</div>
        </section>

        <section class="section-heading">
            <div>
                <h2>Ringkasan Inventaris</h2>
                <p>Informasi berdasarkan data yang tersimpan di database.</p>
            </div>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-top">
                    <span>Total Inventaris</span>
                    <span class="stat-icon purple">▣</span>
                </div>
                <div class="stat-number">
                    <?= $totalBarang ?>
                </div>
                <p>Jumlah seluruh barang terdaftar</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span>Barang Tersedia</span>
                    <span class="stat-icon blue">✓</span>
                </div>
                <div class="stat-number">
                    <?= $barangTersedia ?>
                </div>
                <p>Barang yang dapat dipinjam</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span>Sedang Dipinjam</span>
                    <span class="stat-icon green">⇄</span>
                </div>
                <div class="stat-number">
                    <?= $barangDipinjam ?>
                </div>
                <p>Barang yang sedang digunakan</p>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span>Total Mahasiswa</span>
                    <span class="stat-icon orange">♙</span>
                </div>
                <div class="stat-number">
                    <?= $totalMahasiswa ?>
                </div>
                <p>Mahasiswa yang telah terdaftar</p>
            </div>
        </section>

        <section class="content-card" id="inventaris">
            <div class="card-heading">
                <div>
                    <h2>Daftar Inventaris</h2>
                    <p>Lima barang terakhir yang tercatat di sistem.</p>
                </div>
                <span class="small-badge">Data Barang</span>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($daftarBarang && $daftarBarang->num_rows > 0): ?>
                        <?php while ($barang = $daftarBarang->fetch_assoc()): ?>
                            <?php
                            $status = $barang["status_barang"];
                            $kelasStatus = "status-periksa";

                            if ($status === "Tersedia") {
                                $kelasStatus = "status-tersedia";
                            } elseif ($status === "Sedang Dipinjam") {
                                $kelasStatus = "status-dipinjam";
                            }
                            ?>
                            <tr>
                                <td>
                                    <span class="code-text">
                                        <?= htmlspecialchars($barang["kode_barang"]) ?>
                                    </span>
                                </td>
                                <td class="item-name">
                                    <?= htmlspecialchars($barang["nama_barang"]) ?>
                                </td>
                                <td><?= htmlspecialchars($barang["kategori"] ?? "-") ?></td>
                                <td><?= htmlspecialchars($barang["lokasi"] ?? "-") ?></td>
                                <td>
                                    <span class="status <?= $kelasStatus ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state">
                                Belum ada data barang. Data inventaris akan
                                ditampilkan di sini setelah ditambahkan.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="content-card" id="mahasiswa">
    <div class="card-heading">
        <div>
            <h2>Data Mahasiswa</h2>
            <p>Daftar mahasiswa yang terdaftar di SIPINTAR.</p>
        </div>
        <span class="small-badge">Data Mahasiswa</span>
    </div>

    <?php
    $daftarMahasiswa = $conn->query(
        "SELECT nim, nama_mahasiswa, program_studi, kelas
         FROM mahasiswa
         ORDER BY nama_mahasiswa ASC"
    );
    ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th>Program Studi</th>
                    <th>Kelas</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($daftarMahasiswa && $daftarMahasiswa->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($mhs = $daftarMahasiswa->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($mhs['nim']); ?></td>
                            <td><?= htmlspecialchars($mhs['nama_mahasiswa']); ?></td>
                            <td><?= htmlspecialchars($mhs['program_studi']); ?></td>
                            <td><?= htmlspecialchars($mhs['kelas']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Belum ada data mahasiswa.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
        <footer class="footer">
            SIPINTAR &copy; <?= date("Y") ?> · Sistem Informasi
            Peminjaman Inventaris Terintegrasi
        </footer>
    </main>
</div>

</body>
</html>
