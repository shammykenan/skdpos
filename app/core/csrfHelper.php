<?php
/**
 * CSRF protection.
 *
 * Session cookies are sent automatically by the browser on any request a third
 * party can trigger, so "is this user logged in" does not answer "did this user
 * intend this". State-changing endpoints — void, refund, discount above all —
 * need proof the request came from our own pages.
 *
 * The token travels either as an X-CSRF-Token header (fetch) or a csrf_token
 * form field; assets/js/csrf.js attaches both automatically.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Hidden input for plain HTML forms.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Meta tag + loader that teaches the page to send the token on every
 * same-origin POST, for both fetch() and normal form submits.
 */
function csrf_head(string $baseUrl = ''): string
{
    return '<meta name="csrf-token" content="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">' . "\n"
        . '<script src="' . htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8')
        . '/assets/js/csrf.js"></script>';
}

function csrf_is_valid(): bool
{
    $sent = $_POST['csrf_token']
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? '';

    if ($sent === '') {
        // JSON bodies carry it in the header, but accept it in the payload too.
        $raw = file_get_contents('php://input');
        if ($raw !== '' && $raw !== false) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded['csrf_token'])) {
                $sent = (string) $decoded['csrf_token'];
            }
        }
    }

    return $sent !== ''
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $sent);
}

/**
 * Guard a state-changing endpoint. Only checks unsafe methods, so GET pages
 * can call it harmlessly.
 *
 * @param string $as 'json' for API endpoints, 'html' for form posts.
 */
function csrf_require(string $as = 'json'): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        return;
    }
    if (csrf_is_valid()) {
        return;
    }

    error_log('[csrf] rejected ' . $method . ' ' . ($_SERVER['REQUEST_URI'] ?? '?')
        . ' from ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));

    http_response_code(419); // "page expired" — the conventional CSRF status

    if ($as === 'json') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'status'  => 'csrf_invalid',
            'error'   => 'Security token missing or expired. Please reload the page and try again.',
            'message' => 'Security token missing or expired. Please reload the page and try again.',
        ]);
        exit;
    }

    $_SESSION['error'] = 'Security token missing or expired. Please try again.';
    echo '<!doctype html><meta charset="utf-8"><p style="font:16px system-ui;padding:2rem">'
        . 'Security token missing or expired. Please go back, reload the page and try again.</p>';
    exit;
}
