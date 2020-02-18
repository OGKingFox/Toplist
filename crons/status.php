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

    $socket = @fsockopen($host, $port, $errno, $errstr, 1);
    $status = $socket ? 1 : 0;
    
    if ($socket) {
        fclose($socket);
        $updated++;
    }

    $stmt = $pdo->prepare("UPDATE servers SET is_online = :status WHERE id = :server");
    $stmt->bindParam("status", $status);
    $stmt->bindParam("server", $server['id']);
    $stmt->execute();
}

echo 'Updated '.$updated.' statuses.';
writeLog('Updated '.$updated.' statuses.');
