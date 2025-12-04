<?php
// pembina/anggota/manage.php (CLEANED + PAGINATION + TAB-SAFE)
require_once '../../config/database.php';
require_once __DIR__ . '/../../config/middleware.php';

// Akses: pembina
only('pembina');
requireRole(['pembina']);

$page_title = 'Kelola Anggota';
$current_user = getCurrentUser();
$pembina_id = isset($_SESSION['pembina_id']) ? intval($_SESSION['pembina_id']) : intval($current_user['id'] ?? 0);

// --- Helpers ---
function get_query_param($key, $default = null) {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

// Sanitized tab (only allow these values)
$allowed_tabs = ['pending', 'diterima', 'ditolak'];
$tab = get_query_param('tab', 'pending');
if (!in_array($tab, $allowed_tabs)) $tab = 'pending';

// Pagination
$limit = 10; // rows per page
$page = max(1, intval(get_query_param('page', 1)));
$offset = ($page - 1) * $limit;

// === ACTIONS: approve / reject / delete ===
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);

    // pastikan anggota terkait ekskul pembina
    $cek = query(
        "SELECT ae.id
         FROM anggota_ekskul ae
         JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
         WHERE ae.id = ? AND e.pembina_id = ?",
        [$id, $pembina_id],
        "ii"
    );

    if (!$cek || $cek->num_rows == 0) {
        setFlash('danger', 'Akses ditolak: anggota tidak ditemukan di ekskul Anda.');
        redirect('pembina/anggota/manage.php?tab=' . urlencode($tab) . '&page=' . intval($page));
    }

    if ($action === 'approve') {
        query("UPDATE anggota_ekskul SET status = 'diterima', tanggal_diterima = CURDATE() WHERE id = ?", [$id], 'i');
        setFlash('success', 'Pendaftaran berhasil disetujui!');
    } elseif ($action === 'reject') {
        query("UPDATE anggota_ekskul SET status = 'ditolak' WHERE id = ?", [$id], 'i');
        setFlash('success', 'Pendaftaran berhasil ditolak!');
    } elseif ($action === 'delete') {
        query("DELETE FROM anggota_ekskul WHERE id = ?", [$id], 'i');
        setFlash('success', 'Anggota berhasil dihapus dari ekstrakurikuler.');
    }

    // redirect kembali ke tab & page saat ini
    redirect('pembina/anggota/manage.php?tab=' . urlencode($tab) . '&page=' . intval($page));
}

// === HANDLE POST: update nilai / catatan ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_anggota'])) {
    $anggota_id = intval($_POST['anggota_id']);
    $nilai = trim($_POST['nilai'] ?? '');
    $catatan = trim($_POST['catatan_pembina'] ?? '');

    // validasi kepemilikan
    $cek = query(
        "SELECT ae.id
         FROM anggota_ekskul ae
         JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
         WHERE ae.id = ? AND e.pembina_id = ?",
        [$anggota_id, $pembina_id],
        "ii"
    );

    if (!$cek || $cek->num_rows == 0) {
        setFlash('danger', 'Akses ditolak: tidak bisa mengubah anggota ini.');
        redirect('pembina/anggota/manage.php?tab=' . urlencode($tab) . '&page=' . intval($page));
    }

    query("UPDATE anggota_ekskul SET nilai = ?, catatan_pembina = ?, tanggal_penilaian = CURDATE() WHERE id = ?", [$nilai, $catatan, $anggota_id], "ssi");
    setFlash('success', 'Nilai / catatan anggota berhasil diperbarui.');
    redirect('pembina/anggota/manage.php?tab=' . urlencode($tab) . '&page=' . intval($page));
}

// --- Badge counts (pending + belum dinilai) ---
$count_pending_q = query(
    "SELECT COUNT(*) AS total
     FROM anggota_ekskul ae
     JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
     WHERE ae.status = 'pending' AND e.pembina_id = ?",
    [$pembina_id], 'i'
);
$count_pending = intval($count_pending_q->fetch_assoc()['total'] ?? 0);

$belum_dinilai_q = query(
    "SELECT COUNT(*) AS total
     FROM anggota_ekskul ae
     JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
     WHERE ae.status = 'diterima' AND (ae.nilai IS NULL OR ae.nilai = '') AND e.pembina_id = ?",
    [$pembina_id], 'i'
);
$belum_dinilai = intval($belum_dinilai_q->fetch_assoc()['total'] ?? 0);

// --- Total rows for active tab (for pagination) ---
$total_rows_q = query(
    "SELECT COUNT(*) AS total
     FROM anggota_ekskul ae
     JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
     WHERE e.pembina_id = ? AND ae.status = ?",
    [$pembina_id, $tab], 'is'
);
$total_rows = intval($total_rows_q->fetch_assoc()['total'] ?? 0);
$total_pages = max(1, (int) ceil($total_rows / $limit));
if ($page > $total_pages) $page = $total_pages; // safety
$offset = ($page - 1) * $limit;

// --- Ambil data untuk tab aktif dengan LIMIT/OFFSET ---
$anggota_q = query(
    "SELECT ae.*, u.name, u.nisn, u.kelas, u.jenis_kelamin, u.no_hp, e.nama_ekskul
     FROM anggota_ekskul ae
     JOIN users u ON ae.user_id = u.id
     JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
     WHERE e.pembina_id = ? AND ae.status = ?
     ORDER BY ae.created_at DESC
     LIMIT ? OFFSET ?",
    [$pembina_id, $tab, $limit, $offset],
    "isis"
);

// --- Include view (head / shell) ---
?>
<?php include __DIR__ . '/../../includes/berry_head.php'; ?>
<?php include __DIR__ . '/../../includes/berry_shell_open.php'; ?>

<div class="p-4">

    <?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']); ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <h2 class="mb-4"><i class="bi bi-people-fill"></i> Kelola Anggota (Pembina)</h2>

    <!-- Tabs sebagai link (memudahkan bookmark & pagination) -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?= ($tab=='pending'?'active':'') ?>" href="?tab=pending&page=1">
                <i class="bi bi-clock"></i> Pending
                <?php if ($count_pending > 0): ?>
                    <span class="badge bg-warning ms-1"><?= $count_pending; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($tab=='diterima'?'active':'') ?>" href="?tab=diterima&page=1">
                <i class="bi bi-check-circle"></i> Diterima
                <?php if ($belum_dinilai > 0): ?>
                    <span class="badge bg-danger ms-1"><?= $belum_dinilai; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($tab=='ditolak'?'active':'') ?>" href="?tab=ditolak&page=1">
                <i class="bi bi-x-circle"></i> Ditolak
            </a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <?php if ($tab === 'pending'): ?>
                <!-- Pending Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-warning">
                            <tr>
                                <th>No</th>
                                <th>NISN</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Eskul</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($anggota_q && $anggota_q->num_rows > 0): ?>
                                <?php $no = $offset + 1; while ($row = $anggota_q->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nisn']); ?></td>
                                        <td><?= htmlspecialchars($row['name']); ?></td>
                                        <td><?= htmlspecialchars($row['kelas']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_ekskul']); ?></td>
                                        <td><?= htmlspecialchars(formatTanggal($row['tanggal_daftar'])); ?></td>
                                        <td>
                                            <a href="?action=approve&id=<?= $row['id']; ?>&tab=<?= $tab; ?>&page=<?= $page; ?>" class="btn btn-sm btn-success" title="Setujui" onclick="return confirm('Setujui pendaftaran ini?')">
                                                <i class="bi bi-check-circle"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger btn-reject" data-bs-toggle="modal" data-bs-target="#confirmRejectModal" data-id="<?= $row['id']; ?>" data-name="<?= htmlspecialchars($row['name']); ?>" data-eskul="<?= htmlspecialchars($row['nama_ekskul']); ?>" title="Tolak Pendaftaran">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Tidak ada pendaftaran pending</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($tab === 'diterima'): ?>
                <!-- Diterima Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-success">
                            <tr>
                                <th>No</th>
                                <th>NISN</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Eskul</th>
                                <th>Nilai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($anggota_q && $anggota_q->num_rows > 0): ?>
                                <?php $no = $offset + 1; while ($row = $anggota_q->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nisn']); ?></td>
                                        <td><?= htmlspecialchars($row['name']); ?></td>
                                        <td><?= htmlspecialchars($row['kelas']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_ekskul']); ?></td>
                                        <td>
                                            <?php if (empty($row['nilai'])): ?>
                                                <span class="badge bg-danger">Belum Dinilai</span>
                                            <?php else: ?>
                                                <span class="fw-bold text-success"><?= htmlspecialchars($row['nilai']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-success">Diterima</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editAnggotaModal" data-id="<?= $row['id']; ?>" data-name="<?= htmlspecialchars($row['name']); ?>" data-eskul="<?= htmlspecialchars($row['nama_ekskul']); ?>" data-nilai="<?= htmlspecialchars($row['nilai']); ?>" data-catatan="<?= htmlspecialchars($row['catatan_pembina'] ?? '') ?>" title="Edit Nilai/Data">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <a href="?action=delete&id=<?= $row['id']; ?>&tab=<?= $tab; ?>&page=<?= $page; ?>" class="btn btn-sm btn-danger" title="Hapus Keanggotaan">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada anggota diterima</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Ditolak Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-danger">
                            <tr>
                                <th>No</th>
                                <th>NISN</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Eskul</th>
                                <th>Tanggal Daftar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($anggota_q && $anggota_q->num_rows > 0): ?>
                                <?php $no = $offset + 1; while ($row = $anggota_q->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nisn']); ?></td>
                                        <td><?= htmlspecialchars($row['name']); ?></td>
                                        <td><?= htmlspecialchars($row['kelas']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_ekskul']); ?></td>
                                        <td><?= htmlspecialchars(formatTanggal($row['tanggal_daftar'])); ?></td>
                                        <td><span class="badge bg-danger">Ditolak</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Tidak ada pendaftaran ditolak</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center mt-3">
                        <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
                            <a class="page-link" href="?tab=<?= $tab ?>&page=<?= max(1, $page - 1) ?>">&laquo; Sebelumnya</a>
                        </li>

                        <?php
                        // Tampilkan range ringkas (max 7 tombol)
                        $start = max(1, $page - 3);
                        $end = min($total_pages, $page + 3);
                        for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= ($page == $i ? 'active' : '') ?>">
                                <a class="page-link" href="?tab=<?= $tab ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($page >= $total_pages ? 'disabled' : '') ?>">
                            <a class="page-link" href="?tab=<?= $tab ?>&page=<?= min($total_pages, $page + 1) ?>">Selanjutnya &raquo;</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modals (Edit, Reject, Delete Confirm) -->
<?php // Edit Anggota Modal (sama seperti versi Anda, hanya sedikit disusun) ?>
<div class="modal fade" id="editAnggotaModal" tabindex="-1" aria-labelledby="editAnggotaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="?tab=<?= $tab; ?>&page=<?= $page; ?>">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="editAnggotaModalLabel"><i class="bi bi-pencil-square"></i> Edit Nilai & Catatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="anggota_id" id="anggotaIdInput">
          <input type="hidden" name="update_anggota" value="1">

          <div class="mb-3">
            <label class="form-label">Nama Anggota</label>
            <input type="text" class="form-control" id="anggotaName" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label">Ekstrakurikuler</label>
            <input type="text" class="form-control" id="anggotaEskul" readonly>
          </div>

          <div class="mb-3">
            <label for="inputNilai" class="form-label">Nilai Akhir / Keterangan</label>
            <input type="text" name="nilai" class="form-control" id="inputNilai" placeholder="Masukkan nilai (misal: A, B, C atau Baik, Cukup)">
            <small class="text-muted">Isi dengan nilai/keterangan prestasi anggota</small>
          </div>

          <div class="mb-3">
            <label for="catatanPembina" class="form-label">Catatan Pembina</label>
            <textarea name="catatan_pembina" id="catatanPembina" class="form-control" rows="3" placeholder="Catatan untuk anggota (opsional)"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-info">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="confirmRejectModal" tabindex="-1" aria-labelledby="confirmRejectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Konfirmasi Penolakan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menolak pendaftaran anggota <strong id="rejectAnggotaName">...</strong> ke ekstrakurikuler <strong id="rejectAnggotaEskul">...</strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <a id="rejectButton" href="#" class="btn btn-danger">Tolak Pendaftaran</a>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">Apakah Anda yakin ingin menghapus Anggota ini? Aksi ini tidak dapat dibatalkan.</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <a id="deleteButton" href="#" class="btn btn-danger">Hapus</a>
      </div>
    </div>
  </div>
</div>

<!-- SCRIPTS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete links - gunakan modal
    document.querySelectorAll('a[href*="action=delete"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var href = this.getAttribute('href');
            var btn = document.getElementById('deleteButton');
            btn.setAttribute('href', href);
            var m = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            m.show();
        });
    });

    // Reject modal
    var rejectModal = document.getElementById('confirmRejectModal');
    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var eskul = button.getAttribute('data-eskul');
            rejectModal.querySelector('#rejectAnggotaName').textContent = name;
            rejectModal.querySelector('#rejectAnggotaEskul').textContent = eskul;
            var rejectButton = rejectModal.querySelector('#rejectButton');
            rejectButton.setAttribute('href', '?action=reject&id=' + id + '&tab=' + encodeURIComponent('<?= $tab ?>') + '&page=' + <?= $page ?>);
        });
    }

    // Edit modal
    var editModal = document.getElementById('editAnggotaModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            editModal.querySelector('#anggotaIdInput').value = button.getAttribute('data-id');
            editModal.querySelector('#anggotaName').value = button.getAttribute('data-name');
            editModal.querySelector('#anggotaEskul').value = button.getAttribute('data-eskul');
            editModal.querySelector('#inputNilai').value = button.getAttribute('data-nilai') || '';
            editModal.querySelector('#catatanPembina').value = button.getAttribute('data-catatan') || '';
        });
    }
});
</script>

<?php include __DIR__ . '/../../includes/berry_shell_close.php'; ?>
