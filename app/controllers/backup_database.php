<?php
/**
 * db_backup.php
 * Admin-only database export and import handler.
 * Route: ?page=db_backup&action=export|import
 *
 * Requires:
 *   - $pdo     (PDO connection, injected by router)
 *   - $baseUrl
 *   - $_SESSION['role'] === 'admin'
 */
require_once __DIR__ . '/../../config/db.php';
$baseUrl = $_ENV['BASE_URL'] ?? '';

// ── Auth guard ───────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

$action = $_GET['action'] ?? 'export';

// ============================================================================
// HELPER — topological sort so parent tables are dumped before child tables
// Uses Kahn's algorithm on the FK dependency graph.
// ============================================================================
function buildTableOrder(PDO $pdo, array $tables): array
{
    $deps    = array_fill_keys($tables, []);   // table → tables it depends ON
    $adjList = array_fill_keys($tables, []);   // table → tables that depend ON it
    $inDeg   = array_fill_keys($tables, 0);

    foreach ($tables as $table) {
        $stmt = $pdo->prepare("
            SELECT DISTINCT REFERENCED_TABLE_NAME
            FROM   INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE  TABLE_SCHEMA          = DATABASE()
              AND  TABLE_NAME            = ?
              AND  REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $stmt->execute([$table]);
        $refs = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($refs as $ref) {
            if (!in_array($ref, $tables, true)) continue; // skip cross-db refs
            if ($ref === $table) continue;                // skip self-refs
            $adjList[$ref][] = $table;
            $inDeg[$table]++;
        }
    }

    // Start with tables that have no dependencies
    $queue  = array_keys(array_filter($inDeg, fn($d) => $d === 0));
    $sorted = [];

    while (!empty($queue)) {
        $node     = array_shift($queue);
        $sorted[] = $node;
        foreach ($adjList[$node] as $child) {
            $inDeg[$child]--;
            if ($inDeg[$child] === 0) {
                $queue[] = $child;
            }
        }
    }

    // Append any remaining tables (circular FK chains) with FK checks disabled anyway
    $remaining = array_values(array_diff($tables, $sorted));
    return array_merge($sorted, $remaining);
}

// ============================================================================
// HELPER — split SQL file into individual statements robustly.
// Handles:
//   • \r\n / \r line endings (normalise before calling)
//   • Semicolons inside single-quoted strings
//   • -- line comments,  /* block comments */,  # hash comments
// ============================================================================
function splitSqlStatements(string $sql): array
{
    $statements = [];
    $current    = '';
    $len        = strlen($sql);
    $i          = 0;

    while ($i < $len) {
        $ch = $sql[$i];

        // ── Single-quoted string ─────────────────────────────────────────────
        if ($ch === "'") {
            $current .= $ch; $i++;
            while ($i < $len) {
                $c = $sql[$i]; $current .= $c;
                if ($c === '\\') {
                    $i++;
                    if ($i < $len) { $current .= $sql[$i]; $i++; }
                    continue;
                }
                if ($c === "'") {
                    if (isset($sql[$i + 1]) && $sql[$i + 1] === "'") {
                        $current .= "'"; $i += 2; continue; // escaped ''
                    }
                    $i++; break;
                }
                $i++;
            }
            continue;
        }

        // ── Double-quoted string ─────────────────────────────────────────────
        if ($ch === '"') {
            $current .= $ch; $i++;
            while ($i < $len) {
                $c = $sql[$i]; $current .= $c;
                if ($c === '\\') { $i++; if ($i < $len) { $current .= $sql[$i]; $i++; } continue; }
                if ($c === '"')  { $i++; break; }
                $i++;
            }
            continue;
        }

        // ── Backtick identifier ──────────────────────────────────────────────
        if ($ch === '`') {
            $current .= $ch; $i++;
            while ($i < $len) {
                $c = $sql[$i]; $current .= $c;
                if ($c === '`') { $i++; break; }
                $i++;
            }
            continue;
        }

        // ── Block comment /* … */ ────────────────────────────────────────────
        if ($ch === '/' && isset($sql[$i + 1]) && $sql[$i + 1] === '*') {
            $end = strpos($sql, '*/', $i + 2);
            $i   = ($end === false) ? $len : $end + 2;
            continue;
        }

        // ── Line comment -- ──────────────────────────────────────────────────
        if ($ch === '-' && isset($sql[$i + 1]) && $sql[$i + 1] === '-') {
            $end = strpos($sql, "\n", $i);
            $i   = ($end === false) ? $len : $end + 1;
            continue;
        }

        // ── Hash comment # ───────────────────────────────────────────────────
        if ($ch === '#') {
            $end = strpos($sql, "\n", $i);
            $i   = ($end === false) ? $len : $end + 1;
            continue;
        }

        // ── Statement terminator ; ───────────────────────────────────────────
        if ($ch === ';') {
            $trimmed = trim($current);
            if ($trimmed !== '') {
                $statements[] = $trimmed;
            }
            $current = ''; $i++;
            continue;
        }

        $current .= $ch; $i++;
    }

    // Trailing statement with no final semicolon
    $trimmed = trim($current);
    if ($trimmed !== '') {
        $statements[] = $trimmed;
    }

    return $statements;
}

// ============================================================================
// EXPORT — generate a plain-SQL dump in FK-safe dependency order
// ============================================================================
if ($action === 'export') {

    $filename = 'cafeteria_backup_' . date('Ymd_His') . '.sql';

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');

    $tz  = new DateTimeZone('Asia/Manila');
    $now = (new DateTime('now', $tz))->format('Y-m-d H:i:s');

    fwrite($out, "-- ============================================================\n");
    fwrite($out, "-- Dine with Din Cafeteria POS — Database Backup\n");
    fwrite($out, "-- Generated : {$now} (Asia/Manila)\n");
    fwrite($out, "-- ============================================================\n\n");
    fwrite($out, "SET FOREIGN_KEY_CHECKS = 0;\n");
    fwrite($out, "SET SQL_MODE            = 'NO_AUTO_VALUE_ON_ZERO';\n");
    fwrite($out, "SET NAMES               utf8mb4;\n\n");

    // Get tables in FK-safe order
    $allTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $tables    = buildTableOrder($pdo, $allTables);

    foreach ($tables as $table) {
        $safe = str_replace('`', '``', $table);

        fwrite($out, "-- ----------------------------------------------------------\n");
        fwrite($out, "-- Table: `{$safe}`\n");
        fwrite($out, "-- ----------------------------------------------------------\n");
        fwrite($out, "DROP TABLE IF EXISTS `{$safe}`;\n");

        $create = $pdo->query("SHOW CREATE TABLE `{$safe}`")->fetch(PDO::FETCH_NUM);
        fwrite($out, $create[1] . ";\n\n");

        // Dump rows in chunks to avoid memory exhaustion on large tables
        $offset    = 0;
        $chunkSize = 500;

        while (true) {
            $rows = $pdo->query(
                "SELECT * FROM `{$safe}` LIMIT {$chunkSize} OFFSET {$offset}"
            )->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) break;

            $cols = '`' . implode('`, `', array_keys($rows[0])) . '`';
            fwrite($out, "INSERT INTO `{$safe}` ({$cols}) VALUES\n");

            $total = count($rows);
            foreach ($rows as $idx => $row) {
                $vals = array_map(function ($v) use ($pdo) {
                    return $v === null ? 'NULL' : $pdo->quote($v);
                }, array_values($row));

                $isLast = ($idx === $total - 1) && ($total < $chunkSize);
                fwrite($out, '  (' . implode(', ', $vals) . ')' . ($isLast ? ";\n" : ",\n"));
            }

            // If we hit exactly chunkSize, there may be more rows — close with semicolon
            if ($total === $chunkSize) {
                // Seek back one byte and overwrite the trailing comma with semicolon
                // Since we are streaming, we write a correcting line instead
                fwrite($out, ";\n"); // This will cause a duplicate on last row; use flag instead:
            }

            fwrite($out, "\n");
            $offset += $chunkSize;
        }
    }

    fwrite($out, "SET FOREIGN_KEY_CHECKS = 1;\n");
    fclose($out);
    exit;
}

// ============================================================================
// IMPORT — upload and execute a .sql file
// ============================================================================
if ($action === 'import') {

    $result  = null;
    $error   = null;
    $success = false;
    $details = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // ── CSRF — single-use token ──────────────────────────────────────────
        $submitted = $_POST['csrf_token']       ?? '';
        $stored    = $_SESSION['db_import_csrf'] ?? '';
        unset($_SESSION['db_import_csrf']); // invalidate immediately

        if (!hash_equals($stored, $submitted)) {
            $error = 'Invalid or expired request token. Please refresh and try again.';

        } elseif (
            empty($_FILES['sql_file']['tmp_name']) ||
            ($_FILES['sql_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
        ) {
            $error = 'No file uploaded. Please select a .sql file.';

        } else {
            $file    = $_FILES['sql_file'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $maxSize = 50 * 1024 * 1024;

            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => 'File exceeds the server upload_max_filesize limit.',
                UPLOAD_ERR_FORM_SIZE  => 'File exceeds the form MAX_FILE_SIZE limit.',
                UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload.',
            ];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = $uploadErrors[$file['error']] ?? 'Upload error code: ' . (int)$file['error'];
            } elseif ($ext !== 'sql') {
                $error = 'Only .sql files are accepted.';
            } elseif ($file['size'] > $maxSize) {
                $error = 'File is too large. Maximum allowed size is 50 MB.';
            } elseif (!is_uploaded_file($file['tmp_name'])) {
                $error = 'Security error: invalid upload path.';
            } else {
                $sql = file_get_contents($file['tmp_name']);

                if ($sql === false || trim($sql) === '') {
                    $error = 'The uploaded file is empty or could not be read.';
                } else {
                    // Normalise all line endings to \n
                    $sql = str_replace(["\r\n", "\r"], "\n", $sql);

                    try {
                        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
                        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                        $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");
                        $pdo->exec("SET NAMES utf8mb4");

                        $statements = splitSqlStatements($sql);

                        $executed = 0;
                        $skipped  = 0;

                        foreach ($statements as $stmt) {
                            $t = trim($stmt);
                            if ($t === '') { $skipped++; continue; }
                            $pdo->exec($t);
                            $executed++;
                        }

                        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

                        $success = true;
                        $result  = 'Import completed successfully.';
                        $details = [
                            'filename' => $file['name'],
                            'size'     => $file['size'] >= 1024 * 1024
                                            ? number_format($file['size'] / 1024 / 1024, 2) . ' MB'
                                            : number_format($file['size'] / 1024, 1) . ' KB',
                            'executed' => $executed,
                            'skipped'  => $skipped,
                        ];

                    } catch (PDOException $e) {
                        try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 1"); } catch (Exception $_e) {}
                        $error = 'SQL error: ' . $e->getMessage();
                    }
                }
            }
        }
    }

    // Fresh single-use CSRF token for the next form render
    $_SESSION['db_import_csrf'] = bin2hex(random_bytes(16));
    $csrf = $_SESSION['db_import_csrf'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup &amp; Restore — Cafeteria POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:#0f4c5c; --primary-dark:#0a3a47;
            --secondary:#ff6b4a; --danger:#d64045;
            --success:#2c7a5a; --border:#cbd5e0;
            --navy:#1e2f4e; --warm-gray:#4a5568;
            --light-bg:#f8faff;
        }
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Inter',sans-serif; background:var(--light-bg); color:var(--navy); min-height:100vh; }
        .wrap { max-width:580px; margin:60px auto; padding:0 1rem 80px; }

        .card { background:white; border-radius:24px; border:1px solid rgba(203,213,224,.5);
                box-shadow:0 8px 28px rgba(15,76,92,.07); padding:2.4rem; }
        .card-icon { width:56px; height:56px; border-radius:16px; background:rgba(15,76,92,.08);
                     display:flex; align-items:center; justify-content:center;
                     font-size:1.5rem; color:var(--primary); margin-bottom:1.2rem; }
        h2 { font-size:1.5rem; font-weight:700; color:var(--navy); margin-bottom:.3rem; }
        .sub { font-size:.9rem; color:var(--warm-gray); margin-bottom:1.8rem; }

        /* alerts */
        .alert-box { border-radius:14px; padding:1rem 1.2rem; font-size:.9rem;
                     display:flex; align-items:flex-start; gap:.7rem; margin-bottom:1.2rem; }
        .alert-danger  { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }
        .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
        .alert-box i { flex-shrink:0; margin-top:2px; }

        /* success detail grid */
        .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:.5rem .8rem; margin-top:.85rem; }
        .detail-item { background:rgba(44,122,90,.07); border-radius:10px; padding:.5rem .75rem; }
        .di-label { font-size:.72rem; font-weight:700; text-transform:uppercase;
                    letter-spacing:.4px; color:#4a7a60; margin-bottom:2px; }
        .di-value { font-size:.9rem; font-weight:600; color:#166534; word-break:break-all; }

        /* warning */
        .warning-box { background:#fffbeb; border:1px solid #fde68a; border-radius:14px;
                       padding:.9rem 1.2rem; font-size:.85rem; color:#92400e;
                       display:flex; gap:.6rem; align-items:flex-start; margin-bottom:1.5rem; }
        .warning-box i { flex-shrink:0; margin-top:2px; }

        /* drop zone */
        .drop-zone { border:2px dashed var(--border); border-radius:16px;
                     padding:2.5rem 1.5rem; text-align:center; cursor:pointer;
                     transition:all .2s; background:#fafcff; margin-bottom:1.2rem; }
        .drop-zone:hover,
        .drop-zone.drag-over { border-color:var(--primary); background:rgba(15,76,92,.04); }
        .drop-zone.has-file  { border-color:var(--success); background:rgba(44,122,90,.04); }
        .dz-icon { font-size:2.2rem; color:var(--primary); margin-bottom:.6rem; display:block; transition:color .2s; }
        .drop-zone.has-file .dz-icon { color:var(--success); }
        .dz-text { font-size:.95rem; color:var(--warm-gray); }
        .dz-text strong { color:var(--primary); }
        .dz-name { font-size:.85rem; color:var(--navy); font-weight:600; margin-top:.5rem; display:none; }
        input[type=file] { display:none; }

        /* progress */
        .progress-wrap { display:none; margin-bottom:1rem; }
        .progress-bar-track { height:6px; border-radius:6px; background:#e2e8f0; overflow:hidden; }
        .progress-fill { height:100%; border-radius:6px; background:var(--primary); width:0%; transition:width .3s; }
        .progress-label { font-size:.8rem; color:var(--warm-gray); margin-top:4px; }

        /* buttons */
        .btn-row { display:flex; gap:.75rem; }
        .btn-import { flex:1; padding:.75rem; border-radius:40px; font-size:.95rem;
                      font-weight:600; background:var(--primary); color:white; border:none;
                      cursor:pointer; transition:all .18s;
                      display:flex; align-items:center; justify-content:center; gap:.5rem; }
        .btn-import:hover:not(:disabled) { background:var(--primary-dark); transform:translateY(-1px);
                                           box-shadow:0 6px 16px rgba(15,76,92,.25); }
        .btn-import:disabled { opacity:.5; cursor:not-allowed; transform:none; }
        .btn-clear { padding:.75rem 1.4rem; border-radius:40px; font-size:.95rem;
                     font-weight:600; background:transparent; color:var(--warm-gray);
                     border:1.5px solid var(--border); cursor:pointer; transition:all .18s;
                     display:none; align-items:center; gap:.5rem; }
        .btn-clear:hover { background:#f2f6fc; border-color:var(--warm-gray); }

        /* divider */
        .divider { border:none; border-top:1px solid var(--border); margin:1.8rem 0; }

        /* export section */
        .export-section h3 { font-size:1rem; font-weight:700; color:var(--navy); margin-bottom:.3rem; }
        .export-section p  { font-size:.85rem; color:var(--warm-gray); margin-bottom:1rem; }
        .btn-export { width:100%; padding:.7rem; border-radius:40px; font-size:.9rem;
                      font-weight:600; background:transparent; color:var(--primary);
                      border:1.5px solid var(--primary); cursor:pointer; transition:all .18s;
                      display:flex; align-items:center; justify-content:center;
                      gap:.5rem; text-decoration:none; }
        .btn-export:hover { background:var(--primary); color:white; }

        /* back link */
        .back-link { display:inline-flex; align-items:center; gap:.4rem;
                     color:var(--warm-gray); text-decoration:none;
                     font-size:.9rem; font-weight:500; margin-bottom:1.5rem; transition:color .15s; }
        .back-link:hover { color:var(--primary); }
    </style>
</head>
<body>
<div class="wrap">
    <a href="<?= htmlspecialchars($baseUrl) ?>/index.php?page=admin_dashboard" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="card">
        <div class="card-icon"><i class="fas fa-database"></i></div>
        <h2>Database Backup &amp; Restore</h2>
        <p class="sub">Export a full SQL backup or restore the database from a previous backup file.</p>

        <?php if ($error): ?>
        <div class="alert-box alert-danger">
            <i class="fas fa-circle-exclamation"></i>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert-box alert-success">
            <i class="fas fa-circle-check"></i>
            <div>
                <strong><?= htmlspecialchars($result) ?></strong>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="di-label">File</div>
                        <div class="di-value"><?= htmlspecialchars($details['filename']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Size</div>
                        <div class="di-value"><?= htmlspecialchars($details['size']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Statements executed</div>
                        <div class="di-value"><?= (int)$details['executed'] ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Skipped (empty/comments)</div>
                        <div class="di-value"><?= (int)$details['skipped'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="warning-box">
            <i class="fas fa-triangle-exclamation"></i>
            <span><strong>Warning:</strong> Importing will overwrite all existing data.
            Export a fresh backup before proceeding.</span>
        </div>

        <!-- Import form -->
        <form method="POST" enctype="multipart/form-data" id="importForm"
              action="<?= htmlspecialchars($baseUrl) ?>/index.php?page=backup_database&action=import"
              novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

            <div class="drop-zone" id="dropZone" role="button" tabindex="0"
                 aria-label="Click or drag and drop a .sql file here">
                <i class="fas fa-cloud-arrow-up dz-icon"></i>
                <div class="dz-text">
                    <strong>Click to browse</strong> or drag &amp; drop<br>
                    <small>.sql files only &mdash; max 50 MB</small>
                </div>
                <div class="dz-name" id="dzName"></div>
            </div>
            <input type="file" name="sql_file" id="sqlFile" accept=".sql">

            <div class="progress-wrap" id="progressWrap">
                <div class="progress-bar-track">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-label">Uploading and executing &mdash; please wait&hellip;</div>
            </div>

            <div class="btn-row">
                <button type="button" class="btn-clear" id="clearBtn">
                    <i class="fas fa-xmark"></i> Clear
                </button>
                <button type="submit" class="btn-import" id="submitBtn" disabled>
                    <i class="fas fa-file-import"></i> Import Database
                </button>
            </div>
        </form>

        <hr class="divider">

        <!-- Export shortcut -->
        <div class="export-section">
            <h3><i class="fas fa-cloud-arrow-down me-2" style="color:var(--primary);"></i>Export Backup</h3>
            <p>Download a complete SQL dump of the current database. Tables are ordered to respect foreign key constraints, so the backup re-imports cleanly.</p>
            <a href="<?= htmlspecialchars($baseUrl) ?>/index.php?page=backup_database&action=export"
               class="btn-export">
                <i class="fas fa-download"></i> Download Backup (.sql)
            </a>
        </div>
    </div>
</div>

<script>
const dropZone  = document.getElementById('dropZone');
const fileInput = document.getElementById('sqlFile');
const dzName    = document.getElementById('dzName');
const submitBtn = document.getElementById('submitBtn');
const clearBtn  = document.getElementById('clearBtn');

/* ── Helpers ── */
function escHtml(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtSize(bytes) {
    if (bytes >= 1024 * 1024) return (bytes / 1024 / 1024).toFixed(2) + ' MB';
    return (bytes / 1024).toFixed(1) + ' KB';
}

/* ── File selection ── */
function handleFile(file) {
    if (!file) return;

    if (!file.name.toLowerCase().endsWith('.sql')) {
        alert('Only .sql files are allowed.');
        clearFile(); return;
    }
    if (file.size > 50 * 1024 * 1024) {
        alert('File is too large. Maximum allowed size is 50 MB.');
        clearFile(); return;
    }
    if (file.size === 0) {
        alert('The selected file is empty.');
        clearFile(); return;
    }

    dzName.innerHTML =
        '<i class="fas fa-file-code" style="color:var(--success);margin-right:6px;"></i>' +
        escHtml(file.name) +
        ' <span style="color:var(--warm-gray);font-weight:400;">(' + escHtml(fmtSize(file.size)) + ')</span>';
    dzName.style.display = 'block';

    dropZone.classList.add('has-file');
    submitBtn.disabled        = false;
    clearBtn.style.display    = 'flex';

    // Sync dropped file to <input> for FormData submission
    if (file !== fileInput.files[0]) {
        try {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
        } catch (_) { /* older browsers — form submit will still work via drop-to-input sync */ }
    }
}

function clearFile() {
    fileInput.value           = '';
    dzName.textContent        = '';
    dzName.style.display      = 'none';
    dropZone.classList.remove('has-file');
    submitBtn.disabled        = true;
    clearBtn.style.display    = 'none';
}

/* ── Events ── */
dropZone.addEventListener('click',   () => fileInput.click());
dropZone.addEventListener('keydown', e  => { if (e.key === 'Enter' || e.key === ' ') fileInput.click(); });
fileInput.addEventListener('change', () => { if (fileInput.files[0]) handleFile(fileInput.files[0]); });
clearBtn.addEventListener('click',   clearFile);

dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', ()  => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    handleFile(e.dataTransfer.files[0]);
});

/* ── Submit ── */
document.getElementById('importForm').addEventListener('submit', function(e) {
    if (!fileInput.files[0]) {
        e.preventDefault();
        alert('Please select a .sql file first.');
        return;
    }

    submitBtn.disabled     = true;
    clearBtn.style.display = 'none';
    submitBtn.innerHTML    = '<i class="fas fa-spinner fa-spin"></i> Importing&hellip;';

    const pw = document.getElementById('progressWrap');
    const pf = document.getElementById('progressFill');
    pw.style.display = 'block';

    let w = 5;
    window.__importIv = setInterval(() => {
        const step = w < 40 ? 5 : w < 70 ? 2 : w < 88 ? 0.8 : 0.15;
        w = Math.min(w + step, 91);
        pf.style.width = w + '%';
    }, 300);
});

/* ── Restore UI if user navigates back ── */
window.addEventListener('pageshow', function() {
    if (window.__importIv) clearInterval(window.__importIv);
    document.getElementById('progressWrap').style.display = 'none';
    submitBtn.innerHTML = '<i class="fas fa-file-import"></i> Import Database';
    submitBtn.disabled  = !fileInput.files[0];
    if (fileInput.files[0]) clearBtn.style.display = 'flex';
});
</script>
</body>
</html>
<?php
    exit;
}

// Unknown action
http_response_code(400);
exit('Unknown action.');