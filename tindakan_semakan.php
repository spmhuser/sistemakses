<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('penyemak_it');

$id = (int)($_GET['id'] ?? 0);
$db = getDB();
$stmt = $db->prepare("SELECT * FROM permohonan WHERE id=? AND status='AKSES_DIBERIKAN'");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) { header('Location: dashboard_penyemak.php'); exit; }

// Identiti penyemak (log masuk) — tarik cop jawatan dari Sistem Gaji
$meRow = $db->prepare("SELECT no_kakitangan, nama FROM users WHERE id=?");
$meRow->execute([$_SESSION['user_id']]);
$me      = $meRow->fetch() ?: [];
$noPek   = $me['no_kakitangan'] ?? '';
$myNama  = $me['nama'] ?? ($_SESSION['nama'] ?? '');
$mg = $db->prepare("SELECT jawatan FROM gaji WHERE no_kakitangan=?");
$mg->execute([$noPek]);
$myCop = ($mg->fetch()['jawatan'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catatan = trim($_POST['catatan'] ?? '');
    $u = $db->prepare("UPDATE permohonan SET it_penyemak_nama=?, it_penyemak_cop=?, tarikh_semakan=datetime('now','+8 hours') WHERE id=?");
    $u->execute([$myNama, $myCop, $id]);
    logAudit($id, 'SEMAKAN_PENYEMAK', "Disemak oleh: {$myNama}" . ($catatan !== '' ? "; Catatan: {$catatan}" : ''));
    header('Location: dashboard_penyemak.php?semak=1'); exit;
}

$sistems = $db->prepare("SELECT * FROM permohonan_sistem WHERE permohonan_id=? ORDER BY bil");
$sistems->execute([$id]);
$sistemList = $sistems->fetchAll();
$disemak = !empty($r['it_penyemak_nama']);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Semakan Pemberian Akses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
</head>
<body>
<?php sidebarHTML($_SESSION['nama']??$_SESSION['username'],'Penyemak IT',[
    ['href'=>'dashboard_penyemak.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>false],
]); ?>
<div class="main-content">
    <div class="page-header">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard_penyemak.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Semakan Akses</li>
        </ol></nav>
        <h4>Semakan Pemberian Akses</h4>
        <p><?= htmlspecialchars($r['no_rujukan']??'') ?> &nbsp;|&nbsp; <?= htmlspecialchars($r['nama']) ?></p>
    </div>

    <div class="view-card mb-4">
        <div class="view-card-header"><h6>Maklumat Permohonan &amp; Pemberian Akses</h6></div>
        <div class="view-card-body">
            <div class="info-row">
                <div class="info-item"><label>Nama Pemohon</label><div class="val"><?= htmlspecialchars($r['nama']) ?></div></div>
                <div class="info-item"><label>Jabatan</label><div class="val"><?= htmlspecialchars($r['jabatan']) ?></div></div>
                <div class="info-item"><label>Tujuan</label><div class="val"><span class="badge-status badge-info"><?= tujuanLabel($r['tujuan']) ?></span></div></div>
                <div class="info-item"><label>Pemberi Akses (Admin IT)</label><div class="val"><?= htmlspecialchars($r['it_pemberi_nama'] ?? '-') ?></div></div>
                <div class="info-item"><label>Cop Jawatan Pemberi</label><div class="val"><?= htmlspecialchars($r['it_pemberi_cop'] ?: '-') ?></div></div>
                <div class="info-item"><label>Tarikh Akses Diberikan</label><div class="val"><?= $r['tarikh_it'] ?? '-' ?></div></div>
            </div>
            <?php if(!empty($sistemList)): ?>
            <div style="margin-top:14px;overflow-x:auto">
                <label style="font-size:0.85rem;font-weight:600;text-transform:uppercase;color:#6E6470;display:block;margin-bottom:8px">Sistem Yang Telah Diberikan Akses</label>
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
            <span class="sec-label"><i class="bi bi-patch-check"></i></span>
            <span class="sec-title">Pengesahan Semakan Penyemak</span>
        </div>
        <div class="form-section-body">
            <?php if ($disemak): ?>
            <div style="display:flex;align-items:center;gap:14px;background:#E9F7EF;border:1px solid #BFE6CD;border-radius:12px;padding:16px 18px">
                <div style="width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,#2E9E5B,#23C16B);color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0"><i class="bi bi-check-lg"></i></div>
                <div>
                    <div style="font-weight:700;color:#1E3A5F">Telah disemak oleh <?= htmlspecialchars($r['it_penyemak_nama']) ?></div>
                    <div style="color:#5B6675;font-size:0.9rem">Tarikh: <?= $r['tarikh_semakan'] ?? '-' ?></div>
                </div>
            </div>
            <?php else: ?>
            <form method="POST">
                <div style="max-width:560px">
                    <div class="locked-note" style="font-size:0.82rem;color:#2862C0;background:#E6EFFA;border:1px solid #BFD2EC;border-radius:8px;padding:9px 13px;display:flex;align-items:center;gap:8px;margin-bottom:16px;font-weight:600"><i class="bi bi-shield-lock"></i> Anda mengesahkan telah menyemak ketepatan pemberian akses oleh Admin IT.</div>
                    <div class="row g-3 mb-2">
                        <div class="col-md-7">
                            <label class="field-label">Nama Penyemak</label>
                            <input type="text" class="form-control-custom" readonly value="<?= htmlspecialchars($myNama) ?>" style="background:#EEF3FA;cursor:not-allowed">
                        </div>
                        <div class="col-md-5">
                            <label class="field-label">Cop Jawatan</label>
                            <input type="text" class="form-control-custom" readonly value="<?= htmlspecialchars($myCop ?: '-') ?>" style="background:#EEF3FA;cursor:not-allowed">
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="field-label">Catatan Semakan <span style="font-weight:500;color:#8A7E86">(pilihan)</span></label>
                        <input type="text" name="catatan" class="form-control-custom" placeholder="Cth: Akses disahkan tepat mengikut kelulusan">
                    </div>
                </div>
        </div>
        <div class="action-row">
                <button type="submit" class="btn-primary-dark"><i class="bi bi-patch-check"></i> Sahkan Semakan</button>
                <a href="dashboard_penyemak.php" class="btn-secondary-soft">Batal</a>
        </div>
            </form>
            <?php endif; ?>
        <?php if ($disemak): ?></div><?php endif; ?>
    </div>
</div>
</body></html>
