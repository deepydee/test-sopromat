<?php
require_once 'auth.php';
// Устанавливаем соединение с базой данных
require_once("connect.php");
require_once "functions.php";

$dates = [];
$groups = [];
$tests = [];

// Формируем список тестов
$query = "SELECT * FROM test
          ORDER BY id"; // WHERE enabled = '1'
$com = $pdo->query($query);
while($test = $com->fetch()) {
  $tests[$test['id']] = $test['test_name'];
}

$query = "SELECT * FROM results";
$q = $pdo->query($query);
while($data = $q->fetch()) {
  $date = date_create($data['time']);
  $dates[$data['id']] = date_format($date, 'Y-m-d');
  $groups[] = $data['st_group'];
}
  $unique_dates = array_unique($dates);
  asort($unique_dates);
  $unique_groups = array_unique($groups);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="style.css">
  <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
  <script type="text/javascript" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
  <script src="jquery-3.6.0.min.js"></script>
  <script src="results.js"></script>
  <script src="jquery.tablesorter.min.js"></script>
  
  <title>Результаты тестирования</title>
</head>
<body>
  <div class="container">
    <label for="select_date">Дата:</label>
      <select name="dates" id="select_date">
        <?php foreach($unique_dates as $date): ?>
          <option value="<?=$date?>"><?=$date?></option>
        <?php endforeach; ?>
    </select>
  </div>
  <div class="container">
    <label for="select_test">Тест:</label>
      <select name="sel_test" id="select_test_res">
        <?php foreach($tests as $tid => $test_name): ?>
          <option value="<?=$tid?>"><?=$test_name." (ID теста: ".$tid.")"?></option>
        <?php endforeach; ?>
    </select>
  </div>
  <div class="container">
     <label for="sirname">Фамилия</label><input type="text" size="5" id="sirname">
  </div>
  <div class="container">
    <label for="group">Группа</label>
    <select name="groups" id="group">
      <?php foreach($unique_groups as $group): ?>
        <option value="<?=$group?>"><?=$group?></option>
      <?php endforeach; ?>
    </select>
  </div>
  

  <h1 class='res_header'>Результаты тестирования <span></span></h1>
  <h2 class='test_name'></h2>
  <p class='inf'>Средний % по группе: <span></span></p>
  <table id="resTable" class="result-table">
  </table>

 <div class="data">
  </div>
  <div><button class="btn center" id="submit-id" disabled>Удалить результаты</button></div>
</body>
</html>