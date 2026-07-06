<?php
/**
 * ============================================================================
 *  TETAPAN SAMBUNGAN PANGKALAN DATA (DSN)
 * ============================================================================
 *  Fail ini diasingkan supaya mudah ditukar antara SQLite (pembangunan/ujian)
 *  dan Oracle (pengeluaran) TANPA menyentuh logik aplikasi.
 *
 *  Fail ini di-include secara automatik oleh config.php — dan config.php pula
 *  di-include di semua page (terus atau melalui auth.php). Jadi cukup edit
 *  fail ini sahaja untuk menukar sambungan.
 * ============================================================================
 */

// Pilih pemacu pangkalan data: 'sqlite'  atau  'oracle'
define('DB_DRIVER', 'sqlite');

/* ---------------------------------------------------------------------------
 *  SQLite (pembangunan / ujian)
 * ------------------------------------------------------------------------- */
define('DB_SQLITE_PATH', __DIR__ . '/database.db');

/* ---------------------------------------------------------------------------
 *  Oracle (pengeluaran) — tukar mengikut persekitaran sebenar anda
 *  Format DSN: oci:dbname=//<host>:<port>/<service_name>;charset=AL32UTF8
 *  (Pastikan sambungan PDO_OCI / OCI8 dipasang pada server PHP)
 * ------------------------------------------------------------------------- */
define('DB_ORACLE_DSN',  'oci:dbname=//127.0.0.1:1521/XEPDB1;charset=AL32UTF8');
define('DB_ORACLE_USER', 'capaian');
define('DB_ORACLE_PASS', 'kata_laluan_anda');

/**
 * Bina dan pulangkan objek PDO mengikut DB_DRIVER yang dipilih di atas.
 */
function dbConnect() {
    switch (DB_DRIVER) {
        case 'oracle':
            $pdo = new PDO(DB_ORACLE_DSN, DB_ORACLE_USER, DB_ORACLE_PASS);
            break;

        case 'sqlite':
        default:
            $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
            break;
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if (DB_DRIVER === 'sqlite') {
        // Elak "hang" 120s bila DB dikunci program lain (cth: DB Browser dibuka).
        // busy_timeout: tunggu maksimum 5 saat sahaja, kemudian gagal cepat.
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $pdo->exec('PRAGMA foreign_keys = ON');
        // WAL: benarkan pembaca (cth DB Browser) tanpa menyekat penulis.
        // Dibalut try/catch — jika DB sedang dikunci, biar mod lalai digunakan.
        try { $pdo->exec('PRAGMA journal_mode = WAL'); } catch (Throwable $e) { /* abai */ }
    }
    return $pdo;
}
