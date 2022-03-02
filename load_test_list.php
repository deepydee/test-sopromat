<?php
// Устанавливаем соединение с базой данных
require_once("connect.php");
// Формируем выпадающий список корневых разделов
$query = "SELECT * FROM test
          ORDER BY id"; // WHERE enabled = '1'
echo "<option value='0'>Выберите тест</option>";
$com = $pdo->query($query);
while($test = $com->fetch()) {
  echo "<option data-enabled='{$test['enabled']}' data-questions='{$test['q_quant']}' value='{$test['id']}'>{$test['test_name']}</option>";
}