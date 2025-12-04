<?php
// admin/pengumuman/index.php
require_once '../../config/database.php';
require_once __DIR__ . '/../../config/middleware.php';
only('admin');
requireRole(['admin']);

$page_title = 'Kelola Pengumuman';
$current_user = getCurrentUser();

// --- Konfigurasi Pagination ---
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
// -----------------------------

// =========================================================================
// 1. LOGIKA HAPUS (DELETE)
// =========================================================================
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $current_page = $_GET['page'] ?? 1;

    query("DELETE FROM pengumuman WHERE id = ?", [$delete_id], 'i');
    setFlash('success', 'Pengumuman berhasil dihapus!');
    
    // Redirect dengan mempertahankan halaman
    redirect('admin/pengumuman/index.php?page=' . $current_page);
}

// =========================================================================
// 2. LOGIKA TOGGLE STATUS
// =========================================================================
if (isset($_GET['toggle'])) {
    $toggle_id = $_GET['toggle'];
    $current_page = $_GET['page'] ?? 1;
    
    query("UPDATE pengumuman SET is_active = NOT is_active WHERE id = ?", [$toggle_id], 'i');
    setFlash('success', 'Status pengumuman berhasil diupdate!');
    
    // Redirect dengan mempertahankan halaman
    redirect('admin/pengumuman/index.php?page=' . $current_page);
}

// =========================================================================
// 3. LOGIKA FETCH DATA (PAGINATION)
// =========================================================================

$where = "";
$params = [];
$types = "";

// Jika suatu saat ada user selain admin yang mengakses (walaupun sudah dibatasi di middleware)
if ($current_user['role'] == 'pembina') {
    $where = "WHERE (p.user_id = ? OR e.pembina_id = ?)";
    $params = [$current_user['id'], $current_user['id']];
    $types = "ii";
}

// Query untuk menghitung total data
$count_query_sql = "
    SELECT COUNT(*) as total
    FROM pengumuman p
    LEFT JOIN ekstrakurikulers e ON p.ekstrakurikuler_id = e.id
    LEFT JOIN users u ON p.user_id = u.id
    $where
";
$total_rows_result = query($count_query_sql, $params, $types)->fetch_assoc();
$total_rows = intval($total_rows_result['total'] ?? 0);
$total_pages = ceil($total_rows / $limit);

// Query untuk mengambil data pengumuman per halaman (dengan LIMIT dan OFFSET)
$pengumuman_query_sql = "
    SELECT p.*, e.nama_ekskul, u.name AS pembuat
    FROM pengumuman p
    LEFT JOIN ekstrakurikulers e ON p.ekstrakurikuler_id = e.id
    LEFT JOIN users u ON p.user_id = u.id
    $where
    ORDER BY p.prioritas DESC, p.created_at DESC
    LIMIT ? OFFSET ?
";

$params_pagination = $params;
$params_pagination[] = $limit;
$params_pagination[] = $offset;
$types_pagination = $types . "ii";

// Ambil daftar pengumuman
$pengumuman = query($pengumuman_query_sql, $params_pagination, $types_pagination);

// PRIORITY BADGE
$badge_priority = [
    'tinggi' => 'danger',
    'sedang' => 'warning',
    'rendah' => 'info'
];

// Nomor urut awal untuk halaman ini
$no_start = $offset + 1;

// =========================================================================
// 4. TAMPILAN (VIEW)
// =========================================================================
?>

<?php include __DIR__ . '/../../includes/berry_head.php'; ?>
<?php include __DIR__ . '/../../includes/berry_shell_open.php'; ?>

<div class="p-4">

    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']); ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-megaphone"></i> Kelola Pengumuman <small class="text-muted">(Total: <?= $total_rows; ?>)</small></h2>
        <a href="<?= BASE_URL; ?>admin/pengumuman/tambah.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Tambah Pengumuman
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Ekskul</th>
                            <th>Periode</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pengumuman && $pengumuman->num_rows > 0): ?>
                        <?php $no = $no_start; while ($row = $pengumuman->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['judul']); ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars(substr($row['isi'], 0, 50)); ?>...</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                <?= htmlspecialchars($row['nama_ekskul'] ?? 'Umum'); ?>
                                </span>
                            </td>
                            <td>
                                <small>
                                    <?= $row['tanggal_mulai'] ? date('d/m/Y', strtotime($row['tanggal_mulai'])) : '-'; ?> <br>
                                    s/d <?= $row['tanggal_selesai'] ? date('d/m/Y', strtotime($row['tanggal_selesai'])) : '-'; ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $badge_priority[$row['prioritas']] ?? 'secondary'; ?>">
                                    <?= htmlspecialchars(ucfirst($row['prioritas'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['is_active']): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php $query_page = "?page=" . $page; ?>
                                    
                                    <a href="?toggle=<?= $row['id'] . $query_page; ?>" 
                                       class="btn btn-<?= $row['is_active'] ? 'warning' : 'success'; ?>" 
                                       title="Toggle Status">
                                        <i class="bi bi-<?= $row['is_active'] ? 'pause' : 'play'; ?>"></i>
                                    </a>

                                    <a href="<?= BASE_URL; ?>admin/pengumuman/tambah.php?edit=<?= $row['id']; ?>" 
                                       class="btn btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <a href="?delete=<?= $row['id'] . $query_page; ?>" 
                                       class="btn btn-danger delete-btn-custom" 
                                       title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>

                                </div>
                            </td>
                        </tr>

                        <?php endwhile; ?>

                        <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Belum ada pengumuman</p>
                            </td>
                        </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a>
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
                            <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
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

                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            </div>
    </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Apakah Anda yakin ingin menghapus Pengumuman ini? Aksi ini tidak dapat dibatalkan.
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
        // Menggunakan kelas kustom 'delete-btn-custom' untuk menargetkan link delete yang baru
        var deleteLinks = document.querySelectorAll('a.delete-btn-custom');
        deleteLinks.forEach(function(link) {
            link.removeAttribute('onclick'); // Hapus onclick lama jika ada
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