<?php ## AJAX-обработчик состояния переключателя для варианта ответа пользователя
// Инициируем сессию
session_start();

$_SESSION['state'] = [];
foreach($_POST['checked'] as $k => $v) {
  if(!empty($v)) {
    if(strpos($v, 'nswer') != false) {
      $_SESSION['state']['checked'][$v] = $k;
    } else {
      $_SESSION['state']['typed'][$k] = $v;
    }

  }
}