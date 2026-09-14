<?php
/**
 * Conexão única do portal público com o banco. Mesmo banco usado pelo
 * gerenciador (gerenciador/config/database.php) — ajuste as duas cópias
 * juntas caso troque de servidor/credenciais.
 */
$DB_HOST = 'localhost';
$DB_NAME = 'portal_camara';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    $pdo = null; // o site continua de pé mesmo se o banco cair; cada seção trata o null
}
