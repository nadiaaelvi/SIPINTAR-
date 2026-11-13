
<?php
require_once "koneksi.php";

$pesan = "";
$berhasil = false;

// Mengambil data mahasiswa MPI
$query_mahasiswa = "
    SELECT id_mahasiswa, nim, nama_mahasiswa
    FROM mahasiswa
    WHERE program_studi = 'Manajemen Pendidikan Islam'
    ORDER BY nama_mahasiswa ASC
";
$result_mahasiswa = $conn->query($query_mahasiswa);

// Mengambil barang yang tersedia
$query_barang = "
    SELECT id_barang, kode_barang, nama_barang
    FROM barang
    WHERE status_barang = 'Tersedia'
    ORDER BY nama_barang ASC
";
$result_barang = $conn->query($query_barang);


// PROSES PEMINJAMAN
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_mahasiswa = (int)($_POST["id_mahasiswa"] ?? 0);
    $id_barang = (int)($_POST["id_barang"] ?? 0);

    $nama_file = [];
    $upload_sukses = true;

    $daftar_foto = [
        "foto_mahasiswa",
        "foto_ktm",
        "foto_barang_sebelum"
    ];

    // Memeriksa seluruh foto
    foreach ($daftar_foto as $field) {
        if (
            !isset($_FILES[$field]) ||
            $_FILES[$field]["error"] !== UPLOAD_ERR_OK
        ) {
            $pesan = "Semua foto wajib diambil menggunakan kamera.";
            $upload_sukses = false;
            break;
        }

        if ($_FILES[$field]["size"] > 5 * 1024 * 1024) {
            $pesan = "Ukuran setiap foto maksimal 5 MB.";
            $upload_sukses = false;
            break;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES[$field]["tmp_name"]);

        $jenis_file = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp"
        ];

        if (!isset($jenis_file[$mime])) {
            $pesan = "Format foto harus JPG, PNG, atau WEBP.";
            $upload_sukses = false;
            break;
        }

        $nama_file[$field] =
            bin2hex(random_bytes(16)) . "." . $jenis_file[$mime];
    }

    // Memastikan mahasiswa dan barang masih valid
    if ($upload_sukses) {
        $cek_mahasiswa = $conn->prepare("
            SELECT id_mahasiswa
            FROM mahasiswa
            WHERE id_mahasiswa = ?
            AND program_studi = 'Manajemen Pendidikan Islam'
        ");
        $cek_mahasiswa->bind_param("i", $id_mahasiswa);
        $cek_mahasiswa->execute();

        $mahasiswa_valid =
            $cek_mahasiswa->get_result()->num_rows === 1;

        $cek_barang = $conn->prepare("
            SELECT id_barang
            FROM barang
            WHERE id_barang = ?
            AND status_barang = 'Tersedia'
        ");
        $cek_barang->bind_param("i", $id_barang);
        $cek_barang->execute();

        $barang_valid =
            $cek_barang->get_result()->num_rows === 1;

        if (!$mahasiswa_valid || !$barang_valid) {
            $pesan = "Mahasiswa atau barang tidak valid.";
            $upload_sukses = false;
        }
    }

    // Menyimpan foto dan transaksi
    if ($upload_sukses) {
        $folder_upload = __DIR__ . "/uploads/";

        if (!is_dir($folder_upload)) {
            mkdir($folder_upload, 0755, true);
        }

        $file_tersimpan = [];

        foreach ($daftar_foto as $field) {
            $tujuan = $folder_upload . $nama_file[$field];

            if (!move_uploaded_file(
                $_FILES[$field]["tmp_name"],
                $tujuan
            )) {
                $upload_sukses = false;
                $pesan = "Foto gagal disimpan. Periksa folder uploads.";
                break;
            }

            $file_tersimpan[] = $tujuan;
        }

        if ($upload_sukses) {
            try {
                $conn->begin_transaction();

                // Memasukkan data peminjaman
                $simpan = $conn->prepare("
                    INSERT INTO peminjaman
                    (
                        id_mahasiswa,
                        id_barang,
                        waktu_pinjam,
                        foto_mahasiswa,
                        foto_ktm,
                        foto_barang_sebelum,
                        status_transaksi
                    )
                    VALUES (?, ?, NOW(), ?, ?, ?, 'Dipinjam')
                ");

                $simpan->bind_param(
                    "iisss",
                    $id_mahasiswa,
                    $id_barang,
                    $nama_file["foto_mahasiswa"],
                    $nama_file["foto_ktm"],
                    $nama_file["foto_barang_sebelum"]
                );

                $simpan->execute();

                // Mengubah status barang
                $ubah_status = $conn->prepare("
                    UPDATE barang
                    SET status_barang = 'Sedang Dipinjam'
                    WHERE id_barang = ?
                    AND status_barang = 'Tersedia'
                ");

                $ubah_status->bind_param("i", $id_barang);
                $ubah_status->execute();

                if ($ubah_status->affected_rows !== 1) {
                    throw new Exception(
                        "Barang tidak tersedia untuk dipinjam."
                    );
                }

                $conn->commit();
                $berhasil = true;
                $pesan = "Peminjaman berhasil dicatat.";

            } catch (Throwable $e) {
                $conn->rollback();
                $pesan = "Peminjaman gagal disimpan. Periksa struktur database.";
            }
        }

        // Menghapus foto jika transaksi gagal
        if (!$berhasil) {
            foreach ($file_tersimpan as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }
}

// Memuat ulang daftar barang setelah transaksi
$result_barang = $conn->query($query_barang);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Barang - SIPINTAR</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f3f6fb;
            color: #263445;
        }

        .container {
            max-width: 750px;
            margin: 25px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.08);
        }

        h1 {
            color: #174a80;
            text-align: center;
        }

        .deskripsi {
            text-align: center;
            color: #64748b;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-top: 20px;
            margin-bottom: 8px;
            font-weight: bold;
        }

        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccd5e0;
            border-radius: 8px;
            font-size: 15px;
        }

        .kamera {
            margin-top: 12px;
            padding: 15px;
            background: #f7f9fc;
            border-radius: 10px;
        }

        video, .hasil-foto {
            display: block;
            width: 100%;
            max-height: 350px;
            object-fit: contain;
            background: #202938;
            border-radius: 8px;
            margin-top: 12px;
        }

        .hasil-foto {
            background: #eef2f7;
        }

        button {
            border: none;
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 14px;
        }

        .tombol-kamera {
            background: #174a80;
            color: white;
        }

        .tombol-ambil {
            background: #16845b;
            color: white;
        }

        .tombol-ulang {
            background: #64748b;
            color: white;
        }

        .tombol-kirim {
            width: 100%;
            background: #16845b;
            color: white;
            font-size: 16px;
            font-weight: bold;
            margin-top: 28px;
        }

        .pesan {
            padding: 14px;
            background: #e9f7ef;
            color: #17633d;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .catatan {
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }

        .kembali {
            display: inline-block;
            margin-top: 20px;
            color: #174a80;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Peminjaman Barang</h1>
    <p class="deskripsi">
        SIPINTAR - Sistem Informasi Peminjaman Inventaris Terintegrasi
    </p>

    <?php if ($pesan !== ""): ?>
        <div class="pesan">
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>

    <form id="formPeminjaman" method="POST" enctype="multipart/form-data">

        <label>Nama Mahasiswa</label>
        <select name="id_mahasiswa" required>
            <option value="">-- Pilih Mahasiswa --</option>

            <?php while ($m = $result_mahasiswa->fetch_assoc()): ?>
                <option value="<?= $m['id_mahasiswa'] ?>">
                    <?= htmlspecialchars($m['nama_mahasiswa']) ?>
                    - <?= htmlspecialchars($m['nim']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Barang yang Dipinjam</label>
        <select name="id_barang" required>
            <option value="">-- Pilih Barang --</option>

            <?php while ($b = $result_barang->fetch_assoc()): ?>
                <option value="<?= $b['id_barang'] ?>">
                    <?= htmlspecialchars($b['kode_barang']) ?>
                    - <?= htmlspecialchars($b['nama_barang']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <div class="kamera">
            <label>1. Foto Mahasiswa</label>
            <p class="catatan">Gunakan kamera depan untuk mengambil foto mahasiswa.</p>
            <button type="button" class="tombol-kamera"
                onclick="bukaKamera('mahasiswa', 'user')">
                Buka Kamera Mahasiswa
            </button>
            <video id="video-mahasiswa" autoplay playsinline hidden></video>
            <canvas id="canvas-mahasiswa" hidden></canvas>
            <img id="hasil-mahasiswa" class="hasil-foto" hidden>
            <button type="button" class="tombol-ambil"
                onclick="ambilFoto('mahasiswa')" hidden
                id="ambil-mahasiswa">Ambil Foto</button>
            <button type="button" class="tombol-ulang"
                onclick="bukaKamera('mahasiswa', 'user')" hidden
                id="ulang-mahasiswa">Ambil Ulang</button>
            <p id="status-mahasiswa" class="catatan">Foto belum diambil.</p>
        </div>

        <div class="kamera">
            <label>2. Foto KTM</label>
            <p class="catatan">Gunakan kamera belakang untuk memotret KTM.</p>
            <button type="button" class="tombol-kamera"
                onclick="bukaKamera('ktm', 'environment')">
                Buka Kamera KTM
            </button>
            <video id="video-ktm" autoplay playsinline hidden></video>
            <canvas id="canvas-ktm" hidden></canvas>
            <img id="hasil-ktm" class="hasil-foto" hidden>
            <button type="button" class="tombol-ambil"
                onclick="ambilFoto('ktm')" hidden
                id="ambil-ktm">Ambil Foto</button>
            <button type="button" class="tombol-ulang"
                onclick="bukaKamera('ktm', 'environment')" hidden
                id="ulang-ktm">Ambil Ulang</button>
            <p id="status-ktm" class="catatan">Foto belum diambil.</p>
        </div>

        <div class="kamera">
            <label>3. Foto Barang Sebelum Dipinjam</label>
            <p class="catatan">Gunakan kamera belakang untuk memotret kondisi barang.</p>
            <button type="button" class="tombol-kamera"
                onclick="bukaKamera('barang', 'environment')">
                Buka Kamera Barang
            </button>
            <video id="video-barang" autoplay playsinline hidden></video>
            <canvas id="canvas-barang" hidden></canvas>
            <img id="hasil-barang" class="hasil-foto" hidden>
            <button type="button" class="tombol-ambil"
                onclick="ambilFoto('barang')" hidden
                id="ambil-barang">Ambil Foto</button>
            <button type="button" class="tombol-ulang"
                onclick="bukaKamera('barang', 'environment')" hidden
                id="ulang-barang">Ambil Ulang</button>
            <p id="status-barang" class="catatan">Foto belum diambil.</p>
        </div>

        <p class="catatan">
            Pastikan semua foto sudah diambil langsung menggunakan kamera
            sebelum menekan tombol konfirmasi.
        </p>

        <button type="submit" class="tombol-kirim">
            Konfirmasi Peminjaman
        </button>

    </form>

    <a href="layanan.php" class="kembali">← Kembali ke Halaman Layanan</a>

</div>

<script>
let kameraAktif = null;
let fotoDiambil = {
    mahasiswa: null,
    ktm: null,
    barang: null
};

async function bukaKamera(jenis, kamera) {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("Kamera tidak dapat dibuka. Gunakan localhost atau website HTTPS.");
        return;
    }

    if (kameraAktif) {
        kameraAktif.getTracks().forEach(track => track.stop());
    }

    const video = document.getElementById("video-" + jenis);

    try {
        kameraAktif = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: kamera },
            audio: false
        });

        video.srcObject = kameraAktif;
        video.hidden = false;

        document.getElementById("ambil-" + jenis).hidden = false;
        document.getElementById("hasil-" + jenis).hidden = true;
        document.getElementById("status-" + jenis).textContent =
            "Kamera aktif. Silakan ambil foto.";

        fotoDiambil[jenis] = null;

    } catch (error) {
        alert("Kamera gagal dibuka. Pastikan izin kamera sudah diberikan.");
    }
}

function ambilFoto(jenis) {
    const video = document.getElementById("video-" + jenis);
    const canvas = document.getElementById("canvas-" + jenis);
    const gambar = document.getElementById("hasil-" + jenis);

    if (!video.videoWidth) {
        alert("Tunggu sampai kamera siap.");
        return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const konteks = canvas.getContext("2d");
    konteks.drawImage(video, 0, 0, canvas.width, canvas.height);

    canvas.toBlob(function(blob) {
        if (!blob) {
            alert("Foto gagal diambil. Silakan ulangi.");
            return;
        }

        fotoDiambil[jenis] = blob;
        gambar.src = URL.createObjectURL(blob);
        gambar.hidden = false;
        video.hidden = true;

        document.getElementById("ambil-" + jenis).hidden = true;
        document.getElementById("ulang-" + jenis).hidden = false;
        document.getElementById("status-" + jenis).textContent =
            "Foto berhasil diambil.";

        if (kameraAktif) {
            kameraAktif.getTracks().forEach(track => track.stop());
            kameraAktif = null;
        }
    }, "image/jpeg", 0.85);
}

document.getElementById("formPeminjaman").addEventListener("submit", function(event) {
    event.preventDefault();

    if (
        !fotoDiambil.mahasiswa ||
        !fotoDiambil.ktm ||
        !fotoDiambil.barang
    ) {
        alert("Silakan ambil ketiga foto terlebih dahulu.");
        return;
    }

    const formData = new FormData(this);

    formData.append("foto_mahasiswa", fotoDiambil.mahasiswa, "mahasiswa.jpg");
    formData.append("foto_ktm", fotoDiambil.ktm, "ktm.jpg");
    formData.append("foto_barang_sebelum", fotoDiambil.barang, "barang.jpg");

    const tombol = this.querySelector(".tombol-kirim");
    tombol.disabled = true;
    tombol.textContent = "Menyimpan peminjaman...";

    fetch("peminjaman.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        document.open();
        document.write(html);
        document.close();
    })
    .catch(() => {
        alert("Terjadi kesalahan saat mengirim data.");
        tombol.disabled = false;
        tombol.textContent = "Konfirmasi Peminjaman";
    });
});
</script>

</body>
</html>
