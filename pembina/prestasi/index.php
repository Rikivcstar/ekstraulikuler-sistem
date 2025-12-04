<?php
// admin/prestasi/index.php
require_once '../../config/database.php';
require_once __DIR__ . '/../../config/middleware.php';

// Memastikan hanya Admin atau Pembina yang bisa mengakses
only('pembina');
requireRole(['pembina']);

$page_title = 'Kelola Prestasi';
$current_user = getCurrentUser();

// --- Konfigurasi Pagination ---
$limit = 4; // Jumlah data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;


// 1. LOGIKA HAPUS (DELETE)
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    // Ambil data prestasi untuk verifikasi dan menghapus file sertifikat
    $prestasi_data = query("
        SELECT p.sertifikat, e.pembina_id
        FROM prestasis p
        JOIN ekstrakurikulers e ON p.ekstrakurikuler_id = e.id
        WHERE p.id = ?
    ", [$delete_id], 'i')->fetch_assoc();

    if ($prestasi_data) {
        // Otorisasi Penghapusan
        $is_authorized = ($current_user['role'] == 'pembina' && $prestasi_data['pembina_id'] == $current_user['id']);
        
        if ($is_authorized) {
            // Hapus file sertifikat
            if ($prestasi_data['sertifikat']) {
                deleteFile($prestasi_data['sertifikat']);
            }
            
            // Hapus data dari database
            query("DELETE FROM prestasis WHERE id = ?", [$delete_id], 'i');
            setFlash('success', 'Prestasi berhasil dihapus!');
        } else {
            setFlash('error', 'Anda tidak memiliki izin untuk menghapus prestasi ini.');
        }
    } else {
        setFlash('error', 'Prestasi tidak ditemukan.');
    }
    
    // Redirect kembali ke halaman saat ini setelah operasi
    $redirect_url = 'pembina/prestasi/index.php';
    if (isset($_GET['page'])) {
        $redirect_url .= '?page=' . $page;
    }
    redirect($redirect_url);
}

// 2. LOGIKA FETCH DATA (PAGINATION & FILTER)

// Filter untuk Pembina: hanya menampilkan prestasi ekskul yang dibinanya
$where_clause = "";
$params = [];
$types = "";

if ($current_user['role'] == 'pembina') {
    $where_clause = "WHERE e.pembina_id = ?";
    $params = [$current_user['id']];
    $types = "i";
}

// Query untuk menghitung total data
$count_query_sql = "
    SELECT COUNT(*) as total
    FROM prestasis p
    JOIN ekstrakurikulers e ON p.ekstrakurikuler_id = e.id
    $where_clause
";
$total_rows_result = query($count_query_sql, $params, $types)->fetch_assoc();
$total_rows = intval($total_rows_result['total'] ?? 0);
$total_pages = ceil($total_rows / $limit);

// Query untuk mengambil data prestasi per halaman
$prestasi_query_sql = "
    SELECT p.*, e.nama_ekskul, u.name as nama_siswa, u.kelas, e.pembina_id
    FROM prestasis p
    JOIN ekstrakurikulers e ON p.ekstrakurikuler_id = e.id
    LEFT JOIN anggota_ekskul ae ON p.anggota_id = ae.id
    LEFT JOIN users u ON ae.user_id = u.id
    $where_clause
    ORDER BY p.tanggal DESC
    LIMIT ? OFFSET ?
";

$params_pagination = $params;
$params_pagination[] = $limit;
$params_pagination[] = $offset;
$types_pagination = $types . "ii";

$prestasi = query($prestasi_query_sql, $params_pagination, $types_pagination);

// 3. TAMPILAN (VIEW)
?>

<?php include __DIR__ . '/../../includes/berry_head.php'; ?>
<?php include __DIR__ . '/../../includes/berry_shell_open.php'; ?>
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
        <h2><i class="bi bi-trophy-fill"></i> Prestasi Ekstrakurikuler</h2>
        <a href="<?php echo BASE_URL; ?>pembina/prestasi/tambah.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Tambah Prestasi
        </a>
    </div>

    <div class="row">
        <?php 
        if ($prestasi && $prestasi->num_rows > 0):
            $badge_colors = [
                'internasional' => 'danger',
                'nasional' => 'primary',
                'provinsi' => 'success',
                'kabupaten' => 'info',
                'kecamatan' => 'warning',
                'sekolah' => 'secondary'
            ];
            while ($row = $prestasi->fetch_assoc()):
                $can_edit_delete = ($current_user['role'] == 'pembina' && $row['pembina_id'] == $current_user['id']);
                $tingkat_color = $badge_colors[$row['tingkat']] ?? 'secondary';
        ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-<?php echo $tingkat_color; ?>">
                            <?php echo htmlspecialchars(ucfirst($row['tingkat'])); ?>
                        </span>
                        <span class="badge bg-success"><?php echo htmlspecialchars($row['nama_ekskul']); ?></span>
                    </div>
                    
                    <h5 class="card-title"><?php echo htmlspecialchars($row['nama_prestasi']); ?></h5>
                    
                    <div class="mb-2">
                        <strong class="text-warning">
                            <i class="bi bi-award-fill"></i> <?php echo htmlspecialchars($row['peringkat'] ?? 'Peserta'); ?>
                        </strong>
                    </div>

                    <?php if ($row['nama_siswa']): ?>
                    <p class="mb-1">
                        <i class="bi bi-person"></i> <strong><?php echo htmlspecialchars($row['nama_siswa']); ?></strong> 
                        (<?php echo htmlspecialchars($row['kelas']); ?>)
                    </p>
                    <?php endif; ?>

                    <p class="mb-2">
                        <i class="bi bi-calendar"></i> <?php echo formatTanggal($row['tanggal']); ?>
                    </p>

                    <?php if ($row['penyelenggara']): ?>
                    <p class="mb-2">
                        <i class="bi bi-building"></i> <?php echo htmlspecialchars($row['penyelenggara']); ?>
                    </p>
                    <?php endif; ?>

                    <?php if ($row['deskripsi']): ?>
                    <p class="text-muted small">
                        <?php echo htmlspecialchars(substr($row['deskripsi'], 0, 100)); ?>...
                    </p>
                    <?php endif; ?>

                    <?php if ($row['sertifikat']): ?>
                    <a href="<?php echo UPLOAD_URL . htmlspecialchars($row['sertifikat']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf"></i> Lihat Sertifikat
                    </a>
                    <?php endif; ?>
                </div>
                <?php if ($can_edit_delete): ?>
                <div class="card-footer bg-white">
                    <div class="btn-group w-100">
                        <a href="<?php echo BASE_URL; ?>pembina/prestasi/tambah.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="?delete=<?php echo $row['id']; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-danger delete-prestasi">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php 
            endwhile;
        else:
        ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-trophy fs-1"></i>
                <p class="mt-3 mb-0">Belum ada prestasi yang tercatat untuk ekskul yang Anda bina.</p>
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

                <?php 
                    $range = 2;
                    $start_page = max(1, $page - $range);
                    $end_page = min($total_pages, $page + $range);

                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                        }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++): 
                ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php 
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                    }
                ?>

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
            <p class="text-danger small mt-2">Anda akan diarahkan kembali ke halaman daftar setelah penghapusan.</p>
        </div>
          <div class="modal-footer">
             <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a id="deleteButton" href="#" class="btn btn-danger">Hapus</a>
           </div>
        </div>
        </div>
    </div>

<script>
// Menggunakan Modal Bootstrap untuk konfirmasi hapus (menggantikan window.confirm)
    document.addEventListener('DOMContentLoaded', function() {
        let deleteLinks = document.querySelectorAll('a.delete-prestasi'); 
         deleteLinks.forEach(function(link) {
             link.addEventListener('click', function(e) {
             e.preventDefault();
             let deleteUrl = this.getAttribute('href');
            // Set URL di tombol Hapus Modal
            let deleteButton = document.getElementById('deleteButton');
            deleteButton.setAttribute('href', deleteUrl);
            // Tampilkan Modal
                 let confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                 confirmModal.show();
                });
             });
        });
</script>
<?php include __DIR__ . '/../../includes/berry_shell_close.php'; ?>