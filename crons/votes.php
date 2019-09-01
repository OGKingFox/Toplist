<?php
$start = microtime(true);
include 'db_connect.php';

$start = strtotime(date("Y-m-1 00:00:00"));
$end   = strtotime(date("Y-m-t 23:59:59"));

$stmt = $pdo->prepare("UPDATE servers SET votes = (SELECT COUNT(*) FROM votes WHERE votes.server_id = servers.id AND votes.voted_on-18000 BETWEEN {$start} AND {$end})");
$stmt->execute();
$end = microtime(true);

$updated = $stmt->rowCount();
$elapsed = number_format($end - $start, 4);

echo "[Success] Updated $updated Records in ".$elapsed."s! ";
//error_log(date('[m-d-y g:i A] ')."[Success] Updated $updated Records in ".$elapsed."s! ".PHP_EOL, 3, log_file);
?>