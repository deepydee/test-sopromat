<?php
// Устанавливаем соединение с базой данных
require_once("connect.php");
//print_r($_POST);
if(isset($_POST['remove_ids'])) {

  $remove_ids = $_POST['remove_ids'];
  foreach($remove_ids as $uid => $v) {
    try {
      $query = "DELETE FROM results
                WHERE id = :uid";
      $q = $pdo->prepare($query);
      $q->execute(['uid' => $uid]);
      echo "Результат для пользователя $uid успешно удален<br>";
    } catch (PDOException $e) {
      echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
  }

}