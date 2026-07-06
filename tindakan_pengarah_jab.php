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

// Routing: pastikan permohonan ini milik jabatan yang ditugaskan kepada pengarah
$meRow = $db->prepare("SELECT no_kakitangan FROM users WHERE id=?");
$meRow->execute([$_SESSION['user_id']]);
$noPek = ($meRow->fetch())['no_kakitangan'] ?? '';
$myJab = getJabatanForPengarah($noPek);
if (!$myJab) $myJab = [$_SESSION['jabatan'] ?? ''];
if (!in_array($r['jabatan'], $myJab)) { header('Location: dashboard_pengarah_jab.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pengarah = trim($_POST['nama_pengarah'] ?? '');
    $keputusan     = $_POST['keputusan'] ?? 'LULUS';
    $alasan        = trim($_POST['alasan'] ?? '');
    if ($nama_pengarah) {
        if ($keputusan === 'TOLAK') {
            $u = $db->prepare("UPDATE permohonan SET status='TIDAK_DILULUSKAN', pengarah_jab_id=?, nama_pengarah_jab=?, alasan_pengarah_jab=?, tarikh_pengarah_jab=datetime('now','+8 hours') WHERE id=?");
            $u->execute([$_SESSION['user_id'], $nama_pengarah, $alasan, $id]);
            logAudit($id, 'TOLAK_JABATAN', 'Ditolak oleh ' . $nama_pengarah . ($alasan !== '' ? '; Alasan: ' . $alasan : ''));
        } else {
            $u = $db->prepare("UPDATE permohonan SET status='MENUNGGU_JTIK', pengarah_jab_id=?, nama_pengarah_jab=?, tarikh_pengarah_jab=datetime('now','+8 hours') WHERE id=?");
            $u->execute([$_SESSION['user_id'], $nama_pengarah, $id]);
            logAudit($id, 'PERAKUAN_JABATAN', 'Diperakukan oleh ' . $nama_pengarah);
        }
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
    <style>
        .keputusan-card{border:2px solid #e5e7eb;border-radius:12px;padding:16px 20px;cursor:pointer;transition:all 0.15s;display:flex;align-items:center;gap:12px;}
        .keputusan-card:hover{border-color:#3A86D0;}
        .keputusan-card.lulus{border-color:#16a34a;background:#f0fdf4;}
        .keputusan-card.tolak{border-color:#dc2626;background:#fef2f2;}
        .keputusan-card input{accent-color:#3A86D0;}
    </style>
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
                <div class="info-item"><label>Tarikh Permohonan</label><div class="val"><?=$r['tkh_keyin']?></div></div>
            </div>
            <?php if(!empty($sistemList)): ?>
            <div style="margin-top:16px;overflow-x:auto">
                <label style="font-size:0.85rem;font-weight:600;text-transform:uppercase;color:#6E6470;display:block;margin-bottom:8px">Sistem Yang Dipohon</label>
                <table class="sistem-table" style="font-size:0.9rem">
                    <thead><tr><th>Nama Sistem</th><th>Peranan</th><th>Had Kuasa</th><th>Catatan</th></tr></thead>
                    <tbody>
                    <?php foreach($sistemList as $s): ?>
                    <tr>
                        <td style="font-weight:600;color:#2C5488"><?= htmlspecialchars($s['nama_sistem']) ?></td>
                        <td>
                            <?php if(!empty($s['peranan_sistem'])): ?>
                            <span class="badge-status badge-primary" style="font-size:0.82rem"><?= htmlspecialchars(perananLabel($s['peranan_sistem'])) ?></span>
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

    <!-- Section E Form -->
    <div class="form-card">
        <div class="form-section-header">
            <span class="sec-label">E</span>
            <span class="sec-title">Perakuan Pengarah Jabatan Pemohon</span>
        </div>
        <div class="form-section-body">
            <div style="background:#FFFFFF;border:1px solid #E6EFFA;border-radius:10px;padding:16px;margin-bottom:20px;font-size:0.875rem;color:#374151">
                Saya dengan ini membuat keputusan terhadap permohonan di atas berdasarkan kehendak dan keperluan tugasan pemohon.
            </div>
            <form method="POST" id="perakuanForm">
                <label class="field-label mb-3">Keputusan <span class="req">*</span></label>
                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="keputusan-card" id="cardLulus">
                            <input type="radio" name="keputusan" value="LULUS" required onchange="toggleKpt(this)">
                            <i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:1.3rem"></i>
                            <div><div style="font-weight:700;color:#166534">PERAKUKAN</div><div style="font-size:0.88rem;color:#6b7280">Perakukan &amp; hantar ke JTIK</div></div>
                        </label>
                    </div>
                    <div class="col-md-5">
                        <label class="keputusan-card" id="cardTolak">
                            <input type="radio" name="keputusan" value="TOLAK" onchange="toggleKpt(this)">
                            <i class="bi bi-x-circle-fill" style="color:#dc2626;font-size:1.3rem"></i>
                            <div><div style="font-weight:700;color:#991b1b">TOLAK</div><div style="font-size:0.88rem;color:#6b7280">Permohonan tidak diluluskan</div></div>
                        </label>
                    </div>
                </div>
                <div class="mb-3" id="alasanBox" style="display:none">
                    <label class="field-label">Alasan / Catatan Tolakan <span class="req">*</span></label>
                    <textarea name="alasan" id="alasan" class="form-control-custom" rows="3" placeholder="Nyatakan sebab permohonan ditolak..." style="resize:vertical;max-width:500px"></textarea>
                </div>
                <div class="mb-3">
                    <label class="field-label">Nama Penuh Pengarah <span class="req">*</span></label>
                    <input type="text" name="nama_pengarah" class="form-control-custom" required
                           value="<?= htmlspecialchars($_SESSION['nama'] ?? '') ?>" style="max-width:400px">
                </div>
                <div class="mb-3">
                    <label class="field-label">Tarikh</label>
                    <div style="font-size:0.875rem;color:#374151;padding:10px 0"><?= date('d/m/Y') ?></div>
                </div>
        </div>
        <div class="action-row">
                <button type="submit" class="btn-primary-dark" id="btnSubmit"><i class="bi bi-check-lg"></i> Sahkan Keputusan</button>
                <a href="dashboard_pengarah_jab.php" class="btn-secondary-soft"><i class="bi bi-x-lg"></i> Batal</a>
        </div>
            </form>
            <script>
            function toggleKpt(el){
                document.getElementById('cardLulus').classList.remove('lulus');
                document.getElementById('cardTolak').classList.remove('tolak');
                var box = document.getElementById('alasanBox');
                var alasan = document.getElementById('alasan');
                if (el.value === 'TOLAK') {
                    document.getElementById('cardTolak').classList.add('tolak');
                    box.style.display = 'block'; alasan.required = true;
                } else {
                    document.getElementById('cardLulus').classList.add('lulus');
                    box.style.display = 'none'; alasan.required = false;
                }
            }
            </script>
    </div>
</div>
</body></html>
