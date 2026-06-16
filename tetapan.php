<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/_includes.php';
requireRole('admin_it');

$db = getDB();

function slugKod($s) {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '_', $s);
    return trim($s, '_');
}

/* ---------------- Pengendali tindakan (POST → redirect) ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $tab  = $_POST['tab']  ?? 'sistem';
    $ok   = 'simpan';

    try {
        switch ($aksi) {
            case 'sistem_tambah':
                $nama = trim($_POST['nama'] ?? '');
                if ($nama !== '') {
                    $maxU = (int)$db->query("SELECT COALESCE(MAX(urutan),0) FROM sistem")->fetchColumn();
                    $st = $db->prepare("INSERT INTO sistem (nama, aktif, urutan) VALUES (?,1,?)");
                    $st->execute([$nama, $maxU + 1]);
                    $ok = 'tambah';
                }
                break;

            case 'sistem_kemaskini':
                $id   = (int)($_POST['id'] ?? 0);
                $nama = trim($_POST['nama'] ?? '');
                if ($id && $nama !== '') {
                    $db->prepare("UPDATE sistem SET nama=? WHERE id=?")->execute([$nama, $id]);
                }
                break;

            case 'sistem_toggle':
                $id = (int)($_POST['id'] ?? 0);
                if ($id) $db->prepare("UPDATE sistem SET aktif = 1 - aktif WHERE id=?")->execute([$id]);
                break;

            case 'fungsi_tambah':
                $nama = trim($_POST['nama'] ?? '');
                $kod  = slugKod($_POST['kod'] ?? '') ?: slugKod($nama);
                if ($nama !== '' && $kod !== '') {
                    $wujud = $db->prepare("SELECT COUNT(*) FROM fungsi WHERE kod=?");
                    $wujud->execute([$kod]);
                    if ($wujud->fetchColumn() == 0) {
                        $maxU = (int)$db->query("SELECT COALESCE(MAX(urutan),0) FROM fungsi")->fetchColumn();
                        $db->prepare("INSERT INTO fungsi (kod, nama, aktif, urutan) VALUES (?,?,1,?)")
                           ->execute([$kod, strtoupper($nama), $maxU + 1]);
                        // beri nilai lalai 0 kepada semua peranan
                        foreach (array_keys(SENARAI_PERANAN) as $role) {
                            $db->prepare("INSERT OR IGNORE INTO had_kuasa_preset (role, fungsi_kod, boleh) VALUES (?,?,0)")
                               ->execute([$role, $kod]);
                        }
                        $ok = 'tambah';
                    } else {
                        $ok = 'wujud';
                    }
                }
                break;

            case 'fungsi_kemaskini':
                $id   = (int)($_POST['id'] ?? 0);
                $nama = trim($_POST['nama'] ?? '');
                if ($id && $nama !== '') {
                    $db->prepare("UPDATE fungsi SET nama=? WHERE id=?")->execute([strtoupper($nama), $id]);
                }
                break;

            case 'fungsi_toggle':
                $id = (int)($_POST['id'] ?? 0);
                if ($id) $db->prepare("UPDATE fungsi SET aktif = 1 - aktif WHERE id=?")->execute([$id]);
                break;

            case 'preset_simpan':
                $preset = $_POST['preset'] ?? [];
                $aktifKods = array_keys(tetapanFungsiAktif());
                foreach (array_keys(SENARAI_PERANAN) as $role) {
                    foreach ($aktifKods as $kod) {
                        $boleh = isset($preset[$role][$kod]) ? 1 : 0;
                        $db->prepare("INSERT INTO had_kuasa_preset (role, fungsi_kod, boleh) VALUES (?,?,?)
                                      ON CONFLICT(role, fungsi_kod) DO UPDATE SET boleh=excluded.boleh")
                           ->execute([$role, $kod, $boleh]);
                    }
                }
                break;
        }
    } catch (Throwable $e) {
        $ok = 'ralat';
    }

    header("Location: tetapan.php?tab={$tab}&ok={$ok}");
    exit;
}

/* ---------------- Data untuk paparan ---------------- */
$sistemRows = $db->query("SELECT * FROM sistem ORDER BY urutan, id")->fetchAll();
$fungsiRows = $db->query("SELECT * FROM fungsi ORDER BY urutan, id")->fetchAll();
$fungsiAktif = tetapanFungsiAktif();           // [kod => label]
$presetNow   = tetapanHadKuasa();              // [role => [kod => 0/1]]

$tabAktif = $_GET['tab'] ?? 'sistem';
$okMsg = match($_GET['ok'] ?? '') {
    'tambah' => 'Rekod baharu berjaya ditambah.',
    'simpan' => 'Perubahan berjaya disimpan.',
    'wujud'  => null,
    'ralat'  => null,
    default  => null,
};
$errMsg = match($_GET['ok'] ?? '') {
    'wujud' => 'Kod fungsi tersebut sudah wujud.',
    'ralat' => 'Ralat semasa menyimpan. Sila cuba lagi.',
    default => null,
};
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Tetapan Sistem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php sharedCSS(); ?>
    <style>
        .dash-tabs{display:flex;gap:4px;border-bottom:2px solid #ccfbf1;margin-bottom:20px;flex-wrap:wrap}
        .dash-tab{background:none;border:none;border-bottom:3px solid transparent;padding:10px 20px;font-size:0.85rem;font-weight:600;color:#6b7280;cursor:pointer;margin-bottom:-2px;transition:all 0.15s;text-decoration:none}
        .dash-tab:hover{color:#115e59}
        .dash-tab.active{color:#115e59;border-bottom-color:#0d9488}
        .dash-tab-pane{display:none}
        .dash-tab-pane.active{display:block}
        .inline-form{display:flex;gap:8px;align-items:center}
        .mini-input{border:1px solid #ccfbf1;border-radius:8px;padding:7px 12px;font-size:0.85rem;outline:none;background:#fff;color:#374151}
        .mini-input:focus{border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,0.12)}
        .preset-table input[type=checkbox]{width:17px;height:17px;cursor:pointer;accent-color:#0d9488}
    </style>
</head>
<body>
<?php if ($okMsg)  toastHTML($okMsg, 'success'); ?>
<?php if ($errMsg) toastHTML($errMsg, 'error');  ?>
<?php sidebarHTML($_SESSION['nama'] ?? $_SESSION['username'], 'Admin IT', [
    ['href'=>'dashboard_admin_it.php','icon'=>'bi-grid-1x2','label'=>'Dashboard','active'=>false],
    ['href'=>'tetapan.php','icon'=>'bi-gear','label'=>'Tetapan Sistem','active'=>true],
]); ?>
<div class="main-content">
    <div class="page-header">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard_admin_it.php"><i class="bi bi-house me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item active">Tetapan Sistem</li>
        </ol></nav>
        <h4>Tetapan Sistem</h4>
        <p>Urus senarai sistem dan jenis had kuasa. Hanya Admin IT yang dibenarkan.</p>
    </div>

    <div class="dash-tabs">
        <button class="dash-tab <?= $tabAktif==='sistem'?'active':'' ?>" onclick="switchTab('sistem')"><i class="bi bi-hdd-stack me-1"></i>Senarai Sistem <span class="badge-count"><?= count($sistemRows) ?></span></button>
        <button class="dash-tab <?= $tabAktif==='fungsi'?'active':'' ?>" onclick="switchTab('fungsi')"><i class="bi bi-sliders me-1"></i>Jenis Had Kuasa <span class="badge-count"><?= count($fungsiRows) ?></span></button>
        <button class="dash-tab <?= $tabAktif==='preset'?'active':'' ?>" onclick="switchTab('preset')"><i class="bi bi-grid-3x3 me-1"></i>Had Kuasa Lalai</button>
    </div>

    <!-- ============ TAB: SENARAI SISTEM ============ -->
    <div id="pane-sistem" class="dash-tab-pane <?= $tabAktif==='sistem'?'active':'' ?>">
        <div class="form-card mb-4">
            <div class="form-section-header"><span class="sec-label"><i class="bi bi-plus-lg"></i></span><span class="sec-title">Tambah Sistem Baharu</span></div>
            <div class="form-section-body">
                <form method="POST" class="inline-form" style="flex-wrap:wrap">
                    <input type="hidden" name="aksi" value="sistem_tambah">
                    <input type="hidden" name="tab" value="sistem">
                    <input type="text" name="nama" class="form-control-custom" style="max-width:420px" placeholder="Nama sistem baharu" required>
                    <button type="submit" class="btn-primary-dark"><i class="bi bi-plus-circle"></i> Tambah Sistem</button>
                </form>
            </div>
        </div>

        <div class="table-card">
            <div class="table-card-header"><h6>Senarai Sistem</h6><span class="badge-count"><?= count($sistemRows) ?> sistem</span></div>
            <table class="data-table">
                <thead><tr><th style="padding-left:24px;width:60px">Bil</th><th>Nama Sistem</th><th style="width:110px">Status</th><th style="width:300px">Tindakan</th></tr></thead>
                <tbody>
                <?php if (empty($sistemRows)): ?>
                    <tr><td colspan="4"><div class="empty-state"><i class="bi bi-hdd-stack"></i>Tiada sistem.</div></td></tr>
                <?php else: foreach ($sistemRows as $i => $s): ?>
                    <tr>
                        <td style="padding-left:24px;color:#9ca3af;font-size:0.82rem"><?= $i+1 ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="aksi" value="sistem_kemaskini">
                                <input type="hidden" name="tab" value="sistem">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <input type="text" name="nama" class="mini-input" style="min-width:340px" value="<?= htmlspecialchars($s['nama']) ?>">
                                <button type="submit" class="btn-secondary-soft" style="padding:6px 12px;font-size:0.78rem" title="Simpan nama"><i class="bi bi-save"></i></button>
                            </form>
                        </td>
                        <td>
                            <?php if ($s['aktif']): ?><span class="badge-status badge-success">Aktif</span>
                            <?php else: ?><span class="badge-status badge-secondary">Nyahaktif</span><?php endif; ?>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="aksi" value="sistem_toggle">
                                <input type="hidden" name="tab" value="sistem">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <?php if ($s['aktif']): ?>
                                    <button type="submit" class="btn-danger-soft" style="padding:6px 14px;font-size:0.78rem"><i class="bi bi-slash-circle"></i> Nyahaktif</button>
                                <?php else: ?>
                                    <button type="submit" class="btn-success-soft" style="padding:6px 14px;font-size:0.78rem"><i class="bi bi-check-circle"></i> Aktifkan</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============ TAB: JENIS HAD KUASA ============ -->
    <div id="pane-fungsi" class="dash-tab-pane <?= $tabAktif==='fungsi'?'active':'' ?>">
        <div class="form-card mb-4">
            <div class="form-section-header"><span class="sec-label"><i class="bi bi-plus-lg"></i></span><span class="sec-title">Tambah Jenis Had Kuasa Baharu</span></div>
            <div class="form-section-body">
                <form method="POST" class="inline-form" style="flex-wrap:wrap">
                    <input type="hidden" name="aksi" value="fungsi_tambah">
                    <input type="hidden" name="tab" value="fungsi">
                    <input type="text" name="nama" class="form-control-custom" style="max-width:280px" placeholder="Nama (cth: PELULUS)" required>
                    <input type="text" name="kod" class="form-control-custom" style="max-width:220px" placeholder="Kod (auto jika kosong)">
                    <button type="submit" class="btn-primary-dark"><i class="bi bi-plus-circle"></i> Tambah Had Kuasa</button>
                </form>
                <p class="field-hint" style="margin-top:10px">Kod ialah pengenal unik yang disimpan dalam permohonan. Kod tidak boleh diubah selepas dicipta.</p>
            </div>
        </div>

        <div class="table-card">
            <div class="table-card-header"><h6>Jenis Had Kuasa</h6><span class="badge-count"><?= count($fungsiRows) ?> jenis</span></div>
            <table class="data-table">
                <thead><tr><th style="padding-left:24px">Kod</th><th>Nama (Label)</th><th style="width:110px">Status</th><th style="width:300px">Tindakan</th></tr></thead>
                <tbody>
                <?php if (empty($fungsiRows)): ?>
                    <tr><td colspan="4"><div class="empty-state"><i class="bi bi-sliders"></i>Tiada jenis had kuasa.</div></td></tr>
                <?php else: foreach ($fungsiRows as $f): ?>
                    <tr>
                        <td style="padding-left:24px"><code style="color:#115e59;background:#f0fdfa;padding:2px 8px;border-radius:6px;font-size:0.8rem"><?= htmlspecialchars($f['kod']) ?></code></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="aksi" value="fungsi_kemaskini">
                                <input type="hidden" name="tab" value="fungsi">
                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                <input type="text" name="nama" class="mini-input" style="min-width:260px" value="<?= htmlspecialchars($f['nama']) ?>">
                                <button type="submit" class="btn-secondary-soft" style="padding:6px 12px;font-size:0.78rem" title="Simpan label"><i class="bi bi-save"></i></button>
                            </form>
                        </td>
                        <td>
                            <?php if ($f['aktif']): ?><span class="badge-status badge-success">Aktif</span>
                            <?php else: ?><span class="badge-status badge-secondary">Nyahaktif</span><?php endif; ?>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="aksi" value="fungsi_toggle">
                                <input type="hidden" name="tab" value="fungsi">
                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                <?php if ($f['aktif']): ?>
                                    <button type="submit" class="btn-danger-soft" style="padding:6px 14px;font-size:0.78rem"><i class="bi bi-slash-circle"></i> Nyahaktif</button>
                                <?php else: ?>
                                    <button type="submit" class="btn-success-soft" style="padding:6px 14px;font-size:0.78rem"><i class="bi bi-check-circle"></i> Aktifkan</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============ TAB: HAD KUASA LALAI (PRESET) ============ -->
    <div id="pane-preset" class="dash-tab-pane <?= $tabAktif==='preset'?'active':'' ?>">
        <div class="table-card">
            <div class="table-card-header"><h6>Had Kuasa Lalai Mengikut Peranan</h6></div>
            <form method="POST">
                <input type="hidden" name="aksi" value="preset_simpan">
                <input type="hidden" name="tab" value="preset">
                <div style="overflow-x:auto">
                <table class="sistem-table preset-table">
                    <thead>
                        <tr>
                            <th style="min-width:160px">Peranan</th>
                            <?php foreach ($fungsiAktif as $kod => $label): ?>
                            <th style="text-align:center;white-space:nowrap"><?= htmlspecialchars($label) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach (SENARAI_PERANAN as $role => $roleLabel): ?>
                        <tr>
                            <td style="font-weight:600;color:#115e59"><?= htmlspecialchars($roleLabel) ?></td>
                            <?php foreach ($fungsiAktif as $kod => $label): ?>
                            <td class="check-col" style="text-align:center">
                                <input type="checkbox" name="preset[<?= $role ?>][<?= $kod ?>]" value="1" <?= !empty($presetNow[$role][$kod]) ? 'checked' : '' ?>>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <div class="action-row">
                    <button type="submit" class="btn-primary-dark"><i class="bi bi-save"></i> Simpan Had Kuasa Lalai</button>
                    <span style="font-size:0.8rem;color:#6b7280">Nilai ini menjadi cadangan automatik dalam borang permohonan apabila peranan dipilih.</span>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function switchTab(name) {
    document.querySelectorAll('.dash-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.dash-tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelector('#pane-' + name).classList.add('active');
    event.currentTarget.classList.add('active');
    history.replaceState(null, '', 'tetapan.php?tab=' + name);
}
</script>
</body></html>
