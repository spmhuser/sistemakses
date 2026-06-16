<?php

function chatboxAsset($file) {
    return 'assets/' . $file;
}

function chatboxCSS() { ?>
<style>
@keyframes jarvisPopIn {
    0% { opacity: 0; transform: translateY(80px) scale(0.5); }
    60% { transform: translateY(-10px) scale(1.04); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes jarvis3dIdle {
    0%, 100% { transform: translate3d(0, 0, 0) rotateY(-10deg) rotateX(5deg); }
    25%       { transform: translate3d(0, -16px, 10px) rotateY(6deg) rotateX(-2deg); }
    50%       { transform: translate3d(0, -8px, 6px) rotateY(14deg) rotateX(3deg); }
    75%       { transform: translate3d(0, -20px, 14px) rotateY(-4deg) rotateX(-1deg); }
}
@keyframes jarvisShadowPulse {
    0%, 100% { transform: translateX(-50%) scale(1); opacity: 0.5; }
    50%       { transform: translateX(-50%) scale(0.7); opacity: 0.22; }
}
@keyframes repulsorPulse {
    0%, 100% { opacity: 0.45; transform: scale(0.85); }
    50%       { opacity: 1; transform: scale(1.2); }
}
@keyframes arcGlow {
    0%, 100% { opacity: 0.3; transform: translate(-50%, -50%) scale(1); }
    50%       { opacity: 0.8; transform: translate(-50%, -50%) scale(1.25); }
}
@keyframes signSent {
    0%   { box-shadow: 0 0 0 0 rgba(250, 204, 21, 0.6); }
    40%  { box-shadow: 0 0 28px 8px rgba(250, 204, 21, 0.45); }
    100% { box-shadow: 0 12px 36px rgba(0,0,0,0.55); }
}
@keyframes replyPop {
    0%   { opacity: 0; transform: translateY(8px) scale(0.9); }
    20%  { opacity: 1; transform: translateY(0) scale(1); }
    80%  { opacity: 1; }
    100% { opacity: 0; transform: translateY(-6px); }
}

#jarvis-assistant {
    position: fixed; bottom: 10px; right: 8px; left: auto; z-index: 10000;
    pointer-events: auto;
    transform-style: preserve-3d;
    transition: width 0.45s cubic-bezier(0.34, 1.25, 0.64, 1), height 0.45s;
}
#jarvis-assistant:not(.jarvis-minimized):not(.jarvis-board-open) {
    cursor: default;
}
#jarvis-assistant.jarvis-dragging {
    transition: none;
    user-select: none;
}
#jarvis-assistant.jarvis-dragging * {
    user-select: none;
    cursor: grabbing !important;
}
#jarvis-assistant.jarvis-minimized {
    cursor: grab;
}
#jarvis-assistant.jarvis-minimized.jarvis-dragging .jarvis-hero-img {
    cursor: grabbing;
}
#jarvis-assistant.jarvis-minimized .jarvis-sign-held,
#jarvis-assistant.jarvis-minimized .jarvis-toggle-label { display: none; }
#jarvis-assistant.jarvis-minimized .jarvis-3d-stage {
    width: 88px; height: 110px;
}
#jarvis-assistant.jarvis-minimized .jarvis-3d-inner {
    width: 88px; margin-right: 0; margin-left: 0; right: 0; left: auto;
}
#jarvis-assistant.jarvis-minimized .jarvis-hero-img { width: 88px; }
#jarvis-assistant.jarvis-minimized .jarvis-repulsor,
#jarvis-assistant.jarvis-minimized .jarvis-arc-glow { display: none; }

.jarvis-toggle-btn {
    position: absolute; top: -8px; left: -8px; right: auto; z-index: 20;
    width: 28px; height: 28px; border-radius: 50%;
    background: linear-gradient(145deg, #1e4976, #0d2137);
    border: 2px solid rgba(250, 204, 21, 0.45);
    color: #facc15; font-size: 13px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 14px rgba(0,0,0,0.45);
    transition: transform 0.25s cubic-bezier(0.34,1.25,0.64,1), background 0.2s;
}
.jarvis-toggle-btn:hover { transform: scale(1.15); background: #2563eb; color: #fff; }

/* ── 3D STAGE + CHARACTER ── */
.jarvis-3d-stage {
    position: relative;
    width: 380px; height: 340px;
    perspective: 1000px;
    perspective-origin: 60% 85%;
    transition: width 0.45s cubic-bezier(0.34, 1.25, 0.64, 1), height 0.45s cubic-bezier(0.34, 1.25, 0.64, 1);
}
#jarvis-assistant:not(.jarvis-board-open):not(.jarvis-minimized) .jarvis-3d-stage {
    width: 150px;
    height: 280px;
}
.jarvis-ground-shadow {
    position: absolute; bottom: 0; right: 28%; left: auto;
    width: 100px; height: 24px;
    background: radial-gradient(ellipse, rgba(56,189,248,0.4) 0%, transparent 70%);
    border-radius: 50%; filter: blur(8px);
    animation: jarvisShadowPulse 3s ease-in-out infinite;
    pointer-events: none;
}
.jarvis-character-wrap {
    position: absolute; bottom: 0; right: 0; left: auto;
    width: 150px; height: 280px;
    transform-style: preserve-3d;
    z-index: 4;
    cursor: pointer;
}
#jarvis-assistant.jarvis-near:not(.jarvis-board-open):not(.jarvis-minimized) .jarvis-hero-img {
    filter: drop-shadow(0 18px 36px rgba(0,0,0,0.6)) drop-shadow(0 0 36px rgba(250, 204, 21, 0.45));
}
.jarvis-hover-tip {
    position: absolute;
    bottom: 118px;
    right: 4px;
    z-index: 7;
    background: linear-gradient(135deg, #1e4976, #0d2137);
    color: #facc15;
    font-size: 0.62rem;
    font-weight: 700;
    padding: 5px 10px;
    border-radius: 10px;
    border: 1px solid rgba(250, 204, 21, 0.35);
    box-shadow: 0 6px 18px rgba(0,0,0,0.4);
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    transform: translateY(6px);
    transition: opacity 0.3s, transform 0.3s;
}
#jarvis-assistant.jarvis-near:not(.jarvis-board-open):not(.jarvis-minimized) .jarvis-hover-tip {
    opacity: 1;
    transform: translateY(0);
}
.jarvis-3d-inner {
    position: absolute; bottom: 0; right: 0; left: auto;
    width: 150px;
    transform-style: preserve-3d;
    transform-origin: center bottom;
    animation: jarvis3dIdle 5s ease-in-out infinite;
    will-change: transform;
}
.jarvis-hero-img {
    display: block; width: 150px; height: auto;
    transform: translateZ(24px);
    pointer-events: auto; user-select: none;
    cursor: pointer;
    filter: drop-shadow(0 18px 36px rgba(0,0,0,0.6)) drop-shadow(0 0 28px rgba(56,189,248,0.2));
    transition: filter 0.35s ease;
}
#jarvis-assistant.jarvis-minimized .jarvis-hero-img {
    cursor: grab;
}
.jarvis-repulsor {
    position: absolute; border-radius: 50%; pointer-events: none;
    animation: repulsorPulse 1.1s ease-in-out infinite;
}
.jarvis-repulsor-l { width: 26px; height: 26px; bottom: 36%; left: 6%;
    background: radial-gradient(circle, #bae6fd, rgba(56,189,248,0.4) 50%, transparent 70%); transform: translateZ(32px); }
.jarvis-repulsor-r { width: 26px; height: 26px; bottom: 36%; right: 6%;
    background: radial-gradient(circle, #bae6fd, rgba(56,189,248,0.4) 50%, transparent 70%); transform: translateZ(32px); animation-delay: 0.25s; }
.jarvis-repulsor-feet { width: 48px; height: 18px; bottom: 1%; left: 50%; margin-left: -24px;
    background: radial-gradient(ellipse, rgba(186,230,253,0.85), transparent 70%); transform: translateZ(8px); animation-delay: 0.12s; }
.jarvis-arc-glow {
    position: absolute; top: 40%; left: 50%; width: 34px; height: 34px;
    background: radial-gradient(circle, rgba(56,189,248,0.65), transparent 70%);
    transform: translate(-50%,-50%) translateZ(38px); border-radius: 50%;
    animation: arcGlow 2s ease-in-out infinite; pointer-events: none;
}

/* ── PAPAN TANDA — keluar bila cursor dekat model ── */
.jarvis-sign-held {
    position: absolute;
    bottom: 108px; right: 88px; left: auto;
    transform: rotate(-2deg) translateY(18px) scale(0.88);
    transform-origin: bottom right;
    z-index: 6;
    pointer-events: none;
    opacity: 0;
    visibility: hidden;
    transition:
        opacity 0.4s cubic-bezier(0.34, 1.25, 0.64, 1),
        transform 0.4s cubic-bezier(0.34, 1.25, 0.64, 1),
        visibility 0.4s;
}
#jarvis-assistant.jarvis-board-open:not(.jarvis-minimized) .jarvis-sign-held {
    opacity: 1;
    visibility: visible;
    transform: rotate(-2deg) translateY(0) scale(1);
    pointer-events: auto;
}
.jarvis-sign-pole {
    position: absolute;
    bottom: -52px; right: 8px; left: auto;
    width: 6px; height: 58px;
    background: linear-gradient(90deg, #78350f, #b45309, #78350f);
    border-radius: 3px;
    box-shadow: 2px 2px 6px rgba(0,0,0,0.5);
    transform-origin: bottom center;
    pointer-events: none;
}
.jarvis-sign-pole::after {
    content: '';
    position: absolute; bottom: -4px; left: 50%; margin-left: -10px;
    width: 20px; height: 6px; border-radius: 50%;
    background: rgba(0,0,0,0.35); filter: blur(2px);
}

.jarvis-board {
    position: relative;
    width: 280px;
    background: linear-gradient(160deg, #fef9c3 0%, #fde68a 45%, #fbbf24 100%);
    border: 4px solid #92400e;
    border-radius: 6px;
    padding: 0;
    box-shadow:
        0 12px 36px rgba(0,0,0,0.55),
        inset 0 2px 0 rgba(255,255,255,0.5),
        inset 0 -3px 6px rgba(146,64,14,0.25);
    transform: none;
}
.jarvis-board.jarvis-sent { animation: signSent 0.7s ease-out; }

.jarvis-board-header {
    padding: 8px 12px 6px;
    font-size: 0.68rem; font-weight: 800; color: #78350f;
    text-transform: uppercase; letter-spacing: 0.08em;
    display: flex; align-items: center; gap: 6px;
    border-bottom: 2px dashed rgba(146,64,14,0.35);
    cursor: grab;
    touch-action: none;
}
.jarvis-board-header:active { cursor: grabbing; }
.jarvis-drag-grip {
    margin-left: auto;
    opacity: 0.45;
    font-size: 0.85rem;
    letter-spacing: -2px;
    line-height: 1;
    pointer-events: none;
}
.jarvis-board-header .dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #22c55e; box-shadow: 0 0 8px #22c55e;
    animation: repulsorPulse 1.8s infinite;
    pointer-events: none;
}

.jarvis-board-wood-top {
    height: 8px;
    background: linear-gradient(180deg, #b45309, #92400e);
    border-radius: 3px 3px 0 0;
    pointer-events: none;
}
.jarvis-board-body { padding: 8px 10px 10px; }

.jarvis-chat-log {
    max-height: 140px; min-height: 56px; overflow-y: auto;
    background: rgba(255,255,255,0.55);
    border: 2px solid #d97706; border-radius: 4px;
    padding: 8px; margin-bottom: 8px;
    font-size: 0.78rem; line-height: 1.45; color: #1c1917;
    scroll-behavior: smooth;
}
.jarvis-chat-log::-webkit-scrollbar { width: 4px; }
.jarvis-chat-log::-webkit-scrollbar-thumb { background: #b45309; border-radius: 4px; }

.jarvis-msg { margin-bottom: 8px; animation: replyPop 0.35s ease; }
.jarvis-msg:last-child { margin-bottom: 0; }
.jarvis-msg-user { text-align: right; }
.jarvis-msg-user .jarvis-msg-bubble {
    display: inline-block; text-align: left;
    background: #dbeafe; border: 1px solid #93c5fd;
    border-radius: 8px 8px 2px 8px; padding: 6px 9px;
    color: #1e3a5f; max-width: 95%;
}
.jarvis-msg-bot .jarvis-msg-bubble {
    background: #1e293b; border: 1px solid #334155;
    border-radius: 8px 8px 8px 2px; padding: 6px 9px;
    color: #e2e8f0; max-width: 100%;
}
.jarvis-msg-bot .jarvis-msg-bubble strong { color: #facc15; font-weight: 700; }
.jarvis-msg-label { font-size: 0.58rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px; opacity: 0.65; }
.jarvis-msg-user .jarvis-msg-label { color: #1d4ed8; }
.jarvis-msg-bot .jarvis-msg-label { color: #78350f; }
.jarvis-msg-loading .jarvis-msg-bubble { color: #64748b; font-style: italic; }

.jarvis-board textarea {
    width: 100%; min-height: 52px; max-height: 80px; resize: none;
    background: rgba(255,255,255,0.72);
    border: 2px solid #d97706;
    border-radius: 4px; padding: 9px 11px;
    color: #1c1917; font-family: inherit; font-size: 0.84rem; line-height: 1.45;
    outline: none; box-shadow: inset 0 2px 4px rgba(146,64,14,0.12);
    transition: border-color 0.25s, box-shadow 0.25s;
}
.jarvis-board textarea:focus {
    border-color: #2563eb;
    box-shadow: inset 0 2px 4px rgba(146,64,14,0.1), 0 0 0 3px rgba(37,99,235,0.2);
}
.jarvis-board textarea::placeholder { color: #78716c; }

.jarvis-board-hint {
    font-size: 0.62rem; color: #92400e; margin-top: 7px;
    font-weight: 600; display: flex; align-items: center; gap: 4px;
}
.jarvis-board-hint kbd {
    background: rgba(146,64,14,0.15); color: #78350f;
    padding: 1px 6px; border-radius: 3px; font-size: 0.6rem; font-weight: 800;
}

.jarvis-reply-bubble { display: none; }

@media (max-width: 520px) {
    #jarvis-assistant { transform: scale(0.78); transform-origin: bottom right; }
    .jarvis-3d-stage { width: 310px; height: 300px; }
    .jarvis-sign-held { right: 72px; left: auto; bottom: 90px; }
    .jarvis-board { width: 220px; }
    .jarvis-chat-log { max-height: 110px; }
}
</style>
<?php }

function renderChatbox() {
    if (empty($_SESSION['user_id'])) return;

    $nama = htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? 'Pengguna');
    $img  = htmlspecialchars(chatboxAsset('ironman-jarvis-cutout.png'));
    ?>
<div id="jarvis-assistant" role="complementary" aria-label="JARVIS AI Assistant">
    <div class="jarvis-3d-stage" id="jarvis-stage">
        <div class="jarvis-ground-shadow"></div>

        <!-- Papan chat — fixed, tidak ikut animasi 3D character -->
        <div class="jarvis-sign-held" id="jarvis-sign">
            <div class="jarvis-sign-pole"></div>
            <div class="jarvis-board" id="jarvis-board">
                <div class="jarvis-board-wood-top"></div>
                <div class="jarvis-board-header" id="jarvis-drag-handle" title="Seret untuk alihkan">
                    <span class="dot"></span>
                    JARVIS · <?= $nama ?>
                    <span class="jarvis-drag-grip" aria-hidden="true">⋮⋮</span>
                </div>
                <div class="jarvis-board-body">
                    <div class="jarvis-chat-log" id="jarvis-chat-log">
                        <div class="jarvis-msg jarvis-msg-bot">
                            <div class="jarvis-msg-label">JARVIS</div>
                                    <div class="jarvis-msg-bubble">Hai! Saya JARVIS — assistant pintar macam ChatGPT 👋 Tanya apa-apa pasal sistem ni, saya jawab ikut data sebenar. Contoh: <em>Macam mana flow permohonan?</em> atau <em>Apa itu Speedbiz?</em></div>
                        </div>
                    </div>
                            <textarea id="jarvis-input" placeholder="Tanya apa-apa di sini..." maxlength="1000" rows="2"></textarea>
                    <div class="jarvis-board-hint"><kbd>Enter</kbd> hantar · seret header untuk alih</div>
                </div>
            </div>
        </div>

        <div class="jarvis-character-wrap" id="jarvis-char" title="Dekatkan cursor untuk buka chat">
            <span class="jarvis-hover-tip" aria-hidden="true">💬 Hover untuk chat</span>
            <div class="jarvis-3d-inner" id="jarvis-3d-inner">
                <img src="<?= $img ?>" alt="JARVIS" class="jarvis-hero-img" id="jarvis-hero" width="150" height="266" draggable="false">
                <div class="jarvis-repulsor jarvis-repulsor-l"></div>
                <div class="jarvis-repulsor jarvis-repulsor-r"></div>
                <div class="jarvis-repulsor jarvis-repulsor-feet"></div>
                <div class="jarvis-arc-glow"></div>
            </div>
        </div>
    </div>

    <button type="button" class="jarvis-toggle-btn" id="jarvis-toggle" title="Kecilkan / Besarkan">−</button>
</div>
<?php }

function chatboxJS() {
    if (empty($_SESSION['user_id'])) return;
    ?>
<script>
(function initJarvis() {
    const wrap   = document.getElementById('jarvis-assistant');
    if (!wrap) return;

    const board  = document.getElementById('jarvis-board');
    const input  = document.getElementById('jarvis-input');
    const inner  = document.getElementById('jarvis-3d-inner');
    const stage  = document.getElementById('jarvis-stage');
    const chatLog = document.getElementById('jarvis-chat-log');
    const toggle = document.getElementById('jarvis-toggle');
    const dragHandle = document.getElementById('jarvis-drag-handle');
    const char = document.getElementById('jarvis-char');
    const sign = document.getElementById('jarvis-sign');
    const POS_KEY = 'jarvis_chatbox_pos';
    let idleAnim = true;
    let busy = false;
    let boardPinned = false;
    let hideBoardTimer = null;
    let dragging = false;
    let dragStartX = 0, dragStartY = 0, dragStartLeft = 0, dragStartTop = 0;

    function isInZone(x, y) {
        const pad = 28;
        const expand = (el) => {
            if (!el) return false;
            const r = el.getBoundingClientRect();
            return x >= r.left - pad && x <= r.right + pad && y >= r.top - pad && y <= r.bottom + pad;
        };
        if (expand(char)) return true;
        if (wrap.classList.contains('jarvis-board-open') && expand(sign)) return true;
        return false;
    }

    function openBoard() {
        if (wrap.classList.contains('jarvis-minimized')) return;
        clearTimeout(hideBoardTimer);
        wrap.classList.add('jarvis-board-open');
    }

    function closeBoard() {
        if (boardPinned || busy || wrap.classList.contains('jarvis-minimized')) return;
        wrap.classList.remove('jarvis-board-open');
        wrap.classList.remove('jarvis-near');
    }

    function scheduleCloseBoard() {
        clearTimeout(hideBoardTimer);
        hideBoardTimer = setTimeout(closeBoard, 320);
    }

    function updateBoardProximity(e) {
        if (wrap.classList.contains('jarvis-minimized') || dragging) return;
        const x = e.clientX;
        const y = e.clientY;
        const nearChar = (() => {
            const r = char.getBoundingClientRect();
            const pad = 36;
            return x >= r.left - pad && x <= r.right + pad && y >= r.top - pad && y <= r.bottom + pad;
        })();

        wrap.classList.toggle('jarvis-near', nearChar && !wrap.classList.contains('jarvis-board-open'));

        if (isInZone(x, y)) {
            openBoard();
        } else if (!boardPinned && !busy) {
            scheduleCloseBoard();
        }
    }

    char.addEventListener('mouseenter', openBoard);
    sign.addEventListener('mouseenter', openBoard);
    sign.addEventListener('mouseleave', (e) => {
        if (!isInZone(e.clientX, e.clientY) && !boardPinned && !busy) scheduleCloseBoard();
    });
    char.addEventListener('mouseleave', (e) => {
        if (wrap.classList.contains('jarvis-board-open') && isInZone(e.clientX, e.clientY)) return;
        if (!boardPinned && !busy) scheduleCloseBoard();
    });

    input.addEventListener('focus', () => { boardPinned = true; openBoard(); });
    input.addEventListener('blur', () => {
        boardPinned = false;
        scheduleCloseBoard();
    });

    document.addEventListener('mousemove', updateBoardProximity);

    function clampPosition(left, top) {
        const w = wrap.offsetWidth || (wrap.classList.contains('jarvis-board-open') ? 380 : 150);
        const h = wrap.offsetHeight || (wrap.classList.contains('jarvis-board-open') ? 340 : 280);
        return {
            left: Math.max(8, Math.min(left, window.innerWidth - w - 8)),
            top: Math.max(8, Math.min(top, window.innerHeight - h - 8)),
        };
    }

    function applyFixedPosition(left, top) {
        const p = clampPosition(left, top);
        wrap.style.left = p.left + 'px';
        wrap.style.top = p.top + 'px';
        wrap.style.right = 'auto';
        wrap.style.bottom = 'auto';
        return p;
    }

    function loadSavedPosition() {
        try {
            const raw = localStorage.getItem(POS_KEY);
            if (!raw) return;
            const p = JSON.parse(raw);
            if (typeof p.left === 'number' && typeof p.top === 'number') {
                applyFixedPosition(p.left, p.top);
            }
        } catch (_) {}
    }

    function savePosition() {
        const rect = wrap.getBoundingClientRect();
        localStorage.setItem(POS_KEY, JSON.stringify({
            left: Math.round(rect.left),
            top: Math.round(rect.top),
        }));
    }

    function canStartDrag(e) {
        if (e.target.closest('.jarvis-toggle-btn, textarea, input, .jarvis-chat-log, .jarvis-board-body')) {
            return false;
        }
        if (wrap.classList.contains('jarvis-minimized')) {
            return e.target.closest('.jarvis-hero-img, .jarvis-3d-inner, .jarvis-character-wrap, .jarvis-3d-stage');
        }
        return e.target.closest('#jarvis-drag-handle');
    }

    function onDragStart(e) {
        if (!canStartDrag(e)) return;
        dragging = true;
        idleAnim = false;
        wrap.classList.add('jarvis-dragging');
        const rect = wrap.getBoundingClientRect();
        applyFixedPosition(rect.left, rect.top);
        const pt = e.touches ? e.touches[0] : e;
        dragStartX = pt.clientX;
        dragStartY = pt.clientY;
        dragStartLeft = rect.left;
        dragStartTop = rect.top;
        e.preventDefault();
    }

    function onDragMove(e) {
        if (!dragging) return;
        const pt = e.touches ? e.touches[0] : e;
        applyFixedPosition(
            dragStartLeft + (pt.clientX - dragStartX),
            dragStartTop + (pt.clientY - dragStartY)
        );
        e.preventDefault();
    }

    function onDragEnd() {
        if (!dragging) return;
        dragging = false;
        wrap.classList.remove('jarvis-dragging');
        idleAnim = true;
        if (inner) {
            inner.style.animation = '';
            inner.style.transform = '';
        }
        savePosition();
    }

    loadSavedPosition();

    wrap.addEventListener('mousedown', onDragStart);
    document.addEventListener('mousemove', onDragMove);
    document.addEventListener('mouseup', onDragEnd);
    wrap.addEventListener('touchstart', onDragStart, { passive: false });
    document.addEventListener('touchmove', onDragMove, { passive: false });
    document.addEventListener('touchend', onDragEnd);

    window.addEventListener('resize', () => {
        const rect = wrap.getBoundingClientRect();
        if (wrap.style.left || wrap.style.top) {
            applyFixedPosition(rect.left, rect.top);
        }
    });

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function fmtBot(text) {
        return escHtml(text)
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }
    function appendMsg(role, html) {
        const div = document.createElement('div');
        div.className = 'jarvis-msg jarvis-msg-' + role;
        div.innerHTML = '<div class="jarvis-msg-label">' + (role === 'user' ? 'Anda' : 'JARVIS') + '</div><div class="jarvis-msg-bubble">' + html + '</div>';
        chatLog.appendChild(div);
        chatLog.scrollTop = chatLog.scrollHeight;
        return div;
    }

    document.addEventListener('mousemove', (e) => {
        if (!inner || !idleAnim || dragging || wrap.classList.contains('jarvis-minimized')) return;
        const rect = stage.getBoundingClientRect();
        const cx = rect.left + rect.width * 0.65;
        const cy = rect.top + rect.height * 0.65;
        const dx = (e.clientX - cx) / window.innerWidth;
        const dy = (e.clientY - cy) / window.innerHeight;
        inner.style.animation = 'none';
        inner.style.transform = `translate3d(0, ${-10}px, 6px) rotateY(${dx * 24}deg) rotateX(${-dy * 14}deg)`;
    });

    stage.addEventListener('mouseleave', () => {
        idleAnim = true;
        inner.style.animation = '';
        inner.style.transform = '';
    });
    stage.addEventListener('mouseenter', () => { idleAnim = false; });

    function runAction(action) {
        if (!action || !action.type) return;
        if (action.type === 'redirect' && action.url) {
            busy = true;
            input.disabled = true;
            setTimeout(() => { window.location.href = action.url; }, action.delay || 800);
            return;
        }
        if (action.type === 'minimize') {
            wrap.classList.add('jarvis-minimized');
            toggle.textContent = '+';
            toggle.title = 'Besarkan';
        }
        if (action.type === 'open') {
            wrap.classList.remove('jarvis-minimized');
            toggle.textContent = '−';
            toggle.title = 'Kecilkan';
        }
    }

    async function submitMessage() {
        const text = input.value.trim();
        if (!text || busy) {
            if (!text) input.focus();
            return;
        }

        busy = true;
        openBoard();
        input.disabled = true;
        board.classList.add('jarvis-sent');
        setTimeout(() => board.classList.remove('jarvis-sent'), 700);

        appendMsg('user', escHtml(text));
        input.value = '';

        const loading = appendMsg('bot', 'Sebentar ya, saya sedang fikir jawapan…');
        loading.classList.add('jarvis-msg-loading');

        try {
            const res = await fetch('jarvis_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ message: text })
            });
            const data = await res.json();
            loading.remove();

            if (data.ok && data.answer) {
                appendMsg('bot', fmtBot(data.answer));
                if (data.action) {
                    runAction(data.action);
                    if (data.action.type === 'redirect') return;
                }
            } else {
                appendMsg('bot', escHtml(data.error || 'Alamak, saya tak faham soalan tu. Cuba tanya semula ya.'));
            }
        } catch (err) {
            loading.remove();
            appendMsg('bot', 'Alamak, connection putus. Pastikan awak masih log masuk, then cuba lagi.');
        }

        busy = false;
        input.disabled = false;
        input.focus();
    }

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            submitMessage();
        }
    });

    toggle.addEventListener('click', () => {
        const min = wrap.classList.toggle('jarvis-minimized');
        toggle.textContent = min ? '+' : '−';
        toggle.title = min ? 'Besarkan' : 'Kecilkan';
        if (min) {
            wrap.classList.remove('jarvis-board-open');
            wrap.classList.remove('jarvis-near');
        } else {
            wrap.classList.remove('jarvis-board-open');
            if (inner) inner.style.animation = '';
        }
    });
})();
</script>
<?php }
