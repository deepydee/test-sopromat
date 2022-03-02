<?php
require_once 'functions.php';
require_once 'config.php';
// Устанавливаем соединение с базой данных
require_once("connect.php");

if (isset($_POST['tid']) && isset($_POST['uid'])) {
  $tid = (int)$_POST['tid'];
  $uid = $_POST['uid'];

  $query = "SELECT * FROM results
            WHERE id = :uid";
  $q = $pdo->prepare($query);
  $q->execute(['uid' => $uid]);

  $t_data = $q->fetch();
  $test_all_data_result = unserialize($t_data['test_all_data']);
  echo print_result($test_all_data_result, get_test_images($tid));
}