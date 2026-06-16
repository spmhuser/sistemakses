<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('penyemak');

$id = (int)($_GET['id'] ?? 0);
$db = getDB();
$stmt = $db->prepare("SELECT * FROM permohonan WHERE id=? AND status='MENUNGGU_PENYEMAK'");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) { header('Location: dashboard_penyemak.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tindakan = trim($_POST['tindakan'] ?? '');
    if ($tindakan === 'LULUS') {
        $nama = trim($_POST['nama_penyemak'] ?? ($_SESSION['nama'] ?? $_SESSION['username']));
        $db->prepare("UPDATE permohonan SET status='MENUNGGU_PENGARAH_JAB', penyemak_id=?, nama_penyemak=?, tarikh_penyemak=datetime('now','+8 hours') WHERE id=?")
           ->execute([$_SESSION['user_id'], $nama, $id]);
        header('Location: dashboard_penyemak.php?success=1'); exit;
    } elseif ($tindakan === 'TOLAK') {
        $db->prepare("UPDATE permohonan SET status='TIDAK_DILULUSKAN', penyemak_id=?, nama_penyemak=?, tarikh_penyemak=datetime('now','+8 hours') WHERE id=?")
           ->execute([$_SESSION['user_id'], $_SESSION['nama'] ?? $_SESSION['username'], $id]);
        header('Location: dashboard_penyemak.php?success=1'); exit;
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
    <title>Semakan Permohonan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .keputusan-card{border:2px solid #e5e7eb;border-radius:12px;padding:16px 20px;cursor:pointer;transition:all 0.15s;display:flex;align-items:center;gap:12px;}
        .keputusan-card:hover{border-color:#8b5cf6;}
        .keputusan-card.lulus{border-color:#16a34a;background:#f0fdf4;}
        .keputusan-card.tolak{border-color:#dc2626;background:#fef2f2;}
        .keputusan-card input{accent-color:#8b5cf6;}
    </style>
</head>
<body>
<?php sidebarHTML($_SESSION['nama']??$_SESSION['username'],'Penyemak',[
    ['href'=>'dashboard_penyemak.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>false],
]); ?>
<div class="main-content">
    <div class="page-header">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard_penyemak.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Semakan</li>
        </ol></nav>
        <h4>Semakan Permohonan</h4>
        <p><?= htmlspecialchars($r['no_rujukan']??'') ?> &nbsp;|&nbsp; <?= htmlspecialchars($r['nama']) ?></p>
    </div>

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
                        <td style="font-weight:600;color:#6d28d9"><?= htmlspecialchars($s['nama_sistem']) ?></td>
                        <td>
                            <?php if(!empty($s['peranan_sistem'])): ?>
                            <span class="badge-status badge-primary" style="font-size:0.72rem"><?= htmlspecialchars(SENARAI_PERANAN[$s['peranan_sistem']] ?? strtoupper($s['peranan_sistem'])) ?></span>
                            <?php else: ?><span style="color:#d1d5db">—</span><?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;flex-wrap:wrap;gap:3px">
                            <?php $anyHk=false; foreach(SENARAI_FUNGSI as $f): if($s[$f]??0): $anyHk=true; ?>
                            <span style="display:inline-block;font-size:0.7rem;padding:1px 6px;border-radius:10px;background:#ede9fe;color:#6d28d9;font-weight:600"><?= fungsiLabel($f) ?></span>
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
            <div style="margin-top:12px"><a href="view_permohonan.php?id=<?=$r['id']?>" style="font-size:0.82rem;color:#6d28d9;font-weight:600"><i class="bi bi-eye me-1"></i>Lihat permohonan penuh</a></div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-section-header">
            <span class="sec-label">D</span>
            <span class="sec-title">Semakan Penyemak</span>
        </div>
        <div class="form-section-body">
            <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:10px;padding:16px;margin-bottom:20px;font-size:0.875rem;color:#374151">
                Saya dengan ini mengesahkan bahawa permohonan di atas telah disemak dan maklumat adalah lengkap serta tepat.
            </div>
            <form method="POST" id="semakForm">
                <label class="field-label mb-3">Keputusan Semakan <span class="req">*</span></label>
                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="keputusan-card" id="cardLulus">
                            <input type="radio" name="tindakan" value="LULUS" required onchange="toggleCard(this)">
                            <i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:1.3rem"></i>
                            <div><div style="font-weight:700;color:#166534">LULUS SEMAKAN</div><div style="font-size:0.78rem;color:#6b7280">Hantar ke Pengarah Jabatan</div></div>
                        </label>
                    </div>
                    <div class="col-md-5">
                        <label class="keputusan-card" id="cardTolak">
                            <input type="radio" name="tindakan" value="TOLAK" onchange="toggleCard(this)">
                            <i class="bi bi-x-circle-fill" style="color:#dc2626;font-size:1.3rem"></i>
                            <div><div style="font-weight:700;color:#991b1b">TIDAK LULUS</div><div style="font-size:0.78rem;color:#6b7280">Permohonan ditolak</div></div>
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="field-label">Nama Penyemak <span class="req">*</span></label>
                    <input type="text" name="nama_penyemak" class="form-control-custom" required
                           value="<?= htmlspecialchars($_SESSION['nama'] ?? '') ?>" style="max-width:400px">
                </div>
                <div class="mb-3">
                    <label class="field-label">Tarikh Semakan</label>
                    <div style="font-size:0.875rem;color:#374151;padding:10px 0"><?= date('d/m/Y') ?></div>
                </div>
        </div>
        <div class="action-row">
                <button type="submit" class="btn-primary-dark"><i class="bi bi-check-lg"></i> Rekodkan Semakan</button>
                <a href="dashboard_penyemak.php" class="btn-secondary-soft"><i class="bi bi-x-lg"></i> Batal</a>
        </div>
            </form>
    </div>
</div>
<script>
function toggleCard(el) {
    document.getElementById('cardLulus').classList.remove('lulus');
    document.getElementById('cardTolak').classList.remove('tolak');
    if (el.value==='LULUS') document.getElementById('cardLulus').classList.add('lulus');
    else document.getElementById('cardTolak').classList.add('tolak');
}
</script>
</body></html>
