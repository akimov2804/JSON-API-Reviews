<?php

/**
 * Получение всех отзывов
 *
 * @param $connect - подключение к БД
 * @param $sort - тип сортировки (desc, asc)
 * @param $by - параметр сортировки (created_at, rating)
 * @param $page - страница
 *
 */
function getReviews($connect, $sort, $by, $page){

    $sql = "SELECT * FROM `maps_points`"; // исходный запрос на получение всех отзывов

    if($by === 'created_at' || $by === 'rating'){ // проверка задания сортировки
        if ($by === 'created_at')
            $sql .= "ORDER BY `maps_points`.`CREATED_AT` ";
        else
            $sql .= "ORDER BY `maps_points`.`RATING` ";
        if ($sort === 'desc') // проверка задания типа сортировки
            $sql .= "DESC ";
        elseif ($sort === 'asc')
            $sql .= "ASC ";
    }

    $offset = ($page - 1) * 10; // смещение
    if ($page > 1)
        $sql .= "LIMIT $offset, 10"; // вывод отзывов заданной страницы
    else
        $sql .= "LIMIT 10"; // значение по умолчанию для первой страницы

    $reviews = mysqli_query($connect, $sql); // готовый запрос к БД

    // получение ответа на запрос
    $reviewsList = [];
    while($review = mysqli_fetch_assoc($reviews)){
        $reviewsList = array(
            'name' => $review['NAME'], // имя (никнейм)
            'rating' => $review['RATING'], // рейтинг
            'main_photo' => explode(",", $review['PHOTOS'])[0]); //ссылка на главное (первое) фото
    }
    echo json_encode($reviewsList);
}

/**
 * Получение отзыва по id
 *
 * @param $connect
 * @param $id
 * @param $fields - если параметр true - получение опциональных полей (описание, ссылки на все фото)
 */
function getReview($connect, $id, $fields){
    $review = mysqli_query($connect, "SELECT * FROM `maps_points` WHERE `id` = '$id'"); // запрос к БД на получение отзыва по id

    if(mysqli_num_rows($review) === 0){   // если id некорректный
        http_response_code(404); // вывод ошибки
        $res = [                            // вывод сообщения
            "status" => false,
            "message" => "Review not found"
        ];
        echo json_encode($res);
    }
    else{
        $review = mysqli_fetch_assoc($review);
        $photos = explode(",", $review['PHOTOS']); // получение всех фото

        $reviewList = array(
            'name' => $review['NAME'], // имя (никнейм)
            'rating' => $review['RATING']); // рейтинг

        if($fields == true){
            $reviewList['description'] = $review['DESCRIPTION']; // описание
            for ($i = 0; $i < count($photos); $i++)
                $reviewList["photo#$i"] = $photos[$i]; // ссылки на фото
        }
        else
            $reviewList['main_photo'] = $photos[0]; // ссылка на главное фото

        echo json_encode($reviewList);
    }
}

/**
 * Добавление отзыва
 * @param $connect
 * @param $data - данные для создания отзыва имя, описание, ссылки на фото и рейтинг
 */
function addReview($connect, $data){
    $name = $data['name'];                  // имя
    $description = $data['description'];    // описание
    $rating = $data['rating'];              // рейтинг
    $photos = $data['photos'];              // ссылки на фото
    $photo = explode(",", $photos);

    if (                                    // валидация
        strlen($name) > 50 ||               // имя (никнейм) не больше 50 символов
        ($rating < 1 && $rating > 5) ||   // рейтинг от 1 до 5
        strlen($description) > 1000 ||      // описание не больше 1000 символов
        count($photo) > 3                   // ссылки на фото не больше 3-х
    ){
        http_response_code(400); // ошибка 400 - неверный запрос
        $res = [
            "status" => false
        ];
        echo json_encode($res);
    }
    else{
        $time = time();
        mysqli_query($connect,"INSERT INTO `maps_points`(`ID`, `NAME`, `DESCRIPTION`, `RATING`, `PHOTOS`, `CREATED_AT`) VALUES (NULL,`$name`,`$description`,`$rating`,`$photos`, `$time`)");
        http_response_code(201); // код результата (успех)
        $res = [
            "status" => true,
            "review_id" => mysqli_insert_id($connect) // ID созданного отзыва
        ];
        echo json_encode($res);
    }

}