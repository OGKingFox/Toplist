<?php
$start = microtime(true);
include 'db_connect.php';

$stmt = $pdo->prepare("UPDATE servers SET votes = 0");
$stmt->execute();
$end = microtime(true);

$updated = $stmt->rowCount();
$elapsed = number_format($end - $start, 4);

echo "[Success] Updated $updated Records in ".$elapsed."s! ";
error_log(date('[m-d-y g:i A] ')."[Success] Reset $updated Records in ".$elapsed."s! ".PHP_EOL, 3, log_file);
?>