<?php
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

session_start(); // инициализируем сессию
if (!isset($_SESSION['testing'])) {
  $_SESSION['testing'] = time(); // Обновить сесссию
}

//ini_set("display_errors", 1);
//error_reporting(E_ALL);

require_once 'config.php';
require_once 'functions.php';

$tests = get_tests(); // список тестов

if (isset($_SESSION['prev_tid'])) {
  $prev_tid = $_SESSION['prev_tid'];
} else {
  $prev_tid = 0;
}

$tid = ''; // ID теста
$test_id_array = []; // массив со всеми ID тестов

if(isset($_GET['test']) && count($_GET['test']) > 1) { // мультитест
  foreach($_GET['test'] as $id) {
    $tid .= $id;
  }
  $tid = (int)$tid;
} else { // одиночный тест
  $tid = $_GET['test'][0];
}

if(isset($_GET['test'])) {
  $result = []; // массив правильных ответов
  $keys = []; // ключи (идентефикаторы) вопросов
  // сбрасываем сессию и куки при переключении теста
  if (!empty($prev_tid) && $tid != $prev_tid) {
    $_SESSION = [];
    setcookie('timer', null, time() - 3600);
    setcookie('terminated', null, time() - 3600);
    setcookie('status', null, time() - 3600);
  }

  $level = isset($_GET['level']) ? (int)$_GET['level'] : 3; // уровень сложности (1,2,3)
  $complexity = []; // Если есть разбивка по уровням сложности в процентах 

  foreach($_GET['test'] as $test_id) {
    $test_id_array[$test_id] = (int)$test_id; // заполним массив ID тестов
    foreach($tests as $k => $v) {
      if($v['id'] == $test_id) {
        $limit = $v['q_quant']; // количество вопросов для вывода
      }
    }
    
    if(isset($_GET['pt'])) {
      $cmp = [];
      $cmp[0] = round($limit * ($_GET['pt'][0] / 100), 0); // к-во простых
      $cmp[1] = round($limit * ($_GET['pt'][1] / 100), 0); // средних
      $cmp[2] = $limit - ($cmp[0] + $cmp[1]); // сложных

      for ($i = 0; $i < count($cmp); $i++) {
        $complexity[$i] = $complexity[$i] + $cmp[$i];
      }
    }
    // массив правильных ответов отдельного теста
    $res = get_correct_answers($test_id, $level, $complexity);
    $result = $result + $res; // складываем в общий массив 
    // выбираем несколько случайных ключей из массива ответов
    $ks = [];
    $rnd_ks = array_rand($res, $limit);
    foreach($rnd_ks as $key) $ks[$key] = $key;
    $keys = $keys + $ks; // складываем в общий массив
  }

  // считаем время теста, в зависимости от сложности
  if (!empty($complexity)) {
    $tm = 0;
    for ($i = 0; $i < count($complexity); $i++) {
      switch($i) {
        case 0:
          $tm += $complexity[$i] * 2; // простой - 2 мин
          break;
        case 1:
          $tm += $complexity[$i] * 3; // простой - 3 мин
          break;
        case 2:
          $tm += $complexity[$i] * 5; // сложный - 5 мин
          break;
      }
    }
    $time = $tm . ":15";
    $idle_time = $tm;
  } else {
    $time = (count($keys) * 2) . ":15";
    $idle_time = (count($keys) * 2);
  }
  

  if (isset($_SESSION['testing']) && (time() - $_SESSION['testing'] > $idle_time * 60)) {
    session_unset();     // удалить массив $_SESSION
    session_destroy();   // уничтожить данные сессии
  }

  // выбираем ключи вопросов из сессии или нет?
  if (!isset($_SESSION['keys'])) {
    $_SESSION['keys'] = $keys;
  } else {
    $keys = [];
    $keys = $_SESSION['keys'];
  }

  // итоговый массив вопросов
  $result = array_intersect_key($result, $keys);

  $test_all_data = []; // данные теста
  foreach($_GET['test'] as $test_id) {
    $td = [];
    $td = get_test_data($test_id);
    $test_all_data = $test_all_data + $td;
  }
  // урезаем массив
  $test_all_data = array_intersect_key($test_all_data, $keys);
  //print_arr($test_all_data);
  $test_data = $test_all_data; 
  $_SESSION['prev_tid'] = $tid;

  $test_img = []; // массив с картинками
  foreach($_GET['test'] as $test_id) {
    $t_img = [];
    $t_img = get_test_images($test_id);
    $test_img = $test_img + $t_img;
  }

  if (is_array($test_data)) {
    $count_questions = count($test_data);
    $pagination = pagination($count_questions, $test_data);
  }
//print_arr($_SESSION);
}

if (isset($_POST['test'])) {
  $tid = '';
  $test_id_array = unserialize($_POST['test']);
  if (count($test_id_array) > 1) {
    foreach ($test_id_array as $test_id) {
      $tid .= $test_id;
    }
    $tid = (int)$tid;
  } else {
    $tid = array_keys($test_id_array)[0];
  }
  $keys = unserialize($_POST['keys']);
  
  unset($_POST['test']);
  $result = [];
  foreach ($test_id_array as $test) {
    $res = [];
    $res = get_correct_answers($test);
    $result = $result + $res;
  }

  $result = array_intersect_key($result, $keys);

  if (!is_array($result)) exit('Ошибка!');
  
  $test_all_data = []; // данные теста
  foreach($test_id_array as $test_id) {
    $td = [];
    $td = get_test_data($test_id);
    $test_all_data = $test_all_data + $td;
  }
  // урезаем массив
  $test_all_data = array_intersect_key($test_all_data, $keys);

  // 1 - массив вопрос/ответы, 2 - правильные ответы, 3 - ответы пользователя
  $test_all_data_result = get_test_data_result($test_all_data, $result, $_POST['answers']);
  $sirname = $_POST['sirname']; // фамилия студента
  $group = $_POST['st_group']; // фамилия студента
  $time = $_POST['time']; // времени осталось
  $ufp = $_POST['fp']; // отпечаток
  $idle_time = (count($keys) * 2);

  if (isRetry($ufp, $tid, $idle_time)) {
    session_unset();     // удалить массив $_SESSION
    session_destroy();   // уничтожить данные сессии
    die;
  }

  // записывает результаты в базу
  if(!empty($sirname) && !empty($test_all_data_result)) {
    setTestResults($sirname, $group, $tid, $test_all_data_result, $time, $ufp);
  }
  
  $test_img = []; // массив с картинками

  foreach($test_id_array as $test) {
    $t_img = [];
    $t_img = get_test_images($test);
    $test_img = $test_img + $t_img;
  }
  //print_arr($test_all_data_result);
  echo print_result($test_all_data_result, $test_img);
  die;
}

if (isset($_GET['test'])) {


}
//print_arr($_COOKIE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Система тестирования</title>
  <link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="style.css">
  <link rel="shortcut icon" href="favicon.png" type="image/png">
  <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
  <script type="text/javascript" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
</head>
<body>
  <div class="wrap">
    <?php if ($tests): ?>
      <?php if (isset($_SESSION['my_inside'])): ?>
        <select id = 'select_test'>
          <option value="0"> Выберите тест</option>
        <?php foreach($tests as $test): ?>
          <option value="<?=$test['id']?>"><?=$test['test_name']?></option>
        <?php endforeach; ?>
        </select>
      <?php endif; ?>
      <h1 class="testname"></h1>
      <?php if (isset($test_data)): ?>
        <div class="countdown green"></div>
        <input type="text" id="student_name" placeholder="Фамилия">
        <input type="text" id="student_group" placeholder="Группа">
        <span class="validity-info"></span>
        <input type="hidden" id="timer_val" value="<?=$time?>"></input>
        <input type="hidden" id="idle_time_val" value="<?=$idle_time?>"></input>
        <input hidden type="text" id="visitorId" name="visitorId" value="">
        <span class="none" id="test-off">0</span>
      <?php endif; ?>
    <div class="content">
     
    <?php if (isset($test_data)): ?>
        <input type="hidden" id="test-id" value="<?=serialize($test_id_array)?>">
        <input type="hidden" id="keys" value="<?=serialize($keys)?>">
        <?=$pagination?>
        <p class="count-q">Вопрос <span id="curr_q"></span> из <?=$count_questions?></p>
        
        <div class="test-data">
          <?php foreach($test_data as $id_question => $item): // получаем каждый вопрос и ответы ?>
            <div class="question" data-id="<?=$id_question?>" id="question-<?=$id_question?>">
              <?php foreach($item as $id_answer => $answer): // проходимся по массиву вопорс/ответы?>
                <?php if(!$id_answer): // выводим вопрос ?>
                  <p class="q"><?=translateText($answer)?></p>
                  <?php if(isset($test_img[$id_question][0])): // у вопроса есть картинка? ?>
                    <div class = "q-img">
                      <img src = "<?=$test_img[$id_question][0]?>">
                    </div>
                  <?php endif; ?>
                <?php else: // выводим варианты ответов ?>

                  <p class="a">
                    <?php if(isset($test_img[$id_question][$id_answer])): // у варианта ответа есть картинка? ?>
                      <label for="answer-<?=$id_answer?>" class = "a-img">
                        <input type="radio" name="question-<?=$id_question?>" value="<?=$id_answer?>" id="answer-<?=$id_answer?>" <?=radioCheck("answer-".$id_answer)?>>
                        <img src = "<?=$test_img[$id_question][$id_answer]?>">
                        <?=translateText($answer)?>
                      </label>
                    <?php else: ?>
                      <?php if(count($item) > 2): // более 2-х вариантов - выбор из многих?>
                        <input type="radio" name="question-<?=$id_question?>" value="<?=$id_answer?>" id="answer-<?=$id_answer?>" <?=radioCheck("answer-".$id_answer)?>>
                        <label for="answer-<?=$id_answer?>"><?=translateText($answer)?></label>
                      <?php else: // только 1 вариант - ввести точный ответ?>
                        <input type="text" size="1" name="question-<?=$id_question?>" data-id="<?=$id_answer?>" id="answer-<?=$id_answer?>" value="<?=fillText("answer-".$id_answer)?>">
                        <label for="answer-<?=$id_answer?>">Введите ответ</label>
                      <?php endif; ?>
                    <?php endif; ?>
                  </p>
                <?php endif; // $id_answer?>
              <?php endforeach; // $item?>
            </div> <!-- .question -->
          <?php endforeach;?>
        </div> <!-- .test-data -->
        <div class="buttons">
          <button class="center btn none" id="btn">Завершить тест</button>
        </div>

    <?php else: // isset($test_data) ?>
      <p>Обратитесь к преподавателю</p>
      <span class="none" id="test-off">1</span>
    <?php endif; // isset($test_data) ?>
    </div> <!-- .content -->
    <?php else: // $tests ?>
      <h3>Нет тестов</h3>
    <?php endif; // $tests ?>
  </div> <!-- .wrap -->
  <script>
      // Initialize the agent at application startup.
      
      const fpPromise = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.onload = resolve;
        script.onerror = reject;
        script.async = true;
        script.src = 'https://cdn.jsdelivr.net/npm/'
          + '@fingerprintjs/fingerprintjs-pro@3/dist/fp.min.js';
        document.head.appendChild(script);
      })
        .then(() => FingerprintJS.load({
          token: 'usuXV53nuGBsSqZ9OFHT'
        }));
  </script>

  <script src="jquery-3.6.0.min.js"></script>
  <script src="jquery.cookie.js"></script>
  <script src = "scripts.js"></script>
  <div class="modal_wait"><!-- Place at bottom of page --></div>
</body>
</html>