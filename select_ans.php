<?php ## Формирование списка вариантов ответа на вопрос
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
  require_once("functions.php");

  $level_array = [
    '1' => 'Простой',
    '2' => 'Средняя сложность',
    '3' => 'Сложный',
  ];

  $res = "<div class='q-wrapper'><i class='fa fa-edit' title='Редактировать'></i><i class='fa fa-times remove-question' title='Удалить'></i>\n";
  
  $query = "SELECT * 
            FROM questions
            WHERE id = :id";
  $q = $pdo->prepare($query);
  $q->execute(['id' => $_GET['id']]);
  $question = $q->fetch();
  $res .= "<p>".trim(translateText($question['question']))."</p>\n";
  if(!empty($question['question_img'])) {
    $res .= "<div class = 'img-container center'>\n";
    $res .= "<img src = '{$question['question_img']}'><i class='fa fa-close' title='Удалить'></i>\n";
    $res .= "</div>\n";
  }

  $res .= "<select name='q_level' id='q-level'>\n";
  $res .= "<option value='0'>Сложность вопроса</option>\n";
  foreach ($level_array as $val => $text) {
    $selected = ($val == $question['level']) ? 'selected' : '';
    $res .= "<option value='$val' $selected>$text</option>\n";
  }
  $res .= "</select>\n";
  $res .= "</div>\n";

  $query = "SELECT * 
            FROM answers
            WHERE
              parent_question = :id
            ORDER BY id";
  $q = $pdo->prepare($query);
  $q->execute(['id' => $_GET['id']]);
  $res .= "<ul>\n";
  while($answer = $q->fetch()) {
    $a = translateText($answer['answer']);
    $res .= "<div class='ans-wrapper' data-id='{$answer['id']}' id='ans-{$answer['id']}-wrapper'>\n";
    $res .= "<li class='answer' data-id='{$answer['id']}'>{$a}</li><i class='fa fa-pencil-square-o' title='Редактировать'></i><i class='fa fa-times remove-answer' title='Удалить'></i>\n";
    if(!empty($answer['ans_img'])) {
      $res .= "<div class='img-container center'><img src = '{$answer['ans_img']}'><i class='fa fa-remove' title='Удалить'></i></div>\n";
    }
    if ($answer['correct_answer'] == 1) {
      $res .= "<p><textarea cols='80' rows='5' data-id='{$answer['id']}' id='a-{$answer['id']}' class='edit-a' type='text'></textarea></p>\n";
      $res .= "<input type='radio' name='q-{$answer['parent_question']}' value='{$answer['id']}' id='answer-{$answer['id']}' checked>\n";
      $res .= "<input type='file' name='filename[]' class='btn btn-img' data-id='{$answer['id']}' id='img-btn-{$answer['id']}'/>\n";
      $res .= "</div>";
      
    } else {
      $res .= "<p><textarea cols='80' rows='5' data-id='{$answer['id']}' id='a-{$answer['id']}' class='edit-a' type='text'></textarea></p>\n";
      $res .= "<input type='radio' name='q-{$answer['parent_question']}' value='{$answer['id']}' id='answer-{$answer['id']}'>\n";
      $res .= "<input type='file' name='filename[]' class='btn btn-img' data-id='{$answer['id']}' id='img-btn-{$answer['id']}'/>\n";
      $res .= "</div>\n";
    }
  }
  $res .= "</ul>\n";
  $res .= "<i class='fa fa-plus add-answer' title='Добавить вариант'></i>\n";

  echo $res;