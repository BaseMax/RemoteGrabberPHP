<?php
// RemoteGrabberPHP — https://github.com/BaseMax/RemoteGrabberPHP
// Developed by Max Base (Seyyed Ali Mohammadiyeh)

// === CONFIGURATION ===
set_time_limit(0);
ini_set('zlib.output_compression', 'Off');
ob_implicit_flush(true);
while (ob_get_level()) ob_end_flush();

define('LOG_FILE', __DIR__ . '/grabber.log');
define('USER_AGENT', 'RemoteGrabberPHP/1.1');

function log_message($msg) {
    file_put_contents(LOG_FILE, "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

function sanitize_filename($name) {
    return preg_replace('/[^a-zA-Z0-9\._-]/', '_', basename($name));
}

function get_remote_headers($url) {
    $head = @get_headers($url, 1);
    if (!$head || (!isset($head[0]) || !preg_match('/HTTP\/\d\.\d\s(200|301|302)/', $head[0]))) {
        return false;
    }
    return $head;
}

// === INPUT HANDLING ===
$url = trim($_REQUEST['url'] ?? '');
$confirm = isset($_POST['confirm']);
$customName = trim($_POST['filename'] ?? '');

if (!$url) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>RemoteGrabberPHP</title>
<style>
  body{font-family:sans-serif;padding:2em;}
  input,button{padding:0.5em;width:90%;margin:0.5em 0;}
</style></head><body>
<h1>RemoteGrabberPHP</h1>
<form method="POST">
  <input name="url" placeholder="https://example.com/file.zip" required />
  <input name="filename" placeholder="Optional custom filename" />
  <button type="submit">Check & Continue</button>
</form>
</body></html>
HTML;
    exit;
}

// === FETCH HEADERS ===
$headers = get_remote_headers($url);
if (!$headers) {
    echo "❌ Invalid or unreachable URL.";
    log_message("Invalid URL: $url");
    exit;
}

$type = $headers['Content-Type'] ?? 'unknown';
if (is_array($type)) {
    $type = end($type);
}

$lengthBytes = $headers['Content-Length'] ?? null;
if (is_array($lengthBytes)) {
    $lengthBytes = end($lengthBytes);
}

$sizeReadable = $lengthBytes ? round($lengthBytes / 1024 / 1024, 2) . ' MB' : 'unknown';

$acceptRanges = (isset($headers['Accept-Ranges']) && stripos($headers['Accept-Ranges'], 'bytes') !== false);
$finalFilename = sanitize_filename($customName ?: basename(parse_url($url, PHP_URL_PATH)) ?: 'downloaded_file');

if (!$confirm) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Confirm Download</title>
<style>
  body{font-family:sans-serif;padding:2em;}
  button{padding:0.5em 1em;margin-top:1em;}
</style>
</head><body>
<h2>Confirm Remote Download</h2>
<p><strong>URL:</strong> {$url}</p>
<p><strong>Type:</strong> {$type}</p>
<p><strong>Size:</strong> {$sizeReadable}</p>
<p><strong>Resume Supported:</strong> {$acceptRanges}</p>
<p><strong>Filename:</strong> {$finalFilename}</p>
<form method="POST">
  <input type="hidden" name="url" value="{$url}" />
  <input type="hidden" name="filename" value="{$finalFilename}" />
  <input type="hidden" name="confirm" value="1" />
  <button type="submit">Start Download</button>
</form>
</body></html>
HTML;
    exit;
}

// === INITIATE DOWNLOAD ===
log_message("Download started from $url as $finalFilename");

$fp = fopen('php://output', 'wb');
$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FILE => $fp,
    CURLOPT_BUFFERSIZE => 1024 * 1024,
    CURLOPT_USERAGENT => USER_AGENT,
    CURLOPT_FAILONERROR => true
]);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$finalFilename\"");
header("X-Remote-Grabber: BaseMax/1.1");
header("Connection: Keep-Alive");
header("Cache-Control: no-cache");
if ($acceptRanges) {
    header("Accept-Ranges: bytes");
}

curl_exec($ch);

if (curl_errno($ch)) {
    $err = curl_error($ch);
    log_message("❌ cURL error: $err");
    echo "❌ Download failed: $err";
} else {
    log_message("✅ Download completed: $finalFilename");
}

curl_close($ch);
fclose($fp);
exit;
