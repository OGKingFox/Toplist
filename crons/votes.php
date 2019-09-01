<?php
$start = microtime(true);
include 'db_connect.php';

$month = date("m");

$stmt = $pdo->prepare("UPDATE servers SET votes = (SELECT COUNT(*) 
    FROM votes
    WHERE votes.server_id = servers.id 
    AND votes.voted_on >= UNIX_TIMESTAMP('2019-$month-01 00:00:00'))");

$stmt->execute();
$end = microtime(true);

$updated = $stmt->rowCount();
$elapsed = number_format($end - $start, 4);

echo "[Success] Updated $updated Records in ".$elapsed."s! ";
//error_log(date('[m-d-y g:i A] ')."[Success] Updated $updated Records in ".$elapsed."s! ".PHP_EOL, 3, log_file);
?>