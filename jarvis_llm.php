<?php
require_once __DIR__ . '/jarvis_config.php';

class JarvisLLM {
    private static ?string $resolvedProvider = null;

    public static function isAvailable(): bool {
        if (!JARVIS_LLM_ENABLED) {
            return false;
        }
        return self::resolveProvider() !== null;
    }

    public static function providerName(): string {
        return self::resolveProvider() ?? 'offline';
    }

    /**
     * @param array<int, array{role:string, content:string}> $history
     * @param array<string, mixed> $session
     */
    public static function chat(string $question, string $context, array $history, array $session): array {
        $provider = self::resolveProvider();
        if (!$provider) {
            return ['ok' => false, 'error' => 'LLM tidak dikonfigurasi'];
        }

        $nama = $session['nama'] ?? $session['username'] ?? 'Pengguna';
        $role = $session['role'] ?? '-';

        $system = <<<SYS
Anda JARVIS — pembantu AI untuk sistem **Borang Capaian Sistem (MBSP)** Majlis Bandaraya Seberang Perai.

PERATURAN WAJIB:
1. Jawab dalam **Bahasa Melayu** natural, mesra, macam manusia berbual — bukan robot.
2. Jawab berdasarkan **KONTEKS SISTEM** sahaja. Jangan reka atau andaikan maklumat luar konteks.
3. Jika maklumat tiada dalam konteks, kata jujur: "Saya tak jumpa maklumat tu dalam sistem."
4. Gunakan **bold** untuk perkataan penting. Bullet • bila senarai.
5. Jawapan padat, tepat, dan membantu — macam ChatGPT.
6. Pengguna sekarang: {$nama}, peranan login: {$role}.
7. Jangan sebut "konteks", "LLM", atau "prompt". Terus jawab soalan.
8. Boleh terangkan aliran permohonan, sistem, akaun, status, dan cara guna aplikasi.

TINDAKAN (hanya cadang dalam ayat, sistem handle sendiri):
- Log keluar, buka dashboard, buat permohonan — user boleh minta terus.

KONTEKS SISTEM (data sebenar workspace + database):
{$context}
SYS;

        $messages = [['role' => 'system', 'content' => $system]];
        foreach ($history as $turn) {
            if (!isset($turn['role'], $turn['content'])) {
                continue;
            }
            $r = $turn['role'] === 'assistant' ? 'assistant' : 'user';
            $messages[] = ['role' => $r, 'content' => mb_substr(trim($turn['content']), 0, 1500)];
        }
        $messages[] = ['role' => 'user', 'content' => $question];

        try {
            $text = $provider === 'ollama'
                ? self::callOllama($messages)
                : self::callOpenAICompatible($messages);

            if ($text === '') {
                return ['ok' => false, 'error' => 'Jawapan kosong dari AI'];
            }

            return ['ok' => true, 'answer' => trim($text), 'provider' => $provider];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private static function resolveProvider(): ?string {
        if (self::$resolvedProvider !== null) {
            return self::$resolvedProvider ?: null;
        }

        $want = JARVIS_LLM_PROVIDER;
        if ($want === 'ollama' || ($want === 'auto' && self::pingOllama())) {
            self::$resolvedProvider = 'ollama';
            return 'ollama';
        }
        if (($want === 'openai' || $want === 'auto') && JARVIS_LLM_API_KEY !== '') {
            self::$resolvedProvider = 'openai';
            return 'openai';
        }
        if ($want === 'openai' && JARVIS_LLM_API_KEY === '') {
            self::$resolvedProvider = '';
            return null;
        }

        self::$resolvedProvider = '';
        return null;
    }

    private static function pingOllama(): bool {
        $ch = curl_init(JARVIS_OLLAMA_URL . '/api/tags');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
        ]);
        curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code === 200;
    }

    /** @param array<int, array{role:string, content:string}> $messages */
    private static function callOpenAICompatible(array $messages): string {
        $payload = [
            'model'       => JARVIS_LLM_MODEL,
            'messages'    => $messages,
            'temperature' => 0.35,
            'max_tokens'  => 900,
        ];

        $data = self::httpJson(
            JARVIS_LLM_BASE_URL . '/chat/completions',
            $payload,
            ['Authorization: Bearer ' . JARVIS_LLM_API_KEY]
        );

        return trim($data['choices'][0]['message']['content'] ?? '');
    }

    /** @param array<int, array{role:string, content:string}> $messages */
    private static function callOllama(array $messages): string {
        $payload = [
            'model'    => JARVIS_OLLAMA_MODEL,
            'messages' => $messages,
            'stream'   => false,
            'options'  => ['temperature' => 0.35, 'num_predict' => 900],
        ];

        $data = self::httpJson(JARVIS_OLLAMA_URL . '/api/chat', $payload);

        return trim($data['message']['content'] ?? '');
    }

    private static function httpJson(string $url, array $payload, array $extraHeaders = []): array {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL diperlukan untuk AI JARVIS.');
        }

        $ch = curl_init($url);
        $headers = array_merge(['Content-Type: application/json'], $extraHeaders);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => JARVIS_LLM_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Sambungan AI gagal: ' . $err);
        }

        $data = json_decode($raw, true);
        if ($code >= 400) {
            $msg = is_array($data) ? ($data['error']['message'] ?? $data['error'] ?? $raw) : $raw;
            if (is_array($msg)) {
                $msg = json_encode($msg);
            }
            throw new RuntimeException('AI error (' . $code . '): ' . mb_substr((string)$msg, 0, 200));
        }

        if (!is_array($data)) {
            throw new RuntimeException('Respons AI tidak sah.');
        }

        return $data;
    }
}
