<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('admin_it');

$db  = getDB();
$all     = $db->query("SELECT p.*,u.username FROM permohonan p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC")->fetchAll();
$perlu   = array_filter($all, fn($r)=>$r['status']==='DILULUSKAN');
$selesai = array_filter($all, fn($r)=>$r['status']==='AKSES_DIBERIKAN');
$tolak   = array_filter($all, fn($r)=>$r['status']==='TIDAK_DILULUSKAN');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Admin IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .dash-tabs{display:flex;gap:4px;border-bottom:2px solid #ccfbf1;margin-bottom:0}
        .dash-tab{background:none;border:none;border-bottom:3px solid transparent;padding:10px 20px;font-size:0.85rem;font-weight:600;color:#6b7280;cursor:pointer;margin-bottom:-2px;transition:all 0.15s}
        .dash-tab:hover{color:#115e59}
        .dash-tab.active{color:#115e59;border-bottom-color:#0d9488}
        .dash-tab-pane{display:none}
        .dash-tab-pane.active{display:block}
        .tab-badge{font-size:0.7rem;font-weight:700;padding:1px 7px;border-radius:10px;margin-left:5px}
    </style>
</head>
<body>
<?php if(isset($_GET['success'])) toastHTML('Akses berjaya diberikan.'); ?>
<?php sidebarHTML($_SESSION['nama']??$_SESSION['username'],'Admin IT',[
    ['href'=>'dashboard_admin_it.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>true],
    ['href'=>'tetapan.php','icon'=>'bi-gear','label'=>'Tetapan Sistem','active'=>false],
]); ?>
<div class="main-content">
    <div class="page-header"><h4>Dashboard Admin IT</h4><p>Urus pemberian akses sistem selepas kelulusan JTIK</p></div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-total"><i class="bi bi-collection"></i></div><div><div class="stat-num num-total"><?=count($all)?></div><div class="stat-lbl lbl-total">Jumlah</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-warning"><i class="bi bi-hourglass-split"></i></div><div><div class="stat-num num-warning"><?=count($perlu)?></div><div class="stat-lbl lbl-warning">Perlu Akses</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-primary"><i class="bi bi-key"></i></div><div><div class="stat-num num-primary"><?=count($selesai)?></div><div class="stat-lbl lbl-primary">Akses Diberikan</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-danger"><i class="bi bi-x-circle"></i></div><div><div class="stat-num num-danger"><?=count($tolak)?></div><div class="stat-lbl lbl-danger">Tidak Lulus</div></div></div></div>
    </div>

    <div class="dash-tabs">
        <button class="dash-tab active" onclick="switchTab(this,'tab-perlu')">
            <i class="bi bi-key me-1"></i>Perlu Beri Akses
            <?php if(count($perlu)>0): ?><span class="tab-badge" style="background:#fbbf24;color:#78350f"><?=count($perlu)?></span><?php endif; ?>
        </button>
        <button class="dash-tab" onclick="switchTab(this,'tab-selesai')">
            <i class="bi bi-check2-all me-1"></i>Akses Diberikan
            <span class="tab-badge" style="background:#e5e7eb;color:#374151"><?=count($selesai)?></span>
        </button>
    </div>

    <div id="tab-perlu" class="dash-tab-pane active">
        <div class="table-card" style="border-radius:0 0 12px 12px;border-top:none">
            <table class="data-table">
                <thead><tr><th style="padding-left:24px">#</th><th>No. Rujukan</th><th>Pemohon</th><th>Jabatan</th><th>Tujuan</th><th>Diluluskan JTIK</th><th>Tindakan</th></tr></thead>
                <tbody>
                <?php if(empty($perlu)): ?>
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-check2-circle"></i>Tiada permohonan menunggu pemberian akses.</div></td></tr>
                <?php else: foreach(array_values($perlu) as $i=>$r): ?>
                <tr>
                    <td style="padding-left:24px;color:#9ca3af;font-size:0.8rem"><?=$i+1?></td>
                    <td style="font-weight:600;color:#115e59;font-size:0.82rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td style="font-weight:500"><?= htmlspecialchars($r['nama']) ?></td>
                    <td style="font-size:0.82rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td><span class="badge-status badge-info" style="font-size:0.72rem"><?= tujuanLabel($r['tujuan']) ?></span></td>
                    <td style="font-size:0.82rem;color:#6b7280"><?=$r['tarikh_jtik']??'-'?></td>
                    <td style="display:flex;gap:6px">
                        <a href="view_permohonan.php?id=<?=$r['id']?>" class="btn-success-soft" style="padding:5px 10px;font-size:0.78rem"><i class="bi bi-eye"></i></a>
                        <a href="tindakan_it.php?id=<?=$r['id']?>" class="btn-primary-dark" style="padding:5px 12px;font-size:0.78rem"><i class="bi bi-key"></i> Beri Akses</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="tab-selesai" class="dash-tab-pane">
        <div class="table-card" style="border-radius:0 0 12px 12px;border-top:none">
            <table class="data-table">
                <thead><tr><th style="padding-left:24px">#</th><th>No. Rujukan</th><th>Pemohon</th><th>Jabatan</th><th>Tujuan</th><th>Pemberi Akses</th><th>Tarikh Akses</th><th>Lihat</th></tr></thead>
                <tbody>
                <?php if(empty($selesai)): ?>
                <tr><td colspan="8"><div class="empty-state"><i class="bi bi-inbox"></i>Tiada rekod akses diberikan lagi.</div></td></tr>
                <?php else: foreach(array_values($selesai) as $i=>$r): ?>
                <tr>
                    <td style="padding-left:24px;color:#9ca3af;font-size:0.8rem"><?=$i+1?></td>
                    <td style="font-weight:600;color:#115e59;font-size:0.82rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td style="font-weight:500"><?= htmlspecialchars($r['nama']) ?></td>
                    <td style="font-size:0.82rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td style="font-size:0.82rem"><?= tujuanLabel($r['tujuan']) ?></td>
                    <td style="font-size:0.82rem;color:#6b7280"><?= htmlspecialchars($r['it_pemberi_nama']??'-') ?></td>
                    <td style="color:#9ca3af;font-size:0.8rem"><?= $r['tarikh_it'] ?? '-' ?></td>
                    <td><a href="view_permohonan.php?id=<?=$r['id']?>" class="btn-success-soft" style="padding:5px 12px;font-size:0.78rem"><i class="bi bi-eye"></i> Lihat</a></td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
function switchTab(btn, id) {
    document.querySelectorAll('.dash-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.dash-tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(id).classList.add('active');
}
</script>
</body></html>
