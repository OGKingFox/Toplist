<?php
    include 'db_connect.php';

    $start = microtime(true);
    $stmt = $pdo->prepare("UPDATE servers SET votes = (SELECT COUNT(*) FROM votes WHERE votes.server_id = servers.id)");
    $stmt->execute();
    $end = microtime(true);

    $updated = $stmt->rowCount();
    $elapsed = number_format($end - $start, 4);

    echo "[Success] Updated $updated Records in ".$elapsed."s! ";
    error_log(date('[m-d-y g:i A] ')."[Success] Updated $updated Records in ".$elapsed."s! ".PHP_EOL, 3, log_file);
?>