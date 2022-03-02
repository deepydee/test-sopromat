<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang='ru'>
<head>
  <title>Административная панель</title>
  <meta charset='utf-8'>
  <link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="admin.css">
  <link rel="shortcut icon" href="favicon.png" type="image/png">
  <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
  <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
  <script type="text/javascript" src="jquery-3.6.0.min.js" ></script>
  <script type="text/javascript" src="admin.js"></script>
   </head>

  <body>
  
  <?php
    // Устанавливаем соединение с базой данных
    require_once("connect.php");
  ?>
  <a href="results.php" id="result-id" target="_blank">Результаты</a>
  <div class="info"></div>
  <select id='test'>
  </select>

  <form action="qr.php" class='qr_form' method = "POST" target="_blank">
    <input type="text" name="link" id="qr_link" hidden>
    <div class="submit"><input type="submit" value="Получить QR"></div>
  </form>

  <div class="testname" data-id="0"></div>
  <p class = "edit-t">
    <textarea cols = '80' rows='5' id='test-name' type='text'></textarea>
  </p>
  <p class="test-status">
    Статус теста: <span></span><input type="checkbox" name="test-enable" id="test-status-checkbox">
  </p>
   <p class="quantity-status">
    Выводить вопросов: <span></span><input type="text" size="1" id="q-quant" value="3"> из <span id="all_q_count"></span>
  </p>

  <p class="complexity">
    Простых: <span class="simple_q"></span>
    Средней сложности: <span class="mid_q"></span>
    Сложных: <span class="complex_q"></span>
  </p>

  <p class="complexity-check">
    <input type="checkbox" name="complexity-check" id="complexity-check">
    <label for="complexity-check">Сложная выборка</label>
  </p>

  <p class="complexity-options">
    <input type="radio" name="complexity-option" value="1" id="radio-1">
    <label for="radio-1">% от категории</label>
    <input type="radio" name="complexity-option" value="2" id="radio-2">
    <label for="radio-2">простая градация</label>
    <p class="radio-2-select">
      <select name="simple_degree" id="simple_select">
        <option value="1">Только простые</option>
        <option value="2">Простые и средние</option>
        <option value="3">Все</option>
      </select>
    </p>
    <p class="radio-1-form">
      <label>% простых <input type="text" size="1" id="simple_input"></label>
      <label>% средних <input type="text" size="1" id="mid_input"></label>
      <label>% сложных <input type="text" size="1" id="complex_input"></label>
      <div><span class="complexity-info"></span></div>
      <div class="test-link"></div>
    </p>
  </p>
  

  <button class="btn btn-rem-t" data-id="0" id="rem-btn-t">Удалить тест</button>
  <select id='q' disabled='disabled'>
    <option value='0'>Выберите вопрос</option>
  </select>
  <input type="checkbox" name="translate_chkbox" id="translate_math">
  <label for="translate_math">Преобразовать формулы MathJax</label>

  <div class="question"><i class='fa fa-plus add-question' title='Добавить вопрос'></i></div>
  
  <p class = "edit-q">
    <span class='fld'>
      <textarea cols = '80' rows='5' data-remove='0' id='q-content' type='text'></textarea>
    </span>
    <input type="file" name="filename[]" class="btn btn-img" data-id="0" id="img-btn-0"/>
    <select name="q_level" id="q-level-add-q">
      <option value="0">Сложность вопроса</option>
      <option value="1">Простой</option>
      <option value="2">Средняя сложность</option>
      <option value="3">Сложный</option>
    </select>
  </p>
  <div class="answers">
    <ul>
    </ul>
    <i class='fa fa-plus add-answer' title='Добавить вариант'></i>
  </div>
  <div><button class="btn center" id="submit-id">Добавить в БД</button></div>
  <div class="modal"><!-- Place at bottom of page --></div>
</body>
</html>