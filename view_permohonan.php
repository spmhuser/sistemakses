<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$db = getDB();
$perm = $db->prepare("SELECT p.*, u.username FROM permohonan p JOIN users u ON p.user_id=u.id WHERE p.id=?");
$perm->execute([$id]);
$r = $perm->fetch();
if (!$r) { header('Location: login.php'); exit; }

// Access control
if ($_SESSION['role'] === 'pemohon' && $r['user_id'] != $_SESSION['user_id']) { header('Location: dashboard_pemohon.php'); exit; }

$sistemList = $db->prepare("SELECT * FROM permohonan_sistem WHERE permohonan_id=? ORDER BY bil");
$sistemList->execute([$id]);
$sistems = $sistemList->fetchAll();

$audit = getAuditTrail($id);


$roleNav = match($_SESSION['role']) {
    'pemohon'       => [['href'=>'dashboard_pemohon.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>false]],
    'pengarah_jab'  => [['href'=>'dashboard_pengarah_jab.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>false]],
    'pengarah_jtik' => [['href'=>'dashboard_pengarah_jtik.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>false]],
    'admin_it'      => [['href'=>'dashboard_admin_it.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>false]],
    'penyemak_it'   => [['href'=>'dashboard_penyemak.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>false]],
    default         => [],
};
$roleLabel = match($_SESSION['role']) {
    'pemohon'=>'Pemohon','pengarah_jab'=>'Pengarah Jabatan','pengarah_jtik'=>'Pengarah JTIK','admin_it'=>'Admin IT','penyemak_it'=>'Penyemak IT', default=>''
};
$backUrl = match($_SESSION['role']) {
    'pengarah_jab'=>'dashboard_pengarah_jab.php','pengarah_jtik'=>'dashboard_pengarah_jtik.php','admin_it'=>'dashboard_admin_it.php','penyemak_it'=>'dashboard_penyemak.php',default=>'dashboard_pemohon.php'
};
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Lihat Permohonan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
</head>
<body>
<?php sidebarHTML($_SESSION['nama'] ?? $_SESSION['username'], $roleLabel, $roleNav); ?>
<div class="main-content">
    <div class="page-header">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?=$backUrl?>"><i class="bi bi-house me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item active">Lihat Permohonan</li>
        </ol></nav>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
            <div>
                <h4>Permohonan #<?= htmlspecialchars($r['no_rujukan'] ?? $r['id']) ?></h4>
                <p><?= tujuanLabel($r['tujuan']) ?> &nbsp;|&nbsp; <span class="badge-status <?= statusClass($r['status']) ?>"><?= statusLabel($r['status']) ?></span></p>
            </div>
            <a href="<?=$backUrl?>" class="btn-secondary-soft"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </div>

    <!-- Section A -->
    <div class="view-card">
        <div class="view-card-header"><span style="background:#2C5488;color:#fff;font-size:0.82rem;font-weight:700;padding:3px 9px;border-radius:6px">A</span><h6>Maklumat Kakitangan</h6></div>
        <div class="view-card-body">
            <div class="info-row">
                <div class="info-item"><label>Nama</label><div class="val"><?= htmlspecialchars($r['nama']) ?></div></div>
                <div class="info-item"><label>No. Kakitangan</label><div class="val"><?= htmlspecialchars($r['no_kakitangan']) ?></div></div>
                <div class="info-item"><label>Jawatan</label><div class="val"><?= htmlspecialchars($r['jawatan']) ?></div></div>
                <div class="info-item"><label>Gred Jawatan</label><div class="val"><?= htmlspecialchars($r['gred_jawatan']) ?></div></div>
                <div class="info-item"><label>Jabatan</label><div class="val"><?= htmlspecialchars($r['jabatan']) ?></div></div>
                <div class="info-item"><label>Telefon</label><div class="val"><?= htmlspecialchars($r['telefon']) ?></div></div>
            </div>
        </div>
    </div>

    <!-- Section B -->
    <div class="view-card">
        <div class="view-card-header"><span style="background:#2C5488;color:#fff;font-size:0.82rem;font-weight:700;padding:3px 9px;border-radius:6px">B</span><h6>Maklumat Sistem</h6></div>
        <div class="view-card-body">
            <div class="info-item mb-3"><label>Tujuan Permohonan</label><div class="val"><span class="badge-status badge-info"><?= tujuanLabel($r['tujuan']) ?></span></div></div>
            <?php if (!empty($sistems)): ?>
            <div style="overflow-x:auto">
            <table class="sistem-table">
                <thead><tr><th>Bil</th><th>Nama Sistem</th><th>Peranan</th><th>Had Kuasa</th><th>Catatan</th></tr></thead>
                <tbody>
                <?php foreach($sistems as $s): ?>
                <tr>
                    <td style="text-align:center;color:#6E6470;font-size:0.9rem"><?=$s['bil']?></td>
                    <td><?= htmlspecialchars($s['nama_sistem']) ?></td>
                    <td>
                        <?php if (!empty($s['peranan_sistem'])): ?>
                        <?php $pLabel = perananLabel($s['peranan_sistem']); ?>
                        <span class="badge-status badge-primary"><?= htmlspecialchars($pLabel) ?></span>
                        <?php else: ?>
                        <span style="color:#d1d5db;font-size:0.9rem">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;flex-wrap:wrap;gap:3px">
                        <?php $anyHk = false; foreach(SENARAI_FUNGSI as $f): if ($s[$f] ?? 0): $anyHk = true; ?>
                        <span style="display:inline-block;font-size:0.7rem;padding:2px 6px;border-radius:10px;background:#E6EFFA;color:#2C5488;font-weight:600"><?= fungsiLabel($f) ?></span>
                        <?php endif; endforeach; ?>
                        <?php if (!$anyHk): ?><span style="color:#d1d5db;font-size:0.9rem">—</span><?php endif; ?>
                        </div>
                    </td>
                    <td style="color:#6b7280;font-size:0.92rem"><?= htmlspecialchars($s['catatan'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php else: ?>
            <p style="color:#6E6470;font-size:0.95rem"><i class="bi bi-info-circle me-1"></i>Tiada sistem dipilih (permohonan pembatalan).</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status Timeline -->
    <div class="view-card">
        <div class="view-card-header"><i class="bi bi-activity me-2" style="color:#2C5488"></i><h6>Status Permohonan</h6></div>
        <div class="view-card-body">
            <ul class="timeline">
                <li><div class="tl-dot done"></div><div class="tl-content"><div class="tl-title">Permohonan Dihantar</div><div class="tl-date"><?=$r['created_at']?></div></div></li>
                <?php if ($r['tarikh_pengarah_jab']): ?>
                <li><div class="tl-dot done"></div><div class="tl-content"><div class="tl-title">Disahkan Pengarah Jabatan</div><div class="tl-date"><?=$r['tarikh_pengarah_jab']?></div><?php if($r['nama_pengarah_jab']): ?><div class="tl-note">oleh <?= htmlspecialchars($r['nama_pengarah_jab']) ?></div><?php endif; ?></div></li>
                <?php elseif(in_array($r['status'],['MENUNGGU_PENGARAH_JAB'])): ?>
                <li><div class="tl-dot active"></div><div class="tl-content"><div class="tl-title">Menunggu Pengesahan Pengarah Jabatan</div></div></li>
                <?php endif; ?>
                <?php if ($r['tarikh_jtik']): ?>
                <li><div class="tl-dot <?=$r['kelulusan_jtik']==='DILULUSKAN'?'done':'tl-dot-danger'?>"></div>
                    <div class="tl-content">
                        <div class="tl-title"><?=$r['kelulusan_jtik']==='DILULUSKAN'?'Diluluskan Pengarah JTIK':'Tidak Diluluskan'?></div>
                        <div class="tl-date"><?=$r['tarikh_jtik']?></div>
                        <?php if($r['alasan_jtik']): ?><div class="tl-note">Alasan: <?= htmlspecialchars($r['alasan_jtik']) ?></div><?php endif; ?>
                    </div>
                </li>
                <?php elseif($r['status']==='MENUNGGU_JTIK'): ?>
                <li><div class="tl-dot active"></div><div class="tl-content"><div class="tl-title">Menunggu Kelulusan Pengarah JTIK</div></div></li>
                <?php endif; ?>
                <?php if ($r['tarikh_it']): ?>
                <li><div class="tl-dot done"></div><div class="tl-content"><div class="tl-title">Akses Telah Diberikan oleh Admin IT</div><div class="tl-date"><?=$r['tarikh_it']?></div><?php if($r['it_pemberi_nama'] && $_SESSION['role'] !== 'pemohon'): ?><div class="tl-note">Pemberi Akses: <?= htmlspecialchars($r['it_pemberi_nama']) ?></div><?php endif; ?></div></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Section E - Perakuan Pengarah Jab -->
    <?php if ($r['tarikh_pengarah_jab']): ?>
    <div class="view-card">
        <div class="view-card-header"><span style="background:#2C5488;color:#fff;font-size:0.82rem;font-weight:700;padding:3px 9px;border-radius:6px">E</span><h6>Perakuan Pengarah Jabatan</h6></div>
        <div class="view-card-body">
            <div class="info-row">
                <div class="info-item"><label>Nama Pengarah</label><div class="val"><?= htmlspecialchars($r['nama_pengarah_jab']) ?></div></div>
                <div class="info-item"><label>Tarikh</label><div class="val"><?= $r['tarikh_pengarah_jab'] ?></div></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section F - Kelulusan JTIK -->
    <?php if ($r['tarikh_jtik']): ?>
    <div class="view-card">
        <div class="view-card-header"><span style="background:#2C5488;color:#fff;font-size:0.82rem;font-weight:700;padding:3px 9px;border-radius:6px">F</span><h6>Kelulusan Pengarah JTIK</h6></div>
        <div class="view-card-body">
            <div class="info-row">
                <div class="info-item"><label>Keputusan</label><div class="val"><span class="badge-status <?=$r['kelulusan_jtik']==='DILULUSKAN'?'badge-success':'badge-danger'?>"><?=$r['kelulusan_jtik']?></span></div></div>
                <div class="info-item"><label>Tarikh</label><div class="val"><?=$r['tarikh_jtik']?></div></div>
                <?php if($r['alasan_jtik']): ?><div class="info-item" style="grid-column:1/-1"><label>Alasan</label><div class="val"><?= htmlspecialchars($r['alasan_jtik']) ?></div></div><?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section G - IT -->
    <?php if ($r['tarikh_it']): ?>
    <?php if ($_SESSION['role'] === 'pemohon'): ?>
    <!-- Pemohon hanya nampak pengesahan akses diberikan. Penyemakan IT adalah proses dalaman. -->
    <div class="view-card">
        <div class="view-card-header"><span style="background:#2C5488;color:#fff;font-size:0.82rem;font-weight:700;padding:3px 9px;border-radius:6px">G</span><h6>Status Akses</h6></div>
        <div class="view-card-body">
            <div style="display:flex;align-items:center;gap:14px;background:#E9F7EF;border:1px solid #BFE6CD;border-radius:12px;padding:16px 18px">
                <div style="width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,#2E9E5B,#23C16B);color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0"><i class="bi bi-check-lg"></i></div>
                <div>
                    <div style="font-weight:700;color:#1E3A5F">Akses telah diberikan oleh Admin IT</div>
                    <div style="color:#5B6675;font-size:0.9rem">Tarikh: <?=$r['tarikh_it']?></div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Paparan penuh (termasuk penyemak) hanya untuk peranan dalaman: Admin IT / Pengarah JTIK / Pengarah Jabatan -->
    <div class="view-card">
        <div class="view-card-header"><span style="background:#2C5488;color:#fff;font-size:0.82rem;font-weight:700;padding:3px 9px;border-radius:6px">G</span><h6>Kegunaan IT</h6></div>
        <div class="view-card-body">
            <div class="info-row">
                <div class="info-item"><label>Pemberi Akses</label><div class="val"><?= htmlspecialchars($r['it_pemberi_nama']) ?></div></div>
                <div class="info-item"><label>Cop Jawatan (Pemberi)</label><div class="val"><?= htmlspecialchars($r['it_pemberi_cop'] ?: '-') ?></div></div>
                <div class="info-item"><label>Tarikh Akses</label><div class="val"><?=$r['tarikh_it']?></div></div>
                <div class="info-item"><label>Penyemak IT</label><div class="val"><?= $r['it_penyemak_nama'] ? htmlspecialchars($r['it_penyemak_nama']) : '<span class="badge-status badge-warning">Belum disemak</span>' ?></div></div>
                <?php if (!empty($r['it_penyemak_nama'])): ?>
                <div class="info-item"><label>Cop Jawatan (Penyemak)</label><div class="val"><?= htmlspecialchars($r['it_penyemak_cop'] ?: '-') ?></div></div>
                <div class="info-item"><label>Tarikh Semakan</label><div class="val"><?= $r['tarikh_semakan'] ?? '-' ?></div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'pengarah_jab' && $r['status'] === 'MENUNGGU_PENGARAH_JAB'): ?>
    <div style="margin-top:8px"><a href="tindakan_pengarah_jab.php?id=<?=$r['id']?>" class="btn-primary-dark"><i class="bi bi-pen"></i> Buat Perakuan</a></div>
    <?php endif; ?>
    <?php if ($_SESSION['role'] === 'pengarah_jtik' && $r['status'] === 'MENUNGGU_JTIK'): ?>
    <div style="margin-top:8px"><a href="tindakan_jtik.php?id=<?=$r['id']?>" class="btn-primary-dark"><i class="bi bi-check2-square"></i> Buat Kelulusan</a></div>
    <?php endif; ?>
    <?php if (in_array($_SESSION['role'], ['admin_it','penyemak_it']) && $r['status'] === 'DILULUSKAN'): ?>
    <div style="margin-top:8px"><a href="tindakan_it.php?id=<?=$r['id']?>" class="btn-primary-dark"><i class="bi bi-key"></i> Berikan Akses</a></div>
    <?php endif; ?>
    <?php if ($_SESSION['role'] === 'penyemak_it' && $r['status'] === 'AKSES_DIBERIKAN' && empty($r['it_penyemak_nama'])): ?>
    <div style="margin-top:8px"><a href="tindakan_semakan.php?id=<?=$r['id']?>" class="btn-primary-dark"><i class="bi bi-patch-check"></i> Sahkan Semakan</a></div>
    <?php endif; ?>

    <!-- AUDIT TRAIL — jejak dalaman untuk pegawai sahaja; disembunyikan daripada pemohon -->
    <?php if ($_SESSION['role'] !== 'pemohon'): ?>
    <div class="view-card" style="margin-top:20px">
        <div class="view-card-header"><span style="background:#2C5488;color:#fff;font-size:0.82rem;font-weight:700;padding:3px 9px;border-radius:6px"><i class="bi bi-clock-history"></i></span><h6>Jejak Audit (Audit Trail)</h6></div>
        <div class="view-card-body" style="padding:0">
            <?php if (!$audit): ?>
                <div class="empty-state" style="padding:34px"><i class="bi bi-clock-history"></i>Tiada rekod audit lagi.</div>
            <?php else: ?>
            <div style="overflow-x:auto">
            <table class="data-table">
                <thead><tr><th style="padding-left:24px">Tindakan</th><th>Oleh</th><th>No. Pekerja</th><th>Catatan</th><th>Tarikh &amp; Masa</th></tr></thead>
                <tbody>
                <?php foreach ($audit as $a): ?>
                    <tr>
                        <td style="padding-left:24px"><span class="badge-status badge-primary"><?= htmlspecialchars($a['action']) ?></span></td>
                        <td style="font-weight:600;color:#234B7A"><?= htmlspecialchars($a['processed_name'] ?: '-') ?></td>
                        <td style="color:#2862C0;font-weight:600"><?= htmlspecialchars($a['processed_by'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($a['catatan'] ?: '-') ?></td>
                        <td style="color:#6E6470;font-size:0.9rem"><?= htmlspecialchars($a['processed_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; /* sembunyi audit trail dari pemohon */ ?>
</div>
</body></html>
