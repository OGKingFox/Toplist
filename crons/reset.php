<?php
$start = microtime(true);
include 'db_connect.php';

$stmt = $pdo->prepare("UPDATE servers SET votes = 0");
$stmt->execute();

/*$stmt = $pdo->prepare("TRUNCATE TABLE votes");
$stmt->execute();*/
$end = microtime(true);

$updated = $stmt->rowCount();
$elapsed = number_format($end - $start, 4);

echo "[Success] Updated $updated Records in ".$elapsed."s! ";
writeLog("Reset $updated Records in ".$elapsed."s!");