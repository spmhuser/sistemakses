<?php
/**
 * JARVIS — Q&A engine scoped to sistemakses workspace + live DB (role-aware).
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/jarvis_config.php';
require_once __DIR__ . '/jarvis_llm.php';

class JarvisEngine {
    private string $root;
    private array $session;

    public function __construct(array $session) {
        $this->root    = realpath(__DIR__);
        $this->session = $session;
    }

    /**
     * @param array<int, array{role:string, content:string}> $history
     */
    public function answer(string $question, array $history = []): array {
        $q = mb_strtolower(trim($question));
        if ($q === '') {
            return $this->resp('Hmm, mesej kosong je tu. Apa yang anda nak tanya?', 'help');
        }

        $action = $this->tryAction($q);
        if ($action) {
            return $action;
        }

        $ref = $this->extractReference($q);
        if ($ref) {
            $hit = $this->lookupPermohonan($ref);
            if ($hit) {
                return $hit;
            }
        }

        if (JarvisLLM::isAvailable()) {
            $llm = $this->answerWithLLM($question, $history);
            if ($llm) {
                return $llm;
            }
        }

        return $this->answerRuleBased($question);
    }

    /**
     * @param array<int, array{role:string, content:string}> $history
     */
    private function answerWithLLM(string $question, array $history): ?array {
        $context = $this->buildKnowledgeContext($question);
        $result  = JarvisLLM::chat($question, $context, $history, $this->session);

        if (!$result['ok']) {
            return null;
        }

        return $this->resp(
            $result['answer'],
            'llm:' . ($result['provider'] ?? 'ai')
        );
    }

    private function buildKnowledgeContext(string $question): string {
        $q      = mb_strtolower(trim($question));
        $terms  = $this->extractSearchTerms($question);
        $chunks = [];

        $chunks[] = "=== PROFIL PENGGUNA ===\n" . $this->aboutCurrentUser();
        $chunks[] = "=== ALIRAN KELULUSAN ===\n" . $this->aboutWorkflow();
        $chunks[] = "=== STATUS PERMOHONAN ===\n" . $this->aboutStatuses();
        $chunks[] = "=== PERMOHONAN (DATA LIVE) ===\n" . $this->aboutPermohonan($q);
        $chunks[] = "=== SENARAI SISTEM ===\n" . $this->aboutSystems($q);
        $chunks[] = "=== AKAUN PENGGUNA ===\n" . $this->aboutUsers();
        $chunks[] = "=== PERANAN & HAD KUASA ===\n" . $this->aboutRoles();
        $chunks[] = "=== HALAMAN APLIKASI ===\n" . $this->aboutPages();

        if ($sys = $this->searchSystems($q, $terms)) {
            $chunks[] = "=== CARIAN SISTEM ===\n" . $sys;
        }
        if ($usr = $this->searchUsers($terms)) {
            $chunks[] = "=== CARIAN PENGGUNA ===\n" . $usr;
        }
        $permAction = null;
        if ($perm = $this->searchPermohonanRecords($q, $terms, $permAction)) {
            $chunks[] = "=== CARIAN PERMOHONAN ===\n" . $perm['text'];
        }
        if ($know = $this->searchKnowledge($q, $terms)) {
            $chunks[] = "=== MAKLUMAT BERKAITAN ===\n" . $know;
        }
        if ($files = $this->searchFilesContent($terms)) {
            $chunks[] = "=== FAIL PROJEK ===\n" . $files;
        }

        $chunks[] = "=== DATABASE ===\n" . $this->aboutDatabase();

        $text = implode("\n\n", array_unique(array_filter($chunks)));
        if (mb_strlen($text) > 12000) {
            $text = mb_substr($text, 0, 12000) . "\n\n[... konteks dipendekkan ...]";
        }
        return $text;
    }

    private function answerRuleBased(string $question): array {
        $q = mb_strtolower(trim($question));

        if ($this->isPureGreeting($q)) {
            return $this->resp($this->greeting(), 'help');
        }

        if ($this->match($q, ['saya', 'nama saya', 'role saya', 'jabatan saya', 'siapa saya', 'profil saya'])) {
            return $this->resp($this->aboutCurrentUser(), 'session');
        }

        if ($this->match($q, ['akaun', 'login', 'username', 'password', 'kata laluan', 'demo', 'pengguna', 'user'])) {
            return $this->resp($this->aboutUsers(), 'users');
        }

        if ($this->match($q, ['aliran', 'workflow', 'proses', 'kelulusan', 'flow', 'langkah', 'tindakan', 'siapa lulus', 'macam mana', 'bagaimana'])) {
            return $this->resp($this->aboutWorkflow(), 'workflow');
        }

        if ($this->match($q, ['status', 'menunggu', 'diluluskan', 'ditolak', 'akses diberikan'])) {
            return $this->resp($this->aboutStatuses(), 'status');
        }

        if ($this->match($q, ['permohonan', 'borang', 'rujukan', 'permohonan saya', 'senarai permohonan'])) {
            return $this->resp($this->aboutPermohonan($q), 'database');
        }

        if ($this->match($q, ['sistem', 'senarai sistem', 'capaian', 'spb', 'kehadiran', 'cuti', 'esurat', 'speedbiz'])) {
            return $this->resp($this->aboutSystems($q), 'systems');
        }

        if ($this->match($q, ['peranan', 'role', 'fungsi', 'had kuasa', 'penyedia', 'pelulus', 'pic'])) {
            return $this->resp($this->aboutRoles(), 'roles');
        }

        if ($this->match($q, ['database', 'mysql', 'jadual', 'table', 'setup', 'db'])) {
            return $this->resp($this->aboutDatabase(), 'database');
        }

        if ($this->match($q, ['fail', 'file', 'php', 'kod', 'source', 'workspace', 'projek'])) {
            return $this->resp($this->aboutFiles($q), 'files');
        }

        if ($this->match($q, ['dashboard', 'menu', 'halaman', 'page', 'pautan'])) {
            return $this->resp($this->aboutPages(), 'pages');
        }

        if ($this->hasSearchIntent($q) || $this->shouldSmartFind($q)) {
            $found = $this->smartFind($q);
            if ($found) {
                return $found;
            }
        }

        $found = $this->smartFind($q, true);
        if ($found) {
            return $found;
        }

        return $this->resp(
            "Maaf ya, saya tak jumpa maklumat tu.\n\n" .
            (JarvisLLM::isAvailable() ? '' : "*(Mod AI penuh: setup `.env` dengan OpenAI key atau pasang Ollama)*\n\n") .
            "Cuba tanya pasal:\n" .
            "• Aliran atau status permohonan\n" .
            "• Senarai sistem (contoh Speedbiz, eSurat)\n" .
            "• Akaun demo untuk login\n" .
            "• No rujukan permohonan (BCS-...)\n\n" .
            "Atau: *\"Carikan info Speedbiz\"*, *\"Log keluar\"*",
            'fallback'
        );
    }

    private function tryAction(string $q): ?array {
        if ($this->wantsLogout($q)) {
            $nama = $this->session['nama'] ?? $this->session['username'] ?? 'Pengguna';
            return $this->resp(
                "Ok **{$nama}**, saya log keluar kan awak sekarang ya. Jumpa lagi nanti!",
                'action_logout',
                ['type' => 'redirect', 'url' => 'logout.php', 'delay' => 1400]
            );
        }

        if ($this->match($q, ['buka dashboard', 'pergi dashboard', 'ke dashboard', 'tunjuk dashboard', 'dashboard saya', 'halaman utama', 'buka laman'])) {
            $url = $this->dashboardUrl();
            return $this->resp(
                "Ok, saya bawa awak ke dashboard sekarang…",
                'action_nav',
                ['type' => 'redirect', 'url' => $url, 'delay' => 900]
            );
        }

        if ($this->match($q, ['buat permohonan', 'borang permohonan', 'permohonan baru', 'borang baru', 'hantar permohonan', 'isi borang'])) {
            $role = $this->session['role'] ?? '';
            if ($role === 'pemohon') {
                return $this->resp(
                    "Baik, saya buka borang permohonan untuk awak…",
                    'action_nav',
                    ['type' => 'redirect', 'url' => 'borang_permohonan.php', 'delay' => 900]
                );
            }
            return $this->resp(
                "Borang permohonan tu khas untuk **pemohon** je ya.\n\nAwak login sebagai **{$role}** — cuba taip *\"Buka dashboard\"* untuk tengok senarai tugasan awak.",
                'action_denied'
            );
        }

        if ($this->match($q, ['log masuk', 'login', 'sign in', 'buka login'])) {
            return $this->resp(
                "Awak dah log masuk sebagai **" . ($this->session['username'] ?? '-') . "**.\n\nNak keluar? Taip je *\"Log keluar\"*.",
                'action_info'
            );
        }

        if ($this->match($q, ['kecilkan', 'minimize', 'sembunyikan jarvis', 'tutup chat', 'kecilkan chat', 'sembunyi chat'])) {
            return $this->resp(
                "Ok, saya kecilkan dulu. Nak buka balik, klik **+** je ya.",
                'action_minimize',
                ['type' => 'minimize']
            );
        }

        if ($this->match($q, ['besarkan jarvis', 'buka chat', 'maximize', 'buka semula', 'besarkan chat'])) {
            return $this->resp(
                "Ok dah! Chatbox dah terbuka balik.",
                'action_open',
                ['type' => 'open']
            );
        }

        return null;
    }

    private function wantsLogout(string $q): bool {
        if ($this->match($q, ['log keluar', 'logout', 'log out', 'keluar sistem', 'sign out', 'signout'])) {
            return true;
        }
        if (!str_contains($q, 'keluar') && !str_contains($q, 'logout')) {
            return false;
        }
        return $this->match($q, ['log', 'logout', 'sistem', 'akaun', 'session', 'bantu', 'tolong', 'minta', 'nak', 'sign']);
    }

    private function dashboardUrl(): string {
        $role = $this->session['role'] ?? 'pemohon';
        if ($role === 'admin') {
            $role = 'admin_it';
        }
        $map = [
            'pemohon'       => 'dashboard_pemohon.php',
            'pengarah_jab'  => 'dashboard_pengarah_jab.php',
            'pengarah_jtik' => 'dashboard_pengarah_jtik.php',
            'admin_it'      => 'dashboard_admin_it.php',
        ];
        return $map[$role] ?? 'dashboard_pemohon.php';
    }

    private function resp(string $answer, string $source, ?array $action = null): array {
        if (!str_starts_with($source, 'llm')) {
            $answer = $this->humanize($answer);
        }
        $out = ['ok' => true, 'answer' => $answer, 'source' => $source];
        if ($action) {
            $out['action'] = $action;
        }
        return $out;
    }

    /** Laraskan nada ayat supaya lebih mesra & natural (BM). */
    private function humanize(string $text): string {
        $text = str_replace(
            [
                '**Hasil carian** — `',
                '**Data permohonan (live):**',
                '**Akaun demo** (setup.php / sistemakses_mysql.sql):',
                '**Aliran kelulusan permohonan:**',
                '**Status permohonan:**',
                '**Senarai 27 sistem** (SENARAI_SISTEM dalam config.php):',
                '**Profil session anda:**',
                '**Permohonan dijumpai:**',
                '**Sistem dijumpai:**',
                '**Pengguna dijumpai:**',
                '**Permohonan berkaitan:**',
                '**Fail projek berkaitan:**',
                '**Fail dalam workspace sistemakses:**',
                '→ redirect ke dashboard',
            ],
            [
                'Ok, saya dah cari pasal `',
                '**Ni status permohonan semasa:**',
                '**Senarai akaun dalam sistem:**',
                '**Macam ni aliran permohonan dari mula sampai habis:**',
                '**Status yang ada dalam sistem:**',
                '**Senarai 27 sistem yang boleh dimohon:**',
                '**Profil awak sekarang:**',
                '**Jumpa permohonan ni:**',
                '**Sistem yang saya jumpa:**',
                '**Pengguna yang match carian awak:**',
                '**Permohonan yang berkaitan:**',
                '**Fail dalam projek yang berkaitan:**',
                '**Fail-fail dalam projek ni:**',
                '— boleh tengok dekat dashboard',
            ],
            $text
        );
        return $text;
    }

    private function match(string $q, array $keywords): bool {
        foreach ($keywords as $kw) {
            if (str_contains($q, $kw)) return true;
        }
        return false;
    }

    private function greeting(): string {
        $nama = $this->session['nama'] ?? $this->session['username'] ?? 'kawan';
        $role = $this->humanRole($this->session['role'] ?? '-');
        return "Hai **{$nama}**! 👋 Saya JARVIS, assistant awak untuk **Borang Capaian Sistem** MBSP.\n\n" .
               "Sekarang awak login sebagai **{$role}**.\n\n" .
               "Apa-apa nak tanya, taip je — saya boleh carikan info permohonan, senarai sistem, akaun pengguna, dan lain-lain dalam sistem ni.\n\n" .
               "Contoh: *\"Carikan info Speedbiz\"*, *\"Berapa permohonan saya?\"*, atau *\"Tolong log keluar\"*";
    }

    private function humanRole(string $role): string {
        return match ($role) {
            'pemohon'       => 'Pemohon',
            'pengarah_jab'  => 'Pengarah Jabatan',
            'pengarah_jtik' => 'Pengarah JTIK',
            'admin_it'      => 'Admin IT',
            'admin'         => 'Admin IT',
            default         => $role,
        };
    }

    private function aboutUsers(): string {
        $lines = ["Ni senarai akaun yang ada dalam sistem:\n"];
        try {
            $db = getDB();
            $rows = $db->query("SELECT username, role, nama, jabatan FROM users ORDER BY id")->fetchAll();
            foreach ($rows as $r) {
                $lines[] = "• **{$r['username']}** — {$r['nama']} ({$this->humanRole($r['role'])}), {$r['jabatan']}";
            }
            $lines[] = "\nKata laluan demo biasa:\n**admin/admin** · pemohon1/**user123** · pengarah_jab/**pengarah123** · pengarah_jtik/**jtik123** · admin_it/**it123**";
        } catch (Throwable $e) {
            $lines[] = "• pemohon1, pemohon2 (Pemohon)\n• pengarah_jab\n• pengarah_jtik\n• admin_it\n• admin";
            $lines[] = "\n*(Database tak reachable — ni maklumat asas je)*";
        }
        $lines[] = "\nLogin dekat halaman utama, lepas tu sistem akan bawa awak ke dashboard ikut peranan.";
        return implode("\n", $lines);
    }

    private function aboutWorkflow(): string {
        return "Permohonan capaian sistem ni flow dia macam ni:\n\n" .
               "1. **Pemohon** isi borang & hantar → status: *Menunggu Pengarah Jabatan*\n" .
               "2. **Pengarah Jabatan** buat perakuan → terus ke *Menunggu Kelulusan JTIK*\n" .
               "3. **Pengarah JTIK** lulus atau tolak → *Diluluskan* / *Tidak Diluluskan*\n" .
               "4. **Admin IT** beri akses sebenar → *Akses Diberikan*\n\n" .
               "No rujukan format: **BCS-YYYYMMDD-####**\n" .
               "Tujuan permohonan: permohonan baru, kemaskini capaian, atau pembatalan.\n\n" .
               "Penting: Pengarah Jabatan **tak boleh tolak** — dia hantar je ke JTIK. Penolakan buat dekat peringkat JTIK.";
    }

    private function aboutStatuses(): string {
        $map = [
            'MENUNGGU_PENGARAH_JAB' => statusLabel('MENUNGGU_PENGARAH_JAB'),
            'MENUNGGU_JTIK'         => statusLabel('MENUNGGU_JTIK'),
            'DILULUSKAN'            => statusLabel('DILULUSKAN'),
            'TIDAK_DILULUSKAN'      => statusLabel('TIDAK_DILULUSKAN'),
            'AKSES_DIBERIKAN'       => statusLabel('AKSES_DIBERIKAN'),
        ];
        $lines = ["Setiap permohonan ada status macam ni:\n"];
        foreach ($map as $label) {
            $lines[] = "• {$label}";
        }
        return implode("\n", $lines);
    }

    private function aboutPermohonan(string $q): string {
        try {
            $db   = getDB();
            $role = $this->session['role'] ?? '';
            $uid  = (int)($this->session['user_id'] ?? 0);
            $lines = ["**Ni status permohonan semasa:**\n"];

            if ($role === 'pemohon') {
                $stmt = $db->prepare("SELECT status, COUNT(*) AS c FROM permohonan WHERE user_id=? GROUP BY status");
                $stmt->execute([$uid]);
                $rows = $stmt->fetchAll();
                if (!$rows) return "Awak belum buat sebarang permohonan lagi. Nak mula? Taip *\"Buat permohonan\"* je.";
                $total = 0;
                foreach ($rows as $r) {
                    $lines[] = "• " . statusLabel($r['status']) . ": **{$r['c']}**";
                    $total += $r['c'];
                }
                $lines[] = "\nJumlah keseluruhan: **{$total}** permohonan";

                $recent = $db->prepare("SELECT no_rujukan, status, tujuan, created_at FROM permohonan WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
                $recent->execute([$uid]);
                $list = $recent->fetchAll();
                if ($list) {
                    $lines[] = "\n**5 permohonan terkini awak:**";
                    foreach ($list as $p) {
                        $lines[] = "• {$p['no_rujukan']} — " . statusLabel($p['status']) . " (" . tujuanLabel($p['tujuan']) . ")";
                    }
                }
            } else {
                $total = (int)$db->query("SELECT COUNT(*) FROM permohonan")->fetchColumn();
                $lines[] = "Jumlah semua permohonan dalam sistem: **{$total}**";
                $stats = $db->query("SELECT status, COUNT(*) AS c FROM permohonan GROUP BY status ORDER BY c DESC")->fetchAll();
                foreach ($stats as $r) {
                    $lines[] = "• " . statusLabel($r['status']) . ": **{$r['c']}**";
                }
                if ($role === 'pengarah_jab') {
                    $n = (int)$db->query("SELECT COUNT(*) FROM permohonan WHERE status='MENUNGGU_PENGARAH_JAB'")->fetchColumn();
                    $lines[] = "\nYang menunggu tindakan awak: **{$n}** permohonan.";
                }
                if ($role === 'pengarah_jtik') {
                    $n = (int)$db->query("SELECT COUNT(*) FROM permohonan WHERE status='MENUNGGU_JTIK'")->fetchColumn();
                    $lines[] = "\nYang menunggu kelulusan awak: **{$n}** permohonan.";
                }
                if ($role === 'admin_it') {
                    $n = (int)$db->query("SELECT COUNT(*) FROM permohonan WHERE status='DILULUSKAN'")->fetchColumn();
                    $lines[] = "\nYang perlu awak beri akses: **{$n}** permohonan.";
                }
            }
            return implode("\n", $lines);
        } catch (Throwable $e) {
            return "Hmm, saya tak dapat sambung ke database sekarang. Pastikan MySQL XAMPP dah on & setup.php dah jalan.\n\n" .
                   $this->aboutWorkflow();
        }
    }

    private function aboutSystems(string $q): string {
        $lines = ["Dalam sistem ni ada **27 sistem** yang boleh dimohon capaian:\n"];
        foreach (SENARAI_SISTEM as $bil => $nama) {
            if ($q !== '' && !str_contains(mb_strtolower($nama), $q) && !str_contains($q, (string)$bil)) {
                continue;
            }
            $lines[] = "{$bil}. {$nama}";
        }
        if (count($lines) === 1) {
            foreach (SENARAI_SISTEM as $bil => $nama) {
                $lines[] = "{$bil}. {$nama}";
            }
        }
        $lines[] = "\nPemohon pilih sistem ni masa isi borang (Seksyen B), sekali dengan peranan & had kuasa.";
        return implode("\n", $lines);
    }

    private function aboutRoles(): string {
        $lines = ["**Peranan untuk login ke sistem:**\n• Pemohon\n• Pengarah Jabatan\n• Pengarah JTIK\n• Admin IT\n\n"];
        $lines[] = "**Peranan dalam borang permohonan (capaian sistem):**";
        foreach (SENARAI_PERANAN as $k => $v) {
            $lines[] = "• {$v}";
        }
        $lines[] = "\n**Fungsi had kuasa:** " . implode(', ', array_map('fungsiLabel', SENARAI_FUNGSI));
        $lines[] = "\nHad kuasa ni auto-set ikut peranan bila isi borang — tak perlu tick satu-satu.";
        return implode("\n", $lines);
    }

    private function aboutDatabase(): string {
        return "Database sistem ni guna **MySQL**, nama database: `" . DB_NAME . "`\n\n" .
               "**Jadual utama:**\n" .
               "• **users** — akaun pengguna\n" .
               "• **permohonan** — rekod permohonan capaian\n" .
               "• **permohonan_sistem** — sistem yang dipilih + had kuasa\n\n" .
               "Kalau first time setup, jalankan **setup.php** atau import fail **sistemakses_mysql.sql**.";
    }

    private function aboutFiles(string $q): string {
        $catalog = [
            'config.php'              => 'Konfigurasi DB, senarai sistem, peranan, helper label',
            'auth.php'                => 'Autentikasi session, requireLogin, requireRole',
            'login.php'               => 'Halaman log masuk',
            'logout.php'              => 'Log keluar',
            'setup.php'               => 'Setup database & seed akaun demo',
            'dashboard_pemohon.php'   => 'Dashboard pemohon',
            'borang_permohonan.php'   => 'Borang permohonan capaian (A,B,C)',
            'submit_permohonan.php'   => 'Handler simpan permohonan',
            'view_permohonan.php'     => 'Paparan detail permohonan',
            'dashboard_pengarah_jab.php'  => 'Dashboard Pengarah Jabatan',
            'tindakan_pengarah_jab.php'   => 'Perakuan Pengarah Jabatan (E)',
            'dashboard_pengarah_jtik.php' => 'Dashboard Pengarah JTIK',
            'tindakan_jtik.php'           => 'Kelulusan JTIK (F)',
            'dashboard_admin_it.php'      => 'Dashboard Admin IT',
            'tindakan_it.php'             => 'Pemberian akses IT (G)',
            '_includes.php'           => 'CSS/JS/shared UI sidebar toast',
            '_chatbox.php'            => 'Widget JARVIS AI chatbox',
            'jarvis_engine.php'       => 'Enjin Q&A workspace JARVIS',
            'jarvis_api.php'          => 'API endpoint chat JARVIS',
            'sistemakses_mysql.sql'   => 'Dump SQL schema + data',
        ];

        $lines = ["Ni fail-fail penting dalam projek ni:\n"];
        foreach ($catalog as $file => $desc) {
            if ($q && !str_contains($q, pathinfo($file, PATHINFO_FILENAME)) && !str_contains(mb_strtolower($desc), $q)) {
                continue;
            }
            $lines[] = "• **{$file}** — {$desc}";
        }
        if (count($lines) <= 1) {
            foreach ($catalog as $file => $desc) {
                $lines[] = "• **{$file}** — {$desc}";
            }
        }
        return implode("\n", $lines);
    }

    private function aboutPages(): string {
        $role = $this->session['role'] ?? 'pemohon';
        $map = [
            'pemohon'       => 'Dashboard Pemohon, Borang Permohonan, Lihat Permohonan',
            'pengarah_jab'  => 'Dashboard Pengarah Jabatan, Tindakan Perakuan',
            'pengarah_jtik' => 'Dashboard Pengarah JTIK, Kelulusan Permohonan',
            'admin_it'      => 'Dashboard Admin IT, Pemberian Akses',
        ];
        $pages = $map[$role] ?? implode(', ', $map);
        $human = $this->humanRole($role);
        return "Sebagai **{$human}**, halaman yang awak guna selalu:\n{$pages}\n\nSemua pengguna boleh log masuk, log keluar, dan lihat detail permohonan (ikut hak akses masing-masing).";
    }

    private function isPureGreeting(string $q): bool {
        if ($this->match($q, ['cari', 'carikan', 'info', 'maklumat', 'tentang', 'pasal', 'rujukan', 'bcs-', 'jumpa', 'tunjuk'])) {
            return false;
        }
        return $this->match($q, ['hello', 'hi', 'hai', 'helo', 'salam', 'help', 'apa boleh'])
            || ($this->match($q, ['bantu']) && mb_strlen($q) < 18);
    }

    private function hasSearchIntent(string $q): bool {
        return $this->match($q, [
            'cari', 'carikan', 'carian', 'search', 'find', 'jumpa', 'tunjuk', 'tunjukkan',
            'info', 'maklumat', 'nak tahu', 'nak info', 'apa itu', 'siapa', 'di mana',
            'dapatkan', 'semak', 'lookup', 'no rujukan', 'rujukan',
        ]);
    }

    private function shouldSmartFind(string $q): bool {
        $terms = $this->extractSearchTerms($q);
        if (count($terms) >= 1 && preg_match('/\bbcs[-\d]/i', $q)) {
            return true;
        }
        return count($terms) >= 2 || (count($terms) === 1 && mb_strlen($terms[0]) >= 4);
    }

    private function extractSearchTerms(string $q): array {
        $stop = [
            'cari', 'carikan', 'carian', 'info', 'maklumat', 'tentang', 'mengenai', 'pasal', 'berkenaan',
            'nak', 'saya', 'boleh', 'tolong', 'bantu', 'jarvis', 'apa', 'itu', 'ialah', 'adakah',
            'the', 'about', 'find', 'search', 'show', 'tunjuk', 'tunjukkan', 'beri', 'dapatkan', 'dapat',
            'dari', 'dalam', 'untuk', 'yang', 'dan', 'atau', 'dengan', 'pada', 'ke', 'di', 'sini',
            'bagaimana', 'macam', 'mana', 'please', 'minta', 'mahu', 'ingin', 'tahu', 'please',
            'semua', 'senarai', 'list', 'live', 'data', 'database', 'workspace', 'projek', 'sistem',
            'permohonan', 'borang', 'akaun', 'user', 'pengguna', 'fail', 'file', 'php', 'siapa',
        ];
        $words = preg_split('/\s+/u', mb_strtolower(trim($q)), -1, PREG_SPLIT_NO_EMPTY);
        $terms = [];
        foreach ($words as $w) {
            $w = trim($w, ".,?!\"'`");
            if ($w === '' || mb_strlen($w) < 2 || in_array($w, $stop, true)) {
                continue;
            }
            $terms[] = $w;
        }
        return array_values(array_unique($terms));
    }

    private function extractReference(string $q): ?string {
        if (preg_match('/\b(BCS-\d{8}-\d{4})\b/i', $q, $m)) {
            return strtoupper($m[1]);
        }
        if (preg_match('/\b(BCS[-\d]{4,})\b/i', $q, $m)) {
            return strtoupper($m[1]);
        }
        return null;
    }

    private function smartFind(string $q, bool $relaxed = false): ?array {
        $terms = $this->extractSearchTerms($q);
        if (!$terms && !$relaxed) {
            return null;
        }
        if (!$terms && $relaxed) {
            $terms = preg_split('/\s+/u', mb_strtolower(trim($q)), -1, PREG_SPLIT_NO_EMPTY);
            $terms = array_values(array_filter($terms, fn($w) => mb_strlen($w) >= 3));
            if (!$terms) {
                return null;
            }
        }

        $sections = [];
        $action   = null;

        if ($sys = $this->searchSystems($q, $terms)) {
            $sections[] = $sys;
        }
        if ($usr = $this->searchUsers($terms)) {
            $sections[] = $usr;
        }
        if ($perm = $this->searchPermohonanRecords($q, $terms, $action)) {
            $sections[] = $perm['text'];
            if ($perm['action']) {
                $action = $perm['action'];
            }
        }
        if ($know = $this->searchKnowledge($q, $terms)) {
            $sections[] = $know;
        }
        if ($files = $this->searchFilesContent($terms)) {
            $sections[] = $files;
        }

        if (!$sections) {
            return null;
        }

        $sections = array_unique($sections);
        $label    = implode(' ', $terms);
        $intro    = "Ok, saya dah cari pasal **{$label}**. Ni yang saya jumpa:\n\n";
        $answer   = $intro . implode("\n\n", array_slice($sections, 0, 4));

        if (count($sections) > 4) {
            $answer .= "\n\n*(Ada lagi result — cuba tajukkan carian awak supaya lebih spesifik ya)*";
        }

        return $this->resp($answer, 'search', $action);
    }

    private function lookupPermohonan(string $ref): ?array {
        try {
            $db   = getDB();
            $role = $this->session['role'] ?? '';
            $uid  = (int)($this->session['user_id'] ?? 0);

            $stmt = $db->prepare("
                SELECT p.*, u.username
                FROM permohonan p
                JOIN users u ON u.id = p.user_id
                WHERE p.no_rujukan LIKE ?
                LIMIT 1
            ");
            $stmt->execute([$ref . '%']);
            $p = $stmt->fetch();
            if (!$p) {
                return $this->resp("Hmm, saya tak jumpa permohonan dengan no rujukan **{$ref}**. Double-check no tu betul ke tak.", 'search');
            }
            if ($role === 'pemohon' && (int)$p['user_id'] !== $uid) {
                return $this->resp("Permohonan **{$ref}** memang wujud, tapi bukan under akaun awak. Tak boleh saya tunjuk detail.", 'search');
            }

            $sistem = $db->prepare("SELECT nama_sistem, peranan_sistem FROM permohonan_sistem WHERE permohonan_id=? ORDER BY bil");
            $sistem->execute([$p['id']]);
            $list = $sistem->fetchAll();

            $lines = [
                "Jumpa permohonan **{$p['no_rujukan']}**!",
                "• Pemohon: **{$p['nama']}** (akaun: {$p['username']})",
                "• Jabatan: {$p['jabatan']}",
                "• Jawatan: {$p['jawatan']}, Gred {$p['gred_jawatan']}",
                "• Tujuan: " . tujuanLabel($p['tujuan']),
                "• Status sekarang: **" . statusLabel($p['status']) . "**",
                "• Tarikh hantar: {$p['created_at']}",
            ];
            if ($list) {
                $lines[] = "• Sistem yang dimohon:";
                foreach ($list as $s) {
                    $lines[] = "  - {$s['nama_sistem']}" . ($s['peranan_sistem'] ? " ({$s['peranan_sistem']})" : '');
                }
            }
            $lines[] = "\nSebentar ya, saya buka halaman detail permohonan tu…";

            return $this->resp(
                implode("\n", $lines),
                'search',
                ['type' => 'redirect', 'url' => 'view_permohonan.php?id=' . (int)$p['id'], 'delay' => 1200]
            );
        } catch (Throwable $e) {
            return null;
        }
    }

    private function searchSystems(string $q, array $terms): ?string {
        $matches = [];
        foreach (SENARAI_SISTEM as $bil => $nama) {
            $namaLower = mb_strtolower($nama);
            $score     = 0;
            foreach ($terms as $t) {
                if (str_contains($namaLower, $t)) {
                    $score += 2;
                }
                if ($t === (string)$bil) {
                    $score += 3;
                }
            }
            if (str_contains(mb_strtolower($q), mb_strtolower($nama))) {
                $score += 3;
            }
            if ($score > 0) {
                $matches[] = ['bil' => $bil, 'nama' => $nama, 'score' => $score];
            }
        }
        if (!$matches) {
            return null;
        }
        usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);
        $lines = ["Sistem yang saya jumpa:"];
        foreach (array_slice($matches, 0, 5) as $m) {
            $lines[] = "• **{$m['bil']}. {$m['nama']}** — boleh pilih masa isi borang permohonan";
        }
        return implode("\n", $lines);
    }

    private function searchUsers(array $terms): ?string {
        if ($this->match(implode(' ', $terms), ['admin', 'pemohon', 'pengarah', 'jtik', 'it'])) {
            // allow role-ish terms
        } elseif (!preg_match('/\b(pemohon|pengarah|admin|user|akaun|staff|kakitangan)\b/u', implode(' ', $terms))
            && !preg_match('/\b(pemohon|admin|pengarah)\d*\b/u', implode(' ', $terms))) {
            $looksLikeUser = false;
            foreach ($terms as $t) {
                if (preg_match('/^(pemohon|admin|pengarah)/', $t)) {
                    $looksLikeUser = true;
                    break;
                }
            }
            if (!$looksLikeUser && count($terms) > 2) {
                return null;
            }
        }

        try {
            $db     = getDB();
            $where  = [];
            $params = [];
            foreach ($terms as $t) {
                $where[]  = '(username LIKE ? OR nama LIKE ? OR jabatan LIKE ? OR role LIKE ?)';
                $like     = '%' . $t . '%';
                $params   = array_merge($params, [$like, $like, $like, $like]);
            }
            $sql  = 'SELECT username, nama, role, jabatan FROM users WHERE ' . implode(' OR ', $where) . ' ORDER BY username LIMIT 6';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            if (!$rows) {
                return null;
            }
            $lines = ["Pengguna yang match carian awak:"];
            foreach ($rows as $r) {
                $lines[] = "• **{$r['username']}** — {$r['nama']} ({$this->humanRole($r['role'])}), {$r['jabatan']}";
            }
            return implode("\n", $lines);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function searchPermohonanRecords(string $q, array $terms, ?array &$action): ?array {
        if (!$this->match($q, ['permohonan', 'rujukan', 'status', 'menunggu', 'diluluskan', 'akses', 'bcs'])
            && count($terms) < 2) {
            return null;
        }

        try {
            $db   = getDB();
            $role = $this->session['role'] ?? '';
            $uid  = (int)($this->session['user_id'] ?? 0);

            $statusFilter = null;
            if ($this->match($q, ['menunggu pengarah', 'pengarah jab'])) {
                $statusFilter = 'MENUNGGU_PENGARAH_JAB';
            } elseif ($this->match($q, ['menunggu jtik', 'jtik'])) {
                $statusFilter = 'MENUNGGU_JTIK';
            } elseif ($this->match($q, ['diluluskan'])) {
                $statusFilter = 'DILULUSKAN';
            } elseif ($this->match($q, ['ditolak', 'tidak diluluskan'])) {
                $statusFilter = 'TIDAK_DILULUSKAN';
            } elseif ($this->match($q, ['akses diberikan'])) {
                $statusFilter = 'AKSES_DIBERIKAN';
            }

            $where  = ['1=1'];
            $params = [];

            if ($role === 'pemohon') {
                $where[]  = 'p.user_id = ?';
                $params[] = $uid;
            }

            if ($statusFilter) {
                $where[]  = 'p.status = ?';
                $params[] = $statusFilter;
            }

            $termClauses = [];
            foreach ($terms as $t) {
                $termClauses[] = '(p.no_rujukan LIKE ? OR p.nama LIKE ? OR p.jabatan LIKE ? OR p.jawatan LIKE ? OR ps.nama_sistem LIKE ?)';
                $like          = '%' . $t . '%';
                $params        = array_merge($params, [$like, $like, $like, $like, $like]);
            }
            if ($termClauses) {
                $where[] = '(' . implode(' OR ', $termClauses) . ')';
            }

            $sql = "
                SELECT p.id, p.no_rujukan, p.nama, p.status, p.jabatan, p.tujuan, p.created_at
                FROM permohonan p
                LEFT JOIN permohonan_sistem ps ON ps.permohonan_id = p.id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT 5
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            if (!$rows) {
                return null;
            }

            $lines = ["Permohonan yang berkaitan:"];
            foreach ($rows as $r) {
                $lines[] = "• **{$r['no_rujukan']}** — {$r['nama']}, " . statusLabel($r['status']) . ", " . tujuanLabel($r['tujuan']);
            }
            if (count($rows) === 1) {
                $action = ['type' => 'redirect', 'url' => 'view_permohonan.php?id=' . (int)$rows[0]['id'], 'delay' => 1500];
                $lines[] = "\nJumpa 1 je — saya buka detail permohonan tu sekarang…";
            } else {
                $lines[] = "\nNak tengok detail? Taip no rujukan penuh, contoh: *BCS-20250101-0001*";
            }

            return ['text' => implode("\n", $lines), 'action' => $action];
        } catch (Throwable $e) {
            return null;
        }
    }

    private function searchKnowledge(string $q, array $terms): ?string {
        $topics = [
            ['keys' => ['aliran', 'workflow', 'kelulusan', 'proses', 'langkah'], 'title' => 'Aliran kelulusan', 'body' => fn() => $this->aboutWorkflow()],
            ['keys' => ['status', 'menunggu', 'diluluskan', 'ditolak'], 'title' => 'Status permohonan', 'body' => fn() => $this->aboutStatuses()],
            ['keys' => ['peranan', 'role', 'had', 'kuasa', 'penyedia', 'pelulus'], 'title' => 'Peranan & had kuasa', 'body' => fn() => $this->aboutRoles()],
            ['keys' => ['database', 'mysql', 'jadual', 'table', 'setup'], 'title' => 'Database', 'body' => fn() => $this->aboutDatabase()],
            ['keys' => ['login', 'password', 'akaun', 'demo'], 'title' => 'Akaun & login', 'body' => fn() => $this->aboutUsers()],
            ['keys' => ['dashboard', 'menu', 'halaman'], 'title' => 'Halaman aplikasi', 'body' => fn() => $this->aboutPages()],
        ];

        $joined = implode(' ', $terms);
        foreach ($topics as $topic) {
            $score = 0;
            foreach ($topic['keys'] as $k) {
                if (str_contains($q, $k) || str_contains($joined, $k)) {
                    $score++;
                }
            }
            if ($score > 0) {
                return ($topic['body'])();
            }
        }
        return null;
    }

    private function searchFilesContent(array $terms): ?string {
        $files = array_merge(
            glob($this->root . '/*.php') ?: [],
            glob($this->root . '/*.sql') ?: []
        );

        $hits = [];
        foreach ($files as $path) {
            $name    = basename($path);
            $content = @file_get_contents($path);
            if ($content === false) {
                continue;
            }
            $lower = mb_strtolower($content);
            $score = 0;
            foreach ($terms as $w) {
                if (str_contains(mb_strtolower($name), $w)) {
                    $score += 4;
                }
                $score += min(substr_count($lower, $w), 8);
            }
            if ($score >= 2) {
                $hits[] = ['file' => $name, 'score' => $score, 'snippet' => $this->extractSnippet($content, $terms[0])];
            }
        }

        if (!$hits) {
            return null;
        }
        usort($hits, fn($a, $b) => $b['score'] <=> $a['score']);
        $hits = array_slice($hits, 0, 3);

        $lines = ["Fail dalam projek yang berkaitan:"];
        foreach ($hits as $h) {
            $lines[] = "• **{$h['file']}** — …{$h['snippet']}…";
        }
        return implode("\n", $lines);
    }

    private function aboutCurrentUser(): string {
        return "Ni profil awak dalam sistem sekarang:\n" .
               "• Nama: **" . ($this->session['nama'] ?? '-') . "**\n" .
               "• Username: **" . ($this->session['username'] ?? '-') . "**\n" .
               "• Peranan: **" . $this->humanRole($this->session['role'] ?? '-') . "**\n" .
               "• Jabatan: " . ($this->session['jabatan'] ?? '-');
    }

    private function extractSnippet(string $content, string $word): string {
        $pos = mb_stripos($content, $word);
        if ($pos === false) return mb_substr(preg_replace('/\s+/', ' ', $content), 0, 80);
        $start = max(0, $pos - 40);
        return trim(mb_substr(preg_replace('/\s+/', ' ', $content), $start, 100));
    }
}
