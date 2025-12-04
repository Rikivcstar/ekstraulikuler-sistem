<?php
// admin/pengaturan_sertifikat.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/middleware.php';

// Pastikan hanya admin yang bisa akses
only('admin');
requireRole(['admin']);

$page_title = 'Pengaturan Sertifikat';
$current_user = getCurrentUser();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_pengaturan'])) {
    $predikat = trim($_POST['predikat_sekolah']);
    $nama_sekolah = trim($_POST['nama_sekolah']);
    $alamat = trim($_POST['alamat_sekolah']);
    $nama_pembina = trim($_POST['nama_pembina']);
    $nip = trim($_POST['nip_pembina']);
    $tempat = trim($_POST['tempat_sekolah']);
    $nama_kepala = trim($_POST['nama_kepala_madrasah']);
    $nip_kepala = trim($_POST['nip_kepala_madrasah']);
    
    // Handle background upload
    $background_path = null;
    if (isset($_FILES['background_sertifikat']) && $_FILES['background_sertifikat']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['background_sertifikat'], 'certificate');
        if ($upload_result['success']) {
            $background_path = $upload_result['filename'];
            
            // Hapus background lama jika ada
            $old_bg_result = query("SELECT key_value FROM pengaturan WHERE key_name = 'background_sertifikat'");
            if ($old_bg_result && $old_bg_result->num_rows > 0) {
                $old_bg = $old_bg_result->fetch_assoc();
                if ($old_bg['key_value'] != 'assets/img/certificate-bg.png') {
                    deleteFile($old_bg['key_value']);
                }
            }
        }
    }
    
    // Update pengaturan
    $updates = [
        ['predikat_sekolah', $predikat],
        ['nama_sekolah', $nama_sekolah],
        ['alamat_sekolah', $alamat],
        ['nama_pembina', $nama_pembina],
        ['nip_pembina', $nip],
        ['tempat_sekolah', $tempat],
        ['nama_kepala_madrasah', $nama_kepala],
        ['nip_kepala_madrasah', $nip_kepala]
    ];
    
    // Tambahkan background jika ada upload
    if ($background_path) {
        $updates[] = ['background_sertifikat', 'assets/img/uploads/' . $background_path];
    }
    
    $success = true;
    foreach ($updates as $update) {
        $result = query(
            "INSERT INTO pengaturan (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)",
            [$update[0], $update[1]],
            'ss'
        );
        
        // Cek apakah query berhasil
        if (!$result || (is_array($result) && !$result['success'])) {
            $success = false;
            break;
        }
    }
    
    if ($success) {
        setFlash('success', 'Pengaturan sertifikat berhasil disimpan!');
    } else {
        setFlash('danger', 'Gagal menyimpan pengaturan!');
    }
    
    header("Location: pengaturan_sertifikat.php");
    exit();
}

// Get current settings
$settings = [];
$result = query("SELECT * FROM pengaturan");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['key_name']] = $row['key_value'];
    }
}
?>
<?php include __DIR__ . '/../includes/berry_head.php'; ?>
<?php include __DIR__ . '/../includes/berry_shell_open.php'; ?>

    <div class="p-4">
        <?php
        $flash = getFlash();
        if ($flash):
        ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-gear-fill text-primary"></i> Pengaturan Sertifikat</h2>
                <p class="text-muted">Kelola informasi yang muncul di sertifikat siswa</p>
            </div>
        </div>

        <!-- Alert Info -->
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle"></i>
            <strong>Informasi:</strong> Pengaturan ini akan diterapkan pada semua sertifikat yang dicetak oleh siswa.
        </div>

        <!-- Form Pengaturan -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 text-white"><i class="bi bi-award"></i> Data Sertifikat</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <!-- Background Sertifikat -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-image"></i> Background Sertifikat
                                </label>
                                <?php if (isset($settings['background_sertifikat'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo BASE_URL . htmlspecialchars($settings['background_sertifikat']); ?>" 
                                         alt="Current Background" 
                                         class="img-thumbnail" 
                                         style="max-width: 200px;">
                                </div>
                                <?php endif; ?>
                                <input type="file" name="background_sertifikat" class="form-control" accept="image/*">
                                <small class="text-muted">Upload gambar background baru (PNG/JPG, Landscape A4 direkomendasikan: 2970x2100px)</small>
                            </div>

                            <hr class="my-4">

                            <!-- Data Sekolah -->
                            <h6 class="text-primary mb-3"><i class="bi bi-building"></i> Data Sekolah</h6>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Nama Sekolah</label>
                                <input type="text" name="nama_sekolah" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['nama_sekolah'] ?? 'MTsN 1 LEBAK'); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Predikat Sekolah</label>
                                <input type="text" name="predikat_sekolah" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['predikat_sekolah'] ?? 'TERAKREDITASI A'); ?>" required>
                                <small class="text-muted">Contoh: TERAKREDITASI A</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Alamat Sekolah</label>
                                <input type="text" name="alamat_sekolah" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['alamat_sekolah'] ?? 'Jl. Raya Rangkasbitung, Lebak, Banten'); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Tempat/Kota</label>
                                <input type="text" name="tempat_sekolah" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['tempat_sekolah'] ?? 'Lebak'); ?>" required>
                                <small class="text-muted">Kota untuk tanggal sertifikat</small>
                            </div>

                            <hr class="my-4">

                            <!-- Data Kepala Madrasah -->
                            <h6 class="text-primary mb-3"><i class="bi bi-person-badge-fill"></i> Kepala Madrasah</h6>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Nama Kepala Madrasah</label>
                                <input type="text" name="nama_kepala_madrasah" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['nama_kepala_madrasah'] ?? 'Dr. H. Muhammad Yusuf, M.Pd.I'); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">NIP Kepala Madrasah</label>
                                <input type="text" name="nip_kepala_madrasah" class="form-control" 
                                       value="<?php echo htmlspecialchars($settings['nip_kepala_madrasah'] ?? '197201152005011003'); ?>" required>
                            </div>

                            <hr class="my-4">

                            <div class="d-grid gap-2">
                                <button type="submit" name="simpan_pengaturan" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle"></i> Simpan Pengaturan
                                </button>
                                <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0 text-white"><i class="bi bi-eye"></i> Preview Sertifikat</h6>
                    </div>
                    <div class="card-body text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px;">
                        <i class="bi bi-award-fill" style="font-size: 3rem;"></i>
                        <h5 class="mt-3" id="preview-nama"><?php echo htmlspecialchars($settings['nama_sekolah'] ?? 'MTsN 1 LEBAK'); ?></h5>
                        <div class="badge bg-light text-dark my-2" id="preview-predikat">
                            <?php echo htmlspecialchars($settings['predikat_sekolah'] ?? 'TERAKREDITASI A'); ?>
                        </div>
                        <p class="mb-0" style="font-size: 0.85rem;" id="preview-alamat">
                            <?php echo htmlspecialchars($settings['alamat_sekolah'] ?? 'Jl. Raya Rangkasbitung, Lebak, Banten'); ?>
                        </p>
                        <hr class="my-3 bg-white">
                        <h6>CERTIFICATE</h6>
                        <p style="font-size: 0.8rem;">of Achievement</p>
                        <hr class="my-3 bg-white">
                        
                        <!-- Tanda Tangan -->
                        <div class="row mt-4">
                            <div class="col-6">
                                <div style="font-size: 0.7rem;">
                                    <strong>Kepala Madrasah</strong><br>
                                    <div style="height: 40px;"></div>
                                    <strong id="preview-kepala"><?php echo htmlspecialchars($settings['nama_kepala_madrasah'] ?? 'Dr. H. Muhammad Yusuf'); ?></strong><br>
                                    NIP. <span id="preview-nip-kepala"><?php echo htmlspecialchars($settings['nip_kepala_madrasah'] ?? '197201152005011003'); ?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div style="font-size: 0.7rem;">
                                    <strong>Ketua Pembina</strong><br>
                                    <div style="height: 40px;"></div>
                               
                                    NIP. <span id="preview-nip"><?php echo htmlspecialchars($settings['nip_pembina'] ?? '198505152010011023'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Preview akan update otomatis
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="alert alert-warning mt-4">
            <h6><i class="bi bi-lightbulb"></i> Tips:</h6>
            <ul class="mb-0">
                <li>Pastikan semua data terisi dengan benar sebelum menyimpan</li>
                <li>Background sertifikat sebaiknya berukuran landscape (2970x2100px untuk kualitas terbaik)</li>
                <li>Format background yang didukung: PNG, JPG/JPEG</li>
                <li>Nama dan NIP harus sesuai dengan data resmi</li>
                <li>Perubahan akan langsung berlaku pada semua sertifikat yang dicetak</li>
            </ul>
        </div>
    </div>
        
    <script>
        // Live preview update
        const inputs = {
            'nama_sekolah': 'preview-nama',
            'predikat_sekolah': 'preview-predikat',
            'alamat_sekolah': 'preview-alamat',
            'nama_kepala_madrasah': 'preview-kepala',
            'nip_kepala_madrasah': 'preview-nip-kepala',
            'nama_pembina': 'preview-pembina',
            'nip_pembina': 'preview-nip'
        };
        
        Object.keys(inputs).forEach(inputName => {
            const input = document.querySelector(`input[name="${inputName}"]`);
            const preview = document.getElementById(inputs[inputName]);
            
            if (input && preview) {
                input.addEventListener('input', function(e) {
                    preview.textContent = e.target.value || preview.textContent;
                });
            }
        });
    </script>
<?php include __DIR__ . '/../includes/berry_shell_close.php'; ?>