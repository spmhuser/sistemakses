<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('admin_it');

$id = (int)($_GET['id'] ?? 0);
$db = getDB();
$stmt = $db->prepare("SELECT * FROM permohonan WHERE id=? AND status='DILULUSKAN'");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) { header('Location: dashboard_admin_it.php'); exit; }

// Multi Admin IT: pastikan permohonan ini mengandungi sekurang-kurangnya satu sistem milik admin
$meRow = $db->prepare("SELECT no_kakitangan FROM users WHERE id=?");
$meRow->execute([$_SESSION['user_id']]);
$noPek = ($meRow->fetch())['no_kakitangan'] ?? '';
$mySys = getSistemForAdmin($noPek);
if ($mySys) {
    $ph  = implode(',', array_fill(0, count($mySys), '?'));
    $chk = $db->prepare("SELECT COUNT(*) c FROM permohonan_sistem WHERE permohonan_id=? AND bil IN ($ph)");
    $chk->execute(array_merge([$id], $mySys));
    if ((int)$chk->fetch()['c'] === 0) { header('Location: dashboard_admin_it.php'); exit; }
}

// Pemberi akses = admin IT yang log masuk. Tarik nama & jawatan (cop) automatik dari Sistem Gaji.
$mg = $db->prepare("SELECT nama, jawatan FROM gaji WHERE no_kakitangan = ?");
$mg->execute([$noPek]);
$myGaji      = $mg->fetch() ?: [];
$pemberiNama = $myGaji['nama']    ?? ($_SESSION['nama'] ?? '');
$pemberiCop  = $myGaji['jawatan'] ?? '';

// Senarai penyemak aktif (auto-tarik dari tetapan_penyemak.php)
$penyemakLs = getPenyemakList(true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pemberi sentiasa diambil dari rekod gaji admin (autoritatif), bukan dari input
    $pemberi_nama  = $pemberiNama;
    $pemberi_cop   = $pemberiCop;
    // Penyemak: ambil dari penyemak terpilih (tetapan_penyemak)
    $penyemak_nama = '';
    $penyemak_cop  = '';
    $penyemak_id   = (int)($_POST['penyemak_id'] ?? 0);
    if ($penyemak_id) {
        $pn = $db->prepare("SELECT nama, jawatan FROM penyemak WHERE id = ? AND status = 1");
        $pn->execute([$penyemak_id]);
        if ($prow = $pn->fetch()) { $penyemak_nama = $prow['nama']; $penyemak_cop = $prow['jawatan']; }
    }
    if ($pemberi_nama && $penyemak_nama) {
        $u = $db->prepare("UPDATE permohonan SET status='AKSES_DIBERIKAN',it_pemberi_nama=?,it_pemberi_cop=?,it_penyemak_nama=?,it_penyemak_cop=?,tarikh_it=datetime('now','+8 hours') WHERE id=?");
        $u->execute([$pemberi_nama,$pemberi_cop,$penyemak_nama,$penyemak_cop,$id]);
        logAudit($id, 'AKSES_DIBERIKAN', "Pemberi: {$pemberi_nama}; Penyemak: {$penyemak_nama}");
        header('Location: dashboard_admin_it.php?success=1'); exit;
    }
}

$sistems = $db->prepare("SELECT * FROM permohonan_sistem WHERE permohonan_id=? ORDER BY bil");
$sistems->execute([$id]);
$sistemList = $sistems->fetchAll();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pemberian Akses IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
</head>
<body>
<?php sidebarHTML($_SESSION['nama']??$_SESSION['username'],'Admin IT',[
    ['href'=>'dashboard_admin_it.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>false],
]); ?>
<div class="main-content">
    <div class="page-header">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard_admin_it.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Pemberian Akses</li>
        </ol></nav>
        <h4>Pemberian Akses Sistem</h4>
        <p><?= htmlspecialchars($r['no_rujukan']??'') ?> &nbsp;|&nbsp; <?= htmlspecialchars($r['nama']) ?></p>
    </div>

    <div class="view-card mb-4">
        <div class="view-card-header"><h6>Maklumat Permohonan</h6></div>
        <div class="view-card-body">
            <div class="info-row">
                <div class="info-item"><label>Nama Pemohon</label><div class="val"><?= htmlspecialchars($r['nama']) ?></div></div>
                <div class="info-item"><label>Jabatan</label><div class="val"><?= htmlspecialchars($r['jabatan']) ?></div></div>
                <div class="info-item"><label>Tujuan</label><div class="val"><span class="badge-status badge-info"><?= tujuanLabel($r['tujuan']) ?></span></div></div>
                <div class="info-item"><label>Diluluskan JTIK</label><div class="val"><?=$r['tarikh_jtik']??'-'?></div></div>
            </div>
            <?php if(!empty($sistemList)): ?>
            <div style="margin-top:14px;overflow-x:auto">
                <label style="font-size:0.85rem;font-weight:600;text-transform:uppercase;color:#6E6470;display:block;margin-bottom:8px">Sistem Yang Perlu Diberikan Akses</label>
                <table class="sistem-table" style="font-size:0.9rem">
                    <thead><tr><th>Nama Sistem</th><th>Peranan</th><th>Had Kuasa</th><th>Catatan</th></tr></thead>
                    <tbody>
                    <?php foreach($sistemList as $s): ?>
                    <tr>
                        <td style="font-weight:600;color:#2C5488"><?= htmlspecialchars($s['nama_sistem']) ?></td>
                        <td>
                            <?php if(!empty($s['peranan_sistem'])): ?>
                            <span class="badge-status badge-primary" style="font-size:0.82rem"><?= htmlspecialchars(SENARAI_PERANAN[$s['peranan_sistem']] ?? strtoupper($s['peranan_sistem'])) ?></span>
                            <?php else: ?><span style="color:#d1d5db">—</span><?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;flex-wrap:wrap;gap:3px">
                            <?php $anyHk=false; foreach(SENARAI_FUNGSI as $f): if($s[$f]??0): $anyHk=true; ?>
                            <span style="display:inline-block;font-size:0.7rem;padding:1px 6px;border-radius:10px;background:#E6EFFA;color:#2C5488;font-weight:600"><?= fungsiLabel($f) ?></span>
                            <?php endif; endforeach; ?>
                            <?php if(!$anyHk): ?><span style="color:#d1d5db;font-size:0.9rem">—</span><?php endif; ?>
                            </div>
                        </td>
                        <td style="color:#6b7280"><?= htmlspecialchars($s['catatan']??'-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-card">
        <div class="form-section-header">
            <span class="sec-label">G</span>
            <span class="sec-title">Kegunaan IT – Pemberian Akses</span>
        </div>
        <div class="form-section-body">
            <form method="POST">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div style="border:1px solid #e5e7eb;border-radius:12px;padding:20px">
                            <div style="font-size:0.95rem;font-weight:700;color:#2C5488;margin-bottom:16px"><i class="bi bi-person-check me-2"></i>Pemberi Akses</div>
                            <div class="locked-note" style="font-size:0.82rem;color:#2862C0;background:#E6EFFA;border:1px solid #BFD2EC;border-radius:8px;padding:8px 12px;display:flex;align-items:center;gap:8px;margin-bottom:14px;font-weight:600"><i class="bi bi-shield-lock"></i> Diambil automatik dari rekod anda (Sistem Gaji).</div>
                            <div class="mb-3">
                                <label class="field-label">Nama</label>
                                <input type="text" class="form-control-custom" readonly value="<?= htmlspecialchars($pemberiNama) ?>" style="background:#EEF3FA;cursor:not-allowed">
                            </div>
                            <div class="mb-2">
                                <label class="field-label">Cop Jawatan</label>
                                <input type="text" class="form-control-custom" readonly value="<?= htmlspecialchars($pemberiCop ?: '-') ?>" style="background:#EEF3FA;cursor:not-allowed">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="border:1px solid #e5e7eb;border-radius:12px;padding:20px">
                            <div style="font-size:0.95rem;font-weight:700;color:#2C5488;margin-bottom:16px"><i class="bi bi-person-badge me-2"></i>Penyemak</div>
                            <?php if ($penyemakLs): ?>
                            <div class="mb-3">
                                <label class="field-label">Pilih Penyemak <span class="req">*</span></label>
                                <select name="penyemak_id" id="penyemak_id" class="form-control-custom" required onchange="showCop()">
                                    <option value="" data-cop="">— Pilih Penyemak —</option>
                                    <?php foreach ($penyemakLs as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>" data-cop="<?= htmlspecialchars($p['jawatan'] ?? '', ENT_QUOTES) ?>"<?= count($penyemakLs)===1?' selected':'' ?>><?= htmlspecialchars($p['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="field-label">Cop Jawatan</label>
                                <input type="text" id="penyemak_cop_view" class="form-control-custom" readonly value="<?= htmlspecialchars(count($penyemakLs)===1 ? ($penyemakLs[0]['jawatan'] ?? '') : '') ?>" style="background:#EEF3FA;cursor:not-allowed" placeholder="Auto ikut penyemak">
                            </div>
                            <?php else: ?>
                            <div style="font-size:0.9rem;color:#92580B;background:#FFF3D2;border:1px solid #F0D79A;border-radius:8px;padding:12px 14px">
                                <i class="bi bi-exclamation-triangle me-1"></i> Tiada penyemak aktif.
                                Sila tambah di <a href="tetapan_penyemak.php" style="color:#92580B;font-weight:700;text-decoration:underline">Tetapan Penyemak</a> dahulu.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div style="margin-top:14px;font-size:0.92rem;color:#6b7280">
                    Tarikh pemberian akses: <strong><?= date('d/m/Y') ?></strong>
                </div>
        </div>
        <div class="action-row">
                <button type="submit" class="btn-primary-dark" <?= $penyemakLs ? '' : 'disabled style="opacity:.55;cursor:not-allowed"' ?>><i class="bi bi-key"></i> Sahkan Pemberian Akses</button>
                <a href="dashboard_admin_it.php" class="btn-secondary-soft">Batal</a>
        </div>
            </form>
            <script>
            function showCop(){
                var s = document.getElementById('penyemak_id');
                if(!s) return;
                var cop = s.options[s.selectedIndex].getAttribute('data-cop') || '';
                document.getElementById('penyemak_cop_view').value = cop;
            }
            </script>
    </div>
</div>
</body></html>
