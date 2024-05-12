<?php

// Функция получения цены по id города и id заказываемого товара
function getPriceByCityId($conn, $city_id, $product_id) {
    $response = [];
    $price_query = mysqli_fetch_array(mysqli_query($conn, "SELECT `id`, `price` FROM `prices` WHERE (`city_id` = '$city_id' AND `product_id` = '$product_id')"));
    
    array_push($response, $price_query['id']);
    array_push($response, $price_query['price']);
    
    echo json_encode($response);
    return $response;
}

// Функция регистрации (так как без авторизации не получится оформить заказ, а без регистрации не получится авторизоваться):
function registration($conn, $data) {
    $username = $data['username'];
    $password = $data['password'];
    $city_id = $data['city'];

    // проверка, есть ли в базе пользователь с такими данными:
    $checkExist = mysqli_query($conn, "SELECT * FROM `users` WHERE (`username` = '$username' AND `password` = '$password')");

    if (mysqli_num_rows($checkExist) === 0) {
        // если пользователя с такими данными нет, добавляем его:
        mysqli_query($conn, "INSERT INTO `users` (`username`, `password`, `city_id`) VALUES ('$username', '$password', '$city_id')");

        $res = [
            "status" => true,
            "message" => "registered successfully"
        ];
        echo json_encode($res);
    } else {
        // если пользователь с такими данными есть:
        $res = [
            "status" => false,
            "message" => "already exists"
        ];
        echo json_encode($res);
    }
}

// Функция входа для уже зарегистрированных пользователей:
function login($conn, $data) {
    $username = $data['username'];
    $password = $data['password'];

    // проверка, есть ли в базе пользователь с такими данными:
    $checkExist = mysqli_query($conn, "SELECT * FROM `users` WHERE (`username` = '$username' AND `password` = '$password')");
    if (mysqli_num_rows($checkExist) === 0) {
        // если пользователя с такими данными нет, значит, данные введены неверно:
        $res = [
            "status" => false,
            "message" => "wrong username or password"
        ];
        echo json_encode($res);
    } else {
        // если пользователь с такими данными есть, вход выполнится успешно:
        $res = [
            "status" => true,
            "message" => "logged in successfully"
        ];
        echo json_encode($res);
    }
}

// Функция получения города "по умолчанию" - того, который пользователь указал при регистрации
// (при выборе города для заказа изначально предлагается именно этот город; при желании пользоатель может его изменить и заказать в другой)
function getDefaultCity ($conn, $user_id) {

    $user_id = intval($user_id);

    // получение id города, который пользователь указал при регистрации:
    $default_city_id_mass = [];
    $default_city_id_query = mysqli_query($conn, "SELECT `city_id` FROM `users` WHERE `id` = '$user_id'");
    while ($default_id = mysqli_fetch_assoc($default_city_id_query)) {
        $default_city_id_mass[] = $default_id;
    }
    $default_city_id = intval($default_city_id_mass[0]["city_id"]);

    // получение названия города, который пользователь указал при регистрации:
    $default_city_name_mass = [];
    $default_city_name_query = mysqli_query($conn, "SELECT `name` FROM `cities` WHERE `id` = '$default_city_id'");
    while ($default_name = mysqli_fetch_assoc($default_city_name_query)) {
        $default_city_name_mass[] = $default_name;
    }
    $default_city_name = strval($default_city_name_mass[0]["name"]);

    $response = [
        "user_id" => $user_id,
        "default_city_id" => $default_city_id,
        "default_city_name" => $default_city_name
    ];
    echo json_encode($response);

    return $response;
}

// Функция получения списка складов по id города:
function stocksByCityId ($conn, $city_id) {
    $stocks_mass = [];
    $stocks_query = mysqli_query($conn, "SELECT `id` FROM `stocks` WHERE `city_id` = '$city_id'");
    while ($stock = mysqli_fetch_assoc($stocks_query)) {
        $stocks_mass[] = $stock;
    }
    $response = [];
    foreach($stocks_mass as $stock) {
        array_push($response, $stock['id']);
    }
    echo json_encode($response);
    return $response;
}

// Функция проверки наличия товара на каком-либо складе в выбранном городе:
function checkAvailability($conn, $choosed_city, $product_id) {
    $amount = 0;
    $stocks = stocksByCityId($conn, $choosed_city);
    for ($i = 0; $i < count($stocks); $i++) {
        $stocks_i = $stocks[$i];
        $amount_query = mysqli_fetch_array(mysqli_query($conn, "SELECT `amount` FROM `availability` WHERE (`stock_id` = '$stocks_i' AND `product_id` = '$product_id')"));
        if(!is_null($amount_query)) {
            $amount = $amount + $amount_query[$i];
        }
    }
    if ($amount > 0) {
        return true;
    } else {
        return false;
    }
}

// Функция добавления товара в корзину:
function addToBasket($conn, $data) {
    $product_id = $data['product_id'];
    $user_id = intval($data['user_id']);
    $additions = $data['additions'];
    $choosed_city = $data['city_id'];

    // Проверяем, есть ли товар на каком-либо складе в выбранном городе:
    $exist = checkAvailability($conn, $choosed_city, $product_id);
   
    // Если товар есть в наличии, добавляем его в корзину:
    if ($exist) {
        // Получаем цену товара в зависимости от города:
        $price_mass = getPriceByCityId($conn, $choosed_city, $product_id);
        $price = $price_mass[0];

        // проверяем, существует ли в корзине пользователя абсолютно идентичный данному товар:
        $checkExist = mysqli_query($conn, "SELECT * FROM `basket` WHERE 
        (`product_id` = '$product_id' AND `city_id` = '$choosed_city' AND `user_id` = '$user_id' AND `additions` = '$additions' AND `price` = '$price')");
        
        if (mysqli_num_rows($checkExist) === 0) {
            // если такого заказа нет, то добавляем его в базу:
            mysqli_query($conn, "INSERT INTO `basket` (`product_id`, `city_id`, `user_id`, `additions`, `price`) VALUES ('$product_id', '$choosed_city', '$user_id', '$additions', '$price')");

            $res = [
                "status" => true,
                "message" => "added to basket successfully"
            ];
            echo json_encode($res);
        } else {
            // если такой заказ уже есть, то увеличиваем значение счётчика данного товара (чтобы не выводить 100 одинаковых товаров, а указать, что каких-то товаров в корзине больше одного)
            
            // получаем id товара:
            $basket_fields = [];
            while ($field = mysqli_fetch_assoc($checkExist)) {
                $basket_fields[] = $field;
            }
            $exist_id = intval($basket_fields[0]["id"]);
            echo json_encode($exist_id);

            // получаем его количество:
            $exist_amount_mass = []; 
            $exist_amount_query = mysqli_query($conn, "SELECT `amount` FROM `basket` WHERE `id` = '$exist_id'");
            while ($amount = mysqli_fetch_assoc($exist_amount_query)) {
                $exist_amount_mass[] = $amount;
            }
            $exist_amount = intval($exist_amount_mass[0]["amount"]);

            // увеличиваем значение счётчика и обновляем в базе значение количества товара с таким id:
            $new_amount = $exist_amount + 1;
            mysqli_query($conn, "UPDATE `basket` SET `amount` = '$new_amount' WHERE `id` = '$exist_id'");
            echo json_encode("amount rised from " . $exist_amount . " to " . $new_amount);
        }
    } else {
        // Если товара в наличии нет, добавить его в корзину не получится:
        $res = [
            "status" => false,
            "message" => "not available"
        ];
        echo json_encode($res);
    }
}

// Функция получения списка городов (нужна, чтобы вывести пользователю список для выбора города):
function citiesList($conn) {
    $cities_mass = []; 
    $cities_query = mysqli_query($conn, "SELECT * FROM `cities`");
    while ($city = mysqli_fetch_assoc($cities_query)) {
        $cities_mass[] = $city;
    }
    echo json_encode($cities_mass);
}

// Функция оформления заказа:
function addToOrders($conn, $data) {
    // Данные для заказа:
    $basket_id = $data['basket_id'];

    // Вытаскиваем из basket значения полей product_id и city_id:
    $product_id_query = mysqli_fetch_array(mysqli_query($conn, "SELECT `product_id` FROM `basket` WHERE `id` = '$basket_id'"));
    $product_id = intval($product_id_query["product_id"]);
    $city_id_query = mysqli_fetch_array(mysqli_query($conn, "SELECT `city_id` FROM `basket` WHERE `id` = '$basket_id'"));
    $city_id = intval($city_id_query["city_id"]);

    // Ещё раз проверяем, есть ли товар на выбранном складе (так как между моментами добавления в корзину и оформления заказа могло пройти какое-то время, и товара может уже не быть в наличии):
    $checkAmount = checkAvailability($conn, $city_id, $product_id);

    if (!$checkAmount) {
        // Если товара в наличии нет, возвращаем сообщение об этом:
        
        $res = [
            "status" => false,
            "message" => "not available"
        ];
        echo json_encode($res);
    } else {
        // Если товар есть в наличии, заказ успешно оформляется:
        mysqli_query($conn, "INSERT INTO `orders` (`basket_id`, `status_id`) VALUES ('$basket_id', '1')");
        
        $res = [
            "status" => true,
            "message" => "the order was successfully completed"
        ];
        echo json_encode($res);
    }
}