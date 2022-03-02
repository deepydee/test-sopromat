<?php
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
  $qid = $_POST['qid'];
  $level = $_POST['level'];
  try {
    $query = "UPDATE questions
              SET level = :level
              WHERE id = :qid";
    $q = $pdo->prepare($query);
    $q->execute([
                  'level' => trim($level),
                  'qid'   => trim($qid),
                ]);
    echo "Уровень сложности вопроса $qid изменен на $level";
  } catch (PDOException $e) {
    echo "Ошибка выполнения запроса: " . $e->getMessage();
  }