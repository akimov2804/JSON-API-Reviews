<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'maps_points';

try {
    $connect = mysqli_connect($host, $user, $password, $database);
} catch (mysqli_sql_exception $e) {
    echo 'Подключение не удалось: ' . $e->errorMessage();
}
