<?php
 session_start(); //вызывается до вывода любого контента
// Устанавливаем соединение с базой данных
require_once("connect.php");

 function check_user () { //проверка авторизации пользователя
  if (!isset($_SESSION['my_inside'])) return false;
  else return $_SESSION['current_user'];
 }

 function form() { //вывод формы для авторизации
  echo '<div class="grandParentContaniner">
          <div class="parentContainer">
            <form method="post" class="authform">
              <div class="input-icon input-icon-left icon-login">
                <input type="text" maxlength="32" name="login" placeholder="Логин"><i></i>
              </div>
              <div class="input-icon input-icon-left icon-password">
                <input type="password" maxlength="32" name="pass" placeholder="Пароль"><i></i>
              </div>
              <input type=submit value="Войти">
            </form>
          </div>
        </div>';
 }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="admin.css">
  <title>Авторизация</title>
  <link rel="shortcut icon" href="favicon.png" type="image/png">
</head>
<body>
  <?php
  if (isset($_GET['off'])) { // сделан запрос на выход из сессии? 
    if (check_user()) $_SESSION = array();
    $_SESSION = [];
    unset($_COOKIE[session_name()]);
    session_destroy();
   }
  
   if (!check_user()) { // если не авторизован
    if (!isset($_POST['login']) or !isset($_POST['pass'])) { 
      //echo '<p>Авторизуйтесь</p>'; 
      form(); 
      exit; 
    }
    // если переданы логин и пароль из формы:
    $login = trim(htmlspecialchars($_POST['login'])); 
     // в реальности нужно получить $login и $pass по безопасному протоколу HTTPS
    $pass = trim(htmlspecialchars($_POST['pass'])); 
    
    // получим хэш из базы
    try {
      $query = "SELECT * FROM users
                WHERE user_name = :user_name";
      $q = $pdo->prepare($query);
      $q->execute(['user_name' => $login]);
      $user = $q->fetch();

      $hash = $user['password'];
      $login0 = $user['user_name'];

      } catch (PDOException $e) {
        echo "Ошибка выполнения запроса: " . $e->getMessage();
      }

    if ($login == $login0 && password_verify($pass, $hash)) { // вошли в систему
     $_SESSION['my_inside'] = 1; // "секретная переменная" в сессии, поменяйте ей имя
     $_SESSION['current_user'] = $login; // логин в сессии, чтобы различать пользователей между собой
    }
    else { // не вошли, вывести сообщение и форму
     echo '<p>Неверный логин или пароль!</p>'; form(); exit;
    }
   }
   // Код для авторизованного пользователя:
   echo "<p id='auth_text'>{$_SESSION['current_user']}, Вы авторизованы; <a href=\"{$_SERVER['SCRIPT_NAME']}?off\">Выйти</a></p>";
  ?>
</body>
</html>