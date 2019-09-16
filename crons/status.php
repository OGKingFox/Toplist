<?php
$start = microtime(true);
include 'db_connect.php';

$stmt = $pdo->prepare("SELECT * FROM servers WHERE server_ip IS NOT NULL AND server_port != -1");
$stmt->execute();
$servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;

foreach ($servers as $server) {
    $host = $server['server_ip'];
    $port = $server['server_port'];

    $socket = fsockopen($host, $port, $errno, $errstr, 1);
    $status = $socket ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE servers SET is_online = :status WHERE id = ".$server['id']);
    $stmt->bindParam("status", $status);
    $stmt->execute();

    fclose($socket);
    $updated++;
}

echo 'Updated '.$updated.' statuses.';
writeLog('Updated '.$updated.' statuses.');