# Тестовое задание по направлению "Бэкенд"

## 1

### Выбор базы данных

Для хранения данных будет использовано приложение phpMyAdmin, предназначенное для администрирования баз данных mySQL.
База данных реляционная.

### Описание схемы данных

Данные хранятся в таблицах следующим образом:

![Схема базы данных](https://github.com/esartium/testTaskVk/raw/master/scheme/db_scheme_vk.png)

Назначение таблиц:

+ groups - группы товаров
+ categories - категории товаров
+ subcategories - подкатегории товаров
+ pictures - изображения товаров
+ products - товары
+ additionally - дополнительные поля для товаров
+ cities - города
+ users - данные пользователей
+ stocks - склады
+ availability - доступность для заказа (информация о том, сколько единиц товара есть в наличии на каждом складе)
+ prices - цены товаров в зависимости от города
+ basket - корзина
+ status - статус доставки
+ orders - заказы

##### Команды SQL для создания этой базы данных:

```
CREATE DATABASE `testTaskVk`;

CREATE TABLE `groups` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL
);

CREATE TABLE `categories` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `group_id` INT NOT NULL,
    FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`)
);

CREATE TABLE `subcategories` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `category_id` INT NOT NULL,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
);

CREATE TABLE `pictures` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `picture_link` VARCHAR(255) NOT NULL
);

CREATE TABLE `products` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `subcategory_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `group_id` INT NOT NULL,
    FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories`(`id`),
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`),
    FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`),
    `picture_id` INT NOT NULL,
    FOREIGN KEY (`picture_id`) REFERENCES `pictures`(`id`)
);

CREATE TABLE `additionally` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `addition_name` VARCHAR(255) NOT NULL,
    `addition_value` VARCHAR(255) NOT NULL,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
);

ALTER TABLE `products` ADD `addition` INT;
ALTER TABLE `products` ADD FOREIGN KEY (`addition`) REFERENCES `additionally`(`id`);

CREATE TABLE `cities` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL
);

CREATE TABLE `users` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255),
    `password` VARCHAR(255),
    `city_id` INT NOT NULL,
    FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`)
);

CREATE TABLE `stocks` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `city_id` INT NOT NULL, 
    FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`),
    `name` VARCHAR(255) NOT NULL
);

CREATE TABLE `availability` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    `stock_id` INT NOT NULL,
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`),
    `amount` INT NOT NULL
);

CREATE TABLE `prices` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    `price` INT NOT NULL,
    `city_id` INT NOT NULL,
    FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`)
);

CREATE TABLE `basket` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    `user_id` INT NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    `additions` INT,
    FOREIGN KEY (`additions`) REFERENCES `additionally`(`id`),
    `amount` INT NOT NULL DEFAULT 1,
    `city_id` INT NOT NULL,
    FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`),
    `price` INT NOT NULL,
    FOREIGN KEY (`price`) REFERENCES `prices`(`id`)
);

CREATE TABLE `status` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255)  
);
INSERT INTO `status` (`name`) VALUES ('Оформлен'), ('На сборке продавцом'), ('В пути в распределительный центр'), ('В пути на пункт выдачи'), ('Доставлен');

CREATE TABLE `orders` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `basket_id` INT NOT NULL,
    FOREIGN KEY (`basket_id`) REFERENCES `basket`(`id`),
    `status_id` INT NOT NULL,
    FOREIGN KEY (`status_id`) REFERENCES `status`(`id`)
);
```

### Медиаконтент

Основным медиаконтентом проекта являются изображения товаров. Они будут храниться в файловой системе. С базой данных они связаны посредством таблицы pictures, в которой в поле picture_link находятся пути к изображениям.

Минусом такого решения может быть вероятность возникновения противоречий в случае удаления изображения из файловой системы, в то время как путь к нему всё ещё будет храниться в базе данных. Однако эта проблема решаема (например, функцией, проверяющей наличие запрашиваемой картинки в файловой системе); выбранный мной подход к хранению медиаконтента имеет преимущество в виде скорости обработки изображения (так как файловая система для работы с файлами изначально предназначена и сделает это быстрее, чем база данных, которая при загрузке изображения, хранящегося в виде BLOB, должна будет собирать его из байтов, что приведёт) и меньшей нагрузке на сервер.

## 2

![Схема компонентов системы и их взаимодействия](https://github.com/esartium/testTaskVk/raw/master/scheme/components_scheme_vk.jpg)

## 3

Прототип API находится в данном репозитории в папке "API_prototype".