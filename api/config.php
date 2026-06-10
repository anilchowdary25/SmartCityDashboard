<?php
// api/config.php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

function getDB() {
    $host = 'localhost';
    $db   = 'city_dashboard';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // CRITICAL FOR VUE/CHART.JS TYPE MATCHING:
        PDO::ATTR_EMULATE_PREPARES   => false, 
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ];

    try {
         return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
         throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}

function jsonResponse($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
// Removed the erroneous extra closing brace here