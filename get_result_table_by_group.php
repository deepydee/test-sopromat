<?php
  // Устанавливаем соединение с базой данных
  require_once("connect.php");
  require_once "functions.php";
  $thresh = 50;
  $tests = [];
  $query = "SELECT * FROM test
            ORDER BY id"; 
  $com = $pdo->query($query);
    while($test = $com->fetch()) {
      $tests[$test['id']] = $test['test_name'];
    }
  
  $res = "<thead>\n";
  $res .= "<tr><th>ID</th><th>Дата/время</th><th>Фамилия</th><th>Группа</th><th>Тест ID</th><th>Вопросов</th><th>% верных</th><th>Зачет</th><th><input type=\"checkbox\" id=\"remove_all_results\"></th></tr></thead>\n";

    if(!empty($_POST['group'])) {
      $group = $_POST['group'];
      $tid = $_POST['tid'];
      $query =  "SELECT * FROM results
                WHERE st_group = :group
                AND test_id = :tid
                ORDER BY id";
      $q = $pdo->prepare($query);
      $q->execute([
                    'group' => $group,
                    'tid'   => $tid
                  ]);
    }
    $result = $q->fetchAll();
    $sum = $cnt = $avg = 0;
    foreach($result as $data) {
      $cnt++;
      // переменные результатов
      $test_data = unserialize($data['test_all_data']);

      $all_count = count($test_data); // кол-во вопросов
      $correct_answer_count = 0; // кол-во верных ответов
      $incorrect_answer_count = 0; // кол-во неверных ответов
      $percent = 0; // процент верных ответов

      // подсчет результатов
      foreach($test_data as $item){
        if( isset($item['incorrect_answer']) ) $incorrect_answer_count++;
      }
      $correct_answer_count = $all_count - $incorrect_answer_count;
      $percent = round( ($correct_answer_count / $all_count * 100), 2);
      $sum += $percent;
    }
    $avg = round($sum / $cnt);
    // Порог - среднее по группе
    //$thresh = $avg;

    foreach($result as $data) {
      // переменные результатов
      $test_data = unserialize($data['test_all_data']);
      $tm = $data['timer'];
      $all_count = count($test_data); // кол-во вопросов
      $correct_answer_count = 0; // кол-во верных ответов
      $incorrect_answer_count = 0; // кол-во неверных ответов
      $percent = 0; // процент верных ответов

      // подсчет результатов
      foreach($test_data as $item){
        if( isset($item['incorrect_answer']) ) $incorrect_answer_count++;
      }
      $correct_answer_count = $all_count - $incorrect_answer_count;
      $percent = round( ($correct_answer_count / $all_count * 100), 2);

      $score = ($percent >= $thresh) ? 'Зачтено' : 'Не зачтено';

      $res .= "<tr class='table-row' data-id='$percent'><td>{$data['id']}</td><td>{$data['time']}</td><td class='sirname' data-id='{$data['id']}' data-tid={$data['test_id']}>{$data['user_name']}</td><td class='group' data-group='{$data['st_group']}'>{$data['st_group']}</td><td class='tid' title='".$tests[$data['test_id']]."'>{$data['test_id']}</td><td>$all_count</td><td>$percent% (порог: $thresh%)</td><td>$score</td><td><input type=\"checkbox\" class=\"remove_results\" data-id='{$data['id']}'></td></tr>\n";
      }
echo $res;