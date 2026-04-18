<?php
/**
 * db_backup.php  (v3)
 * Admin-only database export / import / list / delete.
 * Route: ?page=backup_database                        → list view
 *        ?page=backup_database&action=export&label=X  → dump + download
 *        ?page=backup_database&action=download&file=X → re-download
 *        ?page=backup_database&action=import           → POST upload
 *        ?page=backup_database&action=delete           → POST delete
 *
 * Naming: cafeteria_backup_{YYYY-MM-DD}_{HH-MM-SS}_{label}.sql
 * Folder: {BASE_PATH}/backups/
 */
require_once __DIR__ . '/../../config/db.php';
$baseUrl = $_ENV['BASE_URL'] ?? '';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403); exit('Access denied.');
}

define('BACKUP_DIR',       BASE_PATH . '/backups');
define('BACKUP_MAX_FILES', 30);

if (!is_dir(BACKUP_DIR)) {
    mkdir(BACKUP_DIR, 0750, true);
    file_put_contents(BACKUP_DIR . '/.htaccess', "Deny from all\n");
}

$action = $_GET['action'] ?? 'list';

// ── Filename helpers ─────────────────────────────────────────────────────────
function makeBackupFilename(string $label = 'manual'): string {
    $label = strtolower(preg_replace('/[^a-z0-9\-]/i', '-', trim($label)) ?: 'manual');
    $label = trim($label, '-') ?: 'manual';
    $ts    = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d_H-i-s');
    return "cafeteria_backup_{$ts}_{$label}.sql";
}

function parseBackupFilename(string $fn): array {
    if (preg_match('/^cafeteria_backup_(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})_([a-z0-9\-]+)\.sql$/i', $fn, $m)) {
        $ds = $m[1] . ' ' . str_replace('-', ':', $m[2]);
        return ['date' => date('M d, Y', strtotime($m[1])), 'time' => date('h:i:s A', strtotime($ds)), 'label' => ucfirst($m[3]), 'valid' => true];
    }
    return ['date' => '—', 'time' => '—', 'label' => 'Unknown', 'valid' => false];
}

function listBackups(): array {
    $f = glob(BACKUP_DIR . '/cafeteria_backup_*.sql') ?: [];
    usort($f, fn($a, $b) => filemtime($b) - filemtime($a));
    return $f;
}
function pruneBackups(): void { foreach (array_slice(listBackups(), BACKUP_MAX_FILES) as $f) @unlink($f); }

// ── FK-safe table order ──────────────────────────────────────────────────────
function buildTableOrder(PDO $pdo, array $tables): array {
    $adj = array_fill_keys($tables, []); $inDeg = array_fill_keys($tables, 0);
    foreach ($tables as $t) {
        $st = $pdo->prepare("SELECT DISTINCT REFERENCED_TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND REFERENCED_TABLE_NAME IS NOT NULL");
        $st->execute([$t]);
        foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $r) {
            if (!in_array($r, $tables, true) || $r === $t) continue;
            $adj[$r][] = $t; $inDeg[$t]++;
        }
    }
    $q = array_keys(array_filter($inDeg, fn($d) => $d === 0)); $s = [];
    while ($q) { $n = array_shift($q); $s[] = $n; foreach ($adj[$n] as $c) { if (--$inDeg[$c] === 0) $q[] = $c; } }
    return array_merge($s, array_values(array_diff($tables, $s)));
}

// ── Write dump to file handle ────────────────────────────────────────────────
function writeDump(PDO $pdo, $fh): void {
    $now = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');
    fwrite($fh, "-- ============================================================\n-- Dine with Din Cafeteria POS — Database Backup\n-- Generated : {$now} (Asia/Manila)\n-- Host      : " . gethostname() . "\n-- ============================================================\n\nSET FOREIGN_KEY_CHECKS = 0;\nSET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\nSET NAMES utf8mb4;\n\n");
    foreach (buildTableOrder($pdo, $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN)) as $t) {
        $safe = str_replace('`', '``', $t);
        fwrite($fh, "-- ----------------------------------------------------------\n-- Table: `{$safe}`\n-- ----------------------------------------------------------\nDROP TABLE IF EXISTS `{$safe}`;\n");
        $cr = $pdo->query("SHOW CREATE TABLE `{$safe}`")->fetch(PDO::FETCH_NUM);
        fwrite($fh, $cr[1] . ";\n\n");
        $off = 0; $chunk = 500;
        while (true) {
            $rows = $pdo->query("SELECT * FROM `{$safe}` LIMIT {$chunk} OFFSET {$off}")->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) break;
            $cols = '`' . implode('`, `', array_keys($rows[0])) . '`';
            fwrite($fh, "INSERT INTO `{$safe}` ({$cols}) VALUES\n");
            $tot = count($rows);
            foreach ($rows as $i => $row) {
                $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote($v), array_values($row));
                $last = ($i === $tot - 1) && ($tot < $chunk);
                fwrite($fh, '  (' . implode(', ', $vals) . ')' . ($last ? ";\n" : ",\n"));
            }
            if ($tot === $chunk) fwrite($fh, ";\n");
            fwrite($fh, "\n"); $off += $chunk;
        }
    }
    fwrite($fh, "SET FOREIGN_KEY_CHECKS = 1;\n");
}

// ── SQL statement splitter ───────────────────────────────────────────────────
function splitSqlStatements(string $sql): array {
    $stmts = []; $cur = ''; $len = strlen($sql); $i = 0;
    while ($i < $len) {
        $ch = $sql[$i];
        if ($ch === "'" || $ch === '"') {
            $cur .= $ch; $i++;
            while ($i < $len) {
                $c = $sql[$i]; $cur .= $c;
                if ($c === '\\') { $i++; if ($i < $len) { $cur .= $sql[$i]; $i++; } continue; }
                if ($c === $ch) { if (isset($sql[$i+1]) && $sql[$i+1] === $ch) { $cur .= $ch; $i += 2; continue; } $i++; break; }
                $i++;
            }
            continue;
        }
        if ($ch === '`') { $cur .= $ch; $i++; while ($i < $len) { $c = $sql[$i]; $cur .= $c; if ($c === '`') { $i++; break; } $i++; } continue; }
        if ($ch === '/' && isset($sql[$i+1]) && $sql[$i+1] === '*') { $e = strpos($sql,'*/',$i+2); $i = $e===false?$len:$e+2; continue; }
        if ($ch === '-' && isset($sql[$i+1]) && $sql[$i+1] === '-') { $e = strpos($sql,"\n",$i); $i = $e===false?$len:$e+1; continue; }
        if ($ch === '#') { $e = strpos($sql,"\n",$i); $i = $e===false?$len:$e+1; continue; }
        if ($ch === ';') { $t = trim($cur); if ($t !== '') $stmts[] = $t; $cur = ''; $i++; continue; }
        $cur .= $ch; $i++;
    }
    $t = trim($cur); if ($t !== '') $stmts[] = $t;
    return $stmts;
}

// ============================================================================
// ACTION: EXPORT — save to /backups/ folder only, then redirect.
// Use the download button in the table to download the file.
// ============================================================================
if ($action === 'export') {
    $schemaSync = function_exists('dbs_sync_database_schema_if_needed')
        ? dbs_sync_database_schema_if_needed($pdo)
        : ['ran' => false, 'changes' => []];

    $label = makeBackupFilename($_GET['label'] ?? 'manual');
    $fp    = BACKUP_DIR . '/' . $label;
    $fh    = fopen($fp, 'w');
    if (!$fh) { http_response_code(500); exit('Cannot write to backup folder.'); }
    writeDump($pdo, $fh); fclose($fh); pruneBackups();
    $syncNote = !empty($schemaSync['changes'])
        ? ' Schema sync applied: ' . count($schemaSync['changes']) . ' fix(es).'
        : '';
    $_SESSION['backup_success'] = 'Backup saved: ' . $label . ' (' . number_format(filesize($fp) / 1024, 1) . ' KB) — click the download button to save it.';
    if ($syncNote !== '') $_SESSION['backup_success'] .= $syncNote;
    header('Location: ' . $baseUrl . '/index.php?page=backup_database');
    exit;
}

// ============================================================================
// ACTION: DOWNLOAD existing backup
// ============================================================================
if ($action === 'download') {
    $file = basename($_GET['file'] ?? '');
    if (!preg_match('/^cafeteria_backup_[\d\-_]+[a-z0-9\-]+\.sql$/i', $file)) { http_response_code(400); exit('Invalid filename.'); }
    $fp = BACKUP_DIR . '/' . $file;
    if (!is_file($fp)) { http_response_code(404); exit('File not found.'); }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    header('Content-Length: ' . filesize($fp));
    header('Cache-Control: no-cache');
    readfile($fp); exit;
}

// ============================================================================
// ACTION: DELETE
// ============================================================================
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = basename($_POST['file'] ?? '');
    if (!preg_match('/^cafeteria_backup_[\d\-_]+[a-z0-9\-]+\.sql$/i', $file)) { $_SESSION['backup_error'] = 'Invalid filename.'; }
    else { $fp = BACKUP_DIR . '/' . $file; if (is_file($fp)) { unlink($fp); $_SESSION['backup_success'] = "Deleted: {$file}"; } else { $_SESSION['backup_error'] = 'File not found.'; } }
    header('Location: ' . $baseUrl . '/index.php?page=backup_database'); exit;
}

// ============================================================================
// ACTION: IMPORT
// ============================================================================
$importError = null; $importSuccess = false; $importResult = null; $importDetails = [];

if ($action === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $sub = $_POST['csrf_token'] ?? ''; $sto = $_SESSION['db_import_csrf'] ?? '';
    unset($_SESSION['db_import_csrf']);
    if (!hash_equals($sto, $sub)) { $importError = 'Invalid or expired token. Refresh and try again.'; }
    elseif (empty($_FILES['sql_file']['tmp_name']) || ($_FILES['sql_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) { $importError = 'No file uploaded.'; }
    else {
        $file = $_FILES['sql_file']; $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $em = [UPLOAD_ERR_INI_SIZE=>'Exceeds server upload_max_filesize.',UPLOAD_ERR_FORM_SIZE=>'Exceeds form size.',UPLOAD_ERR_PARTIAL=>'Partially uploaded.',UPLOAD_ERR_NO_TMP_DIR=>'No temp folder.',UPLOAD_ERR_CANT_WRITE=>'Cannot write to disk.',UPLOAD_ERR_EXTENSION=>'Blocked by PHP extension.'];
        if ($file['error'] !== UPLOAD_ERR_OK)         { $importError = $em[$file['error']] ?? 'Upload error '.(int)$file['error']; }
        elseif ($ext !== 'sql')                        { $importError = 'Only .sql files accepted.'; }
        elseif ($file['size'] > 50*1024*1024)          { $importError = 'File too large (max 50 MB).'; }
        elseif (!is_uploaded_file($file['tmp_name']))  { $importError = 'Security error.'; }
        else {
            $sql = file_get_contents($file['tmp_name']);
            if (!$sql || !trim($sql)) { $importError = 'File is empty or unreadable.'; }
            else {
                $sql = str_replace(["\r\n","\r"], "\n", $sql);
                try {
                    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
                    $pdo->exec("SET FOREIGN_KEY_CHECKS=0"); $pdo->exec("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'"); $pdo->exec("SET NAMES utf8mb4");
                    $stmts = splitSqlStatements($sql); $exec = $skip = 0;
                    foreach ($stmts as $s) { $t = trim($s); if (!$t) { $skip++; continue; } $pdo->exec($t); $exec++; }
                    $schemaSync = function_exists('dbs_sync_database_schema')
                        ? dbs_sync_database_schema($pdo)
                        : ['ran' => false, 'changes' => []];
                    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
                    $importSuccess = true; $importResult = 'Import completed successfully.';
                    $importDetails = [
                        'filename'     => $file['name'],
                        'size'         => $file['size']>=1048576?number_format($file['size']/1048576,2).' MB':number_format($file['size']/1024,1).' KB',
                        'executed'     => $exec,
                        'skipped'      => $skip,
                        'schema_fixes' => count($schemaSync['changes'] ?? []),
                    ];
                } catch (Throwable $e) {
                    try { $pdo->exec("SET FOREIGN_KEY_CHECKS=1"); } catch(Exception $_e){}
                    $importError = 'SQL error: ' . $e->getMessage();
                }
            }
        }
    }
}

if (empty($_SESSION['db_import_csrf'])) $_SESSION['db_import_csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['db_import_csrf'];
$flashOk  = $_SESSION['backup_success'] ?? null; $flashErr = $_SESSION['backup_error'] ?? null;
unset($_SESSION['backup_success'], $_SESSION['backup_error']);
$backupFiles = listBackups();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Database Backup &amp; Restore — Cafeteria POS</title>
<link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/images/logo/logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--primary:#0f4c5c;--primary-dark:#0a3a47;--secondary:#ff6b4a;--danger:#d64045;--success:#2c7a5a;--border:#cbd5e0;--navy:#1e2f4e;--warm-gray:#4a5568;--light-bg:#f8faff;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:Inter,sans-serif;background:var(--light-bg);color:var(--navy);}
.wrap{max-width:820px;margin:48px auto;padding:0 1rem 80px;}
.card{background:white;border-radius:22px;border:1px solid rgba(203,213,224,.5);box-shadow:0 8px 24px rgba(15,76,92,.06);padding:1.8rem;margin-bottom:1.4rem;}
.chr{display:flex;align-items:center;gap:.7rem;margin-bottom:1.4rem;}
.ci{width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
.ci.teal{background:rgba(15,76,92,.09);color:var(--primary);}
.ci.green{background:rgba(44,122,90,.1);color:var(--success);}
.ci.orange{background:rgba(255,107,74,.1);color:var(--secondary);}
.ct{font-size:1.1rem;font-weight:700;color:var(--navy);}.cs{font-size:.8rem;color:var(--warm-gray);margin-top:1px;}
.ab{border-radius:12px;padding:.85rem 1rem;font-size:.88rem;display:flex;align-items:flex-start;gap:.6rem;margin-bottom:1.1rem;}
.ab i{flex-shrink:0;margin-top:2px;}
.ab-d{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
.ab-s{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}
.ab-w{background:#fffbeb;border:1px solid #fde68a;color:#92400e;}
.dg{display:grid;grid-template-columns:1fr 1fr;gap:.4rem .7rem;margin-top:.7rem;}
.di{background:rgba(44,122,90,.07);border-radius:8px;padding:.4rem .65rem;}
.dil{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#4a7a60;margin-bottom:1px;}
.div2{font-size:.86rem;font-weight:600;color:#166534;word-break:break-all;}
.fi{background:#f2f6fc;border-radius:10px;padding:.55rem .9rem;font-size:.78rem;color:var(--warm-gray);display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:1.1rem;}
.fi code{color:var(--primary);background:rgba(15,76,92,.07);padding:.1rem .4rem;border-radius:5px;font-size:.76rem;}
.er{display:flex;gap:.6rem;align-items:flex-end;flex-wrap:wrap;}
.lw{display:flex;flex-direction:column;gap:.25rem;}
.lw label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--warm-gray);}
.li{border:1.5px solid var(--border);border-radius:11px;padding:.52rem .85rem;font-size:.88rem;font-family:inherit;color:var(--navy);transition:border-color .15s;width:170px;}
.li:focus{outline:none;border-color:var(--primary);}
.be{padding:.6rem 1.4rem;border-radius:40px;font-size:.9rem;font-weight:600;background:var(--primary);color:white;border:none;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:.4rem;white-space:nowrap;}
.be:hover{background:var(--primary-dark);transform:translateY(-1px);box-shadow:0 5px 14px rgba(15,76,92,.25);}
.be:disabled{opacity:.5;cursor:not-allowed;transform:none;}
.pl{margin-top:.6rem;font-size:.76rem;color:var(--warm-gray);}
.pl code{color:var(--primary);background:rgba(15,76,92,.07);padding:.1rem .4rem;border-radius:5px;}
.bt{width:100%;border-collapse:separate;border-spacing:0;}
.bt thead th{background:#f2f6fc;border-bottom:2px solid rgba(15,76,92,.12);font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--primary);padding:.8rem 1rem;white-space:nowrap;}
.bt tbody td{padding:.9rem 1rem;border-bottom:1px solid #edf2f7;vertical-align:middle;font-size:.86rem;}
.bt tbody tr:last-child td{border-bottom:none;}
.bt tbody tr:hover td{background:#fafcff;}
.fn{font-weight:600;color:var(--navy);font-size:.82rem;word-break:break-all;}
.lb{display:inline-block;padding:.18rem .65rem;border-radius:20px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;}
.lb-m{background:rgba(15,76,92,.1);color:var(--primary);}
.lb-a{background:rgba(44,122,90,.12);color:var(--success);}
.lb-o{background:rgba(255,107,74,.1);color:#c04030;}
.sz{font-size:.76rem;color:var(--warm-gray);background:#f2f6fc;padding:.12rem .55rem;border-radius:20px;display:inline-block;}
.nbk{text-align:center;padding:2.5rem 1rem;color:var(--warm-gray);}
.nbk i{font-size:2.2rem;display:block;margin-bottom:.6rem;opacity:.2;}
.ta{display:flex;gap:.35rem;justify-content:flex-end;}
.tb2{width:32px;height:32px;border-radius:9px;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:.82rem;transition:all .15s;}
.tb-dl{background:rgba(15,76,92,.08);color:var(--primary);}
.tb-dl:hover{background:var(--primary);color:white;}
.tb-de{background:rgba(214,64,69,.08);color:var(--danger);}
.tb-de:hover{background:var(--danger);color:white;}
.dz{border:2px dashed var(--border);border-radius:13px;padding:2rem 1.5rem;text-align:center;cursor:pointer;transition:all .2s;background:#fafcff;margin-bottom:1rem;}
.dz:hover,.dz.dgo{border-color:var(--primary);background:rgba(15,76,92,.04);}
.dz.hf{border-color:var(--success);background:rgba(44,122,90,.04);}
.dzi{font-size:1.9rem;color:var(--primary);margin-bottom:.45rem;display:block;transition:color .2s;}
.dz.hf .dzi{color:var(--success);}
.dzt{font-size:.88rem;color:var(--warm-gray);}
.dzt strong{color:var(--primary);}
.dzn{font-size:.82rem;color:var(--navy);font-weight:600;margin-top:.4rem;display:none;}
input[type=file]{display:none;}
.pw{display:none;margin-bottom:.85rem;}
.ptr{height:5px;border-radius:5px;background:#e2e8f0;overflow:hidden;}
.pfi{height:100%;border-radius:5px;background:var(--primary);width:0%;transition:width .3s;}
.plb{font-size:.76rem;color:var(--warm-gray);margin-top:3px;}
.br{display:flex;gap:.6rem;}
.bi{flex:1;padding:.68rem;border-radius:40px;font-size:.9rem;font-weight:600;background:var(--primary);color:white;border:none;cursor:pointer;transition:all .18s;display:flex;align-items:center;justify-content:center;gap:.4rem;}
.bi:hover:not(:disabled){background:var(--primary-dark);transform:translateY(-1px);box-shadow:0 5px 14px rgba(15,76,92,.25);}
.bi:disabled{opacity:.5;cursor:not-allowed;transform:none;}
.bc{padding:.68rem 1.1rem;border-radius:40px;font-size:.9rem;font-weight:600;background:transparent;color:var(--warm-gray);border:1.5px solid var(--border);cursor:pointer;transition:all .15s;display:none;align-items:center;gap:.4rem;}
.bc:hover{background:#f2f6fc;}
.bk{display:inline-flex;align-items:center;gap:.4rem;color:var(--warm-gray);text-decoration:none;font-size:.88rem;font-weight:500;margin-bottom:1.3rem;transition:color .15s;}
.bk:hover{color:var(--primary);}
.mo{display:none;position:fixed;inset:0;background:rgba(15,76,92,.38);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;}
.mo.show{display:flex;}
.mb2{background:white;border-radius:20px;padding:1.8rem;max-width:400px;width:90%;box-shadow:0 20px 40px rgba(0,0,0,.14);text-align:center;}
.mb2 .mi{width:52px;height:52px;border-radius:14px;background:rgba(214,64,69,.1);color:var(--danger);font-size:1.4rem;display:flex;align-items:center;justify-content:center;margin:0 auto .9rem;}
.mb2 h3{font-size:1.1rem;font-weight:700;color:var(--navy);margin-bottom:.35rem;}
.mb2 p{font-size:.85rem;color:var(--warm-gray);}
.mf{font-size:.8rem;font-weight:600;color:var(--danger);word-break:break-all;margin:.6rem 0 1.1rem;}
.mr{display:flex;gap:.6rem;}
.mc{flex:1;padding:.6rem;border-radius:40px;font-size:.86rem;font-weight:600;background:white;color:var(--warm-gray);border:1.5px solid var(--border);cursor:pointer;transition:all .15s;}
.mc:hover{background:#f2f6fc;}
.md{flex:1;padding:.6rem;border-radius:40px;font-size:.86rem;font-weight:600;background:var(--danger);color:white;border:none;cursor:pointer;transition:background .15s;width:100%;}
.md:hover{background:#b03035;}
@media(max-width:560px){.er{flex-direction:column;}.li{width:100%;}.be{width:100%;justify-content:center;}.dg{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div class="wrap">
<a href="<?= htmlspecialchars($baseUrl) ?>/index.php?page=admin_dashboard" class="bk"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

<?php if ($flashOk): ?><div class="ab ab-s"><i class="fas fa-circle-check"></i><div><?= htmlspecialchars($flashOk) ?></div></div><?php endif; ?>
<?php if ($flashErr): ?><div class="ab ab-d"><i class="fas fa-circle-exclamation"></i><div><?= htmlspecialchars($flashErr) ?></div></div><?php endif; ?>

<!-- EXPORT -->
<div class="card">
  <div class="chr"><div class="ci green"><i class="fas fa-cloud-arrow-down"></i></div>
    <div><div class="ct">Export Backup</div><div class="cs">Save a full SQL dump to the server and download it</div></div></div>
  <div class="fi"><i class="fas fa-folder-open"></i> Saved to: <code><?= htmlspecialchars(BACKUP_DIR) ?></code> &mdash; max <?= BACKUP_MAX_FILES ?> files kept</div>
  <div class="er">
    <div class="lw"><label for="exportLabel">Label / Tag</label>
      <input type="text" id="exportLabel" class="li" value="manual" maxlength="30" placeholder="e.g. before-update"></div>
    <button type="button" class="be" id="exportBtn" onclick="doExport()"><i class="fas fa-save"></i> Create Backup</button>
  </div>
  <div class="pl">Preview: <code id="fnPrev"></code></div>
</div>

<!-- SAVED BACKUPS -->
<div class="card">
  <div class="chr"><div class="ci teal"><i class="fas fa-folder-open"></i></div>
    <div><div class="ct">Saved Backups <span style="font-size:.8rem;font-weight:400;color:var(--warm-gray);">(<?= count($backupFiles) ?>/<?= BACKUP_MAX_FILES ?>)</span></div>
    <div class="cs">Click <i class="fas fa-download" style="font-size:.72rem;"></i> to re-download any saved backup</div></div></div>
  <?php if (empty($backupFiles)): ?>
    <div class="nbk"><i class="fas fa-box-open"></i>No backups yet. Use Export above to create one.</div>
  <?php else:
    $showDefault = 5;
    $totalFiles  = count($backupFiles);
    $hasMore     = $totalFiles > $showDefault;
  ?>
  <div style="overflow-x:auto;">
  <table class="bt">
    <thead><tr><th>Filename</th><th>Date</th><th>Time (PHT)</th><th>Label</th><th>Size</th><th style="text-align:right;">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($backupFiles as $idx => $fp):
        $fn  = basename($fp); $meta = parseBackupFilename($fn);
        $b   = filesize($fp); $sz = $b >= 1048576 ? number_format($b/1048576,2).' MB' : number_format($b/1024,1).' KB';
        $lc  = strtolower($meta['label']);
        $lc2 = $lc === 'manual' ? 'lb-m' : ($lc === 'auto' ? 'lb-a' : 'lb-o');
        $hidden = ($idx >= $showDefault) ? 'class="extra-row" style="display:none;"' : '';
    ?>
    <tr <?= $hidden ?>>
      <td><div class="fn"><?= htmlspecialchars($fn) ?></div></td>
      <td><?= htmlspecialchars($meta['date']) ?></td>
      <td><?= htmlspecialchars($meta['time']) ?></td>
      <td><span class="lb <?= $lc2 ?>"><?= htmlspecialchars($meta['label']) ?></span></td>
      <td><span class="sz"><?= htmlspecialchars($sz) ?></span></td>
      <td><div class="ta">
        <a href="<?= htmlspecialchars($baseUrl) ?>/index.php?page=backup_database&action=download&file=<?= urlencode($fn) ?>" class="tb2 tb-dl" title="Download"><i class="fas fa-download"></i></a>
        <button type="button" class="tb2 tb-de" onclick="confirmDelete('<?= htmlspecialchars(addslashes($fn)) ?>')" title="Delete"><i class="fas fa-trash"></i></button>
      </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>

  <?php if ($hasMore): ?>
  <div style="text-align:center;margin-top:.9rem;">
    <button type="button" id="showAllBtn" onclick="toggleAllBackups()"
            style="background:none;border:1.5px solid var(--border);border-radius:40px;padding:.45rem 1.2rem;font-size:.82rem;font-weight:600;color:var(--warm-gray);cursor:pointer;transition:all .15s;display:inline-flex;align-items:center;gap:.4rem;">
      <i class="fas fa-chevron-down" id="showAllIcon"></i>
      <span id="showAllText">Show <?= $totalFiles - $showDefault ?> more</span>
    </button>
  </div>
  <?php endif; ?>

  <?php endif; ?>
</div>

<!-- IMPORT -->
<div class="card">
  <div class="chr"><div class="ci orange"><i class="fas fa-file-import"></i></div>
    <div><div class="ct">Import / Restore</div><div class="cs">Upload a .sql backup file to restore the database</div></div></div>
  <?php if ($importError): ?><div class="ab ab-d"><i class="fas fa-circle-exclamation"></i><div><?= htmlspecialchars($importError) ?></div></div><?php endif; ?>
  <?php if ($importSuccess): ?>
    <div class="ab ab-s"><i class="fas fa-circle-check"></i><div><strong><?= htmlspecialchars($importResult) ?></strong>
      <div class="dg">
        <div class="di"><div class="dil">File</div><div class="div2"><?= htmlspecialchars($importDetails['filename']) ?></div></div>
        <div class="di"><div class="dil">Size</div><div class="div2"><?= htmlspecialchars($importDetails['size']) ?></div></div>
        <div class="di"><div class="dil">Executed</div><div class="div2"><?= (int)$importDetails['executed'] ?></div></div>
        <div class="di"><div class="dil">Skipped</div><div class="div2"><?= (int)$importDetails['skipped'] ?></div></div>
        <div class="di"><div class="dil">Schema Fixes</div><div class="div2"><?= (int)($importDetails['schema_fixes'] ?? 0) ?></div></div>
      </div></div></div>
  <?php endif; ?>
  <div class="ab ab-w" style="margin-bottom:1.1rem;"><i class="fas fa-triangle-exclamation"></i>
    <span><strong>Warning:</strong> This overwrites all existing data. Export a fresh backup first.</span></div>
  <form method="POST" enctype="multipart/form-data" id="importForm"
        action="<?= htmlspecialchars($baseUrl) ?>/index.php?page=backup_database&action=import" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
    <div class="dz" id="dz" role="button" tabindex="0">
      <i class="fas fa-cloud-arrow-up dzi"></i>
      <div class="dzt"><strong>Click to browse</strong> or drag &amp; drop<br><small>.sql files only &mdash; max 50 MB</small></div>
      <div class="dzn" id="dzn"></div>
    </div>
    <input type="file" name="sql_file" id="sf" accept=".sql">
    <div class="pw" id="pw"><div class="ptr"><div class="pfi" id="pfi"></div></div><div class="plb">Uploading and executing &mdash; please wait&hellip;</div></div>
    <div class="br">
      <button type="button" class="bc" id="cb"><i class="fas fa-xmark"></i> Clear</button>
      <button type="submit" class="bi" id="sb" disabled><i class="fas fa-file-import"></i> Import Database</button>
    </div>
  </form>
</div>
</div>

<!-- DELETE MODAL -->
<div class="mo" id="dm">
  <div class="mb2"><div class="mi"><i class="fas fa-trash"></i></div>
    <h3>Delete Backup?</h3><p>You are about to permanently delete:</p>
    <div class="mf" id="dfn"></div>
    <p style="font-size:.78rem;color:#b03035;margin-bottom:1.1rem;">This cannot be undone.</p>
    <div class="mr"><button type="button" class="mc" onclick="closeDM()">Cancel</button>
      <form method="POST" style="flex:1;" action="<?= htmlspecialchars($baseUrl) ?>/index.php?page=backup_database&action=delete">
        <input type="hidden" name="file" id="dfi">
        <button type="submit" class="md"><i class="fas fa-trash me-1"></i> Delete</button>
      </form></div></div></div>

<script>
const BU = <?= json_encode($baseUrl) ?>;
const li = document.getElementById('exportLabel');
const fp = document.getElementById('fnPrev');
function updPrev() {
  const r = li.value.trim().toLowerCase().replace(/[^a-z0-9\-]/g,'-').replace(/^-+|-+$/g,'') || 'manual';
  const n = new Date(), p = v => String(v).padStart(2,'0');
  fp.textContent = 'cafeteria_backup_' + n.getFullYear()+'-'+p(n.getMonth()+1)+'-'+p(n.getDate())+'_'+p(n.getHours())+'-'+p(n.getMinutes())+'-'+p(n.getSeconds())+'_'+r+'.sql';
}
li.addEventListener('input', updPrev); updPrev();

function doExport() {
  const r = li.value.trim().toLowerCase().replace(/[^a-z0-9\-]/g,'-').replace(/^-+|-+$/g,'') || 'manual';
  const btn = document.getElementById('exportBtn');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
  window.location.href = BU + '/index.php?page=backup_database&action=export&label=' + encodeURIComponent(r);
}

function confirmDelete(fn) { document.getElementById('dfn').textContent=fn; document.getElementById('dfi').value=fn; document.getElementById('dm').classList.add('show'); }
function closeDM() { document.getElementById('dm').classList.remove('show'); }
document.getElementById('dm').addEventListener('click', e => { if(e.target===document.getElementById('dm')) closeDM(); });
document.addEventListener('keydown', e => { if(e.key==='Escape') closeDM(); });

const dz=document.getElementById('dz'), sf=document.getElementById('sf'), dn=document.getElementById('dzn'), sb=document.getElementById('sb'), cb=document.getElementById('cb');
function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(b){return b>=1048576?(b/1048576).toFixed(2)+' MB':(b/1024).toFixed(1)+' KB';}
function handleFile(f) {
  if(!f) return;
  if(!f.name.toLowerCase().endsWith('.sql')){alert('Only .sql files allowed.');clearF();return;}
  if(f.size>50*1024*1024){alert('Max 50 MB.');clearF();return;}
  if(f.size===0){alert('File is empty.');clearF();return;}
  dn.innerHTML='<i class="fas fa-file-code" style="color:var(--success);margin-right:5px;"></i>'+esc(f.name)+' <span style="color:var(--warm-gray);font-weight:400;">('+fmt(f.size)+')</span>';
  dn.style.display='block'; dz.classList.add('hf'); sb.disabled=false; cb.style.display='flex';
  if(f!==sf.files[0]){try{const dt=new DataTransfer();dt.items.add(f);sf.files=dt.files;}catch(_){}}
}
function clearF(){sf.value='';dn.textContent='';dn.style.display='none';dz.classList.remove('hf');sb.disabled=true;cb.style.display='none';}
dz.addEventListener('click',()=>sf.click());
dz.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' ')sf.click();});
sf.addEventListener('change',()=>{if(sf.files[0])handleFile(sf.files[0]);});
cb.addEventListener('click',clearF);
dz.addEventListener('dragover',e=>{e.preventDefault();dz.classList.add('dgo');});
dz.addEventListener('dragleave',()=>dz.classList.remove('dgo'));
dz.addEventListener('drop',e=>{e.preventDefault();dz.classList.remove('dgo');handleFile(e.dataTransfer.files[0]);});
document.getElementById('importForm').addEventListener('submit',function(e){
  if(!sf.files[0]){e.preventDefault();alert('Select a .sql file first.');return;}
  sb.disabled=true;cb.style.display='none';sb.innerHTML='<i class="fas fa-spinner fa-spin"></i> Importing…';
  const pw=document.getElementById('pw'),pfi=document.getElementById('pfi');
  pw.style.display='block'; let w=5;
  window.__iv=setInterval(()=>{const s=w<40?5:w<70?2:w<88?.8:.1;w=Math.min(w+s,91);pfi.style.width=w+'%';},300);
});
// Auto-dismiss flash alerts after 5 seconds
setTimeout(() => {
    document.querySelectorAll('.ab').forEach(el => {
        el.style.transition = 'opacity .4s ease, transform .4s ease';
        el.style.opacity    = '0';
        el.style.transform  = 'translateY(-6px)';
        setTimeout(() => el.remove(), 400);
    });
}, 5000);

let allShown = false;
function toggleAllBackups() {
    allShown = !allShown;
    document.querySelectorAll('.extra-row').forEach(r => r.style.display = allShown ? '' : 'none');
    document.getElementById('showAllIcon').className = allShown ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
    const btn = document.getElementById('showAllBtn');
    const remaining = document.querySelectorAll('.extra-row').length;
    document.getElementById('showAllText').textContent = allShown ? 'Show less' : 'Show ' + remaining + ' more';
    btn.style.color = allShown ? 'var(--primary)' : 'var(--warm-gray)';
    btn.style.borderColor = allShown ? 'var(--primary)' : 'var(--border)';
}
window.addEventListener('pageshow',function(){
  if(window.__iv)clearInterval(window.__iv);
  document.getElementById('pw').style.display='none';
  sb.innerHTML='<i class="fas fa-file-import"></i> Import Database';
  sb.disabled=!sf.files[0]; if(sf.files[0])cb.style.display='flex';
});
</script>
</body></html>
