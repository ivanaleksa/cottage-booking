<?php

function connect_to_db() {
    $dsn = file_get_contents('db_conf.local.txt');

    try {
        $pdo = new PDO($dsn);
        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $pdo;
    } catch (PDOException $e) {
        echo "Ошибка подключения к базе данных: " . $e->getMessage();
        return null;
    }
}

?>
