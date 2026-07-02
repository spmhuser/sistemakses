<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('penyemak_it');

$db = getDB();
// Penyemak IT menyemak kerja Admin IT: semua permohonan yang telah diberikan akses
$all = $db->query("SELECT p.*,u.username FROM permohonan p JOIN users u ON p.user_id=u.id
    WHERE p.status='AKSES_DIBERIKAN' ORDER BY p.tarikh_it DESC")->fetchAll();

$belum  = array_filter($all, fn($r)=>empty($r['it_penyemak_nama']));
$selesai= array_filter($all, fn($r)=>!empty($r['it_penyemak_nama']));

// Pemantauan: permohonan yang masih dalam proses (Admin IT belum beri akses)
$proses = $db->query("SELECT p.*,u.username FROM permohonan p JOIN users u ON p.user_id=u.id
    WHERE p.status IN ('MENUNGGU_PENGARAH_JAB','MENUNGGU_JTIK','DILULUSKAN') ORDER BY p.created_at DESC")->fetchAll();

// Nama sistem bagi setiap permohonan (semua set)
$sysByPerm = getSistemNamaByPermohonan(array_merge(array_column($all,'id'), array_column($proses,'id')));

// Admin IT yang bertanggungjawab bagi sistem dalam setiap permohonan (peta id sistem -> admin)
$adminByPerm = [];
$pids = array_column($proses, 'id');
if ($pids) {
    $ph = implode(',', array_fill(0, count($pids), '?'));
    $aq = $db->prepare("SELECT DISTINCT ps.permohonan_id, sa.nama_admin
        FROM permohonan_sistem ps
        JOIN sistem_admin sa ON sa.id_sistem = ps.bil AND sa.status = 1
        WHERE ps.permohonan_id IN ($ph) AND sa.nama_admin IS NOT NULL AND sa.nama_admin <> ''
        ORDER BY sa.nama_admin");
    $aq->execute($pids);
    foreach ($aq->fetchAll() as $row) { $adminByPerm[$row['permohonan_id']][] = $row['nama_admin']; }
}
function renderAdminBadges($names) {
    if (empty($names)) return '<span style="color:#c0c8d4;font-size:0.88rem">— belum ditetapkan —</span>';
    $out = '<div style="display:flex;flex-wrap:wrap;gap:4px">';
    foreach ($names as $n) {
        $out .= '<span style="display:inline-flex;align-items:center;gap:4px;font-size:0.74rem;padding:2px 9px;border-radius:20px;background:#E6EFFA;color:#234B7A;font-weight:600"><i class="bi bi-person-gear"></i>' . htmlspecialchars($n) . '</span>';
    }
    return $out . '</div>';
}

$defaultTab = count($belum) > 0 ? 'tab-belum' : (count($proses) > 0 ? 'tab-proses' : 'tab-selesai');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Penyemak IT</title>
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
<?php if(isset($_GET['semak'])) toastHTML('Semakan berjaya direkodkan.'); ?>
<?php sidebarHTML($_SESSION['nama']??$_SESSION['username'],'Penyemak IT',[
    ['href'=>'dashboard_penyemak.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>true],
]); ?>
<div class="main-content">
    <div class="page-header"><h4>Dashboard Penyemak IT</h4><p>Semak dan sahkan pemberian akses yang dilaksanakan oleh Admin IT</p></div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-info"><i class="bi bi-hourglass-split"></i></div><div><div class="stat-num num-info"><?=count($proses)?></div><div class="stat-lbl lbl-info">Dalam Proses</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-warning"><i class="bi bi-clipboard-check"></i></div><div><div class="stat-num num-warning"><?=count($belum)?></div><div class="stat-lbl lbl-warning">Belum Disemak</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-success"><i class="bi bi-patch-check"></i></div><div><div class="stat-num num-success"><?=count($selesai)?></div><div class="stat-lbl lbl-success">Telah Disemak</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-primary"><i class="bi bi-key"></i></div><div><div class="stat-num num-primary"><?=count($all)?></div><div class="stat-lbl lbl-primary">Jumlah Akses</div></div></div></div>
    </div>

    <div class="dash-tabs">
        <button class="dash-tab <?= $defaultTab==='tab-belum'?'active':'' ?>" onclick="switchTab(this,'tab-belum')">
            <span class="tab-ic"><i class="bi bi-clipboard-check"></i></span><span class="tab-txt">Belum Disemak</span>
            <?php if(count($belum)>0): ?><span class="tab-badge"><?=count($belum)?></span><?php endif; ?>
        </button>
        <button class="dash-tab <?= $defaultTab==='tab-proses'?'active':'' ?>" onclick="switchTab(this,'tab-proses')">
            <span class="tab-ic"><i class="bi bi-hourglass-split"></i></span><span class="tab-txt">Dalam Proses</span>
            <?php if(count($proses)>0): ?><span class="tab-badge"><?=count($proses)?></span><?php endif; ?>
        </button>
        <button class="dash-tab <?= $defaultTab==='tab-selesai'?'active':'' ?>" onclick="switchTab(this,'tab-selesai')">
            <span class="tab-ic"><i class="bi bi-check2-all"></i></span><span class="tab-txt">Telah Disemak</span>
            <span class="tab-badge"><?=count($selesai)?></span>
        </button>
    </div>

    <div id="tab-belum" class="dash-tab-pane <?= $defaultTab==='tab-belum'?'active':'' ?>">
        <div class="table-card">
            <table class="data-table tbl-resp">
                <thead><tr><th style="padding-left:24px">#</th><th>No. Rujukan</th><th>Pemohon</th><th>Jabatan</th><th>Sistem</th><th>Pemberi Akses</th><th>Tarikh Akses</th><th>Tindakan</th></tr></thead>
                <tbody>
                <?php if(empty($belum)): ?>
                <tr><td colspan="8" class="cell-empty"><div class="empty-state"><i class="bi bi-check2-circle"></i>Tiada akses menunggu semakan.</div></td></tr>
                <?php else: foreach(array_values($belum) as $i=>$r): ?>
                <tr>
                    <td data-label="#" style="padding-left:24px;color:#6E6470;font-size:0.9rem"><?=$i+1?></td>
                    <td data-label="No. Rujukan" style="font-weight:600;color:#2C5488;font-size:0.92rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td data-label="Pemohon" style="font-weight:500"><?= htmlspecialchars($r['nama']) ?></td>
                    <td data-label="Jabatan" style="font-size:0.92rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td class="cell-stack" data-label="Sistem" style="max-width:240px"><?= renderSistemBadges($sysByPerm[$r['id']] ?? []) ?></td>
                    <td data-label="Pemberi Akses" style="font-size:0.92rem"><?= htmlspecialchars($r['it_pemberi_nama'] ?? '-') ?></td>
                    <td data-label="Tarikh Akses" style="color:#6E6470;font-size:0.9rem"><?= $r['tarikh_it'] ?? '-' ?></td>
                    <td class="cell-act" style="display:flex;gap:6px">
                        <a href="view_permohonan.php?id=<?=$r['id']?>" class="btn-success-soft" style="padding:5px 10px;font-size:0.88rem"><i class="bi bi-eye"></i> Lihat</a>
                        <a href="tindakan_semakan.php?id=<?=$r['id']?>" class="btn-primary-dark" style="padding:5px 12px;font-size:0.88rem"><i class="bi bi-patch-check"></i> Semak</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="tab-proses" class="dash-tab-pane <?= $defaultTab==='tab-proses'?'active':'' ?>">
        <p style="font-size:0.9rem;color:#6b7280;margin-bottom:12px"><i class="bi bi-info-circle me-1 text-primary"></i>Pemantauan sahaja — permohonan ini masih dalam proses dan belum diberikan akses oleh Admin IT.</p>
        <div class="table-card">
            <table class="data-table tbl-resp">
                <thead><tr><th style="padding-left:24px">#</th><th>No. Rujukan</th><th>Pemohon</th><th>Jabatan</th><th>Sistem</th><th>Tujuan</th><th>Admin IT Bertanggungjawab</th><th>Status Semasa</th><th>Tarikh Mohon</th><th>Lihat</th></tr></thead>
                <tbody>
                <?php if(empty($proses)): ?>
                <tr><td colspan="10" class="cell-empty"><div class="empty-state"><i class="bi bi-check2-circle"></i>Tiada permohonan dalam proses.</div></td></tr>
                <?php else: foreach(array_values($proses) as $i=>$r): ?>
                <tr>
                    <td data-label="#" style="padding-left:24px;color:#6E6470;font-size:0.9rem"><?=$i+1?></td>
                    <td data-label="No. Rujukan" style="font-weight:600;color:#2C5488;font-size:0.92rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td data-label="Pemohon" style="font-weight:500"><?= htmlspecialchars($r['nama']) ?></td>
                    <td data-label="Jabatan" style="font-size:0.92rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td class="cell-stack" data-label="Sistem" style="max-width:240px"><?= renderSistemBadges($sysByPerm[$r['id']] ?? []) ?></td>
                    <td data-label="Tujuan" style="font-size:0.92rem"><?= tujuanLabel($r['tujuan']) ?></td>
                    <td class="cell-stack" data-label="Admin IT Bertanggungjawab" style="max-width:240px"><?= renderAdminBadges($adminByPerm[$r['id']] ?? []) ?></td>
                    <td data-label="Status Semasa"><span class="badge-status <?= statusClass($r['status']) ?>"><?= statusLabel($r['status']) ?></span></td>
                    <td data-label="Tarikh Mohon" style="color:#6E6470;font-size:0.9rem"><?= $r['created_at'] ?></td>
                    <td class="cell-act" style="display:flex;gap:6px;flex-wrap:wrap">
                        <a href="view_permohonan.php?id=<?=$r['id']?>" class="btn-success-soft" style="padding:5px 10px;font-size:0.88rem"><i class="bi bi-eye"></i> Lihat</a>
                        <?php if($r['status']==='DILULUSKAN'): ?>
                        <a href="tindakan_it.php?id=<?=$r['id']?>" class="btn-primary-dark" style="padding:5px 12px;font-size:0.88rem"><i class="bi bi-key"></i> Beri Akses</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="tab-selesai" class="dash-tab-pane <?= $defaultTab==='tab-selesai'?'active':'' ?>">
        <div class="table-card">
            <table class="data-table tbl-resp">
                <thead><tr><th style="padding-left:24px">#</th><th>No. Rujukan</th><th>Pemohon</th><th>Jabatan</th><th>Sistem</th><th>Disemak Oleh</th><th>Tarikh Semakan</th><th>Lihat</th></tr></thead>
                <tbody>
                <?php if(empty($selesai)): ?>
                <tr><td colspan="8" class="cell-empty"><div class="empty-state"><i class="bi bi-inbox"></i>Tiada rekod disemak lagi.</div></td></tr>
                <?php else: foreach(array_values($selesai) as $i=>$r): ?>
                <tr>
                    <td data-label="#" style="padding-left:24px;color:#6E6470;font-size:0.9rem"><?=$i+1?></td>
                    <td data-label="No. Rujukan" style="font-weight:600;color:#2C5488;font-size:0.92rem"><?= htmlspecialchars($r['no_rujukan']??'-') ?></td>
                    <td data-label="Pemohon" style="font-weight:500"><?= htmlspecialchars($r['nama']) ?></td>
                    <td data-label="Jabatan" style="font-size:0.92rem;color:#6b7280"><?= htmlspecialchars($r['jabatan']) ?></td>
                    <td class="cell-stack" data-label="Sistem" style="max-width:240px"><?= renderSistemBadges($sysByPerm[$r['id']] ?? []) ?></td>
                    <td data-label="Disemak Oleh" style="font-size:0.92rem"><?= htmlspecialchars($r['it_penyemak_nama'] ?? '-') ?></td>
                    <td data-label="Tarikh Semakan" style="color:#6E6470;font-size:0.9rem"><?= $r['tarikh_semakan'] ?? '-' ?></td>
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
