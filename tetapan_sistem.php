<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('admin_it');

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    if ($act === 'add') {
        $nama = trim($_POST['nama_sistem'] ?? '');
        $kod  = trim($_POST['kod_sistem']  ?? '');
        if ($nama !== '') {
            $st = $db->prepare("INSERT INTO senarai_sistem (nama_sistem,kod_sistem,status) VALUES (?,?,1)");
            $st->execute([$nama, $kod]);
        }
        header('Location: tetapan_sistem.php?msg=add'); exit;
    }
    if ($act === 'edit') {
        $id   = (int)($_POST['id_sistem'] ?? 0);
        $nama = trim($_POST['nama_sistem'] ?? '');
        $kod  = trim($_POST['kod_sistem']  ?? '');
        if ($id && $nama !== '') {
            $st = $db->prepare("UPDATE senarai_sistem SET nama_sistem=?, kod_sistem=?, tkh_kemaskini=datetime('now','+8 hours') WHERE id_sistem=?");
            $st->execute([$nama, $kod, $id]);
        }
        header('Location: tetapan_sistem.php?msg=edit'); exit;
    }
    if ($act === 'toggle') {
        $id = (int)($_POST['id_sistem'] ?? 0);
        if ($id) {
            $db->prepare("UPDATE senarai_sistem SET status = 1 - status, tkh_kemaskini=datetime('now','+8 hours') WHERE id_sistem=?")->execute([$id]);
        }
        header('Location: tetapan_sistem.php?msg=toggle'); exit;
    }
}

$list     = $db->query("SELECT * FROM senarai_sistem ORDER BY id_sistem")->fetchAll();
$aktif    = count(array_filter($list, fn($r) => $r['status'] == 1));
$nonaktif = count($list) - $aktif;
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tetapan Senarai Sistem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .modal-overlay{position:fixed;inset:0;background:rgba(20,35,60,0.55);-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:9999;padding:20px;}
        .modal-overlay.show{display:flex;}
        .modal-box{background:#fff;border-radius:18px;width:100%;max-width:480px;padding:28px;box-shadow:0 24px 60px rgba(20,35,60,0.4);animation:pop 0.22s ease;}
        @keyframes pop{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
        .modal-box h3{font-size:1.2rem;font-weight:800;color:#1E3A5F;margin-bottom:16px;display:flex;align-items:center;gap:9px;}
        .m-actions{display:flex;gap:10px;margin-top:20px;}
        .m-actions .btn-primary-dark,.m-actions .btn-secondary-soft{flex:1;}
    </style>
</head>
<body>
<?php
$msg = $_GET['msg'] ?? '';
if ($msg === 'add')    toastHTML('Sistem baharu berjaya ditambah.');
if ($msg === 'edit')   toastHTML('Maklumat sistem berjaya dikemas kini.');
if ($msg === 'toggle') toastHTML('Status sistem berjaya dikemas kini.');
?>
<?php sidebarHTML($_SESSION['nama'] ?? $_SESSION['username'], 'Admin IT', [
    ['href'=>'dashboard_admin_it.php', 'icon'=>'bi-grid-1x2',     'label'=>'Dashboard',       'active'=>false],
    ['href'=>'tetapan_sistem.php',     'icon'=>'bi-hdd-stack',    'label'=>'Tetapan Sistem',  'active'=>true],
    ['href'=>'tetapan_peranan.php',    'icon'=>'bi-diagram-3',    'label'=>'Tetapan Peranan', 'active'=>false],
    ['href'=>'tetapan_pengarah.php',   'icon'=>'bi-person-badge', 'label'=>'Tetapan Pengarah','active'=>false],
    ['href'=>'tetapan_admin_sistem.php','icon'=>'bi-person-gear','label'=>'Tetapan Admin Sistem','active'=>false],
    ['href'=>'tetapan_penyemak.php','icon'=>'bi-person-check','label'=>'Tetapan Penyemak','active'=>false],
]); ?>
<div class="main-content">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <h4>Tetapan Senarai Sistem</h4>
            <p>Urus senarai sistem yang boleh dipohon capaian — tambah, kemas kini, atau nyahaktif.</p>
        </div>
        <button class="btn-primary-dark" onclick="openAdd()"><i class="bi bi-plus-lg"></i> Tambah Sistem</button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-total"><i class="bi bi-hdd-stack"></i></div><div><div class="stat-num num-total"><?=count($list)?></div><div class="stat-lbl lbl-total">Jumlah Sistem</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-success"><i class="bi bi-check-circle"></i></div><div><div class="stat-num num-success"><?=$aktif?></div><div class="stat-lbl lbl-success">Aktif</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-danger"><i class="bi bi-slash-circle"></i></div><div><div class="stat-num num-danger"><?=$nonaktif?></div><div class="stat-lbl lbl-danger">Tidak Aktif</div></div></div></div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <h6>Senarai Sistem</h6>
            <span class="badge-count"><?=count($list)?> sistem</span>
        </div>
        <div style="overflow-x:auto">
        <table class="data-table">
            <thead><tr>
                <th style="padding-left:24px;width:60px">ID</th>
                <th style="width:120px">Kod</th>
                <th>Nama Sistem</th>
                <th style="width:130px">Status</th>
                <th style="width:220px">Tindakan</th>
            </tr></thead>
            <tbody>
            <?php foreach ($list as $r): ?>
                <tr>
                    <td style="padding-left:24px;color:#6E6470;font-weight:600"><?=$r['id_sistem']?></td>
                    <td style="font-weight:600;color:#2862C0"><?= htmlspecialchars($r['kod_sistem'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['nama_sistem']) ?></td>
                    <td>
                        <?php if ($r['status'] == 1): ?>
                            <span class="badge-status badge-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge-status badge-danger">Tidak Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:8px;align-items:center">
                            <button class="btn-secondary-soft" style="padding:8px 14px;font-size:0.85rem"
                                onclick='openEdit(<?= (int)$r["id_sistem"] ?>, <?= htmlspecialchars(json_encode($r["nama_sistem"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["kod_sistem"]), ENT_QUOTES) ?>)'>
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form method="POST" style="margin:0" onsubmit="return confirm('Tukar status sistem ini?')">
                                <input type="hidden" name="act" value="toggle">
                                <input type="hidden" name="id_sistem" value="<?=$r['id_sistem']?>">
                                <?php if ($r['status'] == 1): ?>
                                    <button type="submit" class="btn-danger-soft" style="padding:8px 14px;font-size:0.85rem"><i class="bi bi-slash-circle"></i> Nyahaktif</button>
                                <?php else: ?>
                                    <button type="submit" class="btn-success-soft" style="padding:8px 14px;font-size:0.85rem"><i class="bi bi-check-circle"></i> Aktifkan</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT -->
<div class="modal-overlay" id="sysModal">
    <div class="modal-box">
        <h3 id="modalTitle"><i class="bi bi-hdd-stack"></i> Tambah Sistem</h3>
        <form method="POST" id="sysForm">
            <input type="hidden" name="act" id="f_act" value="add">
            <input type="hidden" name="id_sistem" id="f_id" value="">
            <div class="mb-3">
                <label class="field-label">Nama Sistem <span class="req">*</span></label>
                <input type="text" name="nama_sistem" id="f_nama" class="form-control-custom" required placeholder="Cth: Sistem Kehadiran">
            </div>
            <div class="mb-1">
                <label class="field-label">Kod Sistem</label>
                <input type="text" name="kod_sistem" id="f_kod" class="form-control-custom" placeholder="Cth: SYS28">
            </div>
            <div class="m-actions">
                <button type="button" class="btn-secondary-soft" onclick="closeModal()"><i class="bi bi-x-lg"></i> Batal</button>
                <button type="submit" class="btn-primary-dark"><i class="bi bi-check-lg"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAdd(){
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle"></i> Tambah Sistem';
    document.getElementById('f_act').value = 'add';
    document.getElementById('f_id').value  = '';
    document.getElementById('f_nama').value = '';
    document.getElementById('f_kod').value  = '';
    document.getElementById('sysModal').classList.add('show');
    setTimeout(()=>document.getElementById('f_nama').focus(),50);
}
function openEdit(id, nama, kod){
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square"></i> Kemas Kini Sistem';
    document.getElementById('f_act').value = 'edit';
    document.getElementById('f_id').value  = id;
    document.getElementById('f_nama').value = nama || '';
    document.getElementById('f_kod').value  = kod || '';
    document.getElementById('sysModal').classList.add('show');
    setTimeout(()=>document.getElementById('f_nama').focus(),50);
}
function closeModal(){ document.getElementById('sysModal').classList.remove('show'); }
document.getElementById('sysModal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
</script>
</body>
</html>
