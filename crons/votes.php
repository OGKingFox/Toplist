<?php
    include '../public/config.php';

    $log_file = "cron.log";

    try {
        $pdo = new PDO("mysql:host=" . host . ";dbname=" . dbname, username, password);
        $pdo->exec("SET CHARACTER SET utf8");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
        error_log(date('[m-d-y g:i A] ')."[Error] Connection failed: {$e->getMessage()} ".PHP_EOL, 3, $log_file);
        exit;
    }

    $start = microtime(true);
    $stmt = $pdo->prepare("UPDATE servers SET votes = (SELECT COUNT(*) FROM votes WHERE votes.server_id = servers.id)");
    $stmt->execute();
    $end = microtime(true);

    $updated = $stmt->rowCount();
    $elapsed = number_format($end - $start, 4);

    echo "[Success] Updated $updated Records in ".$elapsed."s! ";
    error_log(date('[m-d-y g:i A] ')."[Success] Updated $updated Records! ".PHP_EOL, 3, $log_file);
?>