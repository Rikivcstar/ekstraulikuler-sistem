<?php
// admin/anggota/manage.php
require_once '../../config/database.php';
require_once __DIR__ . '/../../config/middleware.php';
only('admin');
requireRole(['admin']);

$page_title = 'Kelola Anggota';
$current_user = getCurrentUser();

// --- PENGATURAN PAGINATION ---
$limit = 10; // Jumlah baris per halaman
$page_diterima = isset($_GET['page_diterima']) ? (int)$_GET['page_diterima'] : 1;
$offset_diterima = ($page_diterima - 1) * $limit;
$page_pending = isset($_GET['page_pending']) ? (int)$_GET['page_pending'] : 1;
$offset_pending = ($page_pending - 1) * $limit;
$page_ditolak = isset($_GET['page_ditolak']) ? (int)$_GET['page_ditolak'] : 1;
$offset_ditolak = ($page_ditolak - 1) * $limit;
// ------------------------------


if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    
    // Logika action tetap
    if ($action == 'approve') {
        $result = query("UPDATE anggota_ekskul SET status = 'diterima', tanggal_diterima = CURDATE() WHERE id = ?", [$id], 'i');
        if ($result['success']) {
            setFlash('success', 'Pendaftaran berhasil disetujui!');
        }
    } elseif ($action == 'reject') {
        $result = query("UPDATE anggota_ekskul SET status = 'ditolak' WHERE id = ?", [$id], 'i');
        if ($result['success']) {
            setFlash('success', 'Pendaftaran berhasil ditolak!');
        }
    } elseif ($action == 'delete') {
        // Hapus Anggota (Delete)
        $result = query("DELETE FROM anggota_ekskul WHERE id = ?", [$id], 'i');
        if ($result['success']) {
            setFlash('success', 'Anggota berhasil dihapus dari ekstrakurikuler.');
        } else {
            setFlash('danger', 'Gagal menghapus anggota.');
        }
    }
    // Arahkan kembali ke tab yang sesuai
    $redirect_tab = $_GET['tab'] ?? 'pending'; // Ambil dari URL jika ada
    redirect("admin/anggota/manage.php#$redirect_tab");
}

// Proses POST untuk UPDATE NILAI atau Status/Eskul (Opsional)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_anggota'])) {
    $anggota_id = $_POST['anggota_id'];
    $nilai = $_POST['nilai'] ?? null;
    $status = $_POST['status'] ?? null;

    // Tambahkan logika update sesuai kebutuhan Anda (misal: hanya update nilai)
    if ($nilai !== null) {
        $result = query("UPDATE anggota_ekskul SET nilai = ? WHERE id = ?", [$nilai, $anggota_id], 'si');
        if ($result['success']) {
            setFlash('success', 'Nilai anggota berhasil diperbarui.');
        } else {
            setFlash('danger', 'Gagal memperbarui nilai anggota.');
        }
    }
    // Jika Anda ingin mengizinkan perubahan status atau eskul via POST/Modal, tambahkan di sini.

    // Tetapkan tab kembali ke 'diterima' setelah update nilai
    redirect('admin/anggota/manage.php#diterima');
}

// Filter untuk pembina (hanya lihat eskul sendiri)
$where_pembina = "";
$params_pembina = [];
$types_pembina = "";

if ($current_user['role'] == 'pembina') {
    $where_pembina = "AND e.pembina_id = ?";
    $params_pembina = [$current_user['id']];
    $types_pembina = "i";
}

// --- LOGIKA PENGAMBILAN DATA ANGGOTA DENGAN PAGINATION (TERUTAMA UNTUK DITERIMA) ---

// 1. Total Data DITERIMA
$total_diterima_query = query("
    SELECT COUNT(*) as total 
    FROM anggota_ekskul ae
    JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
    WHERE ae.status = 'diterima' $where_pembina
", $params_pembina, $types_pembina);
$total_diterima = $total_diterima_query->fetch_assoc()['total'];
$total_pages_diterima = ceil($total_diterima / $limit);

// 2. Data Anggota DITERIMA (Paginated)
$anggota_diterima = query("
    SELECT ae.*, u.name, u.nisn, u.kelas, u.jenis_kelamin, u.no_hp, e.nama_ekskul, e.pembina_id
    FROM anggota_ekskul ae
    JOIN users u ON ae.user_id = u.id
    JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
    WHERE ae.status = 'diterima' $where_pembina
    ORDER BY ae.tanggal_diterima DESC, u.name ASC
    LIMIT $limit OFFSET $offset_diterima
", $params_pembina, $types_pembina);

// 3. Total Data PENDING (Tanpa pagination untuk saat ini, tapi bisa ditambahkan)
$total_pending_query = query("
    SELECT COUNT(*) as total 
    FROM anggota_ekskul ae
    JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
    WHERE ae.status = 'pending' $where_pembina
", $params_pembina, $types_pembina);
$total_pending = $total_pending_query->fetch_assoc()['total'];
$total_pages_pending = ceil($total_pending / $limit);

// 4. Data Anggota PENDING (Paginated - Opsional, di sini diterapkan juga)
$anggota_pending = query("
    SELECT ae.*, u.name, u.nisn, u.kelas, u.jenis_kelamin, u.no_hp, e.nama_ekskul, e.pembina_id
    FROM anggota_ekskul ae
    JOIN users u ON ae.user_id = u.id
    JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
    WHERE ae.status = 'pending' $where_pembina
    ORDER BY ae.created_at DESC
    LIMIT $limit OFFSET $offset_pending
", $params_pembina, $types_pembina);

// 5. Total Data DITOLAK
$total_ditolak_query = query("
    SELECT COUNT(*) as total 
    FROM anggota_ekskul ae
    JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
    WHERE ae.status = 'ditolak' $where_pembina
", $params_pembina, $types_pembina);
$total_ditolak = $total_ditolak_query->fetch_assoc()['total'];
$total_pages_ditolak = ceil($total_ditolak / $limit);

// 6. Data Anggota DITOLAK (Paginated - Opsional, di sini diterapkan juga)
$anggota_ditolak = query("
    SELECT ae.*, u.name, u.nisn, u.kelas, u.jenis_kelamin, u.no_hp, e.nama_ekskul, e.pembina_id
    FROM anggota_ekskul ae
    JOIN users u ON ae.user_id = u.id
    JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
    WHERE ae.status = 'ditolak' $where_pembina
    ORDER BY ae.created_at DESC
    LIMIT $limit OFFSET $offset_ditolak
", $params_pembina, $types_pembina);


// Statistik Penilaian untuk badge (Tetap)
$belum_dinilai = query("
    SELECT COUNT(*) as total 
    FROM anggota_ekskul ae
    JOIN ekstrakurikulers e ON ae.ekstrakurikuler_id = e.id
    WHERE ae.status = 'diterima' AND (nilai IS NULL OR nilai = '') $where_pembina
", $params_pembina, $types_pembina)->fetch_assoc()['total']; 
// ... Kode HTML ...
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

    <h2 class="mb-4"><i class="bi bi-people-fill"></i> Kelola Anggota</h2>

    <?php
    // Tentukan tab aktif berdasarkan parameter URL atau default ke pending
    $active_tab = $_GET['tab'] ?? 'pending';
    // Gunakan fungsi JS untuk mengaktifkan tab setelah load (di akhir file)
    ?>

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?php echo $active_tab == 'pending' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#pending" id="tab-pending">
                <i class="bi bi-clock"></i> Pending
                <?php 
                if ($total_pending > 0) echo "<span class='badge bg-warning ms-1'>$total_pending</span>";
                ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $active_tab == 'diterima' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#diterima" id="tab-diterima">
                <i class="bi bi-check-circle"></i> Diterima
                 <?php 
                if ($belum_dinilai > 0) echo "<span class='badge bg-danger ms-1'>Nilai: $belum_dinilai</span>";
                ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $active_tab == 'ditolak' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#ditolak" id="tab-ditolak">
                <i class="bi bi-x-circle"></i> Ditolak
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade <?php echo $active_tab == 'pending' ? 'show active' : ''; ?>" id="pending">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
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
                                <?php 
                                $no_pending = $offset_pending + 1;
                                if ($anggota_pending->num_rows > 0):
                                while ($row = $anggota_pending->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $no_pending++; ?></td>
                                    <td><?php echo $row['nisn']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['kelas']; ?></td>
                                    <td><?php echo $row['nama_ekskul']; ?></td>
                                    <td><?php echo formatTanggal($row['tanggal_daftar']); ?></td>
                                    <td>
                                        <a href="?action=approve&id=<?php echo $row['id']; ?>&tab=pending" class="btn btn-sm btn-success" title="Setujui">
                                            <i class="bi bi-check-circle"></i>
                                        </a>
                                        <button 
                                            class="btn btn-sm btn-danger btn-reject" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#confirmRejectModal" 
                                            data-id="<?php echo $row['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                            data-eskul="<?php echo htmlspecialchars($row['nama_ekskul']); ?>"
                                            data-tab="pending"
                                            title="Tolak Pendaftaran"
                                        >
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        </td>
                                </tr>
                                <?php 
                                endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Tidak ada pendaftaran pending</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php 
                    // Tautan Pagination Pending
                    if ($total_pages_pending > 1):
                        echo buildPaginationLinks($total_pages_pending, $page_pending, 'page_pending', 'pending');
                    endif;
                    ?>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?php echo $active_tab == 'diterima' ? 'show active' : ''; ?>" id="diterima">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-success">
                                <tr>
                                    <th>No</th>
                                    <th>NISN</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Eskul</th>
                                    <th>Nilai</th> <th>Status</th>
                                    <th>Aksi</th> </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no_diterima = $offset_diterima + 1;
                                if ($anggota_diterima->num_rows > 0):
                                while ($row = $anggota_diterima->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $no_diterima++; ?></td>
                                    <td><?php echo $row['nisn']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['kelas']; ?></td>
                                    <td><?php echo $row['nama_ekskul']; ?></td>
                                    <td>
                                        <?php 
                                        if (empty($row['nilai'])) {
                                            echo '<span class="badge bg-danger">Belum Dinilai</span>';
                                        } else {
                                            echo '<span class="fw-bold text-success">' . htmlspecialchars($row['nilai']) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><span class="badge bg-success">Diterima</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                            data-bs-target="#editAnggotaModal" 
                                            data-id="<?php echo $row['id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                                            data-eskul="<?php echo htmlspecialchars($row['nama_ekskul']); ?>" 
                                            data-nilai="<?php echo htmlspecialchars($row['nilai']); ?>"
                                            title="Edit Nilai/Data">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        
                                        <a href="?action=delete&id=<?php echo $row['id']; ?>&tab=diterima" class="btn btn-sm btn-danger delete-link" 
                                            data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                            data-eskul="<?php echo htmlspecialchars($row['nama_ekskul']); ?>"
                                            title="Hapus Keanggotaan">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada anggota diterima</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php 
                    // Tautan Pagination Diterima
                    if ($total_pages_diterima > 1):
                        echo buildPaginationLinks($total_pages_diterima, $page_diterima, 'page_diterima', 'diterima');
                    endif;
                    ?>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?php echo $active_tab == 'ditolak' ? 'show active' : ''; ?>" id="ditolak">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
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
                                <?php 
                                $no_ditolak = $offset_ditolak + 1;
                                if ($anggota_ditolak->num_rows > 0):
                                while ($row = $anggota_ditolak->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $no_ditolak++; ?></td>
                                    <td><?php echo $row['nisn']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['kelas']; ?></td>
                                    <td><?php echo $row['nama_ekskul']; ?></td>
                                    <td><?php echo formatTanggal($row['tanggal_daftar']); ?></td>
                                    <td><span class="badge bg-danger">Ditolak</span></td>
                                </tr>
                                <?php 
                                endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Tidak ada pendaftaran ditolak</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php 
                    // Tautan Pagination Ditolak
                    if ($total_pages_ditolak > 1):
                        echo buildPaginationLinks($total_pages_ditolak, $page_ditolak, 'page_ditolak', 'ditolak');
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editAnggotaModal" tabindex="-1" aria-labelledby="editAnggotaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="editAnggotaModalLabel"><i class="bi bi-pencil-square"></i> Edit Anggota</h5>
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
            <small class="text-muted">Isi dengan nilai/keterangan prestasi anggota (misal: Sangat Baik, Cukup, dll.)</small>
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
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title text-white" id="confirmDeleteModalLabel"><i class="bi bi-exclamation-octagon-fill"></i> Konfirmasi Hapus Keanggotaan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Anda akan menghapus keanggotaan dari:</p>
        <ul>
            <li>Nama : <strong id="deleteName"></strong></li>
            <li>Ekstrakurikuler : <strong id="deleteEskul"></strong></li>
        </ul>
        <p class="text-danger fw-bold">Apakah Anda yakin ingin menghapus keanggotaan ini? Aksi ini tidak dapat dibatalkan.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <a id="deleteButton" href="#" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus Permanen</a>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="confirmRejectModal" tabindex="-1" aria-labelledby="confirmRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="confirmRejectModalLabel"><i class="bi bi-exclamation-triangle-fill"></i> Konfirmasi Penolakan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Anda akan menolak pendaftaran anggota ini :</p>
                <ul>
                    <li>Nama : <strong id="rejectName"></strong></li>
                    <li> Ekstrakurikuler : <strong id="rejectEskul"></strong></li>
                </ul>
                <p class="text-danger fw-bold">Apakah Anda yakin ingin melanjutkan penolakan pendaftaran ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a id="rejectButton" href="#" class="btn btn-danger"><i class="bi bi-x-circle"></i> Tolak Pendaftaran</a>
            </div>
        </div>
    </div>
</div>
<script>
    // FUNGSI UNTUK MENGAKTIFKAN TAB DARI URL FRAGMENT (#diterima, #pending, #ditolak)
    document.addEventListener('DOMContentLoaded', function() {
        let url = new URL(window.location.href);
        let tab = url.hash.substring(1) || 'pending'; // Ambil fragment tanpa '#' atau default ke 'pending'
        
        // Cek jika ada parameter 'tab' di GET (untuk redirection setelah action)
        const urlParams = new URLSearchParams(window.location.search);
        const redirectTab = urlParams.get('tab');
        if (redirectTab) {
            tab = redirectTab;
        }

        let tabElement = document.getElementById(`tab-${tab}`);
        if (tabElement) {
            let bsTab = new bootstrap.Tab(tabElement);
            bsTab.show();
        }

        // --- MODAL EDIT NILAI / DATA ANGGOTA ---
        var editAnggotaModal = document.getElementById('editAnggotaModal');
        if (editAnggotaModal) {
            editAnggotaModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var eskul = button.getAttribute('data-eskul');
                var nilai = button.getAttribute('data-nilai');
                
                editAnggotaModal.querySelector('#anggotaIdInput').value = id;
                editAnggotaModal.querySelector('#anggotaName').value = name;
                editAnggotaModal.querySelector('#anggotaEskul').value = eskul;
                editAnggotaModal.querySelector('#inputNilai').value = nilai;
            });
        }

        // --- MODAL KONFIRMASI HAPUS (DELETE) ---
        let deleteLinks = document.querySelectorAll('a.delete-link[href*="action=delete"]');
        deleteLinks.forEach(function(link) {
            // Hilangkan event confirm() default jika ada, meskipun di code ini sudah tidak ada
            // link.removeAttribute('onclick'); 
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let deleteUrl = this.getAttribute('href');
                let name = this.getAttribute('data-name');
                let eskul = this.getAttribute('data-eskul');
                
                // Set URL, Name, dan Eskul di Modal
                document.getElementById('deleteButton').setAttribute('href', deleteUrl);
                document.getElementById('deleteName').textContent = name;
                document.getElementById('deleteEskul').textContent = eskul;

                // Tampilkan Modal
                let confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                confirmModal.show();
            });
        });
        
        // --- MODAL KONFIRMASI PENOLAKAN (REJECT) ---
        let rejectModal = document.getElementById('confirmRejectModal');
        if (rejectModal) {
            rejectModal.addEventListener('show.bs.modal', function (event) {
                let button = event.relatedTarget;
                let id = button.getAttribute('data-id');
                let name = button.getAttribute('data-name');
                let eskul = button.getAttribute('data-eskul');
                let tab = button.getAttribute('data-tab'); // Ambil tab asal

                let rejectUrl = `?action=reject&id=${id}&tab=${tab}`;
                
                document.getElementById('rejectButton').setAttribute('href', rejectUrl);
                document.getElementById('rejectName').textContent = name;
                document.getElementById('rejectEskul').textContent = eskul;
            });
        }
    });
</script>
<?php include __DIR__ . '/../../includes/berry_shell_close.php'; ?>

<?php
/**
 * Fungsi Pembantu untuk Menghasilkan Tautan Pagination
 * * @param int $totalPages Jumlah total halaman.
 * @param int $currentPage Halaman saat ini.
 * @param string $pageParam Nama parameter halaman di URL (misal: 'page_diterima').
 * @param string $tabId ID tab untuk fragment URL.
 * @return string HTML untuk tautan pagination.
 */
function buildPaginationLinks(int $totalPages, int $currentPage, string $pageParam, string $tabId): string {
    if ($totalPages <= 1) {
        return '';
    }

    $output = '<nav aria-label="Page navigation" class="mt-3">';
    $output .= '<ul class="pagination pagination-sm justify-content-end">';
    
    // Fungsi pembantu untuk membuat URL dengan mempertahankan parameter lain
    $buildUrl = function($page) use ($pageParam, $tabId) {
        // Ambil semua parameter GET kecuali yang terkait pagination dan tab
        $params = $_GET;
        unset($params['page_diterima'], $params['page_pending'], $params['page_ditolak'], $params['tab']);
        
        $params[$pageParam] = $page;
        $queryString = http_build_query($params);
        return '?' . $queryString . '#' . $tabId;
    };

    // Tombol Previous
    $prevPage = $currentPage - 1;
    $prevClass = $currentPage <= 1 ? 'disabled' : '';
    $prevUrl = $buildUrl($prevPage);
    $output .= "<li class='page-item $prevClass'><a class='page-link' href='$prevUrl' aria-label='Previous'><span aria-hidden='true'>&laquo;</span></a></li>";

    // Link Halaman
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    if ($start > 1) {
        $output .= "<li class='page-item'><a class='page-link' href='".$buildUrl(1)."'>1</a></li>";
        if ($start > 2) {
            $output .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        $activeClass = $i == $currentPage ? 'active' : '';
        $url = $buildUrl($i);
        $output .= "<li class='page-item $activeClass'><a class='page-link' href='$url'>$i</a></li>";
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $output .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
        $output .= "<li class='page-item'><a class='page-link' href='".$buildUrl($totalPages)."'>$totalPages</a></li>";
    }

    // Tombol Next
    $nextPage = $currentPage + 1;
    $nextClass = $currentPage >= $totalPages ? 'disabled' : '';
    $nextUrl = $buildUrl($nextPage);
    $output .= "<li class='page-item $nextClass'><a class='page-link' href='$nextUrl' aria-label='Next'><span aria-hidden='true'>&raquo;</span></a></li>";
    
    $output .= '</ul>';
    $output .= '</nav>';

    return $output;
}
?>