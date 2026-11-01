-- ============================================================================
-- Migrasi: pilihan lama angsuran 20 Bulan dan 80 Minggu
--
-- Jalankan SEKALI di database yang belum punya kolom tbl_pinjaman_h.satuan
-- (mis. database produksi dari kopera21_gremen.sql). BACKUP DULU.
--
-- Kenapa perlu kolom satuan: "20" sudah dipakai oleh 20 Minggu, jadi angka
-- lama_angsuran saja tidak cukup untuk membedakan 20 Bulan dan 20 Minggu.
-- ============================================================================

-- 1. Kolom satuan pada pinjaman ("Bulan" / "Minggu")
ALTER TABLE `tbl_pinjaman_h`
  ADD COLUMN `satuan` VARCHAR(10) NULL DEFAULT NULL AFTER `lama_angsuran`;

-- 2. Isi satuan pinjaman lama dengan aturan yang selama ini dipakai aplikasi:
--    5 / 10 / 15 = Bulan, selain itu Minggu
UPDATE `tbl_pinjaman_h`
   SET `satuan` = IF(`lama_angsuran` IN (5, 10, 15), 'Bulan', 'Minggu')
 WHERE `satuan` IS NULL OR `satuan` = '';

-- 3. Pilihan baru di menu Tambah Pinjaman (tabel jns_angsuran tidak punya
--    AUTO_INCREMENT, jadi id diambil dari MAX(id) + 1). Aman dijalankan ulang.
INSERT INTO `jns_angsuran` (`id`, `ket`, `satuan`, `aktif`)
SELECT (SELECT COALESCE(MAX(`id`), 0) + 1 FROM `jns_angsuran` AS t), 20, 'Bulan', 'Y'
  FROM DUAL
 WHERE NOT EXISTS (SELECT 1 FROM `jns_angsuran` WHERE `ket` = 20 AND `satuan` = 'Bulan');

INSERT INTO `jns_angsuran` (`id`, `ket`, `satuan`, `aktif`)
SELECT (SELECT COALESCE(MAX(`id`), 0) + 1 FROM `jns_angsuran` AS t), 80, 'Minggu', 'Y'
  FROM DUAL
 WHERE NOT EXISTS (SELECT 1 FROM `jns_angsuran` WHERE `ket` = 80 AND `satuan` = 'Minggu');

-- 4. View v_hitung_pinjaman: tambah kolom satuan, dan tanggal tempo mengikuti
--    satuan (pinjaman mingguan = + N minggu, bulanan = + N bulan).
--    Definisi lainnya sama dengan view di kopera21_gremen.sql.
CREATE OR REPLACE VIEW `v_hitung_pinjaman` AS
SELECT
  `tbl_pinjaman_h`.`id` AS `id`,
  `tbl_pinjaman_h`.`tgl_pinjam` AS `tgl_pinjam`,
  `tbl_pinjaman_h`.`anggota_id` AS `anggota_id`,
  `tbl_pinjaman_h`.`lama_angsuran` AS `lama_angsuran`,
  `tbl_pinjaman_h`.`satuan` AS `satuan`,
  `tbl_pinjaman_h`.`jumlah` AS `jumlah`,
  `tbl_pinjaman_h`.`bunga` AS `bunga`,
  `tbl_pinjaman_h`.`biaya_adm` AS `biaya_adm`,
  `tbl_pinjaman_h`.`lunas` AS `lunas`,
  `tbl_pinjaman_h`.`dk` AS `dk`,
  `tbl_pinjaman_h`.`kas_id` AS `kas_id`,
  `tbl_pinjaman_h`.`user_name` AS `user_name`,
  `tbl_pinjaman_h`.`jumlah` / `tbl_pinjaman_h`.`lama_angsuran` AS `pokok_angsuran`,
  `tbl_pinjaman_h`.`jumlah` / `tbl_pinjaman_h`.`lama_angsuran` * `tbl_pinjaman_h`.`bunga` / 100 AS `bunga_pinjaman`,
  `tbl_pinjaman_h`.`jumlah` * `tbl_pinjaman_h`.`bunga` / 100 AS `total_bunga_pinjaman`,
  (`tbl_pinjaman_h`.`jumlah` / `tbl_pinjaman_h`.`lama_angsuran` * `tbl_pinjaman_h`.`bunga` / 100 + `tbl_pinjaman_h`.`jumlah` / `tbl_pinjaman_h`.`lama_angsuran` + `tbl_pinjaman_h`.`biaya_adm`) * 100 / 100 AS `ags_per_bulan`,
  IF(`tbl_pinjaman_h`.`satuan` = 'Minggu',
     `tbl_pinjaman_h`.`tgl_pinjam` + INTERVAL `tbl_pinjaman_h`.`lama_angsuran` WEEK,
     `tbl_pinjaman_h`.`tgl_pinjam` + INTERVAL `tbl_pinjaman_h`.`lama_angsuran` MONTH) AS `tempo`,
  (`tbl_pinjaman_h`.`jumlah` / `tbl_pinjaman_h`.`lama_angsuran` * `tbl_pinjaman_h`.`bunga` / 100 + `tbl_pinjaman_h`.`jumlah` / `tbl_pinjaman_h`.`lama_angsuran` + `tbl_pinjaman_h`.`biaya_adm`) * 100 / 100 * `tbl_pinjaman_h`.`lama_angsuran` AS `tagihan`,
  `tbl_pinjaman_h`.`keterangan` AS `keterangan`,
  `tbl_pinjaman_h`.`barang_id` AS `barang_id`,
  IFNULL(MAX(`tbl_pinjaman_d`.`angsuran_ke`), 0) AS `bln_sudah_angsur`
FROM (`tbl_pinjaman_h` LEFT JOIN `tbl_pinjaman_d` ON (`tbl_pinjaman_h`.`id` = `tbl_pinjaman_d`.`pinjam_id`))
GROUP BY `tbl_pinjaman_h`.`id`;
