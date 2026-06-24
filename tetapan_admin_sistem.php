<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('admin_it');

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    if ($act === 'add' || $act === 'edit') {
        $idSistem = (int)($_POST['id_sistem'] ?? 0);
        $nama     = trim($_POST['nama_admin'] ?? '');
        $noPek    = trim($_POST['no_pekerja'] ?? '');
        if ($act === 'add' && $idSistem && $nama !== '') {
            $st = $db->prepare("INSERT INTO sistem_admin (id_sistem,no_pekerja,nama_admin,status) VALUES (?,?,?,1)");
            $st->execute([$idSistem, $noPek, $nama]);
        }
        if ($act === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id && $idSistem && $nama !== '') {
                $st = $db->prepare("UPDATE sistem_admin SET id_sistem=?, no_pekerja=?, nama_admin=?, updated_at=datetime('now','+8 hours') WHERE id=?");
                $st->execute([$idSistem, $noPek, $nama, $id]);
            }
        }
        header('Location: tetapan_admin_sistem.php?msg=' . $act); exit;
    }
    if ($act === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) $db->prepare("UPDATE sistem_admin SET status = 1 - status, updated_at=datetime('now','+8 hours') WHERE id=?")->execute([$id]);
        header('Location: tetapan_admin_sistem.php?msg=toggle'); exit;
    }
}

$sistemMap = getSenaraiSistem(false); // id => nama (semua)
$list = $db->query("SELECT * FROM sistem_admin ORDER BY id_sistem, nama_admin")->fetchAll();
$aktif = count(array_filter($list, fn($r) => $r['status'] == 1));
$adminUnik = count(array_unique(array_column(array_filter($list, fn($r)=>$r['status']==1), 'no_pekerja')));
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tetapan Admin Sistem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .modal-overlay{position:fixed;inset:0;background:rgba(20,35,60,0.55);-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:9999;padding:20px;}
        .modal-overlay.show{display:flex;}
        .modal-box{background:#fff;border-radius:18px;width:100%;max-width:500px;padding:28px;box-shadow:0 24px 60px rgba(20,35,60,0.4);animation:pop 0.22s ease;}
        @keyframes pop{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
        .modal-box h3{font-size:1.2rem;font-weight:800;color:#1E3A5F;margin-bottom:16px;display:flex;align-items:center;gap:9px;}
        .m-actions{display:flex;gap:10px;margin-top:20px;}
        .m-actions .btn-primary-dark,.m-actions .btn-secondary-soft{flex:1;}
        select.form-control-custom{appearance:auto;}
    </style>
</head>
<body>
<?php
$msg = $_GET['msg'] ?? '';
if ($msg==='add') toastHTML('Admin sistem berjaya ditambah.');
if ($msg==='edit') toastHTML('Tugasan admin berjaya dikemas kini.');
if ($msg==='toggle') toastHTML('Status tugasan berjaya dikemas kini.');
?>
<?php sidebarHTML($_SESSION['nama'] ?? $_SESSION['username'], 'Admin IT', [
    ['href'=>'dashboard_admin_it.php',   'icon'=>'bi-grid-1x2',     'label'=>'Dashboard',          'active'=>false],
    ['href'=>'tetapan_sistem.php',       'icon'=>'bi-hdd-stack',    'label'=>'Tetapan Sistem',     'active'=>false],
    ['href'=>'tetapan_pengarah.php',     'icon'=>'bi-person-badge', 'label'=>'Tetapan Pengarah',   'active'=>false],
    ['href'=>'tetapan_admin_sistem.php', 'icon'=>'bi-person-gear',  'label'=>'Tetapan Admin Sistem','active'=>true],
]); ?>
<div class="main-content">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <h4>Tetapan Admin Sistem</h4>
            <p>Tetapkan Admin IT yang bertanggungjawab bagi setiap sistem. Permohonan dihalakan ke admin sistem berkaitan.</p>
        </div>
        <button class="btn-primary-dark" onclick="openAdd()"><i class="bi bi-plus-lg"></i> Tugaskan Admin</button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-total"><i class="bi bi-diagram-3"></i></div><div><div class="stat-num num-total"><?=count($list)?></div><div class="stat-lbl lbl-total">Tugasan</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-success"><i class="bi bi-check-circle"></i></div><div><div class="stat-num num-success"><?=$aktif?></div><div class="stat-lbl lbl-success">Aktif</div></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-icon icon-info"><i class="bi bi-people"></i></div><div><div class="stat-num num-info"><?=$adminUnik?></div><div class="stat-lbl lbl-info">Admin Unik</div></div></div></div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <h6>Tugasan Admin – Sistem</h6>
            <span class="badge-count"><?=count($list)?> tugasan</span>
        </div>
        <div style="overflow-x:auto">
        <table class="data-table">
            <thead><tr>
                <th style="padding-left:24px;width:70px">Sistem</th>
                <th>Nama Sistem</th>
                <th>Admin IT</th>
                <th style="width:130px">No. Pekerja</th>
                <th style="width:110px">Status</th>
                <th style="width:210px">Tindakan</th>
            </tr></thead>
            <tbody>
            <?php if (!$list): ?>
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-diagram-3"></i>Tiada tugasan admin lagi.</div></td></tr>
            <?php else: foreach ($list as $r):
                $namaSistem = $sistemMap[(int)$r['id_sistem']] ?? ('Sistem #'.$r['id_sistem']); ?>
                <tr>
                    <td style="padding-left:24px;color:#6E6470;font-weight:600"><?=$r['id_sistem']?></td>
                    <td><?= htmlspecialchars($namaSistem) ?></td>
                    <td style="font-weight:600;color:#234B7A"><?= htmlspecialchars($r['nama_admin']) ?></td>
                    <td style="color:#2862C0;font-weight:600"><?= htmlspecialchars($r['no_pekerja'] ?? '-') ?></td>
                    <td>
                        <?php if ($r['status']==1): ?><span class="badge-status badge-success">Aktif</span>
                        <?php else: ?><span class="badge-status badge-danger">Tidak Aktif</span><?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:8px;align-items:center">
                            <button class="btn-secondary-soft" style="padding:8px 14px;font-size:0.85rem"
                                onclick='openEdit(<?= (int)$r["id"] ?>, <?= (int)$r["id_sistem"] ?>, <?= htmlspecialchars(json_encode($r["nama_admin"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["no_pekerja"]), ENT_QUOTES) ?>)'>
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form method="POST" style="margin:0" onsubmit="return confirm('Tukar status tugasan ini?')">
                                <input type="hidden" name="act" value="toggle">
                                <input type="hidden" name="id" value="<?=$r['id']?>">
                                <?php if ($r['status']==1): ?>
                                    <button type="submit" class="btn-danger-soft" style="padding:8px 14px;font-size:0.85rem"><i class="bi bi-slash-circle"></i> Nyahaktif</button>
                                <?php else: ?>
                                    <button type="submit" class="btn-success-soft" style="padding:8px 14px;font-size:0.85rem"><i class="bi bi-check-circle"></i> Aktifkan</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="aModal">
    <div class="modal-box">
        <h3 id="modalTitle"><i class="bi bi-person-gear"></i> Tugaskan Admin</h3>
        <form method="POST" id="aForm">
            <input type="hidden" name="act" id="f_act" value="add">
            <input type="hidden" name="id" id="f_id" value="">
            <div class="mb-3">
                <label class="field-label">Sistem <span class="req">*</span></label>
                <select name="id_sistem" id="f_sistem" class="form-control-custom" required>
                    <option value="">-- Pilih Sistem --</option>
                    <?php foreach ($sistemMap as $idS=>$namaS): ?>
                        <option value="<?=$idS?>"><?=$idS?> — <?= htmlspecialchars($namaS) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="field-label">Nama Admin <span class="req">*</span></label>
                <input type="text" name="nama_admin" id="f_nama" class="form-control-custom" required placeholder="Nama penuh admin IT">
            </div>
            <div class="mb-1">
                <label class="field-label">No. Pekerja</label>
                <input type="text" name="no_pekerja" id="f_nopek" class="form-control-custom" placeholder="Cth: MB000200">
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
    document.getElementById('modalTitle').innerHTML='<i class="bi bi-plus-circle"></i> Tugaskan Admin';
    document.getElementById('f_act').value='add'; document.getElementById('f_id').value='';
    document.getElementById('f_sistem').value=''; document.getElementById('f_nama').value=''; document.getElementById('f_nopek').value='';
    document.getElementById('aModal').classList.add('show');
}
function openEdit(id,idSistem,nama,nopek){
    document.getElementById('modalTitle').innerHTML='<i class="bi bi-pencil-square"></i> Kemas Kini Tugasan';
    document.getElementById('f_act').value='edit'; document.getElementById('f_id').value=id;
    document.getElementById('f_sistem').value=idSistem; document.getElementById('f_nama').value=nama||''; document.getElementById('f_nopek').value=nopek||'';
    document.getElementById('aModal').classList.add('show');
}
function closeModal(){ document.getElementById('aModal').classList.remove('show'); }
document.getElementById('aModal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
</script>
</body>
</html>
