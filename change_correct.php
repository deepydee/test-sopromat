<?php
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
  
  $qid = $_POST['qid'];
  $old_id = $_POST['old_id'];
  $new_id = $_POST['new_id'];

  try {
      $query = "UPDATE answers
                SET correct_answer = '0'
                WHERE parent_question = :qid
                AND id = :old_id";
      $q = $pdo->prepare($query);
      $q->execute([
                    'qid'      => trim($qid),
                    'old_id'   => trim($old_id),
                  ]);
  } catch (PDOException $e) {
    echo "Ошибка выполнения запроса: " . $e->getMessage();
  }

  try {
    $query = "UPDATE answers
              SET correct_answer = '1'
              WHERE parent_question = :qid
              AND id = :new_id";
    $q = $pdo->prepare($query);
    $q->execute([
                  'qid'      => trim($qid),
                  'new_id'   => trim($new_id),
                ]);
    echo "Правильный вариант ответа успешно отредактирован";
  } catch (PDOException $e) {
    echo "Ошибка выполнения запроса: " . $e->getMessage();
  }