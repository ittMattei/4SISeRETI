<?php

$EXPECTED_KEY = "";

$key    = $_GET['key']    ?? '';
$value  = $_GET['value']  ?? null;
$voltage= $_GET['v']      ?? null;
$uptime = $_GET['uptime'] ?? null;

// Validazione base
if ($key !== $EXPECTED_KEY) {
  http_response_code(403);
  echo "FORBIDDEN";
  exit;
}

if ($value === null || !is_numeric($value)) {
  http_response_code(400);
  echo "BAD_REQUEST";
  exit;
}

$value = (int)$value;
if ($value < 0 || $value > 1023) {
  http_response_code(400);
  echo "OUT_OF_RANGE";
  exit;
}

// Prepara riga di log
$ts = date('c'); // timestamp ISO 8601
$line = $ts . "\t" . $value . "\t" . ($voltage ?? '') . "\t" . ($uptime ?? '') . "\n";

// Scrivi su file
$logFile = "dati.csv";
file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);

echo "OK";
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Storico luminosità</title>
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px 10px; }
    </style>
</head>
<body>
<table>
    <tr>
        <th>value</th>
        <th>voltage</th>
        <th>uptime</th>
    </tr>

<?php
// Se il file esiste, lo leggo e mostro le righe
if (file_exists($logFile)) {
    $righe = file($logFile, FILE_IGNORE_NEW_LINES);

    foreach ($righe as $riga) {
        list($value, $voltage,$uptime) = explode(';', $riga);
        echo "<tr>
                <td>$value</td>
                <td>$voltage</td>
                <td>$uptime</td>
              </tr>";
    }
}
?>

</table>

</body>
</html>

