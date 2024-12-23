<?php
/** @var PDO $pdo */
$pdo = require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
//подготавливаем запросы
$brands = $pdo->prepare("INSERT INTO brands (id, name, url, bold, done) VALUES(?, ?, ?, ?, ?)");
$models = $pdo->prepare("INSERT INTO models (brands_id, name, url, hasPanorama, done) VALUES(?, ?, ?, ?, ?)");
$generations = $pdo->prepare("INSERT INTO generations (models_id, src, src2x, url, title , generationInfo, isNewAuto, isComingSoon, frames, frameTypes, group_name, group_salug, group_short) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$complectations = $pdo->prepare("INSERT INTO complectations (generations_id, name, url, group_name) VALUES(?, ?, ?, ?)");

$content = file_get_contents('Bugatti.json'); //чтение данных из json
$array = json_decode($content, true);

//вставляем данные в таблицу brands
$bold= $array['bold'] ? 1 : 0;

$brands->execute([$array['id'], $array['name'], $array['url'], $bold, $array['done']]);
$brand_id =$pdo ->lastInsertId();
if(!$brand_id) {
    die("Ошибка добавления данных");
}
//вложенные циклы для вставки данных в таблицы
foreach ($array['models'] as $item) {
    $hasPanorama = $item['hasPanorama'] ? 1 : 0;
    $done = $item['done'] ? 1 : 0;
    $models->execute([$brand_id, $item['name'], $item['url'], $hasPanorama, $done]);
    $models_id = $pdo -> lastInsertId();


    foreach ($item['generations'] as $generation) {
        $isNewAuto = $generation['isNewAuto'] ? 1 : 0;
        $isComingSoon = $generation['isComingSoon'] ? 1 : 0;


        $generations->execute([$models_id, $generation['src'], $generation['src2x'], $generation['url'], $generation['title'], $generation['generationInfo'], $isNewAuto, $isComingSoon, $generation['frames'], $generation['frameTypes'], $generation['group'], $generation['groupSalug'], $generation['groupShort']]);
        $generations_id = $pdo -> lastInsertId();
        foreach ($generation['complectations'] as $complectation) {

            $complectations->execute([$generations_id, $complectation['name'], $complectation['url'], $complectation['group']]);
//var_dump(($complectations));
        }
    }
}