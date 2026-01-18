<?php
// view_ldr.php

$logFile = __DIR__ . "/dati.csv";
$maxRows = 200;           // quante righe mostrare
$refreshSeconds = 10;     // 0 per disattivare auto-refresh

function h($s) {
  return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$rows = [];
if (file_exists($logFile)) {
  // Leggi tutte le righe (se il file cresce molto, vedi nota sotto)
  $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

  // Prendi le ultime N righe
  if (count($lines) > $maxRows) {
    $lines = array_slice($lines, -$maxRows);
  }

  // Mostra le più recenti in alto
  $lines = array_reverse($lines);

  foreach ($lines as $line) {
    $parts = explode("\t", $line);
    // Normalizza a 4 colonne
    $ts     = $parts[0] ?? '';
    $value  = $parts[1] ?? '';
    $volt   = $parts[2] ?? '';
    $uptime = $parts[3] ?? '';
    $rows[] = [$ts, $value, $volt, $uptime];
  }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Log LDR</title>
  <?php if ($refreshSeconds > 0): ?>
    <meta http-equiv="refresh" content="<?= (int)$refreshSeconds ?>">
  <?php endif; ?>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; }
    h1 { margin: 0 0 12px; }
    .meta { color: #555; margin-bottom: 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
    th { background: #f6f6f6; }
    tr:nth-child(even) { background: #fafafa; }
    .num { text-align: right; font-variant-numeric: tabular-nums; }
    .empty { color: #888; }
  </style>
</head>
<body>
  <h1>Log LDR</h1>
  <div class="meta">
    File: <code><?= h(basename($logFile)) ?></code> —
    Righe mostrate: <?= count($rows) ?> (max <?= (int)$maxRows ?>)
    <?php if ($refreshSeconds > 0): ?>
      — Auto-refresh: <?= (int)$refreshSeconds ?> s
    <?php endif; ?>
  </div>

  <?php if (empty($rows)): ?>
    <p class="empty">Nessun dato disponibile (file mancante o vuoto).</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Timestamp</th>
          <th class="num">Valore (0–1023)</th>
          <th class="num">Tensione (V)</th>
          <th class="num">Uptime (ms)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as [$ts, $value, $volt, $uptime]): ?>
          <tr>
            <td><?= h($ts) ?></td>
            <td class="num"><?= h($value) ?></td>
            <td class="num"><?= h($volt) ?></td>
            <td class="num"><?= h($uptime) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</body>
</html>
