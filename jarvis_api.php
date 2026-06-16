<?php
session_name('sistem_akses');
session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Awak kena log masuk dulu ya.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? $_POST['message'] ?? '');

if ($message === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Mesej kosong je tu. Apa yang awak nak tanya?']);
    exit;
}

if (mb_strlen($message) > 1000) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Mesej awak terlalu panjang. Pendekkan sikit ya (max 1000 aksara).']);
    exit;
}

require_once __DIR__ . '/jarvis_engine.php';

if (!isset($_SESSION['jarvis_history']) || !is_array($_SESSION['jarvis_history'])) {
    $_SESSION['jarvis_history'] = [];
}

$history = array_slice($_SESSION['jarvis_history'], -JARVIS_HISTORY_MAX);

try {
    $engine = new JarvisEngine($_SESSION);
    $result = $engine->answer($message, $history);

    if (!empty($result['ok']) && !empty($result['answer'])) {
        $_SESSION['jarvis_history'][] = ['role' => 'user', 'content' => $message];
        $_SESSION['jarvis_history'][] = ['role' => 'assistant', 'content' => $result['answer']];
        if (count($_SESSION['jarvis_history']) > JARVIS_HISTORY_MAX * 2) {
            $_SESSION['jarvis_history'] = array_slice($_SESSION['jarvis_history'], -(JARVIS_HISTORY_MAX * 2));
        }
    }

    $result['ai_mode'] = JarvisLLM::isAvailable() ? JarvisLLM::providerName() : 'offline';
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Alamak, ada masalah teknikal. Cuba sebentar lagi ya.']);
}
