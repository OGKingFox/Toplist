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

    $status = fsockopen($host, $port, $errno, $errstr, 1) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE servers SET is_online = :status WHERE id = ".$server['id']);
    $stmt->bindParam("status", $status);
    $stmt->execute();

    $updated++;
}

echo 'Updated '.$updated.' statuses.';