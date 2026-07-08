<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('admin_it');

$db  = getDB();
// Multi Admin IT (Option A): admin hanya nampak permohonan yang mengandungi sistem di bawah tanggungjawabnya
$meRow = $db->prepare("SELECT no_kakitangan FROM users WHERE id=?");
$meRow->execute([$_SESSION['user_id']]);
$noPek = ($meRow->fetch())['no_kakitangan'] ?? '';
$mySys = getSistemForAdmin($noPek);
if ($mySys) {
    $ph   = implode(',', array_fill(0, count($mySys), '?'));
    $stmt = $db->prepare("SELECT p.*,u.username FROM permohonan p JOIN users u ON p.user_id=u.id
        WHERE p.id IN (SELECT permohonan_id FROM permohonan_sistem WHERE bil IN ($ph)) ORDER BY p.tkh_keyin DESC");
    $stmt->execute($mySys);
    $all = $stmt->fetchAll();
} else {
    $all = $db->query("SELECT p.*,u.username FROM permohonan p JOIN users u ON p.user_id=u.id ORDER BY p.tkh_keyin DESC")->fetchAll();
}
$perlu   = array_filter($all, fn($r)=>$r['status']==='DILULUSKAN');
$selesai = array_filter($all, fn($r)=>in_array($r['status'],['AKSES_DIBERIKAN','TIDAK_DILULUSKAN']));
$tolak   = array_filter($all, fn($r)=>$r['status']==='TIDAK_DILULUSKAN');
$sysByPerm = getSistemNamaByPermohonan(array_column($all,'id'));
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
        .dash-tabs{display:inline-flex;gap:4px;margin-bottom:22px;background:#EAF1FB;padding:6px;border-radius:50px;box-shadow:inset 0 1px 4px rgba(40,70,120,0.16)}
        .dash-tab{position:relative;display:inline-flex;align-items:center;gap:9px;padding:11px 26px;border:none;background:transparent;cursor:pointer;border-radius:50px;transition:all 0.25s cubic-bezier(.2,.8,.2,1)}
        .dash-tab .tab-ic{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;background:transparent;color:#2E73D8;transition:all 0.25s}
        .dash-tab .tab-txt{font-size:0.95rem;font-weight:700;color:#6E7787;transition:color 0.2s;letter-spacing:0.2px}
        .dash-tab:hover .tab-ic{color:#1E3A5F}
        .dash-tab:hover .tab-txt{color:#1E3A5F}
        .dash-tab.active{background:linear-gradient(135deg,#2E73D8,#1FBCD4);box-shadow:0 6px 16px rgba(46,115,216,0.42)}
        .dash-tab.active .tab-ic{background:rgba(255,255,255,0.22);color:#fff}
        .dash-tab.active .tab-txt{color:#fff;font-weight:800}
        .dash-tab .tab-badge{min-width:22px;height:22px;padding:0 7px;display:inline-flex;align-items:center;justify-content:center;font-size:0.74rem;font-weight:800;border-radius:20px;background:#1FBCD4;color:#fff}
        .dash-tab.active .tab-badge{background:#fff;color:#234B7A}
        .dash-tab-pane{display:none}
        .dash-tab-pane.active{display:block}
        @media (max-width:768px){
            .dash-tabs{display:flex;width:100%;gap:4px}
            .dash-tab{flex:1;justify-content:center;padding:10px 8px}
            .dash-tab .tab-txt{font-size:0.82rem}
            .dash-tab .tab-ic{width:24px;height:24px;font-size:14px}
        }
    </style>
</head>
<body>
<?php if(isset($_GET['success'])) toastHTML('Akses berjaya diberikan.'); ?>
<?php sidebarHTML($_SESSION['nama']??$_SESSION['username'],'Admin IT',[
    ['href'=>'dashboard_admin_it.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>true],
    ['href'=>'laporan.php','icon'=>'bi-bar-chart-line','label'=>'Laporan & Statistik','active'=>false],
    ['href'=>'tetapan_sistem.php','icon'=>'bi-hdd-stack','label'=>'Tetapan Sistem','active'=>false],
    ['href'=>'tetapan_peranan.php','icon'=>'bi-diagram-3','label'=>'Tetapan Peranan','active'=>false],
    ['href'=>'tetapan_pengarah.php','icon'=>'bi-person-badge','label'=>'Tetapan Pengarah','active'=>false],
    ['href'=>'tetapan_admin_sistem.php','icon'=>'bi-person-gear','label'=>'Tetapan Admin Sistem','active'=>false],
    ['href'=>'tetapan_penyemak.php','icon'=>'bi-person-check','label'=>'Tetapan Penyemak','active'=>false],
]); ?>
<div class="main-content">
    <div class="page-header"><h4>Dashboard Admin IT</h4><p>Urus pemberian akses sistem selepas kelulusan JTIK</p></div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-total"><i class="bi bi-collection"></i></div><div><div class="stat-num num-total"><?=count($all)?></div><div class="stat-lbl lbl-total">Jumlah</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-warning"><i class="bi bi-hourglass-split"></i></div><div><div class="stat-num num-warning"><?=count($perlu)?></div><div class="stat-lbl lbl-warning">Perlu Akses</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-primary"><i class="bi bi-key"></i></div><div><div class="stat-num num-primary"><?=count($selesai)?></div><div class="stat-lbl lbl-primary">Akses Diberikan</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-danger"><i class="bi bi-x-circle"></i></div><div><div class="stat-num num-danger"><?=count($tolak)?></div><div class="stat-lbl lbl-danger">Tidak Lulus</div></div></div></div>
    </div>

    <div class="filter-bar" data-target=".data-table">
        <div class="flt-search-wrap"><i class="bi bi-search"></i>
            <input type="text" class="flt-q" placeholder="Cari No. Rujukan, pemohon, jabatan, sistem, tujuan...">
        </div>
    </div>

    <div class="dash-tabs">
        <button class="dash-tab active" onclick="switchTab(this,'tab-perlu')">
            <span class="tab-ic"><i class="bi bi-key"></i></span><span class="tab-txt">Perlu Beri Akses</span>
            <?php if(count($perlu)>0): ?><span class="tab-badge"><?=count($perlu)?></span><?php endif; ?>
        </button>
        <button class="dash-tab" onclick="switchTab(this,'tab-selesai')">
            <span class="tab-ic"><i class="bi bi-check2-all"></i></span><span class="tab-txt">Selesai</span>
            <span class="tab-badge"><?=count($selesai)?></span>
        </button>
    </div>

    <div id="tab-perlu" class="dash-tab-pane active">
        <div class="table-card">
            <table class="data-table tbl-resp">
                <thead><tr><th style="padding-left:24px">#</th><th>No. Rujukan</th><th>Pemohon</th><th>Jabatan</th><th>Sistem</th><th>Tujuan</th><th>Diluluskan JTIK</th><th>Tindakan</th></tr></thead>
                <tbody>
                <?php if(empty($perlu)): ?>
                <tr><td colspan="8" class="cell-empty"><div class="empty-state"><i class="bi bi-check2-circle"></i>Tiada permohonan menunggu pemberian akses.</div></td></tr>
                <?php else: foreach(array_values($perlu) as $i=>$r): ?>
                <tr>
                    <td data-label="#" style="padding-left:24px;color:#6E6470;font-size:0.9rem"><?=$i+1?></td>
                    <td data-label="No. Rujukan" style="font-weight:600;color:#2C5488;font-size:0.92rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td data-label="Pemohon" style="font-weight:500"><?= htmlspecialchars($r['nama']) ?></td>
                    <td data-label="Jabatan" style="font-size:0.92rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td class="cell-stack" data-label="Sistem" style="max-width:240px"><?= renderSistemBadges($sysByPerm[$r['id']] ?? []) ?></td>
                    <td data-label="Tujuan"><span class="badge-status badge-info" style="font-size:0.82rem"><?= tujuanLabel($r['tujuan']) ?></span></td>
                    <td data-label="Diluluskan JTIK" style="font-size:0.92rem;color:#6b7280"><?=$r['tarikh_jtik']??'-'?></td>
                    <td class="cell-act" style="display:flex;gap:6px">
                        <a href="view_permohonan.php?id=<?=$r['id']?>" class="btn-success-soft" style="padding:5px 10px;font-size:0.88rem"><i class="bi bi-eye"></i> Lihat</a>
                        <a href="tindakan_it.php?id=<?=$r['id']?>" class="btn-primary-dark" style="padding:5px 12px;font-size:0.88rem"><i class="bi bi-key"></i> Beri Akses</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="tab-selesai" class="dash-tab-pane">
        <div class="table-card">
            <table class="data-table tbl-resp">
                <thead><tr><th style="padding-left:24px">#</th><th>No. Rujukan</th><th>Pemohon</th><th>Jabatan</th><th>Sistem</th><th>Tujuan</th><th>Status</th><th>Tarikh</th><th>Lihat</th></tr></thead>
                <tbody>
                <?php if(empty($selesai)): ?>
                <tr><td colspan="9" class="cell-empty"><div class="empty-state"><i class="bi bi-inbox"></i>Tiada rekod lagi.</div></td></tr>
                <?php else: foreach(array_values($selesai) as $i=>$r): ?>
                <tr>
                    <td data-label="#" style="padding-left:24px;color:#6E6470;font-size:0.9rem"><?=$i+1?></td>
                    <td data-label="No. Rujukan" style="font-weight:600;color:#2C5488;font-size:0.92rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td data-label="Pemohon" style="font-weight:500"><?= htmlspecialchars($r['nama']) ?></td>
                    <td data-label="Jabatan" style="font-size:0.92rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td class="cell-stack" data-label="Sistem" style="max-width:240px"><?= renderSistemBadges($sysByPerm[$r['id']] ?? []) ?></td>
                    <td data-label="Tujuan" style="font-size:0.92rem"><?= tujuanLabel($r['tujuan']) ?></td>
                    <td data-label="Status"><span class="badge-status <?= statusClass($r['status']) ?>"><?= statusLabel($r['status']) ?></span></td>
                    <td data-label="Tarikh" style="color:#6E6470;font-size:0.9rem"><?= $r['tarikh_it'] ?? $r['tarikh_jtik'] ?? '-' ?></td>
                    <td class="cell-act"><a href="view_permohonan.php?id=<?=$r['id']?>" class="btn-success-soft" style="padding:5px 12px;font-size:0.88rem"><i class="bi bi-eye"></i> Lihat</a></td>
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
