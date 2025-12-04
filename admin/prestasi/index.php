<?php
// admin/prestasi/index.php
require_once '../../config/database.php';
require_once __DIR__ . '/../../config/middleware.php';
only('admin');
requireRole(['admin']);

$page_title = 'Kelola Prestasi';
$current_user = getCurrentUser();

// --- Konfigurasi Pagination ---
$limit = 4; // Jumlah data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
// -----------------------------

// Fungsi konfirmasi hapus (akan digunakan di frontend)
function confirmDelete() {
    // Fungsi ini tidak dipakai lagi karena diganti modal, tapi jika dipakai kembali
    return "return confirm('Apakah Anda yakin ingin menghapus prestasi ini? Aksi ini tidak dapat dibatalkan.');";
}

// Hapus prestasi
if (isset($_GET['delete'])) {
    $prestasi = query("SELECT sertifikat FROM prestasis WHERE id = ?", [$_GET['delete']], 'i')->fetch_assoc();
    if ($prestasi['sertifikat']) {
        deleteFile($prestasi['sertifikat']);
    }
    query("DELETE FROM prestasis WHERE id = ?", [$_GET['delete']], 'i');
    setFlash('success', 'Prestasi berhasil dihapus!');
    redirect('admin/prestasi/index.php');
}

// Filter (Clause ini seharusnya kosong untuk Admin, tapi saya biarkan di sini)
$where_clause = "";
$params = [];
$types = "";

// Ambil Total Prestasi (untuk pagination)
// Admin melihat semua, jadi tidak ada filter
$count_query_sql = "
    SELECT COUNT(*) as total
    FROM prestasis p
    JOIN ekstrakurikulers e ON p.ekstrakurikuler_id = e.id
    $where_clause
";
$total_rows = query($count_query_sql)->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Ambil prestasi (dengan LIMIT dan OFFSET)
$prestasi_query_sql = "
    SELECT p.*, e.nama_ekskul, u.name as nama_siswa, u.kelas
    FROM prestasis p
    JOIN ekstrakurikulers e ON p.ekstrakurikuler_id = e.id
    LEFT JOIN anggota_ekskul ae ON p.anggota_id = ae.id
    LEFT JOIN users u ON ae.user_id = u.id
    $where_clause
    ORDER BY p.tanggal DESC
    LIMIT ? OFFSET ?
";

// Admin tidak memiliki $params/$types, jadi kita buat array baru untuk limit/offset
$params_pagination = [$limit, $offset];
$types_pagination = "ii";

$prestasi = query($prestasi_query_sql, $params_pagination, $types_pagination);

// Statistik Penilaian untuk badge (Tetap)
$belum_dinilai = query("SELECT COUNT(*) as total FROM anggota_ekskul WHERE status = 'diterima' AND nilai = ''")->fetch_assoc()['total'];
?>

<?php include __DIR__ . '/../../includes/berry_head.php'; ?>
<?php include __DIR__ . '/../../includes/berry_shell_open.php'; ?>
<div class="p-4">
    <?php
    $flash = getFlash();
    if ($flash):
    ?>
    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
        <?php echo $flash['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-trophy-fill"></i> Kelola Prestasi</h2>
        <a href="<?php echo BASE_URL; ?>admin/prestasi/tambah.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Tambah Prestasi
        </a>
    </div>

    <div class="row">
        <?php 
        if ($prestasi && $prestasi->num_rows > 0):
            while ($row = $prestasi->fetch_assoc()):
                $badge_color = [
                    'internasional' => 'danger',
                    'nasional' => 'primary',
                    'provinsi' => 'success',
                    'kabupaten' => 'info',
                    'kecamatan' => 'warning',
                    'sekolah' => 'secondary'
                ];
        ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-<?php echo $badge_color[$row['tingkat']] ?? 'secondary'; ?>">
                            <?php echo ucfirst($row['tingkat']); ?>
                        </span>
                        <span class="badge bg-success"><?php echo $row['nama_ekskul']; ?></span>
                    </div>
                    
                    <h5 class="card-title"><?php echo $row['nama_prestasi']; ?></h5>
                    
                    <div class="mb-2">
                        <strong class="text-warning">
                            <i class="bi bi-award-fill"></i> <?php echo $row['peringkat'] ?? 'Peserta'; ?>
                        </strong>
                    </div>

                    <?php if ($row['nama_siswa']): ?>
                    <p class="mb-1">
                        <i class="bi bi-person"></i> <strong><?php echo $row['nama_siswa']; ?></strong> 
                        (<?php echo $row['kelas']; ?>)
                    </p>
                    <?php endif; ?>

                    <p class="mb-2">
                        <i class="bi bi-calendar"></i> <?php echo formatTanggal($row['tanggal']); ?>
                    </p>

                    <?php if ($row['penyelenggara']): ?>
                    <p class="mb-2">
                        <i class="bi bi-building"></i> <?php echo $row['penyelenggara']; ?>
                    </p>
                    <?php endif; ?>

                    <?php if ($row['deskripsi']): ?>
                    <p class="text-muted small">
                        <?php echo substr($row['deskripsi'], 0, 100); ?>...
                    </p>
                    <?php endif; ?>

                    <?php if ($row['sertifikat']): ?>
                    <a href="<?php echo UPLOAD_URL . $row['sertifikat']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf"></i> Lihat Sertifikat
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white">
                    <div class="btn-group w-100">
                        <a href="<?php echo BASE_URL; ?>admin/prestasi/tambah.php?edit=<?php echo $row['id']; ?>" class="btn btn-outline-warning me-2">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-outline-danger delete-prestasi">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            endwhile;
        else:
        ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-trophy fs-1"></i>
                <p class="mt-3 mb-0">Belum ada prestasi yang tercatat</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    </div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Apakah Anda yakin ingin menghapus prestasi ini? Aksi ini tidak dapat dibatalkan.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <a id="deleteButton" href="#" class="btn btn-danger">Hapus</a>
      </div>
    </div>
  </div>
</div>

<script>
    // Menambahkan fungsi modal untuk konfirmasi hapus (menggantikan window.confirm)
    document.addEventListener('DOMContentLoaded', function() {
        // Hapus fungsi onclick lama (jika ada) dan tambahkan event listener ke elemen dengan class 'delete-prestasi'
        var deleteLinks = document.querySelectorAll('a.delete-prestasi');
        deleteLinks.forEach(function(link) {
            link.removeAttribute('onclick'); // Hapus onclick lama
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var deleteUrl = this.getAttribute('href');
                
                // Set URL di tombol Hapus Modal
                var deleteButton = document.getElementById('deleteButton');
                deleteButton.setAttribute('href', deleteUrl);
                
                // Tampilkan Modal
                var confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                confirmModal.show();
            });
        });
    });
</script>
<?php include __DIR__ . '/../../includes/berry_shell_close.php'; ?>