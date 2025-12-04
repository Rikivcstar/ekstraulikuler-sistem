<?php
// cetak_sertifikat.php - Auto Download Version
require_once 'config/database.php';
require_once 'config/middleware.php';
only('siswa');
requireRole(['siswa']);

$page_title = 'Cetak Sertifikat';

$print_mode = false;
$sertifikat = null;

// Cek sertifikat berdasarkan NISN atau ID
if (isset($_POST['nisn']) || isset($_GET['nisn']) || isset($_GET['id'])) {
    $nisn = $_POST['nisn'] ?? $_GET['nisn'] ?? null;
    $anggota_id = $_GET['id'] ?? null;
    
    // Query Utama untuk mengambil data sertifikat
    $sql = "
        SELECT u.nisn, u.name, u.kelas, e.nama_ekskul, 
               pembina.name as nama_pembina, pembina.tanda_tangan as ttd_pembina,
               sert.nomor_sertifikat, sert.tanggal_terbit, sert.keterangan,
               ae.nilai, ae.tanggal_penilaian, ae.catatan_pembina, ae.tanggal_daftar, ae.id as anggota_id
        FROM users u
        JOIN anggota_ekskul ae ON u.id = ae.user_id
        JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
        LEFT JOIN users pembina ON e.pembina_id = pembina.id
        LEFT JOIN sertifikats sert ON ae.id = sert.anggota_id
        WHERE ae.status = 'diterima' AND u.role = 'siswa'
    ";

    $params = [];
    $types = "";

    if ($anggota_id) {
        $sql .= " AND ae.id = ? LIMIT 1";
        $params = [$anggota_id];
        $types = "i";
    } else {
        $sql .= " AND u.nisn = ? ORDER BY sert.tanggal_terbit DESC LIMIT 1";
        $params = [$nisn];
        $types = "s";
    }
    
    $result = query($sql, $params, $types);
    
    if ($result && $result->num_rows > 0) {
        $sertifikat = $result->fetch_assoc();
        
        // Auto Generate Nomor Sertifikat jika belum ada
        if (!$sertifikat['nomor_sertifikat']) {
            $nomor = 'CERT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $tanggal_terbit = date('Y-m-d');
            $aid = $sertifikat['anggota_id'] ?? $anggota_id;
            
            query("INSERT INTO sertifikats (anggota_id, nomor_sertifikat, tanggal_terbit) VALUES (?, ?, ?)",
                [$aid, $nomor, $tanggal_terbit], 'iss');
            
            // Update data sertifikat yang sedang ditampilkan
            $sertifikat['nomor_sertifikat'] = $nomor;
            $sertifikat['tanggal_terbit'] = $tanggal_terbit;
        }
        
        if (isset($_GET['print'])) {
            $print_mode = true;
        }
    }
}

// Get pengaturan dari database
$settings = [];
$result = query("SELECT * FROM pengaturan");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['key_name']] = $row['key_value'];
    }
}

$predikat_sekolah = $settings['predikat_sekolah'] ?? 'TERAKREDITASI A';
$nama_sekolah = $settings['nama_sekolah'] ?? 'MTsN 1 LEBAK';
$alamat_sekolah = $settings['alamat_sekolah'] ?? 'Jl. Raya Rangkasbitung, Lebak, Banten';
$tempat_sekolah = $settings['tempat_sekolah'] ?? 'Lebak';
$nama_kepala = $settings['nama_kepala_madrasah'] ?? 'Dr. H. Muhammad Yusuf, M.Pd.I';
$nip_kepala = $settings['nip_kepala_madrasah'] ?? '197201152005011003';
$background_sertifikat = $settings['background_sertifikat'] ?? 'assets/img/certificate-bg.png';

// Fungsi untuk konversi nilai ke predikat
function getNilaiPredikat($nilai) {
    switch($nilai) {
        case 'A': return 'SANGAT BAIK';
        case 'B': return 'BAIK';
        case 'C': return 'CUKUP';
        default: return '-';
    }
}

$tahun_kurikulum = date('Y') . '/' . (date('Y') + 1);

// Hanya load header jika BUKAN mode print
if (!$print_mode) {
    require_once 'includes/header.php';
}
?>

<?php if (!$print_mode): ?>
<section class="bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 text-white">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="reveal bg-white text-slate-800 rounded-2xl shadow-xl ring-1 ring-slate-900/10 overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-700 to-emerald-600 text-white px-6 py-5 text-center">
                <h3 class="text-2xl font-extrabold flex items-center justify-center gap-2">
                    <i class="bi bi-award-fill"></i>
                    Cetak Sertifikat
                </h3>
            </div>
            <div class="p-6">
                <div class="mb-4 rounded-xl bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 px-4 py-3 flex items-start gap-2">
                    <i class="bi bi-info-circle mt-0.5"></i>
                    <span>Masukkan NISN Anda untuk mengecek dan mencetak sertifikat.</span>
                </div>

                <form method="POST" action="" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">NISN (Nomor Induk Siswa Nasional)</label>
                        <input type="text" name="nisn" placeholder="Masukkan NISN Anda" required autofocus
                                class="mt-1 w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 shadow">
                            <i class="bi bi-search"></i>
                            Cek Sertifikat
                        </button>
                        <a href="<?php echo BASE_URL; ?>" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50">
                            <i class="bi bi-arrow-left"></i>
                            Kembali
                        </a>
                    </div>
                </form>

                <?php if (isset($_POST['nisn']) && !$sertifikat): ?>
                <div class="mt-5 rounded-xl bg-amber-50 text-amber-800 ring-1 ring-amber-200 px-4 py-3 flex items-start gap-2">
                    <i class="bi bi-exclamation-triangle mt-0.5"></i>
                    <span>Sertifikat tidak ditemukan. Pastikan Anda sudah terdaftar dan aktif di ekstrakurikuler.</span>
                </div>
                <?php endif; ?>

                <?php if ($sertifikat && !$print_mode): ?>
                <div class="my-6 h-px bg-slate-200"></div>
                <div class="rounded-2xl bg-emerald-50 ring-1 ring-emerald-200 p-5">
                    <h5 class="font-bold text-emerald-800 flex items-center gap-2"><i class="bi bi-check-circle"></i> Sertifikat Ditemukan!</h5>
                    <div class="my-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                        <div class="flex justify-between sm:block"><span class="text-slate-500">Nama</span><span class="font-semibold text-slate-800"><?php echo htmlspecialchars($sertifikat['name']); ?></span></div>
                        <div class="flex justify-between sm:block"><span class="text-slate-500">NISN</span><span class="font-semibold text-slate-800"><?php echo htmlspecialchars($sertifikat['nisn']); ?></span></div>
                        <div class="flex justify-between sm:block"><span class="text-slate-500">Kelas</span><span class="font-semibold text-slate-800"><?php echo htmlspecialchars($sertifikat['kelas']); ?></span></div>
                        <div class="flex justify-between sm:block"><span class="text-slate-500">Ekstrakurikuler</span><span class="font-semibold text-slate-800"><?php echo htmlspecialchars($sertifikat['nama_ekskul']); ?></span></div>
                        <?php if ($sertifikat['nilai']): ?>
                        <div class="sm:col-span-2 flex items-center gap-2">
                            <span class="text-slate-500">Nilai</span>
                            <?php 
                                $badge = $sertifikat['nilai'] == 'A' ? 'bg-emerald-600 text-white' : ($sertifikat['nilai'] == 'B' ? 'bg-amber-400 text-black' : 'bg-rose-500 text-white');
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo $badge; ?>">
                                Nilai <?php echo $sertifikat['nilai']; ?> - <?php echo getNilaiPredikat($sertifikat['nilai']); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between sm:block"><span class="text-slate-500">Nomor Sertifikat</span><span class="font-semibold text-slate-800"><?php echo htmlspecialchars($sertifikat['nomor_sertifikat']); ?></span></div>
                        <div class="flex justify-between sm:block"><span class="text-slate-500">Tanggal Terbit</span><span class="font-semibold text-slate-800"><?php echo formatTanggal($sertifikat['tanggal_terbit']); ?></span></div>
                    </div>
                    <div class="mt-3">
                        <a href="?nisn=<?php echo urlencode($sertifikat['nisn']); ?>&print=1" target="_blank" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 shadow">
                            <i class="bi bi-download"></i>
                            Download Sertifikat PDF
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php 
require_once 'includes/footer.php'; 
?>

<?php else: ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat - <?php echo htmlspecialchars($sertifikat['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: #e8ecef;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            color: white;
        }
        
        .loading-spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #198754;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            font-size: 18px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            display: flex;
            gap: 10px;
        }
        
        .no-print button {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            font-family: 'Montserrat', sans-serif;
        }
        
        .btn-download {
            background: #198754;
            color: white;
        }
        
        .btn-download:hover {
            background: #146c43;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25, 135, 84, 0.4);
        }
        
        .btn-close {
            background: #6c757d;
            color: white;
        }
        
        .btn-close:hover {
            background: #5c636a;
        }
        
        .certificate {
            width: 297mm;
            height: 210mm;
            position: relative;
            background-image: url('<?php echo BASE_URL . htmlspecialchars($background_sertifikat); ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            box-shadow: 0 0 50px rgba(0,0,0,0.2);
        }
        
        .tahun-kurikulum {
            position: absolute;
            top: 15mm;
            right: 20mm;
            background: #003366;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 10;
        }
        
        .school-header {
            position: absolute;
            top: 25mm;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 5;
        }
        
        .school-header h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 34px;
            font-weight: 700;
            color: #003366;
            margin: 0 0 8px 0;
            letter-spacing: 4px;
        }
        
        .school-header .predikat-badge {
            display: inline-block;
            background: #003366;
            color: white;
            padding: 6px 20px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: 2px;
        }
        
        .school-header p {
            font-size: 13px;
            color: #555;
            margin: 0;
        }
        
        .cert-main {
            position: absolute;
            top: 65mm;
            left: 30mm;
            right: 30mm;
            text-align: center;
        }
        
        .cert-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 52px;
            font-weight: 700;
            color: #003366;
            letter-spacing: 10px;
            margin-bottom: 8px;
        }
        
        .cert-subtitle {
            font-size: 15px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 20px;
        }
        
        .cert-text {
            font-size: 13px;
            color: #444;
            margin: 12px 0;
            line-height: 1.5;
        }
        
        .student-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 40px;
            font-weight: 700;
            color: #003366;
            margin: 15px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .student-details {
            font-size: 13px;
            color: #555;
            margin: 10px 0;
        }
        
        .student-details span {
            display: inline-block;
            margin: 10px 12px;
            padding: 4px 14px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .eskul-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 700;
            color: #003366;
            margin: 18px 0;
            text-transform: uppercase;
        }
        
        .grade-section {
            margin: 18px 0 0 0;
        }
        
        .nilai-display {
            display: inline-block;
            padding: 10px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 3px;
            margin: 8px 0;
            border: 3px solid #003366;
        }
        
        .nilai-A {
            background: #28a745 !important;
            color: white !important;
        }
        
        .nilai-B {
            background: #ffc107 !important;
            color: #000 !important;
        }
        
        .nilai-C {
            background: #dc3545 !important;
            color: white !important;
        }
        
        .catatan-pembina {
            font-size: 11px;
            color: #666;
            font-style: italic;
            margin-top: 10px;
            padding: 8px 18px;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 6px;
            max-width: 550px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .signature-area {
            position: absolute;
            bottom: 25mm;
            left: 40mm;
            right: 40mm;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-block {
            width: 200px;
            text-align: center;
        }
        
        .sig-location {
            font-size: 11px;
            color: #555;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .sig-title {
            font-size: 12px;
            font-weight: 700;
            color: #003366;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        
        .sig-image {
            width: 160px;
            height: 65px;
            margin: 6px auto;
            display: block;
            object-fit: contain;
        }
        
        .sig-name {
            font-size: 14px;
            font-weight: 700;
            color: #003366;
            border-top: 2px solid #003366;
            padding-top: 6px;
            margin-top: 4px;
            text-transform: uppercase;
        }
        
        .sig-nip {
            font-size: 10px;
            color: #777;
            margin-top: 3px;
        }
        
        .cert-number {
            position: absolute;
            bottom: 12mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #888;
            font-weight: 600;
        }
        
        .cert-number strong {
            color: #003366;
            font-weight: 700;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
        
        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
            to {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">Mempersiapkan sertifikat...</div>
        <div style="font-size: 14px; margin-top: 10px; opacity: 0.8;">Mohon tunggu sebentar</div>
    </div>

    <div class="no-print">
        <button class="btn-download" onclick="downloadPDF()">
            📥 Download PDF
        </button>
        <button class="btn-close" onclick="window.close()">
            ✖ Tutup
        </button>
    </div>

    <div class="certificate" id="certificate">
        <div class="tahun-kurikulum">
            KURIKULUM <?php echo $tahun_kurikulum; ?>
        </div>
        
        <div class="school-header">
            <h1><?php echo strtoupper(htmlspecialchars($nama_sekolah)); ?></h1>
            <div class="predikat-badge"><?php echo strtoupper(htmlspecialchars($predikat_sekolah)); ?></div>
            <p><?php echo htmlspecialchars($alamat_sekolah); ?></p>
        </div>
        
        <div class="cert-main">
            <h1 class="cert-title">CERTIFICATE</h1>
            <p class="cert-subtitle">of Achievement</p>
            
            <p class="cert-text">This is to certify that</p>
            
            <div class="student-name"><?php echo strtoupper(htmlspecialchars($sertifikat['name'])); ?></div>
            
            <div class="student-details">
                <span><strong>NISN:</strong> <?php echo htmlspecialchars($sertifikat['nisn']); ?></span>
                <span><strong>Kelas:</strong> <?php echo htmlspecialchars($sertifikat['kelas']); ?></span>
            </div>
            
            <p class="cert-text">
                Telah berpartisipasi aktif dan menunjukkan dedikasi luar biasa<br>
                dalam kegiatan ekstrakurikuler
            </p>
            
            <div class="eskul-name"><?php echo strtoupper(htmlspecialchars($sertifikat['nama_ekskul'])); ?></div>
            
            <?php if ($sertifikat['nilai']): ?>
            <div class="grade-section">
                <div class="nilai-display nilai-<?php echo htmlspecialchars($sertifikat['nilai']); ?>">
                    ⭐ NILAI <?php echo htmlspecialchars($sertifikat['nilai']); ?> ⭐
                </div>
                
                <?php if ($sertifikat['catatan_pembina']): ?>
                <div class="catatan-pembina">
                    <strong>Catatan Pembina:</strong><br>
                    "<?php echo htmlspecialchars($sertifikat['catatan_pembina']); ?>"
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="signature-area">
            <div class="signature-block">
                <div class="sig-location"><?php echo htmlspecialchars($tempat_sekolah); ?>, <?php echo formatTanggal($sertifikat['tanggal_terbit']); ?></div>
                <div class="sig-title">Kepala Madrasah</div>
                <img src="<?php echo BASE_URL; ?>assets/img/stempel.jpg" alt="Signature" class="sig-image"> 
                <div class="sig-name"><?php echo htmlspecialchars($nama_kepala); ?></div>
                <div class="sig-nip">NIP. <?php echo htmlspecialchars($nip_kepala); ?></div>
            </div>
            
            <div class="signature-block">
                <div class="sig-location"><?php echo htmlspecialchars($tempat_sekolah); ?>, <?php echo formatTanggal($sertifikat['tanggal_terbit']); ?></div>
                <div class="sig-title">Pembina Ekstrakurikuler</div>
                
                <?php 
                    $ttd_pembina_path = $sertifikat['ttd_pembina'] ?? '';
                    $ttd_url = '';
                    
                    // Debug: tampilkan path yang didapat dari database
                    // echo "<!-- DEBUG TTD Path: " . htmlspecialchars($ttd_pembina_path) . " -->";
                    
                    // Cek apakah tanda tangan ada dan valid
                    if (!empty($ttd_pembina_path)) {
                        // Cek jika path sudah lengkap dengan BASE_URL atau belum
                        if (strpos($ttd_pembina_path, 'http') === 0) {
                            // Jika sudah full URL, gunakan langsung
                            $ttd_url = $ttd_pembina_path;
                        } else {
                            // Jika relatif path, gabung dengan BASE_URL
                            $ttd_url = BASE_URL . $ttd_pembina_path;
                        }
                        
                        // Cek apakah file benar-benar ada di server
                        $file_path = str_replace(BASE_URL, '', $ttd_url);
                        
                        // Debug
                        // echo "<!-- DEBUG File Check: " . htmlspecialchars($file_path) . " -->";
                        // echo "<!-- DEBUG File Exists: " . (file_exists($file_path) ? 'YES' : 'NO') . " -->";
                        
                        if (!file_exists($file_path)) {
                            // File tidak ada, gunakan fallback
                            $ttd_url = BASE_URL . 'assets/img/Stempel Kemenag.jpg';
                        }
                    } else {
                        // Tidak ada tanda tangan, gunakan fallback
                        $ttd_url = BASE_URL . 'assets/img/Stempel Kemenag.jpg';
                    }
                ?>

                <img src="<?php echo $ttd_url; ?>" alt="Signature Pembina" class="sig-image" onerror="this.src='<?php echo BASE_URL; ?>assets/img/Stempel Kemenag.jpg'">
                
                <div class="sig-name"><?php echo strtoupper(htmlspecialchars($sertifikat['nama_pembina'] ?? 'Pembina')); ?></div>
                <div class="sig-nip">&nbsp;</div>
            </div>
        </div>
        
        <div class="cert-number">
            Certificate No: <strong><?php echo htmlspecialchars($sertifikat['nomor_sertifikat']); ?></strong>
        </div>
    </div>

    <script>
        // Auto download saat halaman load
        window.onload = function() {
            // Tunggu 2 detik untuk memastikan semua resource sudah load
            setTimeout(function() {
                // Hanya jalankan download jika sertifikat ditemukan
                if (document.getElementById('certificate')) {
                    downloadPDF();
                } else {
                    document.getElementById('loadingOverlay').style.display = 'none';
                }
            }, 2000);
        };

        function downloadPDF() {
            const element = document.getElementById('certificate');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const filename = 'Sertifikat_<?php echo htmlspecialchars($sertifikat['name']); ?>_<?php echo htmlspecialchars($sertifikat['nomor_sertifikat']); ?>.pdf';
            
            // Tampilkan loading
            loadingOverlay.style.display = 'flex';
            
            const opt = {
                margin: 0,
                filename: filename,
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { 
                    scale: 2,
                    useCORS: true,
                    letterRendering: true,
                    logging: false
                },
                jsPDF: { 
                    unit: 'mm', 
                    format: 'a4', 
                    orientation: 'landscape' 
                }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                // Sembunyikan loading setelah selesai
                loadingOverlay.style.display = 'none';
                
                // Tampilkan notifikasi sukses
                showNotification('Sertifikat berhasil didownload!', 'success');
            }).catch(function(error) {
                // Sembunyikan loading jika error
                loadingOverlay.style.display = 'none';
                
                // Tampilkan notifikasi error
                showNotification('Gagal mendownload sertifikat. Silakan coba lagi.', 'error');
                console.error('Error generating PDF:', error);
            });
        }
        
        function showNotification(message, type) {
            // Buat elemen notifikasi
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: ${type === 'success' ? '#198754' : '#dc3545'};
                color: white;
                padding: 15px 30px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 10000;
                font-weight: 600;
                animation: slideDown 0.3s ease;
            `;
            notification.textContent = message;
            
            // Tambahkan ke body
            document.body.appendChild(notification);
            
            // Hapus setelah 3 detik
            setTimeout(function() {
                notification.style.animation = 'slideUp 0.3s ease';
                setTimeout(function() {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>
<?php endif; ?>