<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('pemohon');

$db   = getDB();
$user = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$_SESSION['user_id']]);
$u = $user->fetch();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Borang Permohonan Capaian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .radio-card{border:2px solid #e5e7eb;border-radius:10px;padding:12px 16px;cursor:pointer;transition:all 0.15s;display:flex;align-items:center;gap:10px;}
        .radio-card:hover{border-color:#0d9488;background:#f0fdfa;}
        .radio-card.selected{border-color:#0d9488;background:#ccfbf1;}
        .radio-card input{accent-color:#0d9488;}
    </style>
</head>
<body>
<?php sidebarHTML($_SESSION['nama'] ?? $_SESSION['username'], 'Pemohon', [
    ['href'=>'dashboard_pemohon.php', 'icon'=>'bi-grid-1x2',   'label'=>'Dashboard',       'active'=>false],
    ['href'=>'borang_permohonan.php', 'icon'=>'bi-plus-circle', 'label'=>'Buat Permohonan', 'active'=>true],
]); ?>
<div class="main-content">
    <div class="page-header">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard_pemohon.php"><i class="bi bi-house me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Borang Permohonan</li>
            </ol>
        </nav>
        <h4>Borang Capaian Sistem</h4>
        <p>KOD BORANG: 119/D35 &nbsp;|&nbsp; Isi semua maklumat dengan tepat dan lengkap</p>
    </div>

    <form method="POST" action="submit_permohonan.php" id="mainForm">

    <!-- SECTION A -->
    <div class="form-card mb-4">
        <div class="form-section-header">
            <span class="sec-label">A</span>
            <span class="sec-title">Maklumat Kakitangan</span>
        </div>
        <div class="form-section-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="field-label">Nama <span class="req">*</span></label>
                    <input type="text" name="nama" class="form-control-custom" required value="<?= htmlspecialchars($u['nama'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="field-label">No. Kakitangan <span class="req">*</span></label>
                    <input type="text" name="no_kakitangan" class="form-control-custom" required value="<?= htmlspecialchars($u['no_kakitangan'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="field-label">Jawatan <span class="req">*</span></label>
                    <input type="text" name="jawatan" class="form-control-custom" required value="<?= htmlspecialchars($u['jawatan'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="field-label">Gred Jawatan <span class="req">*</span></label>
                    <input type="text" name="gred_jawatan" class="form-control-custom" required value="<?= htmlspecialchars($u['gred_jawatan'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="field-label">Jabatan <span class="req">*</span></label>
                    <input type="text" name="jabatan" class="form-control-custom" required value="<?= htmlspecialchars($u['jabatan'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="field-label">Telefon <span class="req">*</span></label>
                    <input type="text" name="telefon" class="form-control-custom" required value="<?= htmlspecialchars($u['telefon'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION B -->
    <div class="form-card mb-4">
        <div class="form-section-header">
            <span class="sec-label">B</span>
            <span class="sec-title">Maklumat Sistem</span>
        </div>
        <div class="form-section-body">
            <label class="field-label mb-3">Tujuan Permohonan <span class="req">*</span></label>
            <div class="row g-2 mb-4">
                <?php foreach(['baru'=>'Permohonan Baru','kemaskini'=>'Kemaskini Capaian','pembatalan'=>'Pembatalan Capaian'] as $val=>$lbl): ?>
                <div class="col-md-4">
                    <label class="radio-card" id="rc_<?=$val?>">
                        <input type="radio" name="tujuan" value="<?=$val?>" <?=$val==='baru'?'checked':''?> onchange="toggleSistem(this)">
                        <span style="font-size:0.875rem;font-weight:600"><?=$lbl?></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="sistemSection">
                <p style="font-size:0.82rem;color:#6b7280;margin-bottom:12px">
                    <i class="bi bi-info-circle me-1 text-primary"></i>
                    Tandakan (✓) sistem yang dipohon dan nyatakan catatan jika perlu.
                    <span id="pembatalanNote" style="display:none;color:#dc2626"> (Tidak perlu diisi bagi permohonan pembatalan capaian)</span>
                </p>
                <div style="overflow-x:auto">
                <table class="sistem-table">
                    <thead>
                        <tr>
                            <th class="check-col">Pilih</th>
                            <th style="width:40px">Bil</th>
                            <th>Senarai Sistem</th>
                            <th style="min-width:160px">Peranan <span style="color:#dc2626;font-size:0.7rem">(pilih 1)</span></th>
                            <th style="min-width:300px">Had Kuasa</th>
                            <th style="min-width:180px">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach (SENARAI_SISTEM as $bil => $nama): ?>
                        <tr id="row-<?=$bil?>">
                            <td class="check-col">
                                <input type="checkbox" name="sistem[]" value="<?=$bil?>"
                                       onchange="toggleRow(this,<?=$bil?>)">
                            </td>
                            <td style="color:#9ca3af;font-size:0.8rem;text-align:center"><?=$bil?></td>
                            <td><?= htmlspecialchars($nama) ?></td>
                            <td>
                                <select name="peranan_sistem_<?=$bil?>" id="ps-<?=$bil?>"
                                        disabled onchange="updateHadKuasa(<?=$bil?>)"
                                        style="border:1px solid #ccfbf1;border-radius:6px;padding:5px 8px;font-size:0.8rem;width:100%;outline:none;background:#f9f9f9;color:#9ca3af;cursor:not-allowed">
                                    <option value="">-- Pilih Peranan --</option>
                                    <?php foreach(SENARAI_PERANAN as $key=>$label): ?>
                                    <option value="<?=$key?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td id="hk-cell-<?=$bil?>">
                                <div style="display:flex;flex-wrap:wrap;gap:4px">
                                <?php foreach(SENARAI_FUNGSI as $f): ?>
                                    <label id="hklbl-<?=$bil?>-<?=$f?>"
                                           style="display:inline-flex;align-items:center;gap:3px;font-size:0.72rem;padding:2px 6px;border-radius:12px;border:1px solid #e5e7eb;background:#f9f9f9;color:#9ca3af;cursor:not-allowed;white-space:nowrap">
                                        <input type="checkbox" name="had_kuasa_<?=$bil?>[<?=$f?>]" value="1"
                                               id="hk-<?=$bil?>-<?=$f?>" disabled style="accent-color:#0d9488">
                                        <?= fungsiLabel($f) ?>
                                    </label>
                                <?php endforeach; ?>
                                </div>
                            </td>
                            <td><input type="text" name="catatan_<?=$bil?>" id="cat-<?=$bil?>"
                                       placeholder="Catatan (jika ada)" disabled
                                       style="background:#f9f9f9;color:#9ca3af;cursor:not-allowed"></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION C -->
    <div class="form-card mb-4">
        <div class="form-section-header">
            <span class="sec-label">C</span>
            <span class="sec-title">Pengesahan Pemohon</span>
        </div>
        <div class="form-section-body">
            <div style="background:#f0fdfa;border:1px solid #ccfbf1;border-radius:10px;padding:16px;margin-bottom:20px;font-size:0.875rem;color:#374151">
                Saya dengan ini mengesahkan maklumat yang diberikan di atas adalah benar dan mengaku akan bertanggungjawab terhadap permohonan ini.
            </div>
        </div>
        <div class="action-row">
            <button type="submit" class="btn-primary-dark"><i class="bi bi-send"></i> Hantar Permohonan</button>
            <a href="dashboard_pemohon.php" class="btn-secondary-soft"><i class="bi bi-x-lg"></i> Batal</a>
        </div>
    </div>

    </form>
</div>

<script>
// Had kuasa preset dari config.php
const hadKuasa = <?= json_encode(HAD_KUASA) ?>;
const senaraiFungsi = <?= json_encode(SENARAI_FUNGSI) ?>;

function enableHadKuasa(bil) {
    senaraiFungsi.forEach(f => {
        const cb  = document.getElementById('hk-' + bil + '-' + f);
        const lbl = document.getElementById('hklbl-' + bil + '-' + f);
        if (!cb) return;
        cb.disabled = false;
        lbl.style.cssText = 'display:inline-flex;align-items:center;gap:3px;font-size:0.72rem;padding:2px 6px;border-radius:12px;border:1px solid #5eead4;background:#fff;color:#374151;cursor:default;white-space:nowrap';
    });
    updateHadKuasa(bil);
}

function disableHadKuasa(bil) {
    senaraiFungsi.forEach(f => {
        const cb  = document.getElementById('hk-' + bil + '-' + f);
        const lbl = document.getElementById('hklbl-' + bil + '-' + f);
        if (!cb) return;
        cb.disabled = true;
        cb.checked  = false;
        lbl.style.cssText = 'display:inline-flex;align-items:center;gap:3px;font-size:0.72rem;padding:2px 6px;border-radius:12px;border:1px solid #e5e7eb;background:#f9f9f9;color:#9ca3af;cursor:not-allowed;white-space:nowrap';
    });
}

function updateHadKuasa(bil) {
    const sel = document.getElementById('ps-' + bil);
    if (!sel || !sel.value) return;
    const kuasa = hadKuasa[sel.value] || {};
    senaraiFungsi.forEach(f => {
        const cb = document.getElementById('hk-' + bil + '-' + f);
        if (cb) cb.checked = kuasa[f] === 1;
    });
}
function toggleSistem(el) {
    document.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
    el.closest('.radio-card').classList.add('selected');
    const note = document.getElementById('pembatalanNote');
    if (el.value === 'pembatalan') note.style.display = 'inline';
    else note.style.display = 'none';
}
document.querySelector('input[name="tujuan"]:checked').closest('.radio-card').classList.add('selected');

function toggleRow(chk, bil) {
    const sel = document.getElementById('ps-' + bil);
    const cat = document.getElementById('cat-' + bil);
    const row = document.getElementById('row-' + bil);
    if (chk.checked) {
        sel.disabled = false;
        sel.required = true;
        sel.style.cssText = 'border:1px solid #5eead4;border-radius:6px;padding:5px 8px;font-size:0.8rem;width:100%;outline:none;background:#fff;color:#374151;cursor:default';
        cat.disabled = false;
        cat.style.cssText = 'background:#fff;color:#374151;cursor:default';
        row.style.background = '#f0fdfa';
        enableHadKuasa(bil);
    } else {
        sel.disabled = true;
        sel.required = false;
        sel.value = '';
        sel.style.cssText = 'border:1px solid #ccfbf1;border-radius:6px;padding:5px 8px;font-size:0.8rem;width:100%;outline:none;background:#f9f9f9;color:#9ca3af;cursor:not-allowed';
        cat.value = '';
        cat.disabled = true;
        cat.style.cssText = 'background:#f9f9f9;color:#9ca3af;cursor:not-allowed';
        row.style.background = '';
        disableHadKuasa(bil);
    }
}
</script>
</body></html>
