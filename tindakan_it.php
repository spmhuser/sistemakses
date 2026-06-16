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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pemberi_nama   = trim($_POST['pemberi_nama']   ?? '');
    $pemberi_cop    = trim($_POST['pemberi_cop']    ?? '');
    $penyemak_nama  = trim($_POST['penyemak_nama']  ?? '');
    $penyemak_cop   = trim($_POST['penyemak_cop']   ?? '');
    if ($pemberi_nama && $penyemak_nama) {
        $u = $db->prepare("UPDATE permohonan SET status='AKSES_DIBERIKAN',it_pemberi_nama=?,it_pemberi_cop=?,it_penyemak_nama=?,it_penyemak_cop=?,tarikh_it=datetime('now','+8 hours') WHERE id=?");
        $u->execute([$pemberi_nama,$pemberi_cop,$penyemak_nama,$penyemak_cop,$id]);
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
    <title>Pemberian Akses IT – Sistem Capaian Sistem</title>
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
                <label style="font-size:0.75rem;font-weight:600;text-transform:uppercase;color:#9ca3af;display:block;margin-bottom:8px">Sistem Yang Perlu Diberikan Akses</label>
                <table class="sistem-table" style="font-size:0.8rem">
                    <thead><tr><th>Nama Sistem</th><th>Peranan</th><th>Had Kuasa</th><th>Catatan</th></tr></thead>
                    <tbody>
                    <?php foreach($sistemList as $s): ?>
                    <tr>
                        <td style="font-weight:600;color:#003087"><?= htmlspecialchars($s['nama_sistem']) ?></td>
                        <td>
                            <?php if(!empty($s['peranan_sistem'])): ?>
                            <span class="badge-status badge-primary" style="font-size:0.72rem"><?= htmlspecialchars(SENARAI_PERANAN[$s['peranan_sistem']] ?? strtoupper($s['peranan_sistem'])) ?></span>
                            <?php else: ?><span style="color:#d1d5db">—</span><?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;flex-wrap:wrap;gap:3px">
                            <?php $anyHk=false; foreach(SENARAI_FUNGSI as $f): if($s[$f]??0): $anyHk=true; ?>
                            <span style="display:inline-block;font-size:0.7rem;padding:1px 6px;border-radius:10px;background:#e8f0fe;color:#003087;font-weight:600"><?= fungsiLabel($f) ?></span>
                            <?php endif; endforeach; ?>
                            <?php if(!$anyHk): ?><span style="color:#d1d5db;font-size:0.8rem">—</span><?php endif; ?>
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
                            <div style="font-size:0.85rem;font-weight:700;color:#003087;margin-bottom:16px"><i class="bi bi-person-check me-2"></i>Pemberi Akses</div>
                            <div class="mb-3">
                                <label class="field-label">Nama <span class="req">*</span></label>
                                <input type="text" name="pemberi_nama" class="form-control-custom" required value="<?= htmlspecialchars($_SESSION['nama']??'') ?>">
                            </div>
                            <div class="mb-2">
                                <label class="field-label">Cop Jawatan</label>
                                <input type="text" name="pemberi_cop" class="form-control-custom" placeholder="Cth: Pegawai Teknologi Maklumat">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="border:1px solid #e5e7eb;border-radius:12px;padding:20px">
                            <div style="font-size:0.85rem;font-weight:700;color:#003087;margin-bottom:16px"><i class="bi bi-person-badge me-2"></i>Penyemak</div>
                            <div class="mb-3">
                                <label class="field-label">Nama <span class="req">*</span></label>
                                <input type="text" name="penyemak_nama" class="form-control-custom" required placeholder="Nama penyemak">
                            </div>
                            <div class="mb-2">
                                <label class="field-label">Cop Jawatan</label>
                                <input type="text" name="penyemak_cop" class="form-control-custom" placeholder="Cth: Ketua Unit IT">
                            </div>
                        </div>
                    </div>
                </div>
                <div style="margin-top:14px;font-size:0.82rem;color:#6b7280">
                    Tarikh pemberian akses: <strong><?= date('d/m/Y') ?></strong>
                </div>
        </div>
        <div class="action-row">
                <button type="submit" class="btn-primary-dark"><i class="bi bi-key"></i> Sahkan Pemberian Akses</button>
                <a href="dashboard_admin_it.php" class="btn-secondary-soft">Batal</a>
        </div>
            </form>
    </div>
</div>
</body></html>
