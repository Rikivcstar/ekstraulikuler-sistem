-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 04 Des 2025 pada 03.07
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistem_eskul`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota_ekskul`
--

CREATE TABLE `anggota_ekskul` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ekstrakurikuler_id` int(11) NOT NULL,
  `tanggal_daftar` date NOT NULL,
  `alasan_daftar` text DEFAULT NULL,
  `status` enum('pending','diterima','ditolak','keluar') DEFAULT 'pending',
  `nilai` enum('A','B','C','') DEFAULT '',
  `tanggal_penilaian` date DEFAULT NULL,
  `catatan_pembina` text DEFAULT NULL,
  `tanggal_diterima` date DEFAULT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `alasan_keluar` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `anggota_ekskul`
--

INSERT INTO `anggota_ekskul` (`id`, `user_id`, `ekstrakurikuler_id`, `tanggal_daftar`, `alasan_daftar`, `status`, `nilai`, `tanggal_penilaian`, `catatan_pembina`, `tanggal_diterima`, `tanggal_keluar`, `alasan_keluar`, `created_at`, `updated_at`) VALUES
(1, 4, 1, '2024-07-15', 'Ingin mengembangkan jiwa kepemimpinan', 'diterima', 'B', '2025-11-20', 'baik', '2024-07-16', NULL, NULL, '2025-10-25 11:04:24', '2025-11-20 10:41:54'),
(2, 5, 3, '2024-07-15', 'Hobi bermain futsal', 'diterima', 'A', '2025-11-12', '', '2024-07-16', NULL, NULL, '2025-10-25 11:04:24', '2025-11-12 06:04:03'),
(3, 6, 2, '2024-07-16', 'Ingin memperbaiki bacaan Al-Quran', 'diterima', 'A', '2025-11-29', 'No komen', '2024-07-17', NULL, NULL, '2025-10-25 11:04:24', '2025-11-29 09:36:16'),
(4, 7, 4, '2024-07-16', 'Ingin lancar berbahasa Inggris', 'diterima', 'A', '2025-11-30', 'baik', '2024-07-17', NULL, NULL, '2025-10-25 11:04:24', '2025-11-30 04:35:17'),
(7, 10, 3, '2024-07-18', 'Tim futsal sekolah', 'diterima', 'A', '2025-11-12', '', '2024-07-19', NULL, NULL, '2025-10-25 11:04:24', '2025-11-12 06:22:57'),
(11, 5, 5, '2025-11-17', 'sfjshfjhjhfjhhshshhfhsjhfjshfssfsf', 'diterima', 'A', '2025-11-30', 'baik', '2025-11-17', NULL, NULL, '2025-11-17 10:08:30', '2025-11-30 04:35:24'),
(14, 29, 4, '2025-11-20', 'hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', 'diterima', 'A', '2025-11-21', 'Sangat Baik', '2025-11-20', NULL, NULL, '2025-11-20 10:52:00', '2025-11-21 06:22:14'),
(15, 5, 7, '2025-11-20', 'hhhhhhhhhhhhhhhhhhhhhhhhhhhhh', 'diterima', '', NULL, NULL, '2025-11-20', NULL, NULL, '2025-11-20 11:40:59', '2025-11-20 11:42:48'),
(16, 27, 1, '2025-11-22', 'ingin mengikuti saja eskul ini', 'diterima', 'A', '2025-11-30', 'Baik', '2025-11-22', NULL, NULL, '2025-11-22 16:06:17', '2025-11-30 03:15:28'),
(17, 32, 10, '2025-11-29', 'saya ingin menjadi pemain basket dunia untuk mengharumkan nama bangsa indonesia', 'ditolak', '', NULL, NULL, NULL, NULL, NULL, '2025-11-29 09:23:55', '2025-11-29 09:27:52'),
(18, 32, 8, '2025-11-29', 'karna dengan mengikuti ekskul paskibra saya dapat melatih kedisiplinan dan kerapihan dalam baris berbaris', 'diterima', '', NULL, NULL, '2025-11-29', NULL, NULL, '2025-11-29 09:26:24', '2025-11-29 09:27:40'),
(19, 32, 9, '2025-11-29', 'testttttttttttttttttttt', 'diterima', '', NULL, NULL, '2025-11-30', NULL, NULL, '2025-11-29 09:32:48', '2025-11-30 12:37:39'),
(20, 32, 1, '2025-11-30', 'kosongannnnnnnnnnnnnnnnnnnn', 'diterima', '', NULL, NULL, NULL, NULL, NULL, '2025-11-30 02:52:58', '2025-11-30 04:36:21'),
(21, 32, 3, '2025-11-30', '-------------------------------------------------------', 'diterima', '', NULL, NULL, '2025-11-30', NULL, NULL, '2025-11-30 12:47:16', '2025-11-30 12:47:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `berita`
--

CREATE TABLE `berita` (
  `id` int(11) NOT NULL,
  `ekstrakurikuler_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `judul` varchar(200) NOT NULL,
  `konten` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `tanggal_post` date NOT NULL,
  `views` int(11) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `berita`
--

INSERT INTO `berita` (`id`, `ekstrakurikuler_id`, `user_id`, `judul`, `konten`, `gambar`, `tanggal_post`, `views`, `is_published`, `created_at`, `updated_at`) VALUES
(2, 2, 3, 'Kegiatan Tilawah Memperingati Nuzulul Quran', 'Ekstrakurikuler Tilawah mengadakan kegiatan khusus memperingati Nuzulul Quran dengan mengadakan tadarus bersama dan lomba tahfidz internal.', 'berita/691aa356c5ead.jpg', '2024-10-10', 102, 1, '2025-10-25 11:04:24', '2025-11-20 02:12:22'),
(3, 1, 2, 'Pramuka Mengadakan Kemah Akhir Tahun', 'Anggota Pramuka akan mengadakan kemah akhir tahun di Bumi Perkemahan Lebak. Kegiatan ini bertujuan melatih kemandirian dan kekompakan anggota.', 'berita/691aa345564f0.jpg', '2024-10-05', 163, 1, '2025-10-25 11:04:24', '2025-11-30 13:07:04'),
(4, 10, 1, 'Club Basket akan melakukan pertandingan di jakarta internsional ', 'kosong', 'berita/692c3a1aa1af5.jpg', '2025-11-30', 0, 1, '2025-11-30 12:34:20', '2025-11-30 12:35:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ekstrakurikulers`
--

CREATE TABLE `ekstrakurikulers` (
  `id` int(11) NOT NULL,
  `nama_ekskul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `pembina_id` int(11) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `kuota` int(11) DEFAULT 30,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ekstrakurikulers`
--

INSERT INTO `ekstrakurikulers` (`id`, `nama_ekskul`, `deskripsi`, `pembina_id`, `gambar`, `kuota`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Pramuka', 'Kegiatan kepramukaan untuk membentuk karakter siswa yang mandiri, disiplin, dan berjiwa kepemimpinan', 2, 'eskul/69187512491c3.jpg', 50, 'aktif', '2025-10-25 11:04:24', '2025-11-15 12:41:54'),
(2, 'Tilawah', 'Meningkatkan kemampuan membaca Al-Quran dengan tartil dan memperdalam ilmu tajwid', 3, 'eskul/6918756fa06b4.jpg', 30, 'aktif', '2025-10-25 11:04:24', '2025-11-15 12:43:27'),
(3, 'Futsal', 'Olahraga futsal untuk meningkatkan kebugaran dan kerja sama tim', 3, 'eskul/691875351366d.png', 25, 'aktif', '2025-10-25 11:04:24', '2025-11-18 08:15:50'),
(4, 'English Club', 'Meningkatkan kemampuan berbahasa Inggris melalui conversation dan games', 2, 'eskul/691876773225d.jpg', 30, 'aktif', '2025-10-25 11:04:24', '2025-11-15 12:47:51'),
(5, 'Seni Musik', 'Belajar alat musik dan vokal untuk mengembangkan bakat seni', 2, 'eskul/692821ac71fe9.jpg', 20, 'aktif', '2025-10-25 11:04:24', '2025-11-27 10:02:20'),
(8, 'Paskibra', 'Rohis Adalah Kegiatan ekstrakulikuler untuk memperdalam ilmu keagamaan', 3, 'eskul/69271b8dc5f33.jpg', 30, 'aktif', '2025-11-21 13:26:13', '2025-11-26 15:23:57'),
(9, 'Pencak Silat', 'Ekstrakulikuler ini adalah kegiatan di mana siswa bisa melatih seni bela diri ', 33, 'eskul/69271bcb9fbd2.jpg', 30, 'aktif', '2025-11-26 15:24:59', '2025-11-30 12:40:03'),
(10, 'Basket Ball', 'Ekstrakulikuler ini adalah kegiatan dimana siswa dapat berlatih kecepatan dan juga kelincahan di waktu yg bersamaan', 3, 'eskul/69271ce1b3e34.jpg', 30, 'aktif', '2025-11-26 15:29:37', '2025-11-26 15:29:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `galeris`
--

CREATE TABLE `galeris` (
  `id` int(11) NOT NULL,
  `ekstrakurikuler_id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_upload` date DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `galeris`
--

INSERT INTO `galeris` (`id`, `ekstrakurikuler_id`, `judul`, `gambar`, `deskripsi`, `tanggal_upload`, `urutan`, `is_active`, `uploaded_by`, `created_at`) VALUES
(6, 5, 'Kegiatan Tilawah Memperingati Nuzulul Quran', 'galeri/galeri_1763893321_6922e0494f9e1.jpeg', 'No caption', '2025-11-23', 0, 1, 5, '2025-11-23 10:22:01'),
(7, 1, 'Pramuka Mengadakan Kemah Akhir Tahun', 'galeri/galeri_1764237619_692821339a9a7.jpg', 'Kosongan duluuuuuuuuuuuuu', '2025-11-27', 0, 1, 27, '2025-11-27 10:00:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `inventaris`
--

CREATE TABLE `inventaris` (
  `id` int(11) NOT NULL,
  `ekstrakurikuler_id` int(11) NOT NULL,
  `nama_barang` varchar(200) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `kondisi` enum('baik','rusak ringan','rusak berat') DEFAULT 'baik',
  `tanggal_beli` date DEFAULT NULL,
  `harga` decimal(15,2) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `inventaris`
--

INSERT INTO `inventaris` (`id`, `ekstrakurikuler_id`, `nama_barang`, `kategori`, `jumlah`, `satuan`, `kondisi`, `tanggal_beli`, `harga`, `lokasi`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 3, 'Bola Futsal Mikasa', 'Peralatan Olahraga', 5, 'buah', 'baik', '2024-09-05', 400000.00, 'Gudang Olahraga', NULL, '2025-10-25 11:04:24', '2025-10-25 11:04:24'),
(2, 3, 'Cone Training', 'Peralatan Latihan', 20, 'buah', 'baik', '2024-09-05', 300000.00, 'Gudang Olahraga', NULL, '2025-10-25 11:04:24', '2025-10-25 11:04:24'),
(3, 2, 'Al-Quran Mushaf', 'Perlengkapan Tilawah', 15, 'buah', 'baik', '2024-07-10', 1500000.00, 'Musholla', NULL, '2025-10-25 11:04:24', '2025-10-25 11:04:24'),
(4, 1, 'Tenda Pramuka', 'Perlengkapan Kemah', 10, 'buah', 'baik', '2024-08-15', 5000000.00, 'Gudang Pramuka', NULL, '2025-10-25 11:04:24', '2025-10-25 11:04:24'),
(5, 5, 'Gitar Akustik', 'Alat Musik', 3, 'buah', 'baik', '2024-07-20', 3000000.00, 'Ruang Musik', NULL, '2025-10-25 11:04:24', '2025-10-25 11:04:24'),
(6, 5, 'Keyboard Yamaha', 'Alat Musik', 1, 'buah', 'baik', '2024-07-20', 4500000.00, 'Ruang Musik', NULL, '2025-10-25 11:04:24', '2025-10-25 11:04:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_latihans`
--

CREATE TABLE `jadwal_latihans` (
  `id` int(11) NOT NULL,
  `ekstrakurikuler_id` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jadwal_latihans`
--

INSERT INTO `jadwal_latihans` (`id`, `ekstrakurikuler_id`, `hari`, `jam_mulai`, `jam_selesai`, `lokasi`, `keterangan`, `is_active`, `created_at`) VALUES
(1, 1, 'Sabtu', '14:00:00', '16:00:00', 'Lapangan Upacara', 'Latihan rutin Pramuka', 1, '2025-10-25 11:04:24'),
(2, 2, 'Jumat', '15:00:00', '17:00:00', 'Musholla', 'Belajar tilawah dan tahsin', 1, '2025-10-25 11:04:24'),
(5, 4, 'Jumat', '15:03:00', '16:30:00', 'Lab Bahasa', 'English conversation practice', 1, '2025-10-25 11:04:24'),
(7, 7, 'Jumat', '06:39:00', '09:39:00', 'Aula Rohis', 'Jadwal kegiatan ektrakulikuler dari rohis', 1, '2025-11-20 10:40:32'),
(8, 5, 'Jumat', '00:38:00', '01:40:00', 'ruang musik', NULL, 1, '2025-11-21 05:38:46'),
(9, 10, 'Rabu', '13:40:00', '15:29:00', 'Lapangan basket', 'sudah harus ada di tempat tidak boleh telat', 1, '2025-11-29 09:48:38'),
(10, 8, 'Senin', '15:06:00', '16:59:00', 'lapangan bola', '', 1, '2025-11-29 09:49:25'),
(11, 4, 'Minggu', '11:41:00', '00:41:00', 'ruang musik', NULL, 1, '2025-11-30 03:41:40'),
(12, 9, 'Minggu', '15:40:00', '16:40:00', 'lapanga futsal', 'kosonggg----------------', 1, '2025-11-30 12:40:43'),
(13, 3, 'Senin', '11:25:00', '00:25:00', 'lapanga futsal', '', 1, '2025-12-01 04:25:09');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `key_name` varchar(64) NOT NULL,
  `key_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`key_name`, `key_value`, `updated_at`) VALUES
('alamat_sekolah', 'Jl. Raya Rangkasbitung, Lebak, Banten', '2025-11-15 15:01:43'),
('background_sertifikat', 'assets/img/uploads/certificate/692c424197506.png', '2025-11-30 13:10:25'),
('nama_kepala_madrasah', 'Dr. H. Muhammad Yusuf, M.Pd.I', '2025-11-28 14:04:25'),
('nama_pembina', '', '2025-11-30 13:10:25'),
('nama_sekolah', 'MTsN 1 LEBAK', '2025-11-15 15:01:43'),
('nip_kepala_madrasah', '197201152005011003', '2025-11-28 14:04:43'),
('nip_pembina', '', '2025-11-30 13:10:25'),
('predikat_sekolah', 'TERAKREDITASI A', '2025-11-15 15:01:43'),
('tempat_sekolah', 'Lebak', '2025-11-15 15:01:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int(11) NOT NULL,
  `ekstrakurikuler_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `prioritas` enum('rendah','sedang','tinggi') DEFAULT 'sedang',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `ekstrakurikuler_id`, `user_id`, `judul`, `isi`, `tanggal_mulai`, `tanggal_selesai`, `prioritas`, `is_active`, `created_at`) VALUES
(3, 2, 3, 'Latihan Tambahan Menjelang MTQ', 'Bagi anggota tilawah yang akan mengikuti MTQ, harap hadir latihan tambahan setiap hari Kamis.', '2024-10-15', '2024-11-15', 'rendah', 1, '2025-10-25 11:04:24'),
(5, NULL, 2, 'Maulid nabi muhammad saw 12 rabbiul awal', 'Maulid nabi muhammad saw 12 rabbiul awal akan diadakan tahun ini pada 17 desember 2025', '2025-11-22', '2025-12-22', 'tinggi', 1, '2025-11-22 04:35:27'),
(6, NULL, 2, 'Kemah Tour Guide Mengadakan Kemah Akhir Tahun', 'kemah yang diadakan pada tahun ini akan diadakan di gunung semeru', '2025-11-30', '2025-12-30', 'tinggi', 1, '2025-11-30 03:36:52'),
(7, 1, 3, 'Latihan Perdana Semester Baru', 'Latihan perdana akan dimulai minggu depan. Harap semua anggota hadir tepat waktu.', '2025-02-10', '2025-02-10', 'sedang', 1, '2025-11-30 04:19:30'),
(8, 4, 3, 'Rapat Pembina Ekskul', 'Seluruh pembina ekskul diharapkan hadir dalam rapat koordinasi bulan ini.', '2025-02-15', '2025-02-15', 'tinggi', 1, '2025-11-30 04:19:30'),
(9, 4, 4, 'Pengumpulan Berkas Anggota Baru', 'Calon anggota baru diwajibkan mengumpulkan formulir pendaftaran.', '2025-02-01', '2025-02-05', 'tinggi', 1, '2025-11-30 04:19:30'),
(10, 1, 3, 'Perubahan Jadwal Latihan', 'Jadwal latihan minggu ini berubah menjadi hari Jumat.', '2025-02-08', '2025-02-08', 'rendah', 0, '2025-11-30 04:19:30'),
(11, 5, 5, 'Persiapan Lomba Tingkat Kota', 'Anggota terpilih wajib mengikuti briefing dan latihan tambahan.', '2025-02-20', '2025-02-25', 'tinggi', 1, '2025-11-30 04:19:30'),
(12, 4, 4, 'Pengumuman Libur Latihan', 'Latihan ditiadakan sementara karena kegiatan sekolah.', '2025-02-12', '2025-02-12', 'sedang', 1, '2025-11-30 04:19:30'),
(13, 1, 3, 'Pembagian Kostum Ekskul', 'Kostum baru akan dibagikan pada pertemuan berikutnya.', '2025-02-18', '2025-02-18', 'sedang', 1, '2025-11-30 04:19:30'),
(14, 5, 5, 'Open Recruitment Anggota Baru', 'Ekskul kembali membuka penerimaan anggota baru. Daftar sekarang!', '2025-03-01', '2025-03-10', 'tinggi', 1, '2025-11-30 04:19:30'),
(15, 3, 4, 'Pemberitahuan Evaluasi Bulanan', 'Evaluasi kinerja anggota akan dilakukan minggu ke-4 setiap bulan.', '2025-02-22', '2025-02-22', 'rendah', 1, '2025-11-30 04:19:30'),
(16, 1, 3, 'Informasi Penting Kegiatan Outdoor', 'Wajib membawa perlengkapan pribadi dan jas hujan.', '2025-03-05', '2025-03-05', 'sedang', 1, '2025-11-30 04:19:30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `presensis`
--

CREATE TABLE `presensis` (
  `id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `jadwal_latihan_id` int(11) DEFAULT NULL,
  `jadwal_id` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `status` enum('hadir','izin','sakit','alpha') DEFAULT 'hadir',
  `keterangan` text DEFAULT NULL,
  `waktu_presensi` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `presensis`
--

INSERT INTO `presensis` (`id`, `anggota_id`, `jadwal_latihan_id`, `jadwal_id`, `tanggal`, `status`, `keterangan`, `waktu_presensi`, `created_at`) VALUES
(1, 1, NULL, 1, '2024-10-19', 'hadir', NULL, '2024-10-19 07:05:00', '2025-10-25 11:04:24'),
(2, 2, NULL, 3, '2024-10-23', 'hadir', NULL, '2024-10-23 08:10:00', '2025-10-25 11:04:24'),
(3, 3, NULL, 2, '2024-10-18', 'hadir', NULL, '2024-10-18 08:00:00', '2025-10-25 11:04:24'),
(4, 4, NULL, 5, '2024-10-24', 'hadir', NULL, '2024-10-24 08:05:00', '2025-10-25 11:04:24'),
(5, 5, NULL, 6, '2024-10-22', 'hadir', NULL, '2024-10-22 08:15:00', '2025-10-25 11:04:24'),
(6, 1, NULL, 1, '2024-10-12', 'hadir', NULL, '2024-10-12 07:00:00', '2025-10-25 11:04:24'),
(7, 2, NULL, 3, '2024-10-16', 'izin', NULL, '2024-10-16 08:00:00', '2025-10-25 11:04:24'),
(8, 3, NULL, 2, '2024-10-11', 'hadir', NULL, '2024-10-11 08:05:00', '2025-10-25 11:04:24'),
(9, 7, NULL, NULL, '2025-10-25', 'hadir', '', '2025-10-25 11:13:36', '2025-10-25 11:13:36'),
(10, 2, NULL, NULL, '2025-10-25', 'hadir', '', '2025-10-25 11:13:36', '2025-10-25 11:13:36'),
(11, 7, NULL, NULL, '2025-11-08', 'hadir', '', '2025-11-08 07:27:12', '2025-11-08 07:27:12'),
(12, 2, NULL, NULL, '2025-11-08', 'hadir', '', '2025-11-08 07:27:13', '2025-11-08 07:27:13'),
(13, 4, NULL, NULL, '2025-11-18', 'hadir', '', '2025-11-18 04:10:53', '2025-11-18 04:10:53'),
(14, 7, NULL, NULL, '2025-11-18', 'sakit', 'panas dalam', '2025-11-18 04:11:41', '2025-11-18 04:11:41'),
(15, 2, NULL, NULL, '2025-11-18', 'hadir', '', '2025-11-18 04:11:41', '2025-11-18 04:11:41'),
(16, 11, NULL, 6, '2025-11-18', 'hadir', 'Check-in via QR Code', '2025-11-18 16:42:48', '2025-11-18 16:42:48'),
(17, 13, NULL, 5, '2025-11-20', 'hadir', 'Check-in via QR Code', '2025-11-20 02:20:12', '2025-11-20 02:20:12'),
(18, 5, NULL, NULL, '2025-11-20', 'hadir', '', '2025-11-20 10:41:31', '2025-11-20 10:41:31'),
(19, 11, NULL, NULL, '2025-11-20', 'hadir', '', '2025-11-20 10:41:31', '2025-11-20 10:41:31'),
(20, 14, NULL, 5, '2025-11-20', 'hadir', 'Check-in via QR Code', '2025-11-20 10:53:39', '2025-11-20 10:53:39'),
(21, 11, NULL, 8, '2025-11-21', 'hadir', 'Check-in via QR Code', '2025-11-21 05:45:58', '2025-11-21 05:45:58'),
(22, 16, NULL, 1, '2025-11-22', 'hadir', 'Check-in via QR Code', '2025-11-22 16:09:21', '2025-11-22 16:09:21'),
(23, 19, NULL, 12, '2025-11-30', 'hadir', 'Check-in via QR Code', '2025-11-30 12:42:28', '2025-11-30 12:42:28'),
(24, 2, NULL, 13, '2025-12-01', 'hadir', 'Check-in via QR Code', '2025-12-01 04:28:37', '2025-12-01 04:28:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `prestasis`
--

CREATE TABLE `prestasis` (
  `id` int(11) NOT NULL,
  `ekstrakurikuler_id` int(11) NOT NULL,
  `anggota_id` int(11) DEFAULT NULL,
  `nama_prestasi` varchar(200) NOT NULL,
  `tingkat` enum('sekolah','kecamatan','kabupaten','provinsi','nasional','internasional') NOT NULL,
  `peringkat` varchar(50) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `penyelenggara` varchar(200) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `sertifikat` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `prestasis`
--

INSERT INTO `prestasis` (`id`, `ekstrakurikuler_id`, `anggota_id`, `nama_prestasi`, `tingkat`, `peringkat`, `tanggal`, `penyelenggara`, `deskripsi`, `sertifikat`, `created_at`) VALUES
(5, 1, 6, 'Juara 1 Pramuka Competition Tingkat Provinsiii', 'kecamatan', 'Juara 1 dan Juara Unggulan', '2025-11-18', 'Dinas Kecamatan', 'Pramuka ekskul  Mendapatkan juara pertama tingkat kecamatan', 'prestasi/691d2dedbd710.png', '2025-11-19 02:39:41'),
(6, 5, 11, 'Juara 1 Seni Musik Competition Tingkat Provinsiii', 'provinsi', 'Juara 1 dan Juara Unggulan', '2025-11-18', 'Dinas Provinsi', 'test cobaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'prestasi/691d30448acb2.pdf', '2025-11-19 02:49:40'),
(11, 3, 2, 'Juara 1 FUTSAL Competition Tingkat provinsi', 'provinsi', 'Juara 1 dan Juara Unggulan', '2025-11-18', 'Dinas Provinsi', 'kosonggggggggggggggg', 'assets/img/uploads/sertifikat/sertifikat_1764240087_69282ad76d5ef.pdf', '2025-11-27 10:41:27'),
(12, 2, 3, 'Juara 1 Tilawah Competition Tingkat Internasional', 'kecamatan', 'Juara 2', '2025-11-18', 'Dinas Kecamatan', 'ksonggggggggggggggggg', 'prestasi/692832b28f56e.pdf', '2025-11-27 11:14:58'),
(15, 9, 19, 'Juara 1 Pencak Silat Competition Tingkat provinsi', 'provinsi', 'Juara 1 dan Juara Unggulan', '2025-11-30', 'Dinas Provinsi', 'kosongggggggggggg', 'prestasi/692c3d80381a8.pdf', '2025-11-30 12:50:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sertifikats`
--

CREATE TABLE `sertifikats` (
  `id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `nomor_sertifikat` varchar(50) NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `masa_berlaku` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `file_sertifikat` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sertifikats`
--

INSERT INTO `sertifikats` (`id`, `anggota_id`, `nomor_sertifikat`, `tanggal_terbit`, `masa_berlaku`, `keterangan`, `file_sertifikat`, `created_at`) VALUES
(1, 2, 'CERT-2025-5792', '2025-11-08', NULL, NULL, NULL, '2025-11-08 07:27:34'),
(2, 4, 'CERT-2025-9874', '2025-11-11', NULL, NULL, NULL, '2025-11-11 06:31:09'),
(3, 13, 'CERT-2025-0983', '2025-11-21', NULL, NULL, NULL, '2025-11-21 06:14:59'),
(4, 11, 'CERT-2025-8813', '2025-11-21', NULL, NULL, NULL, '2025-11-21 06:18:19'),
(5, 14, 'CERT-2025-8819', '2025-11-21', NULL, NULL, NULL, '2025-11-21 06:18:59'),
(6, 16, 'CERT-2025-6778', '2025-11-22', NULL, NULL, NULL, '2025-11-22 16:10:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pembina','siswa') DEFAULT 'siswa',
  `nisn` varchar(20) DEFAULT NULL,
  `kelas` varchar(10) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `tanda_tangan` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `nisn`, `kelas`, `jenis_kelamin`, `no_hp`, `alamat`, `foto`, `tanda_tangan`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@mtsn1lebak.sch.id', '$2y$10$0VvJ82xDLHfnO0BUNEroPOM4miC8IaLsGp7IUsYBK14eLodpxHe2m', 'admin', NULL, NULL, NULL, NULL, NULL, 'assets/img/uploads/users/foto_1_1764426917.png', NULL, 1, '2025-10-25 11:04:24', '2025-11-29 14:35:17'),
(2, 'Ahmad Sabar', 'ahmad@mtsn1lebak.sch.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pembina', NULL, NULL, NULL, '089999992922', 'Kp.Lebak Rangkkas', 'assets/img/uploads/users/foto_2_1763515650.png', 'assets/img/uploads/signatures/ttd_2_1764340988.png', 1, '2025-10-25 11:04:24', '2025-11-28 14:43:08'),
(3, 'Ustadz Muhsin', 'muhsin@mtsn1lebak.sch.id', '$2y$10$MtqqmcSilKyRyhk2zc21geRxjZDmE2Co22dbyJm1O24mywdmioYNm', 'pembina', NULL, NULL, NULL, NULL, NULL, 'assets/img/uploads/users/foto_3_1764563222.jpeg', NULL, 1, '2025-10-25 11:04:24', '2025-12-01 04:27:20'),
(4, 'Ahmad Fauzi', 'ahmad.fauzi@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa', '2023001', '8A', 'L', '081234567890', 'Lebak Gedong', NULL, NULL, 1, '2025-10-25 11:04:24', '2025-10-25 11:04:24'),
(5, 'RIKI REZA', 'rikireza@gmail.com', '$2y$10$QS2ZMKthal8oMGNceAxqd.x2cIz/n5G1JOeeJFlt40PxSrhzqWtna', 'siswa', '2023002', '7A', 'P', '088488494844', 'jl.melati raya bojong', 'assets/img/uploads/users/foto_5_1763638809.jpeg', NULL, 1, '2025-10-25 11:04:24', '2025-11-21 13:12:53'),
(6, 'Budi Santoso', 'budi.santoso@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa', '2023003', '8A', 'L', '081234567892', 'Cibadak', NULL, NULL, 1, '2025-10-25 11:04:24', '2025-10-25 11:04:24'),
(7, 'Dewi Sartika', 'dewi.sartika@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa', '2023004', '8C', 'P', '081234567893', 'Maja', NULL, NULL, 1, '2025-10-25 11:04:24', '2025-10-25 11:04:24'),
(8, 'Eko Prasetyo', 'eko.prasetyo@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa', '2023005', '8A', 'L', '081234567894', 'Malingping', NULL, NULL, 1, '2025-10-25 11:04:24', '2025-10-25 11:04:24'),
(9, 'Fatimah Zahra', 'fatimah.zahra@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa', '2023006', '8B', 'P', '081234567895', 'Banjarsari', NULL, NULL, 1, '2025-10-25 11:04:24', '2025-10-25 11:04:24'),
(10, 'Galih Ramadan', 'galih.ramadan@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa', '2023007', '8C', 'L', '081234567896', 'Cipanas', NULL, NULL, 1, '2025-10-25 11:04:24', '2025-10-25 11:04:24'),
(11, 'Hana Pertiwi', 'hana.pertiwi@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa', '2023008', '8A', 'P', '081234567897', 'Warunggunung', NULL, NULL, 1, '2025-10-25 11:04:24', '2025-10-25 11:04:24'),
(27, 'RIKI KENNEDY', 'rikireza5r@gmail.com', '$2y$10$UhIO.xNWy7a4waowGRiCPOeeDaUJEoLIY3cydP3/G/wR.eL5v9kH6', 'siswa', '212121211', '9A', 'L', '089282928292', 'Jl.melati raya', 'assets/img/uploads/users/foto_27_1763638136.jpg', NULL, 1, '2025-11-17 09:58:31', '2025-11-27 09:49:24'),
(28, 'Aziz calim', NULL, '$2y$10$0hcJxX4nPnul2G8D6mrI5e7x2fwWCCN8a/aVOrpoJOWcatdY97OmC', 'siswa', '212132323', '8C', 'L', '0899999999', 'jalan melati raya', 'assets/img/uploads/users/foto_28_1763731390.png', NULL, 1, '2025-11-17 10:13:01', '2025-11-21 13:23:17'),
(32, 'Abdul dudul', 'abdul@gmail.com', '$2y$10$KIsi6WtOqCWYhAvNiVogUOWaPOhZKBCgSUSp1ZJrX/PXcDnQg/tZS', 'siswa', '25252525', '8A', 'L', '089292929292', 'Jl.Lebak Banten', 'assets/img/uploads/users/foto_32_1764408169.png', NULL, 1, '2025-11-27 09:28:47', '2025-11-29 09:22:49'),
(33, 'Mr.Kennedy', 'kennedy@gmail.com', '$2y$10$zUVs13DBJH6yWqlxk8wnUerQraHK0am/j3kzJQ7zNYswRj3cUcuhu', 'pembina', '', '', '', '', '', NULL, NULL, 1, '2025-11-30 12:38:56', '2025-11-30 12:38:56');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_anggota_aktif`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_anggota_aktif` (
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_jadwal_lengkap`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_jadwal_lengkap` (
`id` int(11)
,`ekstrakurikuler_id` int(11)
,`nama_ekskul` varchar(100)
,`hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')
,`jam_mulai` time
,`jam_selesai` time
,`lokasi` varchar(100)
,`pembina` varchar(100)
,`is_active` tinyint(1)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_anggota_aktif`
--
DROP TABLE IF EXISTS `v_anggota_aktif`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_anggota_aktif`  AS SELECT `u`.`id` AS `user_id`, `u`.`name` AS `name`, `u`.`nis` AS `nis`, `u`.`kelas` AS `kelas`, `u`.`jenis_kelamin` AS `jenis_kelamin`, `u`.`no_hp` AS `no_hp`, `ae`.`id` AS `anggota_id`, `ae`.`ekstrakurikuler_id` AS `ekstrakurikuler_id`, `e`.`nama_ekskul` AS `nama_ekskul`, `ae`.`tanggal_daftar` AS `tanggal_daftar`, `ae`.`status` AS `status`, `ae`.`nilai` AS `nilai`, `ae`.`tanggal_penilaian` AS `tanggal_penilaian`, `ae`.`catatan_pembina` AS `catatan_pembina` FROM ((`users` `u` join `anggota_ekskul` `ae` on(`u`.`id` = `ae`.`user_id`)) join `ekstrakurikulers` `e` on(`ae`.`ekstrakurikuler_id` = `e`.`id`)) WHERE `ae`.`status` = 'diterima' ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_jadwal_lengkap`
--
DROP TABLE IF EXISTS `v_jadwal_lengkap`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_jadwal_lengkap`  AS SELECT `j`.`id` AS `id`, `j`.`ekstrakurikuler_id` AS `ekstrakurikuler_id`, `e`.`nama_ekskul` AS `nama_ekskul`, `j`.`hari` AS `hari`, `j`.`jam_mulai` AS `jam_mulai`, `j`.`jam_selesai` AS `jam_selesai`, `j`.`lokasi` AS `lokasi`, concat(`u`.`name`) AS `pembina`, `j`.`is_active` AS `is_active` FROM ((`jadwal_latihans` `j` join `ekstrakurikulers` `e` on(`j`.`ekstrakurikuler_id` = `e`.`id`)) left join `users` `u` on(`e`.`pembina_id` = `u`.`id`)) WHERE `j`.`is_active` = 1 ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `anggota_ekskul`
--
ALTER TABLE `anggota_ekskul`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_member` (`user_id`,`ekstrakurikuler_id`),
  ADD KEY `idx_anggota_user` (`user_id`),
  ADD KEY `idx_anggota_eskul` (`ekstrakurikuler_id`),
  ADD KEY `idx_anggota_status` (`status`);

--
-- Indeks untuk tabel `berita`
--
ALTER TABLE `berita`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_berita_eskul` (`ekstrakurikuler_id`),
  ADD KEY `idx_berita_published` (`is_published`);

--
-- Indeks untuk tabel `ekstrakurikulers`
--
ALTER TABLE `ekstrakurikulers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pembina_id` (`pembina_id`);

--
-- Indeks untuk tabel `galeris`
--
ALTER TABLE `galeris`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ekstrakurikuler_id` (`ekstrakurikuler_id`),
  ADD KEY `fk_galeris_uploaded_by` (`uploaded_by`);

--
-- Indeks untuk tabel `inventaris`
--
ALTER TABLE `inventaris`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ekstrakurikuler_id` (`ekstrakurikuler_id`);

--
-- Indeks untuk tabel `jadwal_latihans`
--
ALTER TABLE `jadwal_latihans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jadwal_eskul` (`ekstrakurikuler_id`),
  ADD KEY `idx_jadwal_hari` (`hari`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`key_name`),
  ADD UNIQUE KEY `uniq_key_name` (`key_name`);

--
-- Indeks untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ekstrakurikuler_id` (`ekstrakurikuler_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `presensis`
--
ALTER TABLE `presensis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_presensi` (`anggota_id`,`tanggal`),
  ADD KEY `jadwal_id` (`jadwal_id`),
  ADD KEY `idx_presensi_anggota` (`anggota_id`),
  ADD KEY `idx_presensi_tanggal` (`tanggal`),
  ADD KEY `jadwal_latihan_id` (`jadwal_latihan_id`);

--
-- Indeks untuk tabel `prestasis`
--
ALTER TABLE `prestasis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `anggota_id` (`anggota_id`),
  ADD KEY `idx_prestasi_eskul` (`ekstrakurikuler_id`);

--
-- Indeks untuk tabel `sertifikats`
--
ALTER TABLE `sertifikats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_sertifikat` (`nomor_sertifikat`),
  ADD KEY `anggota_id` (`anggota_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `anggota_ekskul`
--
ALTER TABLE `anggota_ekskul`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `berita`
--
ALTER TABLE `berita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `ekstrakurikulers`
--
ALTER TABLE `ekstrakurikulers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `galeris`
--
ALTER TABLE `galeris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `inventaris`
--
ALTER TABLE `inventaris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `jadwal_latihans`
--
ALTER TABLE `jadwal_latihans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `presensis`
--
ALTER TABLE `presensis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `prestasis`
--
ALTER TABLE `prestasis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `sertifikats`
--
ALTER TABLE `sertifikats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `galeris`
--
ALTER TABLE `galeris`
  ADD CONSTRAINT `fk_galeris_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
