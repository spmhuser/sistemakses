<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('pemohon');

$db   = getDB();
$user = $db->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$_SESSION['user_id']]);
$u = $user->fetch();

// Sumber rasmi data kakitangan: Sistem Gaji (rujuk ikut No. Kakitangan pengguna)
$gs = $db->prepare("SELECT * FROM gaji WHERE no_kakitangan = ?");
$gs->execute([$u['no_kakitangan'] ?? '']);
$g = $gs->fetch();
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
        .radio-card{position:relative;border:2px solid #DCE6F2;border-radius:14px;padding:18px 14px;cursor:pointer;transition:all 0.18s;display:flex;flex-direction:column;align-items:center;gap:9px;text-align:center;background:#fff;}
        .radio-card:hover{border-color:#3A86D0;background:#F4F8FD;transform:translateY(-2px);box-shadow:0 5px 14px rgba(46,115,216,0.12);}
        .radio-card.selected{border-color:#2E73D8;background:#E6EFFA;box-shadow:0 6px 16px rgba(46,115,216,0.20);}
        .radio-card input{position:absolute;opacity:0;pointer-events:none;}
        .radio-card .rc-ic{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:21px;background:#EEF3FA;color:#2E73D8;transition:all 0.18s;}
        .radio-card.selected .rc-ic{background:linear-gradient(135deg,#2E73D8,#1FBCD4);color:#fff;}
        .radio-card .rc-txt{font-size:0.92rem;font-weight:700;color:#3A4658;}
        .radio-card .rc-check{position:absolute;top:9px;right:10px;color:#2E73D8;font-size:1.15rem;opacity:0;transition:opacity 0.18s;}
        .radio-card.selected .rc-check{opacity:1;}
        .form-control-custom[readonly]{background:#EEF3FA;color:#3A4658;cursor:not-allowed;border-style:dashed;}
        .locked-note{font-size:0.84rem;color:#2862C0;background:#E6EFFA;border:1px solid #BFD2EC;border-radius:8px;padding:9px 13px;display:flex;align-items:center;gap:8px;margin-bottom:16px;font-weight:600;}
        .block-card{text-align:center;padding:46px 30px;}
        .block-icon{width:84px;height:84px;border-radius:50%;background:#FFE2E0;color:#E23B36;display:flex;align-items:center;justify-content:center;font-size:42px;margin:0 auto 18px;}
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

    <?php if (!$g): ?>
    <!-- SEKATAN: No. Kakitangan tiada dalam Sistem Gaji -->
    <div class="form-card mb-4">
        <div class="form-section-body block-card">
            <div class="block-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <h4 style="font-weight:800;color:#1E3A5F;margin-bottom:10px">Data Belum Dalam Sistem Gaji</h4>
            <p style="color:#5B6675;max-width:540px;margin:0 auto 22px;line-height:1.65">
                No. Kakitangan anda (<b><?= htmlspecialchars($u['no_kakitangan'] ?? '-') ?></b>) belum didaftarkan dalam <b>Sistem Gaji</b>.
                Sila maklum <b>Unit Gaji</b> untuk mendaftar data anda terlebih dahulu sebelum boleh memohon capaian sistem.
            </p>
            <a href="dashboard_pemohon.php" class="btn-secondary-soft"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </div>
    <?php else: ?>
    <form method="POST" action="submit_permohonan.php" id="mainForm">

    <!-- SECTION A -->
    <div class="form-card mb-4">
        <div class="form-section-header">
            <span class="sec-label">A</span>
            <span class="sec-title">Maklumat Kakitangan</span>
        </div>
        <div class="form-section-body">
            <div class="locked-note"><i class="bi bi-shield-lock"></i> Maklumat diambil automatik dari <b>Sistem Gaji</b> dan tidak boleh diubah.</div>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="field-label">Nama</label>
                    <input type="text" name="nama" class="form-control-custom" readonly value="<?= htmlspecialchars($g['nama'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="field-label">No. Kakitangan</label>
                    <input type="text" name="no_kakitangan" class="form-control-custom" readonly value="<?= htmlspecialchars($g['no_kakitangan'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="field-label">Jawatan</label>
                    <input type="text" name="jawatan" class="form-control-custom" readonly value="<?= htmlspecialchars($g['jawatan'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="field-label">Gred Jawatan</label>
                    <input type="text" name="gred_jawatan" class="form-control-custom" readonly value="<?= htmlspecialchars($g['gred_jawatan'] ?? '') ?>">
                </div>
                <div class="col-md-8">
                    <label class="field-label">Jabatan</label>
                    <input type="text" name="jabatan" class="form-control-custom" readonly value="<?= htmlspecialchars($g['jabatan'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="field-label">Telefon</label>
                    <input type="text" name="telefon" class="form-control-custom" readonly value="<?= htmlspecialchars($g['telefon'] ?? '') ?>">
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
            <label class="field-label mb-3">Tujuan Permohonan <span class="req">*</span> <span style="font-size:0.8rem;font-weight:500;color:#6B6675">(pilih satu)</span></label>
            <div class="row g-3 mb-4">
                <?php foreach([
                    'baru'       => ['Permohonan Baru',     'bi-plus-circle'],
                    'kemaskini'  => ['Kemaskini Capaian',   'bi-pencil-square'],
                    'pembatalan' => ['Pembatalan Capaian',  'bi-x-circle'],
                ] as $val=>$info): ?>
                <div class="col-md-4">
                    <label class="radio-card" id="rc_<?=$val?>">
                        <input type="radio" name="tujuan" value="<?=$val?>" required onchange="toggleSistem(this)">
                        <i class="bi bi-check-circle-fill rc-check"></i>
                        <span class="rc-ic"><i class="bi <?=$info[1]?>"></i></span>
                        <span class="rc-txt"><?=$info[0]?></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="sistemSection">
                <p style="font-size:0.92rem;color:#6b7280;margin-bottom:12px">
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
                    <?php foreach (getSenaraiSistem(true) as $bil => $nama): ?>
                        <tr id="row-<?=$bil?>">
                            <td class="check-col">
                                <input type="checkbox" name="sistem[]" value="<?=$bil?>"
                                       onchange="toggleRow(this,<?=$bil?>)">
                            </td>
                            <td style="color:#6E6470;font-size:0.9rem;text-align:center"><?=$bil?></td>
                            <td><?= htmlspecialchars($nama) ?></td>
                            <td>
                                <select name="peranan_sistem_<?=$bil?>" id="ps-<?=$bil?>"
                                        disabled onchange="updateHadKuasa(<?=$bil?>)"
                                        style="border:1px solid #E6EFFA;border-radius:6px;padding:5px 8px;font-size:0.9rem;width:100%;outline:none;background:#f9f9f9;color:#6E6470;cursor:not-allowed">
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
                                           style="display:inline-flex;align-items:center;gap:3px;font-size:0.82rem;padding:2px 6px;border-radius:12px;border:1px solid #e5e7eb;background:#f9f9f9;color:#6E6470;cursor:not-allowed;white-space:nowrap">
                                        <input type="checkbox" name="had_kuasa_<?=$bil?>[<?=$f?>]" value="1"
                                               id="hk-<?=$bil?>-<?=$f?>" disabled style="accent-color:#3A86D0">
                                        <?= fungsiLabel($f) ?>
                                    </label>
                                <?php endforeach; ?>
                                </div>
                            </td>
                            <td><input type="text" name="catatan_<?=$bil?>" id="cat-<?=$bil?>"
                                       placeholder="Catatan (jika ada)" disabled
                                       style="background:#f9f9f9;color:#6E6470;cursor:not-allowed"></td>
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
            <div style="background:#FFFFFF;border:1px solid #E6EFFA;border-radius:10px;padding:16px;margin-bottom:20px;font-size:0.875rem;color:#374151">
                Saya dengan ini mengesahkan maklumat yang diberikan di atas adalah benar dan mengaku akan bertanggungjawab terhadap permohonan ini.
            </div>
        </div>
        <div class="action-row">
            <button type="submit" class="btn-primary-dark"><i class="bi bi-send"></i> Hantar Permohonan</button>
            <a href="dashboard_pemohon.php" class="btn-secondary-soft"><i class="bi bi-x-lg"></i> Batal</a>
        </div>
    </div>

    </form>
    <?php endif; ?>
</div>

<?php if ($g): ?>
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
        lbl.style.cssText = 'display:inline-flex;align-items:center;gap:3px;font-size:0.82rem;padding:2px 6px;border-radius:12px;border:1px solid #9DBCE0;background:#fff;color:#374151;cursor:default;white-space:nowrap';
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
        lbl.style.cssText = 'display:inline-flex;align-items:center;gap:3px;font-size:0.82rem;padding:2px 6px;border-radius:12px;border:1px solid #e5e7eb;background:#f9f9f9;color:#6E6470;cursor:not-allowed;white-space:nowrap';
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
const _ct = document.querySelector('input[name="tujuan"]:checked');
if (_ct) _ct.closest('.radio-card').classList.add('selected');

function toggleRow(chk, bil) {
    const sel = document.getElementById('ps-' + bil);
    const cat = document.getElementById('cat-' + bil);
    const row = document.getElementById('row-' + bil);
    if (chk.checked) {
        sel.disabled = false;
        sel.required = true;
        sel.style.cssText = 'border:1px solid #9DBCE0;border-radius:6px;padding:5px 8px;font-size:0.9rem;width:100%;outline:none;background:#fff;color:#374151;cursor:default';
        cat.disabled = false;
        cat.style.cssText = 'background:#fff;color:#374151;cursor:default';
        row.style.background = '#FFFFFF';
        enableHadKuasa(bil);
    } else {
        sel.disabled = true;
        sel.required = false;
        sel.value = '';
        sel.style.cssText = 'border:1px solid #E6EFFA;border-radius:6px;padding:5px 8px;font-size:0.9rem;width:100%;outline:none;background:#f9f9f9;color:#6E6470;cursor:not-allowed';
        cat.value = '';
        cat.disabled = true;
        cat.style.cssText = 'background:#f9f9f9;color:#6E6470;cursor:not-allowed';
        row.style.background = '';
        disableHadKuasa(bil);
    }
}
</script>
<?php endif; ?>
</body></html>
