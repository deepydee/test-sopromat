<?php
// Устанавливаем соединение с базой данных
require_once("connect.php");
$tid = $_POST['tid'];
$quantity = $_POST['quantity'];
try {
  $query = "UPDATE test
            SET q_quant = :quantity
            WHERE id = :tid";
  $q = $pdo->prepare($query);
  $q->execute([
                'quantity'      => trim($quantity),
                'tid'           => trim($tid),
              ]);
  echo "Для теста $tid будет выведено $quantity вопросов";
} catch (PDOException $e) {
echo "Ошибка выполнения запроса: " . $e->getMessage();
}