<?php

/**
 * распечатка массива
 */
function print_arr($arr) {
  echo '<pre>' . print_r($arr, true) . '</pre>';
}

/**
 * получение списка тестов
 */
function get_tests() {
  global $db;
  $query = "SELECT * FROM test WHERE enabled = '1'";
  $res = mysqli_query($db, $query);
  if (!$res) return false;
  $data = array();
  while ($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
  }
  return $data;
}

/**
 * получение данных теста
 */
function get_test_data($test_id) {
  if(!$test_id) return;
  global $db;
  $query = "SELECT q.question, q.parent_test, q.question_img,
            a.id, a.answer, a.parent_question, a.ans_img
            FROM questions q
            LEFT JOIN answers a ON q.id = a.parent_question
            LEFT JOIN test ON test.id = q.parent_test
            WHERE q.parent_test = $test_id AND test.enabled = '1'
            ORDER BY a.parent_question, RAND()";
  /*
  $query = "SELECT q.question, q.parent_test, q.question_img,
            a.id, a.answer, a.parent_question, a.ans_img
            FROM questions q
            JOIN (SELECT id FROM questions WHERE questions.parent_test = $test_id ORDER BY RAND() LIMIT $limit) as q2 ON q.id=q2.id
            LEFT JOIN answers a ON q.id = a.parent_question
            LEFT JOIN test ON test.id = q.parent_test
            WHERE test.enabled = '1'";
*/
  $res = mysqli_query($db, $query);
  $data = NULL;
  while ($row = mysqli_fetch_assoc($res)) {
    if (!$row['parent_question']) return false;
    $data[$row['parent_question']][0] = $row['question'];
    $data[$row['parent_question']][$row['id']] = $row['answer'];
  }
  return $data;
}

/**
 * получение массива изображений
 */
function get_test_images($test_id) {
  if(!$test_id) return;
  global $db;
  $query = "SELECT q.question, q.parent_test, q.question_img,
            a.id, a.answer, a.parent_question, a.ans_img
            FROM questions q
            LEFT JOIN answers a ON q.id = a.parent_question
            LEFT JOIN test ON test.id = q.parent_test
            WHERE q.parent_test = $test_id AND test.enabled = '1'";
  
  $res = mysqli_query($db, $query);
  $data = [];

  while ($row = mysqli_fetch_assoc($res)) {
    if (!$row['parent_question']) return false;
    if ($row['question_img'] != '') {
      $data[$row['parent_question']][0] = $row['question_img'];
    }
    if ($row['ans_img'] != '') {
      $data[$row['parent_question']][$row['id']] = $row['ans_img'];
    }
  }
  return $data;
}

/**
 * строим пагинацию
 */
function pagination($count_questions, $test_data) {
  $keys = array_keys($test_data);
  $pagination = '<div class="pagination">';
  $pagination .= '<ul>';
  $pagination .= '<li><i class="nav-prev nav-disabled fa fa-angle-double-left" aria-hidden="true" title="Назад"></i><li>';
  for ($i = 1; $i <= $count_questions; $i++ ) {
    $key = array_shift($keys);
    if ($i == 1) {
      $pagination .= '<li><a class="nav-active '.addClassAnswered($key).'" href = "#question-'.$key.'" title="Вопрос '.$i.'">'.$i.'</a></li>';
    } else {
      $pagination .= '<li><a href = "#question-'.$key.'" title="Вопрос '.$i.'" class="'.addClassAnswered($key).'">'.$i.'</a></li>';
    }
  }
  $pagination .= '<li><i class="nav-next fa fa-angle-double-right" aria-hidden="true" title="Вперед"></i><li>';
  $pagination .= '</ul>';
  $pagination .= '</div>';
  return $pagination;
}

/**
 * получение id вопрос/ответ
 */
function get_correct_answers($test, $level = 3, $complexity = []) {
  if (!$test) return false;
  global $db;
  if (empty($complexity)) {
    // включать в выборку:
    switch ($level) {
      // простые вопросы
      case 1:
        $level_condition = "AND q.level = 1";
        break;
      // + средняя сложность
      case 2:
        $level_condition = "AND (q.level = 1 OR q.level = 2)";
        break;
      // + сложные
      case 3:
        $level_condition = "";
        break;
    }

    $query = "SELECT q.id AS question_id, a.id AS answer_id
              FROM questions q
              LEFT JOIN answers a
              ON q.id = a.parent_question
              LEFT JOIN test
              ON test.id = q.parent_test
              WHERE q.parent_test = $test AND a.correct_answer = '1' ".
              $level_condition
              ." AND test.enabled = '1'";

    $res = mysqli_query($db, $query);
    $data = NULL;
    while ($row = mysqli_fetch_assoc($res)) {
      $data[$row['question_id']] = $row['answer_id'];
    }
  } else {
    $data = [];
    for ($i = 0; $i < count($complexity); $i++) {
      $dt = [];
      $query = "SELECT q.id AS question_id, a.id AS answer_id
                FROM questions q
                LEFT JOIN answers a
                ON q.id = a.parent_question
                LEFT JOIN test
                ON test.id = q.parent_test
                WHERE q.parent_test = $test AND a.correct_answer = '1' ".
                "AND q.level = ".($i + 1) ." AND test.enabled = '1'";
      //echo "$query<br>";
      $res = mysqli_query($db, $query);
      
      while ($row = mysqli_fetch_assoc($res)) {
        $dt[$row['question_id']] = $row['answer_id'];
      }

        $keys = [];
        $rand_keys = array_rand($dt, $complexity[$i]);
        if (gettype($rand_keys) === 'integer') {
          $keys[$rand_keys] = $rand_keys;
          $dt = array_intersect_key($dt, $keys);
        } else {
          foreach($rand_keys as $key) $keys[$key] = $key;
          $dt = array_intersect_key($dt, $keys);
        }
        

      //echo "<br>DT array <br>";
      //print_r($dt);
      $data = $data + $dt;
      //echo "<br>DATA array <br>";
      //print_r($data);
      //echo "<br>---<br>";
    }
  }
  return $data;
}

/**
 * итоги
 * 1 - массив вопрос/ответы
 * 2 - правильные ответы
 * 3 - ответы пользователя
 */
function get_test_data_result($test_all_data, $result, $user_answers) {
  // заполняем массив $test_all_data правильными ответами и данными
  // о неотвеченных вопросах
  foreach ($result as $q => $a) {
    
    if(count($test_all_data[$q]) > 2) { // варианты ответа
      $test_all_data[$q]['correct_answer'] = $a;
      // добавим в массив данные о неотвеченных вопросах
      if (!isset($user_answers[$q])) {
        $test_all_data[$q]['incorrect_answer'] = 0;
      }
    } else { // точное соответствие
      $test_all_data[$q]['correct_answer'] = $test_all_data[$q][$a];
    }
  }
    // добавим неверный ответ, если таковой был
    foreach ($user_answers as $q => $a) {
      $a = preg_replace('/,/','.', $a); // заменим в ответе "," на "."
      //echo "a = $a<br>";
      // удалим из массива "левые" значения вопросов
      if (!isset($test_all_data[$q])) {
        unset($user_answers[$q]);
        continue;
      }
      // если есть "левые" значения ответов
      if (!isset($test_all_data[$q][$a]) && count($test_all_data[$q]) > 3) {
        $test_all_data[$q]['incorrect_answer'] = 0;
        continue;
      }
      if(count($test_all_data[$q]) > 3) {
        // добавим неверный ответ
        if ($test_all_data[$q]['correct_answer'] != $a) {
          $test_all_data[$q]['incorrect_answer'] = $a;
        }
      } else {
        $correct = $test_all_data[$q]['correct_answer'];

        if (abs(($correct - $a)/$correct) * 100 > 6) {
          $test_all_data[$q]['incorrect_answer'] = $a;
        }
      }

    }
  return $test_all_data;
}

/**
 * печать результатов
 */
function print_result($test_all_data_result, $test_img) {
	// переменные результатов
	$all_count = count($test_all_data_result); // кол-во вопросов
	$correct_answer_count = 0; // кол-во верных ответов
	$incorrect_answer_count = 0; // кол-во неверных ответов
	$percent = 0; // процент верных ответов

	// подсчет результатов
	foreach($test_all_data_result as $item){
		if( isset($item['incorrect_answer']) ) $incorrect_answer_count++;
	}
	$correct_answer_count = $all_count - $incorrect_answer_count;
	$percent = round( ($correct_answer_count / $all_count * 100), 2);

  if ($percent < 40) return 'Вы набрали менее 50%, попробуйте пройти тест заново.';

	// вывод результатов
	$print_res = '<div class="questions">';
		$print_res .= '<div class="count-res">';
			$print_res .= "<p>Всего вопросов: <b>{$all_count}</b></p>";
			$print_res .= "<p>Из них отвечено верно: <b>{$correct_answer_count}</b></p>";
			$print_res .= "<p>Из них отвечено неверно: <b>{$incorrect_answer_count}</b></p>";
			$print_res .= "<p>% верных ответов: <b>{$percent}</b></p>";
		$print_res .= '</div>';	// .count-res

		// вывод теста...
		foreach($test_all_data_result as $id_question => $item){ // получаем вопрос + ответы
			$correct_answer = $item['correct_answer'];
			$incorrect_answer = null;
			if( isset($item['incorrect_answer']) ){
				$incorrect_answer = $item['incorrect_answer'];
				$class = 'question-res error';
			}else{
				$class = 'question-res ok';
			}
			$print_res .= "<div class='$class'>";
			foreach($item as $id_answer => $answer){ // проходимся по массиву ответов
				if( $id_answer === 0 ){
					// вопрос
					$print_res .= "<p class='q'>".translateText($answer)."</p>";
          // у вопроса есть картинка?
          if(isset($test_img[$id_question][0])) {
            $img = $test_img[$id_question][0];
            $print_res .= "<div class = \"q-img\">".
            "<img src = \"$img\">".
            "</div>";
          } 
				} elseif( is_numeric($id_answer) ){
					// ответ
					if( $id_answer == $correct_answer || ($answer == $correct_answer) ){
						// если это верный ответ
						$class = 'a ok2';
            $icon_class = 'fa fa-check';
					} elseif( $id_answer == $incorrect_answer ){
						// если это неверный ответ
						$class = 'a error2';
            $icon_class = 'fa fa-times';
					} else{
						$class = 'a';
            $icon_class = '';
					}
          if(count($item) == 4) { // неверный ответ при "точном соответствии"
            $print_res .= "<p class='a error2'>".$incorrect_answer;
            $print_res .= "<p class='$class'>".translateText($answer);
          } else {
            $print_res .= "<p class='$class'>".translateText($answer);
          }
					
          if(isset($test_img[$id_question][$id_answer])) {
            $img = $test_img[$id_question][$id_answer];
            $print_res .= "<i class = \"a-img\">".
            "<img src = \"$img\">".
            "</i>";
            $print_res .= " <i class='$icon_class'></i></p>";
          } else {
            $print_res .= " <i class='$icon_class'></i></p>";
          }
				}
			}
			$print_res .= '</div>'; // .question-res
		}

	$print_res .= '</div>'; // .questions

	return $print_res;
}

/**
 * Функция преобразования текста
 */
function translateText($txt) {
  /*
  $txt = preg_replace('/([A-Za-z])(\d{1,})/u', '$1<sub>$2</sub>', $txt);
  $txt = preg_replace('/deg/u', '<sup>&deg;</sup>', $txt);
  $txt = preg_replace('/Alpha/u','&Alpha;', $txt);
  $txt = preg_replace('/alpha/u','&alpha;', $txt);
  $txt = preg_replace('/Beta/u','&Beta;', $txt);
  $txt = preg_replace('/beta/u','&beta;', $txt);
  $txt = preg_replace('/Gamma/u','&Gamma;', $txt);
  $txt = preg_replace('/gamma/u','&gamma;', $txt);
  $txt = preg_replace('/Delta/u','&Delta;', $txt);
  $txt = preg_replace('/delta/u','&delta;', $txt);
  $txt = preg_replace('/Epsilon/u','&Epsilon;', $txt);
  $txt = preg_replace('/epsilon/u','&epsilon;', $txt);
  $txt = preg_replace('/Zeta/u','&Zeta;', $txt);
  $txt = preg_replace('/zeta/u','&zeta;', $txt);
  $txt = preg_replace('/Eta/u','&Eta;', $txt);
  $txt = preg_replace('/(?<!\S)(eta)/u','&eta;', $txt);
  //$txt = preg_replace('/eta/','&eta;', $txt);
  $txt = preg_replace('/Theta/u','&Theta;', $txt);
  $txt = preg_replace('/theta/u','&theta;', $txt);
  $txt = preg_replace('/Iota/u','&Iota;', $txt);
  $txt = preg_replace('/iota/u','&iota;', $txt);
  $txt = preg_replace('/Kappa/u','&Kappa;', $txt);
  $txt = preg_replace('/kappa/u','&kappa;', $txt);
  $txt = preg_replace('/Lambda/u','&Lambda;', $txt);
  $txt = preg_replace('/lambda/u','&lambda;', $txt);
  $txt = preg_replace('/Mu/u','&Mu;', $txt);
  $txt = preg_replace('/(?<!\S)(mu)/u','&mu;', $txt);
  $txt = preg_replace('/Nu/u','&Nu;', $txt);
  $txt = preg_replace('/(?<!\S)(nu)/u','&nu;', $txt);
  $txt = preg_replace('/Xi/u','&Xi;', $txt);
  $txt = preg_replace('/(?<!\S)(xi)/u','&xi;', $txt);
  $txt = preg_replace('/Omicron/u','&Omicron;', $txt);
  $txt = preg_replace('/omicron/u','&omicron;', $txt);
  $txt = preg_replace('/Pi/u','&Pi;', $txt);
  $txt = preg_replace('/(?<!\S)(pi)/u','&pi;', $txt);
  $txt = preg_replace('/Rho/','&Rho;', $txt);
  $txt = preg_replace('/rho/u','&rho;', $txt);
  $txt = preg_replace('/Sigma/u','&Sigma;', $txt);
  $txt = preg_replace('/sigma/u','&sigma;', $txt);
  $txt = preg_replace('/Tau/u','&Tau;', $txt);
  $txt = preg_replace('/(?<!\S)(tau)/u',' &tau;', $txt);
  $txt = preg_replace('/Upsilon/u','&Upsilon;', $txt);
  $txt = preg_replace('/upsilon/u','&upsilon;', $txt);
  $txt = preg_replace('/Phi/u','&Phi;', $txt);
  $txt = preg_replace('/(?<!\S)(phi)/u','&phi;', $txt);
  $txt = preg_replace('/Chi/u','&Chi;', $txt);
  $txt = preg_replace('/chi/u','&chi;', $txt);
  $txt = preg_replace('/Psi/u','&Psi;', $txt);
  $txt = preg_replace('/(?<!\S)(psi)/u','&psi;', $txt);
  $txt = preg_replace('/Omega/u','&Omega;', $txt);
  $txt = preg_replace('/omega/u','&omega;', $txt);
  $txt = preg_replace('/([A-Za-zА-Я])(\d{1,2})/u', '$1<sub>$2</sub>', $txt);
  $txt = preg_replace('/([A-Za-z])([а-я]{1,2})/u', '$1<sub>$2</sub>', $txt);
  $txt = preg_replace('/([а-я])(\d{1})/u', '$1<sup>$2</sup>', $txt);
  $txt = preg_replace('/(\d{1,})\*(\d{1,})/u', '$1&#183;$2', $txt);
  $txt = preg_replace('/(\((\D{1,3})\))/u', '<sub>$2</sub>', $txt);
  */
  //$txt = preg_replace('/(\^)(-*\d+)\s*/u', '<sup>$2 </sup>', $txt);
  /*
  $txt = preg_replace('/(?<=;)([A-Za-km-zа-яА-Я]+)/u', '<sub>$1</sub>', $txt);
  $txt = preg_replace('/(?<=\S)(max|min)/u', '<sub>$1</sub>', $txt);
  $txt = preg_replace('/(?<=y)(\S+)/u', '<sub>$1</sub>', $txt);
  $txt = preg_replace('/(?<=F)([^\|L><]{1,3})/u', '<sub>$1</sub>', $txt);
  $txt = preg_replace('/(?<=n)([^u;\s]{1,3})/u', '<sub>$1</sub>', $txt);
  */
  //$txt = preg_replace('/(?<=σ)(\S{1,3})/u', '<sub>$1</sub>', $txt);
  //$txt = preg_replace('/\*/u', '&#183;', $txt);
  
  return $txt;
}
/**
 * Читаем CSV
 */
function getCSVInfo($fname) {
  $f = fopen($fname, "rt") or die('Read file error');
  if ($f) {
    for ($i = 0; $data = fgetcsv($f, 1000, ";"); $i++){
      $answers[$i] = $data; 
    }
  fclose($f);
  }
  return $data;
}

/**
 * Установка радиокнопки в требуемое состояние
 */

 function radioCheck($key) {
   if (empty($_SESSION['state']['checked'][$key])) return "";
   else return "checked";
 }

 /**
  * Ввод текста
  */

function fillText($key) {
  if (empty($_SESSION['state']['typed'][$key])) return "";
  else return $_SESSION['state']['typed'][$key];
}

 /**
  * Отвеченный вопрос
  */

function addClassAnswered($key) {
  $answered_q = [];
  foreach($_SESSION as $k => $v) {
    if(strpos($k, 'nswer') != false) $answered_q[] = $_SESSION[$k];
  }
  foreach ($answered_q as $v) {
    if ($v == $key) {
      return "answered";
    }
  }
  return "";
}

 /**
  * Отправить результаты на сервер
  */
  function setTestResults($uname, $group, $tid, $results, $time, $ufp) {
    // Устанавливаем соединение с базой данных
    require_once("connect.php");
    $uname = filter_var($uname, FILTER_SANITIZE_STRING);
    // Если тест уже проходился - удалить старый результат
    try {
      $query = "INSERT INTO
                results
                  VALUES (
                          NULL,
                          :ufp,
                          :uname,
                          :tid,
                          :test_data,
                          :tm,
                          NOW(),
                          :st_group)";
      $q = $pdo->prepare($query);
      $q->execute([
                      'ufp'            => trim($ufp),
                      'uname'          => trim($uname),
                      'tid'            => trim($tid),
                      'test_data'      => serialize($results),
                      'tm'             => trim($time),
                      'st_group'       => trim($group)
                    ]);
    } catch (PDOException $e) {
      echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
  }

  /**
   * Достать отпечаток пользователя из базы
   */
  function isRetry($ufp, $tid, $idle_time) {
    global $db;
    // массив отпечатков
    $fp_array = [];

    $query = "SELECT * FROM results";
    $res = mysqli_query($db, $query);
    while ($data = mysqli_fetch_assoc($res)) {
      if(!empty($data['fp'])) {
        $fp_array[$data['fp']][$data['test_id']] = $data['time'];
      }
    }
    // вытаскиваем отпечатки из базы
    foreach ($fp_array as $k => $fp) {
      foreach ($fp as $t_id => $tm) {
        if($ufp == $k && $t_id == $tid) { // повторная попытка
          $timestamp = strtotime($tm);
          $passed = time() - $timestamp;
          //echo "passed $passed<br>";
          $idl = $idle_time * 60;
          //echo "idle $idl<br>";
          if ($passed < $idl) {
            //echo "ok<br>";
            echo "Вы уже проходили данный тест. Прошло $passed секунд из $idl. Результаты не записаны. Ожидайте...<br>";
            return true;
          }
        }
      }
    }
    return false;
  }


/* Выбрать несколько

SELECT question, question_img, answer, ans_img
		FROM questions JOIN answers
        ON questions.id = answers.parent_question
        AND (parent_test = 22 OR parent_test = 4 OR parent_test = 25)
        ORDER BY question, RAND();

*/