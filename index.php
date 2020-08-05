<?php
header('Content-Type: application/json');

require 'dbconnect.php'; // подключение к бд
require 'methods.php'; // функции для работы с API

$method = $_SERVER['REQUEST_METHOD']; // получение метода запроса

// получение параметров запроса
$q = $_GET['q'];
$params = explode('/', $q);
$sort = $_GET['sort']; // тип сортировки: desc - по убыванию, asc - по возрастанию
$by = $_GET['by']; // сортировка по полю: created_at - дата создания, rating - рейтинг
$page = $_GET['page']; // номер страницы
$fields = $_GET['fields']; // если параметр true - получение опциональных полей (описание, ссылки на все фото)
$type = $params[0]; // обращение к API review
$id = $params[1]; // получение id

if($method === 'GET'){
    if($type === 'review'){
        if(isset($id))
            getReview($connect, $id, $fields); // в случае получения корректного id
        else
            getReviews($connect, $sort, $by, $page); // вывод всех отзывов если id не введен
    }
}
elseif ($method === 'POST'){
    if($type === 'review')
        addReview($connect, $_POST); // добавление нового отзыва
}
