<?php
// admin/pengumuman/index.php
require_once '../../config/database.php';
require_once __DIR__ . '/../../config/middleware.php';
only('pembina');
requireRole(['pembina']);

$page_title = 'Kelola Pengumuman';
$current_user = getCurrentUser();


// ===============================
//          PAGINATION
// ===============================
$limit = 10; // Limit per halaman
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;


// ===============================
//        PROSES DELETE
// ===============================
if (isset($_GET['delete'])) {
    query("DELETE FROM pengumuman WHERE id = ?", [$_GET['delete']], 'i');
    setFlash('success', 'Pengumuman berhasil dihapus!');
    redirect('pembina/pengumuman/index.php');
}


// ===============================
//        TOGGLE STATUS
// ===============================
if (isset($_GET['toggle'])) {
    query("UPDATE pengumuman SET is_active = NOT is_active WHERE id = ?", [$_GET['toggle']], 'i');
    setFlash('success', 'Status pengumuman berhasil diupdate!');
    redirect('pembina/pengumuman/index.php');
}


// ===============================
//             FILTER
// ===============================
$where = "";
$params = [];
$types  = "";

if ($current_user['role'] == 'pembina') {
    $where      = "WHERE (p.user_id = ? OR e.pembina_id = ?)";
    $params     = [$current_user['id'], $current_user['id']];
    $types      = "ii";
}


// ===============================
//   HITUNG TOTAL DATA (COUNT)
// ===============================
$total_sql = "
    SELECT COUNT(*) AS total
    FROM pengumuman p
    LEFT JOIN ekstrakurikulers e ON p.ekstrakurikuler_id = e.id
    $where
";

$total_rows = query($total_sql, $params, $types)->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);


// ===============================
//        AMBIL DATA UTAMA
// ===============================
$pengumuman_sql = "
    SELECT p.*, e.nama_ekskul, u.name AS pembuat
    FROM pengumuman p
    LEFT JOIN ekstrakurikulers e ON p.ekstrakurikuler_id = e.id
    LEFT JOIN users u ON p.user_id = u.id
    $where
    ORDER BY p.prioritas DESC, p.created_at DESC
    LIMIT ? OFFSET ?
";

$params2 = $params;
$params2[] = $limit;
$params2[] = $offset;

$types2 = $types . "ii";

$pengumuman = query($pengumuman_sql, $params2, $types2);


// PRIORITY BADGE
$badge_priority = [
    'tinggi' => 'danger',
    'sedang' => 'warning',
    'rendah' => 'info'
];

?>

<?php include __DIR__ . '/../../includes/berry_head.php'; ?>
<?php include __DIR__ . '/../../includes/berry_shell_open.php'; ?>

<div class="p-4">

    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type']; ?> alert-dismissible fade show">
        <?= $flash['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-megaphone"></i> Kelola Pengumuman</h2>
        <a href="<?= BASE_URL; ?>pembina/pengumuman/tambah.php" class="btn btn-success">
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
                        <?php 
                            $no = $offset + 1; 
                            while ($row = $pengumuman->fetch_assoc()): 
                        ?>

                        <tr>
                            <td><?= $no++; ?></td>

                            <td>
                                <strong><?= $row['judul']; ?></strong><br>
                                <small class="text-muted"><?= substr($row['isi'], 0, 50); ?>...</small>
                            </td>

                            <td>
                                <?= $row['nama_ekskul'] ?: '<span class="badge bg-secondary">Umum</span>'; ?>
                            </td>

                            <td>
                                <small>
                                    <?= $row['tanggal_mulai'] ? date('d/m/Y', strtotime($row['tanggal_mulai'])) : '-'; ?> <br>
                                    s/d <?= $row['tanggal_selesai'] ? date('d/m/Y', strtotime($row['tanggal_selesai'])) : '-'; ?>
                                </small>
                            </td>

                            <td>
                                <span class="badge bg-<?= $badge_priority[$row['prioritas']] ?? 'secondary'; ?>">
                                    <?= ucfirst($row['prioritas']); ?>
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

                                    <a href="?toggle=<?= $row['id']; ?>" 
                                       class="btn btn-<?= $row['is_active'] ? 'warning' : 'success'; ?>" 
                                       title="Toggle Status">
                                        <i class="bi bi-<?= $row['is_active'] ? 'pause' : 'play'; ?>"></i>
                                    </a>

                                    <a href="<?= BASE_URL; ?>pembina/pengumuman/tambah.php?edit=<?= $row['id']; ?>" 
                                       class="btn btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <a href="?delete=<?= $row['id']; ?>" 
                                       class="btn btn-danger delete-link" 
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

            <!-- PAGINATION -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center">

                    <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a>
                    </li>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                    <?php endfor; ?>

                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= $page + 1; ?>">Next</a>
                    </li>

                </ul>
            </nav>
            <?php endif; ?>

        </div>
    </div>
</div>


<!-- Modal konfirmasi hapus -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Apakah Anda yakin ingin menghapus Pengumuman ini? Aksi ini tidak dapat dibatalkan.
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <a id="deleteButton" href="#" class="btn btn-danger">Hapus</a>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let links = document.querySelectorAll(".delete-link");

    links.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            let url = this.getAttribute("href");

            let modalDelete = new bootstrap.Modal(document.getElementById("confirmDeleteModal"));
            document.getElementById("deleteButton").setAttribute("href", url);
            modalDelete.show();
        });
    });
});
</script>

<?php include __DIR__ . '/../../includes/berry_shell_close.php'; ?>
