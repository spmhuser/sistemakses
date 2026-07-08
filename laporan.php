<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('admin_it');

$db = getDB();

$total = (int)$db->query("SELECT COUNT(*) c FROM permohonan")->fetch()['c'];

// Ikut status
$byStatusRaw = $db->query("SELECT status, COUNT(*) c FROM permohonan GROUP BY status")->fetchAll();
$byStatus = [];
foreach ($byStatusRaw as $row) $byStatus[$row['status']] = (int)$row['c'];
$cAkses  = $byStatus['AKSES_DIBERIKAN'] ?? 0;
$cTolak  = $byStatus['TIDAK_DILULUSKAN'] ?? 0;
$cProses = $total - $cAkses - $cTolak;

// Ikut jabatan
$byJabatan = $db->query("SELECT jabatan, COUNT(*) c FROM permohonan GROUP BY jabatan ORDER BY c DESC")->fetchAll();

// Ikut tujuan
$byTujuan = $db->query("SELECT tujuan, COUNT(*) c FROM permohonan GROUP BY tujuan ORDER BY c DESC")->fetchAll();

// Sistem paling banyak dipohon
$topSistem = $db->query("SELECT nama_sistem, COUNT(*) c FROM permohonan_sistem GROUP BY nama_sistem ORDER BY c DESC LIMIT 10")->fetchAll();

// Trend bulanan (12 bulan terakhir)
$byMonth = $db->query("SELECT substr(tkh_keyin,1,7) ym, COUNT(*) c FROM permohonan WHERE tkh_keyin IS NOT NULL GROUP BY ym ORDER BY ym")->fetchAll();

// Sediakan data untuk carta
$statusLabels = []; $statusData = []; $statusColors = [];
$colorMap = [
    'MENUNGGU_PENGARAH_JAB' => '#E8920C', 'MENUNGGU_JTIK' => '#2F6BE8',
    'DILULUSKAN' => '#E8920C', 'AKSES_DIBERIKAN' => '#18A957', 'TIDAK_DILULUSKAN' => '#E23B36',
];
foreach ($byStatus as $k => $v) { $statusLabels[] = statusLabel($k); $statusData[] = $v; $statusColors[] = $colorMap[$k] ?? '#8A93A0'; }

$jabLabels = array_map(fn($r)=>$r['jabatan'], $byJabatan);
$jabData   = array_map(fn($r)=>(int)$r['c'], $byJabatan);

$tjLabels = array_map(fn($r)=>tujuanLabel($r['tujuan']), $byTujuan);
$tjData   = array_map(fn($r)=>(int)$r['c'], $byTujuan);

$sysLabels = array_map(fn($r)=>$r['nama_sistem'], $topSistem);
$sysData   = array_map(fn($r)=>(int)$r['c'], $topSistem);

$mLabels = array_map(fn($r)=>$r['ym'], $byMonth);
$mData   = array_map(fn($r)=>(int)$r['c'], $byMonth);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Laporan &amp; Statistik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .chart-card{background:#fff;border-radius:18px;box-shadow:0 4px 18px rgba(40,70,120,0.10);padding:20px 22px;margin-bottom:20px;}
        .chart-card h6{font-size:1.02rem;font-weight:800;color:#1E3A5F;margin:0 0 14px;display:flex;align-items:center;gap:8px;}
        .chart-wrap{position:relative;height:300px;}
    </style>
</head>
<body>
<?php sidebarHTML($_SESSION['nama'] ?? $_SESSION['username'], 'Admin IT', [
    ['href'=>'dashboard_admin_it.php',  'icon'=>'bi-grid-1x2',     'label'=>'Dashboard',            'active'=>false],
    ['href'=>'laporan.php',             'icon'=>'bi-bar-chart-line','label'=>'Laporan & Statistik',  'active'=>true],
    ['href'=>'tetapan_sistem.php',      'icon'=>'bi-hdd-stack',    'label'=>'Tetapan Sistem',       'active'=>false],
    ['href'=>'tetapan_peranan.php',     'icon'=>'bi-diagram-3',    'label'=>'Tetapan Peranan',      'active'=>false],
    ['href'=>'tetapan_pengarah.php',    'icon'=>'bi-person-badge', 'label'=>'Tetapan Pengarah',     'active'=>false],
    ['href'=>'tetapan_admin_sistem.php','icon'=>'bi-person-gear',  'label'=>'Tetapan Admin Sistem', 'active'=>false],
    ['href'=>'tetapan_penyemak.php',    'icon'=>'bi-person-check', 'label'=>'Tetapan Penyemak',     'active'=>false],
]); ?>
<div class="main-content">
    <div class="page-header"><h4>Laporan &amp; Statistik</h4><p>Ringkasan dan analitik permohonan capaian sistem</p></div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-total"><i class="bi bi-collection"></i></div><div><div class="stat-num num-total"><?=$total?></div><div class="stat-lbl lbl-total">Jumlah Permohonan</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-warning"><i class="bi bi-hourglass-split"></i></div><div><div class="stat-num num-warning"><?=$cProses?></div><div class="stat-lbl lbl-warning">Dalam Proses</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-success"><i class="bi bi-check-circle"></i></div><div><div class="stat-num num-success"><?=$cAkses?></div><div class="stat-lbl lbl-success">Akses Diberikan</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-danger"><i class="bi bi-x-circle"></i></div><div><div class="stat-num num-danger"><?=$cTolak?></div><div class="stat-lbl lbl-danger">Tidak Lulus</div></div></div></div>
    </div>

    <?php if ($total === 0): ?>
    <div class="chart-card"><div class="empty-state"><i class="bi bi-bar-chart"></i>Tiada data permohonan untuk dilaporkan lagi.</div></div>
    <?php else: ?>
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="chart-card"><h6><i class="bi bi-pie-chart text-primary"></i> Mengikut Status</h6><div class="chart-wrap"><canvas id="cStatus"></canvas></div></div>
        </div>
        <div class="col-lg-7">
            <div class="chart-card"><h6><i class="bi bi-building text-primary"></i> Mengikut Jabatan</h6><div class="chart-wrap"><canvas id="cJabatan"></canvas></div></div>
        </div>
        <div class="col-lg-7">
            <div class="chart-card"><h6><i class="bi bi-hdd-stack text-primary"></i> 10 Sistem Paling Banyak Dipohon</h6><div class="chart-wrap"><canvas id="cSistem"></canvas></div></div>
        </div>
        <div class="col-lg-5">
            <div class="chart-card"><h6><i class="bi bi-bullseye text-primary"></i> Mengikut Tujuan</h6><div class="chart-wrap"><canvas id="cTujuan"></canvas></div></div>
        </div>
        <div class="col-12">
            <div class="chart-card"><h6><i class="bi bi-graph-up text-primary"></i> Trend Permohonan Bulanan</h6><div class="chart-wrap"><canvas id="cTrend"></canvas></div></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($total > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Segoe UI', sans-serif";
Chart.defaults.color = '#5B6675';
const BLUE = '#2E73D8', TEAL = '#1FBCD4', NAVY = '#1E3A5F';

new Chart(document.getElementById('cStatus'), {
    type: 'doughnut',
    data: { labels: <?= json_encode($statusLabels) ?>, datasets: [{ data: <?= json_encode($statusData) ?>, backgroundColor: <?= json_encode($statusColors) ?>, borderWidth: 2, borderColor: '#fff' }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('cJabatan'), {
    type: 'bar',
    data: { labels: <?= json_encode($jabLabels) ?>, datasets: [{ label: 'Permohonan', data: <?= json_encode($jabData) ?>, backgroundColor: BLUE, borderRadius: 6 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});

new Chart(document.getElementById('cSistem'), {
    type: 'bar',
    data: { labels: <?= json_encode($sysLabels) ?>, datasets: [{ label: 'Kali dipohon', data: <?= json_encode($sysData) ?>, backgroundColor: TEAL, borderRadius: 6 }] },
    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
});

new Chart(document.getElementById('cTujuan'), {
    type: 'pie',
    data: { labels: <?= json_encode($tjLabels) ?>, datasets: [{ data: <?= json_encode($tjData) ?>, backgroundColor: [BLUE, TEAL, '#E8920C'], borderWidth: 2, borderColor: '#fff' }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('cTrend'), {
    type: 'line',
    data: { labels: <?= json_encode($mLabels) ?>, datasets: [{ label: 'Permohonan', data: <?= json_encode($mData) ?>, borderColor: BLUE, backgroundColor: 'rgba(46,115,216,0.12)', fill: true, tension: 0.3, pointBackgroundColor: NAVY }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
<?php endif; ?>
</body>
</html>
