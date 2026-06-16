<?php
/**
 * Konfigurasi JARVIS AI — LLM (ChatGPT / Ollama / OpenAI-compatible).
 * Salin .env.example ke .env dan isi API key.
 */
$envFile = __DIR__ . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val, " \t\n\r\0\x0B\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$val}");
        }
    }
}

define('JARVIS_LLM_ENABLED', getenv('JARVIS_LLM_ENABLED') !== '0');
define('JARVIS_LLM_PROVIDER', getenv('JARVIS_LLM_PROVIDER') ?: 'auto');
define('JARVIS_LLM_API_KEY', getenv('JARVIS_LLM_API_KEY') ?: (getenv('OPENAI_API_KEY') ?: ''));
define('JARVIS_LLM_MODEL', getenv('JARVIS_LLM_MODEL') ?: 'gpt-4o-mini');
define('JARVIS_LLM_BASE_URL', rtrim(getenv('JARVIS_LLM_BASE_URL') ?: 'https://api.openai.com/v1', '/'));
define('JARVIS_OLLAMA_URL', rtrim(getenv('JARVIS_OLLAMA_URL') ?: 'http://127.0.0.1:11434', '/'));
define('JARVIS_OLLAMA_MODEL', getenv('JARVIS_OLLAMA_MODEL') ?: 'llama3.2');
define('JARVIS_LLM_TIMEOUT', (int)(getenv('JARVIS_LLM_TIMEOUT') ?: 45));
define('JARVIS_HISTORY_MAX', 12);
