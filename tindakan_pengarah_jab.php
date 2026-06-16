<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('pengarah_jab');

$id = (int)($_GET['id'] ?? 0);
$db = getDB();
$stmt = $db->prepare("SELECT * FROM permohonan WHERE id=? AND status='MENUNGGU_PENGARAH_JAB'");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) { header('Location: dashboard_pengarah_jab.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pengarah = trim($_POST['nama_pengarah'] ?? '');
    if ($nama_pengarah) {
        $u = $db->prepare("UPDATE permohonan SET status='MENUNGGU_JTIK', pengarah_jab_id=?, nama_pengarah_jab=?, tarikh_pengarah_jab=datetime('now','+8 hours') WHERE id=?");
        $u->execute([$_SESSION['user_id'], $nama_pengarah, $id]);
        header('Location: dashboard_pengarah_jab.php?success=1'); exit;
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
    <title>Perakuan Pengarah Jabatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
</head>
<body>
<?php sidebarHTML($_SESSION['nama']??$_SESSION['username'],'Pengarah Jabatan',[
    ['href'=>'dashboard_pengarah_jab.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>false],
]); ?>
<div class="main-content">
    <div class="page-header">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard_pengarah_jab.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Perakuan</li>
        </ol></nav>
        <h4>Perakuan Pengarah Jabatan</h4>
        <p><?= htmlspecialchars($r['no_rujukan']??'') ?> &nbsp;|&nbsp; <?= htmlspecialchars($r['nama']) ?></p>
    </div>

    <!-- Maklumat ringkas -->
    <div class="view-card mb-4">
        <div class="view-card-header"><h6>Maklumat Pemohon</h6></div>
        <div class="view-card-body">
            <div class="info-row">
                <div class="info-item"><label>Nama</label><div class="val"><?= htmlspecialchars($r['nama']) ?></div></div>
                <div class="info-item"><label>No. Kakitangan</label><div class="val"><?= htmlspecialchars($r['no_kakitangan']) ?></div></div>
                <div class="info-item"><label>Jawatan</label><div class="val"><?= htmlspecialchars($r['jawatan']) ?></div></div>
                <div class="info-item"><label>Jabatan</label><div class="val"><?= htmlspecialchars($r['jabatan']) ?></div></div>
                <div class="info-item"><label>Tujuan</label><div class="val"><span class="badge-status badge-info"><?= tujuanLabel($r['tujuan']) ?></span></div></div>
                <div class="info-item"><label>Tarikh Permohonan</label><div class="val"><?=$r['created_at']?></div></div>
            </div>
            <?php if(!empty($sistemList)): ?>
            <div style="margin-top:16px;overflow-x:auto">
                <label style="font-size:0.75rem;font-weight:600;text-transform:uppercase;color:#9ca3af;display:block;margin-bottom:8px">Sistem Yang Dipohon</label>
                <table class="sistem-table" style="font-size:0.8rem">
                    <thead><tr><th>Nama Sistem</th><th>Peranan</th><th>Had Kuasa</th><th>Catatan</th></tr></thead>
                    <tbody>
                    <?php foreach($sistemList as $s): ?>
                    <tr>
                        <td style="font-weight:600;color:#831843"><?= htmlspecialchars($s['nama_sistem']) ?></td>
                        <td>
                            <?php if(!empty($s['peranan_sistem'])): ?>
                            <span class="badge-status badge-primary" style="font-size:0.72rem"><?= htmlspecialchars(SENARAI_PERANAN[$s['peranan_sistem']] ?? strtoupper($s['peranan_sistem'])) ?></span>
                            <?php else: ?><span style="color:#d1d5db">—</span><?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;flex-wrap:wrap;gap:3px">
                            <?php $anyHk=false; foreach(SENARAI_FUNGSI as $f): if($s[$f]??0): $anyHk=true; ?>
                            <span style="display:inline-block;font-size:0.7rem;padding:1px 6px;border-radius:10px;background:#fce7f3;color:#831843;font-weight:600"><?= fungsiLabel($f) ?></span>
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

    <!-- Section E Form -->
    <div class="form-card">
        <div class="form-section-header">
            <span class="sec-label">E</span>
            <span class="sec-title">Perakuan Pengarah Jabatan Pemohon</span>
        </div>
        <div class="form-section-body">
            <div style="background:#fdf2f8;border:1px solid #fce7f3;border-radius:10px;padding:16px;margin-bottom:20px;font-size:0.875rem;color:#374151">
                Saya dengan ini mengesahkan permohonan di atas dibuat selaras dengan kehendak dan keperluan pemohon untuk melaksanakan tugasan.
            </div>
            <form method="POST">
                <div class="mb-3">
                    <label class="field-label">Nama Penuh Pengarah <span class="req">*</span></label>
                    <input type="text" name="nama_pengarah" class="form-control-custom" required
                           value="<?= htmlspecialchars($_SESSION['nama'] ?? '') ?>" style="max-width:400px">
                </div>
                <div class="mb-3">
                    <label class="field-label">Tarikh Perakuan</label>
                    <div style="font-size:0.875rem;color:#374151;padding:10px 0"><?= date('d/m/Y') ?></div>
                </div>
        </div>
        <div class="action-row">
                <button type="submit" class="btn-primary-dark"><i class="bi bi-check-lg"></i> Sahkan Perakuan</button>
                <a href="dashboard_pengarah_jab.php" class="btn-secondary-soft"><i class="bi bi-x-lg"></i> Batal</a>
        </div>
            </form>
    </div>
</div>
</body></html>
