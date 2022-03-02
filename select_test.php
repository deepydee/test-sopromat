<?php ## Формирование пунктов выпадающего списка вопросов
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
  $res = "";

  // Кол-во вопросов в тксте разного уровня
  $simple = 0;
  $middle = 0;
  $complex = 0;


  $query = "SELECT * 
            FROM questions
            WHERE
              parent_test = :id
            ORDER BY id";
  $q = $pdo->prepare($query);
  $q->execute(['id' => $_GET['id']]);

  // считаем вопросы 
  while($question = $q->fetch()) {
    switch($question['level']) {
      case 1:
        $simple++;
        break;
      case 2:
        $middle++;
        break;
      case 3:
        $complex++;
        break;
    }
  }
  $res .= "<option value='0' data-simple='$simple' data-mid='$middle' data-complex='$complex'>Выберите вопрос</option>";

  $query = "SELECT * 
            FROM questions
            WHERE
              parent_test = :id
            ORDER BY id";
  $q = $pdo->prepare($query);
  $q->execute(['id' => $_GET['id']]);
  
  while($question = $q->fetch()) {
    $res .= "<option value='{$question['id']}'>{$question['question']}</option>";
  }
echo $res;