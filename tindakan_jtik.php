<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('pengarah_jtik');

$id = (int)($_GET['id'] ?? 0);
$db = getDB();
$stmt = $db->prepare("SELECT * FROM permohonan WHERE id=? AND status='MENUNGGU_JTIK'");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) { header('Location: dashboard_pengarah_jtik.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kelulusan = trim($_POST['kelulusan'] ?? '');
    $alasan    = trim($_POST['alasan']    ?? '');
    if (in_array($kelulusan, ['DILULUSKAN','TIDAK_DILULUSKAN'])) {
        $status = $kelulusan === 'DILULUSKAN' ? 'DILULUSKAN' : 'TIDAK_DILULUSKAN';
        $u = $db->prepare("UPDATE permohonan SET status=?,kelulusan_jtik=?,alasan_jtik=?,pengarah_jtik_id=?,tarikh_jtik=? WHERE id=?");
        $u->execute([$status,$kelulusan,$alasan,$_SESSION['user_id'],dbNow(),$id]);
        header('Location: dashboard_pengarah_jtik.php?success=1'); exit;
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
    <title>Kelulusan JTIK – Sistem Capaian Sistem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .keputusan-card{border:2px solid #e5e7eb;border-radius:12px;padding:16px 20px;cursor:pointer;transition:all 0.15s;display:flex;align-items:center;gap:12px;}
        .keputusan-card:hover{border-color:#1976d2;}
        .keputusan-card.lulus{border-color:#16a34a;background:#f0fdf4;}
        .keputusan-card.tolak{border-color:#dc2626;background:#fef2f2;}
        .keputusan-card input{accent-color:#1976d2;}
    </style>
</head>
<body>
<?php sidebarHTML($_SESSION['nama']??$_SESSION['username'],'Pengarah JTIK',[
    ['href'=>'dashboard_pengarah_jtik.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>false],
]); ?>
<div class="main-content">
    <div class="page-header">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard_pengarah_jtik.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Kelulusan JTIK</li>
        </ol></nav>
        <h4>Kelulusan Pengarah JTIK</h4>
        <p><?= htmlspecialchars($r['no_rujukan']??'') ?> &nbsp;|&nbsp; <?= htmlspecialchars($r['nama']) ?></p>
    </div>

    <div class="view-card mb-4">
        <div class="view-card-header"><h6>Maklumat Permohonan</h6></div>
        <div class="view-card-body">
            <div class="info-row">
                <div class="info-item"><label>Nama Pemohon</label><div class="val"><?= htmlspecialchars($r['nama']) ?></div></div>
                <div class="info-item"><label>Jabatan</label><div class="val"><?= htmlspecialchars($r['jabatan']) ?></div></div>
                <div class="info-item"><label>Tujuan</label><div class="val"><span class="badge-status badge-info"><?= tujuanLabel($r['tujuan']) ?></span></div></div>
                <div class="info-item"><label>Pengarah Jabatan</label><div class="val"><?= htmlspecialchars($r['nama_pengarah_jab']??'-') ?></div></div>
            </div>
            <?php if(!empty($sistemList)): ?>
            <div style="margin-top:14px;overflow-x:auto">
                <label style="font-size:0.75rem;font-weight:600;text-transform:uppercase;color:#9ca3af;display:block;margin-bottom:8px">Sistem Dipohon</label>
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
            <div style="margin-top:12px"><a href="view_permohonan.php?id=<?=$r['id']?>" style="font-size:0.82rem;color:#003087;font-weight:600"><i class="bi bi-eye me-1"></i>Lihat permohonan penuh</a></div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-section-header">
            <span class="sec-label">F</span>
            <span class="sec-title">Kelulusan Pengarah JTIK</span>
        </div>
        <div class="form-section-body">
            <form method="POST" id="jtikForm">
                <label class="field-label mb-3">Keputusan <span class="req">*</span></label>
                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="keputusan-card" id="cardLulus">
                            <input type="radio" name="kelulusan" value="DILULUSKAN" required onchange="toggleCard(this)">
                            <i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:1.3rem"></i>
                            <div><div style="font-weight:700;color:#166534">DILULUSKAN</div><div style="font-size:0.78rem;color:#6b7280">Permohonan diluluskan</div></div>
                        </label>
                    </div>
                    <div class="col-md-5">
                        <label class="keputusan-card" id="cardTolak">
                            <input type="radio" name="kelulusan" value="TIDAK_DILULUSKAN" onchange="toggleCard(this)">
                            <i class="bi bi-x-circle-fill" style="color:#dc2626;font-size:1.3rem"></i>
                            <div><div style="font-weight:700;color:#991b1b">TIDAK DILULUSKAN</div><div style="font-size:0.78rem;color:#6b7280">Permohonan ditolak</div></div>
                        </label>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="field-label">Alasan / Catatan</label>
                    <textarea name="alasan" class="form-control-custom" rows="3" placeholder="Nyatakan alasan keputusan (jika perlu)..." style="resize:vertical"></textarea>
                </div>
                <div style="font-size:0.82rem;color:#6b7280;margin-bottom:16px">
                    Pengarah: <strong><?= htmlspecialchars($_SESSION['nama']??$_SESSION['username']) ?></strong> &nbsp;|&nbsp; Tarikh: <strong><?= date('d/m/Y') ?></strong>
                </div>
        </div>
        <div class="action-row">
                <button type="submit" class="btn-primary-dark"><i class="bi bi-check2-square"></i> Rekodkan Kelulusan</button>
                <a href="dashboard_pengarah_jtik.php" class="btn-secondary-soft">Batal</a>
        </div>
            </form>
    </div>
</div>
<script>
function toggleCard(el) {
    document.getElementById('cardLulus').classList.remove('lulus');
    document.getElementById('cardTolak').classList.remove('tolak');
    if (el.value==='DILULUSKAN') document.getElementById('cardLulus').classList.add('lulus');
    else document.getElementById('cardTolak').classList.add('tolak');
}
</script>
<?php sharedJS(); ?>
</body></html>
