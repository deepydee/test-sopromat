<?php
//print_r($_FILES);
//print_r($_POST);

$img_names = [];
$imgDir = "img";        // каталог для хранения изображений
  
  if(!empty($_FILES)){
    //print_r($_FILES);
    @mkdir($imgDir, 0777);  // создаем, если его еще нет
    $data = $_FILES['file'];
    //$img_names = $data['name'];
    // копируем файлы в каталог
    foreach($data['tmp_name'] as $k => $v) {
      $tmp = $v;
      // Проверяем, принят ли файл.
      if (is_uploaded_file($tmp)) {
        $info = @getimagesize($tmp);
        // Проверяем, является ли файл изображением.
        if (preg_match('{image/(.*)}is', $info['mime'], $p)) {
          // Имя берем равным текущему времени в секундах, а 
          // расширение - как часть MIME-типа после "image/".
          //echo time()."<br>";
          $name = "$imgDir/".time().".".$p[1];
          $img_names[$k] = $name;
          //$name = "$imgDir/".$data['name'][$k].".".$p[1];
          //$name = "$imgDir/".$data['name'][$k];
          // Добавляем файл в каталог с фотографиями.
          move_uploaded_file($tmp, $name);
          sleep(1);
          //echo "<img src=$name data-id='$k'>\n";
        } else {
          echo "<h2>Попытка добавить файл недопустимого формата!</h2>";
        }
      } else {
        echo "<h2>Ошибка закачки #{$data['error']}!</h2>";
      }
    }
    //print_r($img_names);
  }

  // меняем статус теста
  if (isset($_POST['test_enabled'])) {
    $status = (int)$_POST['test_enabled'];
    $test_id = (int)$_POST['test_id'];
    setTestStatus($test_id, $status);
  }  
  // добавление нового теста
  if (isset($_POST['new_test_name']) && !empty($_POST['new_test_name'])) {
    addNewTest($_POST['test_id'], $_POST['new_test_name']);
  }

  // удалить тест
  if (isset($_POST['remove_test']) && $_POST['remove_test'] == 1) {
    //print_r($_POST);
    removeTest($_POST['test_id']);
  }

  if (isset($_POST['q_id'])) {
    $question_id = $_POST['q_id'];
  }

  // добавить вопрос + ответ
  if ($_POST['update_q'] == 0 && isset($_POST['question']) && !empty($_POST['question']) && isset($_POST['ans']) && !empty($_POST['ans'])) {
    addQA ($_POST['test_id'], $_POST['question'], $_POST['level'], $img_names, $_POST['ans'], $_POST['correct_answer']);
    exit();
  }

  // добавить новый вопрос
  if ($_POST['update_q'] == 0 && isset($_POST['question']) && !empty($_POST['question'])) {
    //echo "<p>Change question id = {$_POST['q_id']}: " . $_POST['question'] . "</p>";
    $img = '';
    if (isset($img_names[0])) {
      $img = $img_names[0];
    }
    // ID последнего добавленного вопроса
    $question_id = addQuestion($_POST['test_id'], $_POST['question'], $img, $_POST['level']);
  }

  // обновить вопрос
  if($_POST['update_q'] == 1) {
    $question = $_POST['question'];
    $img = '';
    if (isset($img_names[0])) {
      $img = $img_names[0];
    }
    $qid = $_POST['q_id'];
    updateQuestion($qid, $question, $img);
  }

  // удалить вопрос
  if (isset($_POST['remove_q']) && $_POST['remove_q'] == 1) {
    removeQuestion($_POST['q_id']);
  }

  // добавить варианты ответа
  if (isset($_POST['ans']) && !empty($_POST['ans'])) {
    addAnswer($question_id, $_POST['ans'], $_POST['correct_answer'], $img_names);
  }

  // обновить картинку варианта ответа
  if(!empty($_FILES) && empty($_POST['question']) && empty($_POST['ans']) && empty($_POST['rem_ans'])) {
    updateAnswer($img_names);
  }

  // удалить картинку
  if(isset($_POST['rem_img']) && !empty($_POST['rem_img'])) {
    removeAnsImg($_POST['rem_img']);
  }

  if (isset($_POST['correct_answer'])) {
    //echo "<p>correct = {$_POST['correct_answer']}</p>";
  }

  if (isset($_POST['rem_ans']) && !empty($_POST['rem_ans'])) {
    removeAnswer($_POST['rem_ans']);
  }

  if(isset($_POST['rem_q_img_id']) && !empty($_POST['rem_q_img_id'])) {
    removeQuestionImg($_POST['rem_q_img_id']);
  }

/**
 * Добавить новый тест
 */
function addNewTest($test_id, $testname) {
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
  try {
    // 1. Проверяем переданы ли POST-параметры, 
    // если ответ положительный, помещаем новое
    // сообщение в базу данных
    if(!empty($testname))
    {
      $error = [];
      if(empty($testname)) {
        $error[] = "Отстуствует название теста";
      }
      // Если нет ошибок, помещаем сообщение
      // в базу данных
      if(empty($error))
      {
        // если это новый тест
        if ($test_id == 0) {
          $query = "INSERT INTO
                    test
                      VALUES (
                              NULL,
                              :testname,
                              '0',
                              DEFAULT)";
          $test = $pdo->prepare($query);
          $test->execute([
            'testname' => trim($testname),
          ]);
          echo "Тест с именем $testname успешно добавлен";
        } else { // редактируем название старого
          $query = "UPDATE test
                    SET test_name = :testname
                    WHERE      id = :testid";
          $test = $pdo->prepare($query);
          $test->execute([
            'testname' => trim($testname),
            'testid'   => trim($test_id),
          ]);
          echo "Тест с именем $testname успешно отредактирован";
        }
      }
    }
  } catch (PDOException $e) {
    echo "Ошибка выполнения запроса: " . $e->getMessage();
  }
}

/**
 * Включить/выключить тест
 */
function setTestStatus($test_id, $status) {
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
  try {
    // 1. Проверяем переданы ли POST-параметры, 
    // если ответ положительный, помещаем новое
    // сообщение в базу данных
    if(!empty($test_id))
    {
      $error = [];
      if(!isset($status)) {
        $error[] = "Отстуствует статус теста";
      }
      if(!isset($test_id)) {
        $error[] = "Отстуствует ID теста";
      }
      // Если нет ошибок, помещаем сообщение
      // в базу данных
      if(empty($error))
      {
          $query = "UPDATE test
                    SET enabled = :test_stat
                    WHERE    id = :testid";
          $test = $pdo->prepare($query);
          $test->execute([
            'test_stat' => trim($status),
            'testid'   => trim($test_id),
          ]);
          if ($status) {
            echo "Тест $test_id успешно включен";
          } else {
            echo "Тест $test_id успешно выключен";
          }
      }
    }
  } catch (PDOException $e) {
    echo "Ошибка выполнения запроса: " . $e->getMessage();
  }
}

/**
 * Добавить новый вопрос
 */
function addQuestion($tid, $question, $img, $level) {
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
  $shortened_q = substr($question, 0, 10)."...";
  $img = empty($img) ? '' : "$img";
  try {
        $query = "INSERT INTO
                  questions
                    VALUES (
                            NULL,
                            :question,
                            :level,
                            :test,
                            :img)";
        $q = $pdo->prepare($query);
        $q->execute([
                        'question' => trim($question),
                        'level'    => trim($level),
                        'test'     => trim($tid),
                        'img'      => trim($img)
                      ]);
        $qid = $pdo->lastInsertId();
        echo "Вопрос $shortened_q успешно добавлен в тест (ID $tid)";
  } catch (PDOException $e) {
    echo "Ошибка выполнения запроса: " . $e->getMessage();
  }
  return $qid;
}

/**
 * Обновить вопрос
 */
function updateQuestion($qid, $question, $img) {
  // Устанавливаем соединение с базой данных
  require_once("connect.php");

  try {
    if (!empty($img)) {
      $query = "UPDATE questions
                SET     question = :question,
                    question_img = :img
                WHERE         id = :qid";
      $ans = $pdo->prepare($query);
      $ans->execute([
        'question' => $question,
        'img'      => $img,
        'qid'      => $qid,
      ]);
    } else {
      $query = "UPDATE questions
                SET     question = :question
                WHERE         id = :qid";
      $ans = $pdo->prepare($query);
      $ans->execute([
        'question' => $question,
        'qid'      => $qid,
      ]);
    }
    echo "Вопрос $qid успешно обновлен<br>";
  } catch (PDOException $e) {
      echo "Ошибка выполнения запроса: " . $e->getMessage();
  }

}

/**
 * Удалить вопрос
 */
function removeQuestion($qid) {
   // Устанавливаем соединение с базой данных
   require_once("connect.php");
   // удалить картинки, если есть
   $fname = '';
   try {
    $query = "SELECT * FROM questions
              WHERE id = :qid";
    $q = $pdo->prepare($query);
    $q->execute([ 'qid' => trim($qid), ]);
    $question = $q->fetch();
    $fname = $question['question_img'];
    if (!empty($fname)) {
      unlink($fname);
      $fname = '';
    }
    } catch (PDOException $e) {
      echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
   try {
        $query = "DELETE FROM questions
                  WHERE id = :qid";
        $q = $pdo->prepare($query);
        $q->execute(['qid' => trim($qid)]);
        echo "Вопрос $qid успешно удален из теста<br>";
    } catch (PDOException $e) {
      echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
    // удалить все связанные варианты ответов
    try {
      // выбираем все варианты с данным ID вопроса
      $query = "SELECT * 
                FROM answers
                WHERE
                  parent_question = :id";
      $q = $pdo->prepare($query);
      $q->execute(['id' => $qid]);
      // проходимся по каждому
      while($answer = $q->fetch()) {
        $ans_id = $answer['id'];
        // и пытаемся удалить
        // картинку, если есть
        $fname = $answer['ans_img'];
        if (!empty($fname)) unlink($fname);
        // сам вариант
        try {
          //echo "$ans_id<br>";
          $query = "DELETE FROM answers
                    WHERE id = :aid";
          $del = $pdo->prepare($query);
          $del->execute(['aid' => $ans_id]);
          echo "Вариант $ans_id успешно удален из теста<br>";
        } catch (PDOException $e) {
          echo "Ошибка выполнения запроса: " . $e->getMessage();
        }
      }
      
    } catch (PDOException $e) {
      echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
    
}

/**
 * Добавить вариант ответа
 */
function addAnswer($qid, $answer, $correct_answer, $img) {
  // Устанавливаем соединение с базой данных
  require_once("connect.php");

  foreach ($answer as $id => $ans) {
    $shortened_answer =  strlen($ans) > 10 ? 
                         mb_substr($ans, 0, 10)."..." : 
                         $ans;
    $img = $img[$id];
    $is_correct = ($correct_answer == $id) ? 1 : 0;
    $img = empty($img) ? '' : "$img";
    if (empty($answer)) $shortened_answer = $img;
    if ($id >= 10000) { // новый вариант
      try {
        $query = "INSERT INTO answers
                  VALUES (
                            NULL,
                            :answer,
                            :question,
                            :correct,
                            :img)";
        $q = $pdo->prepare($query);
        $q->execute([
                        'answer'    => trim($ans),
                        'question'  => trim($qid),
                        'correct'   => trim($is_correct),
                        'img'       => trim($img)
                      ]);
        echo "Вариант ответа $shortened_answer для вопроса $qid успешно добавлен<br>";
      } catch (PDOException $e) {
        echo "Ошибка выполнения запроса: " . $e->getMessage();
      }
    } else { // обновить
      try {
        if (empty($img)) {
          $query = "UPDATE answers
                    SET answer = :ans,
                        correct_answer = :correct
                    WHERE id = :id";
          $q = $pdo->prepare($query);
          $q->execute([
            'ans'     => trim($ans),
            'correct' => trim($is_correct),
            'id'      => trim($id),
          ]);
        } else {
          $query = "UPDATE answers
                    SET answer = :ans,
                        correct_answer = :correct,
                        ans_img = :img
                    WHERE id = :id";
          $q = $pdo->prepare($query);
          $q->execute([
            'ans'     => trim($ans),
            'correct' => trim($is_correct),
            'img'     => trim($img),
            'id'      => trim($id),
          ]);
        }
        echo "Вариант ответа $shortened_answer ($id) успешно обновлен<br>";
      } catch (PDOException $e) {
          echo "Ошибка выполнения запроса: " . $e->getMessage();
      }
    }
  }
}

/**
 * Удалить вариант ответа
 */
function removeAnswer($rem_ans) {
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
  foreach($rem_ans as $aid => $v) {
    try {
      $query = "DELETE FROM answers
                WHERE id = :aid";
      $q = $pdo->prepare($query);
      $q->execute(['aid' => $aid]);
      echo "Вариант ответа $aid успешно удален<br>";
    } catch (PDOException $e) {
      echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
  }
}

/**
 * Добавить вопрос + варианты ответа
 */
function addQA ($tid, $question, $level, $img, $answer, $correct_answer) {
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
  $shortened_q =  strlen($question) > 10 ? 
                  mb_substr($question, 0, 10)."..." : 
                  $question;
  // сначала добавим вопрос и получим его ID
  $q_image = empty($img[0]) ? '' : "{$img[0]}";
  try {
        $query = "INSERT INTO
                  questions
                    VALUES (
                            NULL,
                            :question,
                            :level,
                            :test,
                            :img)";
        $q = $pdo->prepare($query);
        $q->execute([
                        'question' => trim($question),
                        'level'    => trim($level),
                        'test'     => trim($tid),
                        'img'      => trim($q_image)
                      ]);
        $qid = $pdo->lastInsertId();
        echo "Вопрос $shortened_q успешно добавлен в тест $tid<br>";
  } catch (PDOException $e) {
    echo "Ошибка выполнения запроса: " . $e->getMessage();
  }
  // теперь варианты ответа
  foreach ($answer as $id => $ans) {
    $image = $img[$id];
    $is_correct = ($correct_answer == $id) ? 1 : 0;
    $image = empty($image) ? '' : "$image";
    if (!empty($ans)) {
      $shortened_answer =  strlen($ans) > 10 ? 
                         mb_substr($ans, 0, 10)."..." : 
                         $ans;
    } else {
      $shortened_answer = $image;
    }
    try {
          $query = "INSERT INTO answers
                    VALUES (
                              NULL,
                              :answer,
                              :question,
                              :correct,
                              :img)";
          $q = $pdo->prepare($query);
          $q->execute([
                          'answer'    => trim($ans),
                          'question'  => trim($qid),
                          'correct'   => trim($is_correct),
                          'img'       => trim($image)
                        ]);
          echo "Вариант ответа $shortened_answer для вопроса $shortened_q ($qid) успешно добавлен<br>";
    } catch (PDOException $e) {
      echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
  }
}

/**
 * Обновить вариант ответа
 */
function updateAnswer($img_names) {
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
  foreach($img_names as $aid => $img) {
    try {
      $query = "UPDATE answers
                SET ans_img = :img
                WHERE      id = :aid";
      $ans = $pdo->prepare($query);
      $ans->execute([
        'img'   => $img,
        'aid'   => $aid,
      ]);
      echo "Вариант ответа $aid успешно отредактирован<br>";
    } catch (PDOException $e) {
        echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
  }
}

/**
 * Удалить картинку из варианта
 */
function removeAnsImg($rem_ans) {
 // Устанавливаем соединение с базой данных
 require_once("connect.php");
 foreach($rem_ans as $aid => $img) {
  // удалить картинки с диска
  $fname = '';
  try {
    $query = "SELECT * FROM answers
              WHERE id = :qid";
    $q = $pdo->prepare($query);
    $q->execute(['qid' => trim($aid),]);
    $question = $q->fetch();
    $fname = $question['ans_img'];
    if (!empty($fname)) {
      unlink($fname);
      echo "$fname удален с диска...";
      $fname = '';
    }
    } catch (PDOException $e) {
      echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
  $img = '';
  try {
    $query = "UPDATE answers
              SET ans_img = :img
              WHERE      id = :aid";
    $ans = $pdo->prepare($query);
    $ans->execute([
      'img'   => $img,
      'aid'   => $aid,
    ]);
    echo "Изображение для ответа $aid успешно удалено<br>";
    } catch (PDOException $e) {
        echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
  }
}

/**
 * Удалить картинку вопроса
 */
function removeQuestionImg($qid) {
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
   // удалить картинку с диска
   $fname = '';
   try {
     $query = "SELECT * FROM questions
               WHERE id = :qid";
     $q = $pdo->prepare($query);
     $q->execute(['qid' => trim($qid),]);
     $question = $q->fetch();
     $fname = $question['question_img'];
     if (!empty($fname)) {
       unlink($fname);
       echo "$fname удален с диска...";
       $fname = '';
     }
     } catch (PDOException $e) {
       echo "Ошибка выполнения запроса: " . $e->getMessage();
     }
   $img = '';
   try {
     $query = "UPDATE questions
               SET question_img = :img
               WHERE         id = :qid";
     $ans = $pdo->prepare($query);
     $ans->execute([
       'img'   => $img,
       'qid'   => $qid,
     ]);
     echo "Изображение для вопроса $qid успешно удалено<br>";
     } catch (PDOException $e) {
         echo "Ошибка выполнения запроса: " . $e->getMessage();
     }
 }

/**
 * Удалить тест
 */
function removeTest($tid) {
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
  // удалить тест
  try {
    $query = "DELETE FROM test
              WHERE id = :tid";
    $qt = $pdo->prepare($query);
    $qt->execute([
                'tid' => trim($tid),
              ]);
    echo "Тест $tid успешно удален<br>";
  } catch (PDOException $e) {
    echo "Ошибка выполнения запроса: " . $e->getMessage();
  }
  // удалить все связанные варианты ответов
  try {
    // выбираем все вопросы с данным ID теста
    $query = "SELECT * 
              FROM questions
              WHERE
                parent_test = :id";
    $q = $pdo->prepare($query);
    $q->execute(['id' => $tid]);
    // проходимся по каждому
    while($question = $q->fetch()) {
      $qid = $question['id'];
      try {
        $query = "DELETE FROM questions
                  WHERE id = :qid";
        $q1 = $pdo->prepare($query);
        $q1->execute([
                    'qid' => trim($qid),
                  ]);
        echo "Вопрос $qid успешно удален из теста $tid<br>";
    } catch (PDOException $e) {
      echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
    // удалить все связанные варианты ответов
    try {
      // выбираем все варианты с данным ID вопроса
      $query = "SELECT * 
                FROM answers
                WHERE
                  parent_question = :id";
      $q2 = $pdo->prepare($query);
      $q2->execute(['id' => $qid]);
      // проходимся по каждому
      while($answer = $q2->fetch()) {
        $ans_id = $answer['id'];
        // и пытаемся удалить
        try {
          //echo "$ans_id<br>";
          $query = "DELETE FROM answers
                    WHERE id = :aid";
          $del = $pdo->prepare($query);
          $del->execute([
                      'aid' => $ans_id,
                    ]);
          echo "Вариант $ans_id успешно удален из теста $tid<br>";
        } catch (PDOException $e) {
          echo "Ошибка выполнения запроса: " . $e->getMessage();
        }
        //echo "Вариант $ans_id успешно удален из теста<br>";
      }
    } catch (PDOException $e) {
      echo "Ошибка выполнения запроса: " . $e->getMessage();
    }
    }
  } catch (PDOException $e) {
    echo "Ошибка выполнения запроса: " . $e->getMessage();
  }
}

