<?php

// Основной файл. Здесь мы в зависимости от запроса вызываем API-методы.

require_once './functions.php';

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: *');
header('Content-type: application/json');

$conn = mysqli_connect("localhost", "root", "root", "testTaskVk");
if($conn->connect_error) {
    die("Ошибка соединения: " . $conn->connect_error);
}

$type = $_GET['q'];
$params = explode('/', $type);

$typeMain = $params[0];
if (count($params) > 1) {
    $typeSecond = $params[1];
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($typeSecond)) {
            switch ($typeMain) {
                case 'stocksByCityId':
                    if (isset($typeSecond)) {
                        // здесь typeecond - id города, для которого получаем список складов
                        stocksByCityId($conn, $typeSecond);
                    }
                    break;
                case 'getDefaultCity':
                    if (isset($typeSecond)) {
                        // здесь typeSecond - id пользователя, город которого получаем
                        getDefaultCity($conn, $typeSecond);
                    }
            }
        } 
        if ($typeMain === 'citiesList') {
            citiesList($conn);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        switch ($typeMain) {
            case 'reg':
                registration($conn, $data);
                break;
            case 'login':
                login($conn, $data);
                break;
            case 'addToBasket':
                addToBasket($conn, $data);
                break;
            case 'addToOrders':
                addToOrders($conn, $data);
                break;
            case 'getPriceByCityId':
                $city_id = $data['city_id'];
                $product_id = $data['product_id'];
                getPriceByCityId($conn, $city_id, $product_id);
                break;
            case 'checkAvailability':
                $city_id = $data['city_id'];
                $product_id = $data['product_id'];
                checkAvailability($conn, $city_id, $product_id);
                break;
        }
        break;
}