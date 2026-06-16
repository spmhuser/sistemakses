-- Sistem Akses - MySQL/MariaDB Import
-- JANGAN import database.db (SQLite) terus ke phpMyAdmin.
-- Atau buka http://localhost/sistemakses/setup.php untuk setup automatik.

CREATE DATABASE IF NOT EXISTS `sistemakses` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sistemakses`;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `permohonan_sistem`;
DROP TABLE IF EXISTS `permohonan`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `username`      VARCHAR(100) UNIQUE NOT NULL,
    `password`      VARCHAR(255) NOT NULL,
    `role`          VARCHAR(50) NOT NULL,
    `nama`          VARCHAR(255),
    `no_kakitangan` VARCHAR(50),
    `jawatan`       VARCHAR(255),
    `gred_jawatan`  VARCHAR(50),
    `jabatan`       VARCHAR(255),
    `telefon`       VARCHAR(50),
    `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `permohonan` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `no_rujukan`            VARCHAR(50) UNIQUE,
    `user_id`               INT NOT NULL,
    `nama`                  VARCHAR(255) NOT NULL,
    `no_kakitangan`         VARCHAR(50) NOT NULL,
    `jawatan`               VARCHAR(255) NOT NULL,
    `gred_jawatan`          VARCHAR(50) NOT NULL,
    `jabatan`               VARCHAR(255) NOT NULL,
    `telefon`               VARCHAR(50) NOT NULL,
    `tujuan`                VARCHAR(50) NOT NULL,
    `status`                VARCHAR(50) NOT NULL DEFAULT 'MENUNGGU_PENGARAH_JAB',
    `tarikh_pemohon`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `pengarah_jab_id`       INT,
    `nama_pengarah_jab`     VARCHAR(255),
    `tarikh_pengarah_jab`   DATETIME,
    `pengarah_jtik_id`      INT,
    `kelulusan_jtik`        VARCHAR(50),
    `alasan_jtik`           TEXT,
    `tarikh_jtik`           DATETIME,
    `it_pemberi_nama`       VARCHAR(255),
    `it_pemberi_cop`        VARCHAR(255),
    `it_penyemak_nama`      VARCHAR(255),
    `it_penyemak_cop`       VARCHAR(255),
    `tarikh_it`             DATETIME,
    `created_at`            DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `permohonan_sistem` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `permohonan_id`  INT NOT NULL,
    `bil`            INT NOT NULL,
    `nama_sistem`    VARCHAR(255) NOT NULL,
    `catatan`        TEXT,
    `peranan_sistem` VARCHAR(50) DEFAULT '',
    `penyedia`       TINYINT(1) DEFAULT 0,
    `pengemaskini`   TINYINT(1) DEFAULT 0,
    `penyemak`       TINYINT(1) DEFAULT 0,
    `pelapor`        TINYINT(1) DEFAULT 0,
    `pengesah`       TINYINT(1) DEFAULT 0,
    `pelulus`        TINYINT(1) DEFAULT 0,
    `penghapus`      TINYINT(1) DEFAULT 0,
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` VALUES
(1,'pemohon1','$2y$10$s.WWP/U/cXKL1yHTboc0yO3f4F3U9tQkiCdT/px0Yur4WvrICF3tC','pemohon','Ahmad Fadzil bin Ismail','MB001234','Pegawai Tadbir','N41','Jabatan Perbendaharaan','04-5399000','2026-06-16 11:51:31'),
(2,'pemohon2','$2y$10$qjIsJvFP3zb1flSo3jt6ROByvjjpxqHGYyoM8tRhPQfV5M3viZ0U6','pemohon','Siti Norzahra binti Rashid','MB001235','Pembantu Tadbir','N19','Jabatan Kejuruteraan','04-5399001','2026-06-16 11:51:31'),
(3,'pengarah_jab','$2y$10$kNTQ3U8e33HZOilGYpu3RebXeW9UVrNXZ1Sedztmi5yrmGMgzacEm','pengarah_jab','Mohd Azhar bin Abdul Karim','MB000100','Pengarah Jabatan','JUSA C','Jabatan Perbendaharaan','04-5399010','2026-06-16 11:51:31'),
(4,'pengarah_jtik','$2y$10$C.gzvm42XRMxtIxVX6Hpre7d5yq3nYTKuLzGrGq6j9nUbKzmHChT2','pengarah_jtik','Abdul Fikri Ridzauudin b Abdullah','MB000050','Pengarah JTIK','JUSA C','JTIK','04-5399020','2026-06-16 11:51:31'),
(5,'admin_it','$2y$10$YbIkUXW/T/zpHvO3x0mRe..KwGB4c68GWNqiDz1giyUXtWM2Upo9u','admin_it','Razif bin Hamdan','MB000200','Pegawai Teknologi Maklumat','F41','JTIK','04-5399030','2026-06-16 11:51:31'),
(6,'admin','$2y$10$xlWUP1qViU2HjagXaf/yduhPlDz/Fhyhk0qY/drCMO3F99yeoVnG.','admin','Administrator Sistem','MB000001','Administrator','F44','JTIK','04-5399000',NOW());

INSERT INTO `permohonan` VALUES
(1,'BCS-20260616-2527',1,'Ahmad Fadzil bin Ismail','MB001234','Pegawai Tadbir','N41','Jabatan Perbendaharaan','04-5399000','baru','MENUNGGU_PENGARAH_JAB','2026-06-16 12:04:25',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-16 12:04:25'),
(2,'BCS-20260616-0853',1,'Ahmad Fadzil bin Ismail','MB001234','Pegawai Tadbir','N41','Jabatan Perbendaharaan','04-5399000','baru','MENUNGGU_PENGARAH_JAB','2026-06-16 12:04:38',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-16 12:04:38'),
(3,'BCS-20260616-0098',1,'Ahmad Fadzil bin Ismail','MB001234','Pegawai Tadbir','N41','Jabatan Perbendaharaan','04-5399000','pembatalan','MENUNGGU_JTIK','2026-06-16 12:17:46',3,'Mohd Azhar bin Abdul Karim','2026-06-16 12:18:13',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-16 12:17:46');

INSERT INTO `permohonan_sistem` VALUES
(1,1,1,'Sistem Kehadiran','','pic_jabatan',1,1,1,1,0,0,0),
(2,1,2,'Sistem Cuti','','admin_sistem',1,1,1,1,0,0,1),
(3,2,9,'Sistem Speedbiz','','pengguna_jab',1,1,0,1,0,0,0),
(4,2,10,'Sistem eSurat','','admin_sistem',1,1,1,1,0,0,1),
(5,3,1,'Sistem Kehadiran','','pic_jabatan',1,1,1,1,0,0,0),
(6,3,2,'Sistem Cuti','','pic_jabatan',1,1,1,1,0,0,0),
(7,3,3,'Sistem Pengurusan OT','','pengguna_jab',1,1,0,1,0,0,0);

SET FOREIGN_KEY_CHECKS = 1;
