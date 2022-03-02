$(function() {
  var num = 10000; // счетчик новых вопросов
  var test_list_selected = 0; // выбранный тест
  var qid;
  var remove_test = 0;
  var remove_q_img = 0;
  var update_question = 0;
  var qr = '#';
  var complexity = 0;
  // заполнить список тестов динамически
  $('#test').load('load_test_list.php');
  
  // сразу выводим название теста
  //console.log('loading');
  $('.testname').html($('#test option:selected').text());

  // при клике только по "выберите тест" - создание имени нового теста
  $('.testname').on('click', function() {
    $('#submit-id').css('display', 'block');
    $('#submit-id').html('Добавить тест');
    $('#submit-id').prop('disabled', false);
    $('.edit-t').fadeIn();
    $('#test-name').val($(this).text());
    if ($('#test option:selected').val() == 0) {
      $('#q').prop('disabled', true);
      $('.question').hide();
      $('.answers').hide();
    }
  });

  // выбираем тест из выпадающего списка
  $('#test').on('change', function(){
    $('#submit-id').hide();
    $('.qr_form').show();
    // количество выводимых вопросов из базы
    questions_num = $('#test option:selected').data('questions');
    $('#q-quant').val(questions_num);

    $('.quantity-status').show();
    test_list_selected = $('#test option:selected').val();
    //console.log(test_list_selected);
    // показать кнопку удаления теста
    $('#rem-btn-t').fadeIn();
    $('.add-question').fadeIn();
    // скрываем варианты ответа, если они остались
    $('.answers').fadeOut();
    $('.edit-q').hide();
    $('.test-status').show();
    $('.testname').html($('#test option:selected').text());
    if($('#test option:selected').val() == 0) {
      $('.testname').text('Добавить тест');
    }
    // тест включен?
    test_enabled = $('#test option:selected').data('enabled');
    //console.log(test_enabled);
    if (test_enabled) {
      // установить флажок
      $('#test-status-checkbox').prop('checked', true);
      $('.test-status span').removeClass('disabled');
      $('.test-status span').addClass('enabled');
      $('.test-status span').text('В работе');
    } else {
      $('#test-status-checkbox').prop('checked', false);
      $('.test-status span').removeClass('enabled');
      $('.test-status span').addClass('disabled');
      $('.test-status span').text('Выключен');
    }
    if ($('#test option:selected').val() != 0) { // $('#test-name').is(":visible") && 
      //$('.edit-t').fadeOut();
      $('.question').show();
      $('#q').show();
      $('.test-status').show();
      $('.quantity-status').show();
      $('#submit-id').prop('disabled', false);
    } else {
      $('.question').hide(); 
      $('#q').hide(); 
      $('#submit-id').prop('disabled', true);
      $('.test-status').hide();
      $('.quantity-status').hide();
    }
    // AJAX-запрос
    $.ajax({
      url: 'select_test.php?id=' + $('#test').val()
    })
    .done(function(data){
      $('#q').html(data);
      $('.btn-add-to-test').show();
      $('.btn-add-all').show();
      $('.btn-clean').show();
      $('.complexity').show();
      $('.complexity-check').show();
      $('.simple_q').text($('#q option[value=0]').data('simple'));
      $('.mid_q').text($('#q option[value=0]').data('mid'));
      $('.complex_q').text($('#q option[value=0]').data('complex'));
      
      $('.qr_form').show();
      $('#q').prop('disabled', false);
      $('#all_q_count').text($('#q option').length - 1);
    });
  });

  // формируем список выбранных тестов
  $('.btn-add-to-test').on('click', function() {
    //$('.tests-selected').html('<li>'+$('#test option:selected').text()+'</li>');
    $("<li data-id='"+ $('#test option:selected').val()+"'><i class='fa fa-minus rem-frm-tst-lst'></i>"+$('#test option:selected').text()+"</li>").appendTo('.tests-selected');
    var get_params = '?test[]=';
    var length = $('.tests-selected li').length;
    $('.tests-selected li').each(function(index, el) {
      var tid = $(el).data('id');
      if (index === (length - 1)) {
        get_params = get_params + tid;
      } else {
        get_params = get_params + tid + '&test[]=';
      }
    });

    var protocol = window.location.protocol;
    var host = window.location.host;
    var path = window.location.pathname;
    path = protocol + '//' + host + path.substring(0,path.length-9) 
                    + get_params;
    $('#qr_link').val(path);
    $('.test-link').html("<a href='"+path+"' target='_blank'>Ссылка на тест</a>");
  });

  // Очистить список тем
  $('.btn-clean').on('click', function() {
    $('.tests-selected').empty();
  });

  // выбрать все темы
  $('.btn-add-all').on('click', function() {
    $('.tests-selected').empty();
    $('#test option').each(function(index, el) {
      if($(el).data('enabled') == 1) {
        $("<li data-id='"+ $(el).val()+"'><i class='fa fa-minus rem-frm-tst-lst'></i>"+$(el).text()+"</li>").appendTo('.tests-selected');
      }
    });
    var get_params = '?test[]=';
    var length = $('.tests-selected li').length;
    $('.tests-selected li').each(function(index, el) {
      var tid = $(el).data('id');
      if (index === (length - 1)) {
        get_params = get_params + tid;
      } else {
        get_params = get_params + tid + '&test[]=';
      }
    });

    var protocol = window.location.protocol;
    var host = window.location.host;
    var path = window.location.pathname;
    path = protocol + '//' + host + path.substring(0,path.length-9) 
                    + get_params;
    $('#qr_link').val(path);
    $('.test-link').html("<a href='"+path+"' target='_blank'>Ссылка на тест</a>");
  });

  // удалить тему из списка
  $('.tests-selected').on('click', '.rem-frm-tst-lst', function() {
    $(this).parent().remove();
    var get_params = '?test[]='; //+$('#test').val();
    var length = $('.tests-selected li').length;
    $('.tests-selected li').each(function(index, el) {
      var tid = $(el).data('id');
      if (index === (length - 1)) {
        get_params = get_params + tid;
      } else {
        get_params = get_params + tid + '&test[]=';
      }
    });

    var protocol = window.location.protocol;
    var host = window.location.host;
    var path = window.location.pathname;
    path = protocol + '//' + host + path.substring(0,path.length-9) 
                    + get_params;
    $('#qr_link').val(path);
    $('.test-link').html("<a href='"+path+"' target='_blank'>Ссылка на тест</a>");
  });
  
  // Выводить все вопросы
  $('#all_q_count').on('click', function() {
    $('#q-quant').val($('#all_q_count').text());
    var test_id = $('#test option:selected').val();
    var q_quant = $('#q-quant').val();
    // AJAX-запрос
    $.ajax({
      url: "change_q_quant.php",
      method: 'post',
      data: {'tid'      : test_id,
             'quantity' : q_quant,
            }
    }).done(function(html){
      // Успешное получение ответа
      $(".info").html(html);
    });
  });


  // флажок ВКЛ/ВЫКЛ тест
  $('#test-status-checkbox').on('change', function() {
    var test_id = $('#test option:selected').val();
    if(this.checked) {
      $('.test-status span').removeClass('disabled');
      $('.test-status span').addClass('enabled');
      $('.test-status span').text('В работе');
      test_enabled = 1;
    } else {
      $('.test-status span').removeClass('enabled');
      $('.test-status span').addClass('disabled');
      $('.test-status span').text('Выключен');
      test_enabled = 0;
    }
    // AJAX-запрос
    $.ajax({
      url: "add_field.php",
      method: 'post',
      data: {'test_enabled' : test_enabled,
             'test_id'      : test_id,
            }
    }).done(function(html){
      // Успешное получение ответа
      $(".info").html(html);
    });
  });

  // меняем количество вопросов
  $('#q-quant').on('input',function(e){
    var test_id = $('#test option:selected').val();
    var q_quant = $(this).val();
    // AJAX-запрос
    $.ajax({
      url: "change_q_quant.php",
      method: 'post',
      data: {'tid'      : test_id,
             'quantity' : q_quant,
            }
    }).done(function(html){
      // Успешное получение ответа
      $(".info").html(html);
      $('#test').load('load_test_list.php', function(){
        $('#test').val(test_id).change();
      });
    });
  });

  // блок выборки вопросов в тест
  $('#complexity-check').on('change', function() {
    // отобразить/скрыть блок
    if(this.checked) {
      complexity = 1;
      $('.complexity-options').fadeIn();
    } else {
      complexity = 0;
      $('.complexity-options').fadeOut();
      $('.radio-2-select').fadeOut();
      $('.radio-1-form').fadeOut();
    }
  });

  // выбор варианта выборки вопросов
  $('.complexity-options input:radio').on('change', function() {
    if ($(this).val() == 1) {
      // задать кол-во вопросов в %
      $('.radio-1-form').fadeIn(0);
      $('.radio-2-select').fadeOut(0);
    } else {
      // простая выборка
      $('.radio-1-form').fadeOut(0);
      $('.radio-2-select').fadeIn(0);
    }
  });

  // простая выборка
  $('#simple_select').on('change', function() {
    $('.complexity-info').fadeOut().removeClass('disabled');
    var all_q = +$('#q-quant').val(); // выводить вопросов
    var all_s = +$('.simple_q').text(); // всего простых в базе
    var all_m = +$('.mid_q').text(); // всего средних в базе

    if($(this).val() == 1 && (all_s < all_q)) {
      $('.complexity-info').text('Не хватает простых вопросов в базе. Уменьшите общее количество вопросов, или измените уровень')
                           .addClass('disabled')
                           .fadeIn(500);
    } else if ($(this).val() == 2 && ((all_s + all_m) < all_q)) {
      $('.complexity-info').text('Не хватает простых и средних вопросов в базе. Уменьшите общее количество вопросов, или измените уровень')
                           .addClass('disabled')
                           .fadeIn(500);
    } else {
      
      var get_params = '?test[]=';
      var get_compl_params = '&level=' + $(this).val();
      var length = $('.tests-selected li').length;
      $('.tests-selected li').each(function(index, el) {
        var tid = $(el).data('id');
        if (index === (length - 1)) {
          get_params = get_params + tid;
        } else {
          get_params = get_params + tid + '&test[]=';
        }
      });

      var protocol = window.location.protocol;
      var host = window.location.host;
      var path = window.location.pathname;
      path = protocol + '//' + host + path.substring(0,path.length-9) 
                      + get_params + get_compl_params;
      $('#qr_link').val(path);
      $('.test-link').html("<a href='"+path+"' target='_blank'>Ссылка на тест</a>");
    }
  });

  // выборка по процентам
  var s, m, c;
  s = 0; // кол-во простых вопросов
  m = 0; // средних
  c = 0; // сложных
  $('#simple_input').on('input', function() {
    $('.complexity-info').fadeOut().removeClass('disabled');
    var all_q = $('#q-quant').val(); // выводить вопросов
    var all_s = $('.simple_q').text(); // всего простых в базе
    var s_pcnt = +$(this).val(); // % простых
    var m_pcnt, c_pcnt;
    s = Math.round((s_pcnt/100) * all_q); // выбрать в тест простых
    if (s > all_s) { // превышаем количество в базе
      s = all_s;
      s_pcnt = Math.round((s/all_q)*100);
      $(this).val(s_pcnt);
      $('.complexity-info').text('Не хватает простых вопросов в базе. Количество вопросов скорректировано')
                           .addClass('disabled')
                           .fadeIn(500);
    }
    m_pcnt = +$('#mid_input').val();
    c_pcnt = +$('#complex_input').val();

    if ( (s_pcnt + m_pcnt + c_pcnt) < 100 || (s_pcnt + m_pcnt + c_pcnt) > 100) {
      $('.complexity-info').text('Сумма полей не равна 100%. Скорректируйте поля!')
                           .addClass('disabled')
                           .fadeIn(500);
    } else {
      m = Math.round((m_pcnt/100) * all_q);
      c = Math.round((c_pcnt/100) * all_q);
      
      var get_params = '?test[]=';
      var get_compl_params = '&pt[]=' + s_pcnt +
                             '&pt[]=' + m_pcnt +
                             '&pt[]=' + c_pcnt;
      var length = $('.tests-selected li').length;
      $('.tests-selected li').each(function(index, el) {
        var tid = $(el).data('id');
        if (index === (length - 1)) {
          get_params = get_params + tid;
        } else {
          get_params = get_params + tid + '&test[]=';
        }
      });

      var protocol = window.location.protocol;
      var host = window.location.host;
      var path = window.location.pathname;
      path = protocol + '//' + host + path.substring(0,path.length-9) 
                      + get_params + get_compl_params;
      $('#qr_link').val(path);
      $('.test-link').html("<a href='"+path+"' target='_blank'>Ссылка на тест</a>");
      $('.complexity-info').text('Выбираем ' + s + ' простых, '
      + m + ' средней сложности и ' 
      + c + ' сложных вопроса')
                              .addClass('enabled')
                              .fadeIn(500);
    }
  });

  $('#mid_input').on('input', function() {
    $('.complexity-info').fadeOut().removeClass('disabled');
    var all_q = $('#q-quant').val(); // выводить вопросов
    var all_m = $('.mid_q').text(); // всего средних в базе
    var m_pcnt = +$(this).val(); // % средних
    var s_pcnt, c_pcnt;
    m = Math.round((m_pcnt/100) * all_q); // выбрать в тест простых
    if (m > all_m) { // превышаем количество в базе
      m = all_m;
      m_pcnt = Math.round((m/all_q)*100);
      $(this).val(m_pcnt);
      $('.complexity-info').text('Не хватает средних вопросов в базе. Количество вопросов скорректировано')
                           .addClass('disabled')
                           .fadeIn(500);
    }
    s_pcnt = +$('#simple_input').val();
    c_pcnt = +
    $('#complex_input').val();
    if ( (s_pcnt + m_pcnt + c_pcnt) < 100 || (s_pcnt + m_pcnt + c_pcnt) > 100) {
      $('.complexity-info').text('Сумма полей не равна 100%. Скорректируйте поля!')
                           .addClass('disabled')
                           .fadeIn(500);
    } else {
      s = Math.round((s_pcnt/100) * all_q);
      c = Math.round((c_pcnt/100) * all_q);
     
      var get_params = '?test[]=';
      var get_compl_params = '&pt[]=' + s_pcnt +
                             '&pt[]=' + m_pcnt +
                             '&pt[]=' + c_pcnt;
      var length = $('.tests-selected li').length;
      $('.tests-selected li').each(function(index, el) {
        var tid = $(el).data('id');
        if (index === (length - 1)) {
          get_params = get_params + tid;
        } else {
          get_params = get_params + tid + '&test[]=';
        }
      });

      var protocol = window.location.protocol;
      var host = window.location.host;
      var path = window.location.pathname;
      path = protocol + '//' + host + path.substring(0,path.length-9) 
                      + get_params + get_compl_params;
      $('#qr_link').val(path);
      $('.test-link').html("<a href='"+path+"' target='_blank'>Ссылка на тест</a>");
      $('.complexity-info').text('Выбираем ' + s + ' простых, '
      + m + ' средней сложности и ' 
      + c + ' сложных вопроса')
                              .addClass('enabled')
                              .fadeIn(500);    
    }
  });

  $('#complex_input').on('input', function() {
    $('.complexity-info').fadeOut().removeClass('disabled');
    var all_q = $('#q-quant').val(); // выводить вопросов
    var all_c = $('.complex_q').text(); // всего сложных в базе
    var c_pcnt = +$(this).val(); // % сложных
    var s_pcnt, m_pcnt;
    c = Math.round((c_pcnt/100) * all_q); // выбрать в тест простых
    if (c > all_c) { // превышаем количество в базе
      c = all_c;
      c_pcnt = Math.round((c/all_q)*100);
      $(this).val(c_pcnt);
      $('.complexity-info').text('Не хватает сложных вопросов в базе. Количество вопросов скорректировано')
                           .addClass('disabled')
                           .fadeIn(500);
    }
    s_pcnt = +$('#simple_input').val();
    m_pcnt = +$('#mid_input').val();
    if ( (s_pcnt + m_pcnt + c_pcnt) < 100 || (s_pcnt + m_pcnt + c_pcnt) > 100) {
      $('.complexity-info').text('Сумма полей не равна 100%. Скорректируйте поля!')
                           .addClass('disabled')
                           .fadeIn(500);
    } else {
      s = Math.round((s_pcnt/100) * all_q);
      m = Math.round((m_pcnt/100) * all_q);
      
      var get_params = '?test[]=';
      var get_compl_params = '&pt[]=' + s_pcnt +
                             '&pt[]=' + m_pcnt +
                             '&pt[]=' + c_pcnt;
      var length = $('.tests-selected li').length;
      $('.tests-selected li').each(function(index, el) {
        var tid = $(el).data('id');
        if (index === (length - 1)) {
          get_params = get_params + tid;
        } else {
          get_params = get_params + tid + '&test[]=';
        }
      });

      var protocol = window.location.protocol;
      var host = window.location.host;
      var path = window.location.pathname;
      path = protocol + '//' + host + path.substring(0,path.length-9) 
                      + get_params + get_compl_params;
      $('#qr_link').val(path);
      $('.test-link').html("<a href='"+path+"' target='_blank'>Ссылка на тест</a>");
      $('.complexity-info').text('Выбираем ' + s + ' простых, '
      + m + ' средней сложности и ' 
      + c + ' сложных вопроса')
                              .addClass('enabled')
                              .fadeIn(500);
    }
  });



  // кнопка удаления теста
  $('#rem-btn-t').on('click', function() {
    if (confirm('Вы действительно хотите удалить тест?')) {
      remove_test = 1;
      $('#submit-id').css('display', 'block');
      $('#submit-id').html('Удалить тест');
    }
  });

  // выбираем вопрос из выпадающего списка
  $('#q').on('change', function() {
    $('.edit-q').hide();
    $('.info').text('');
    //$('.question').text('Редактировать вопрос');
    
    // AJAX-запрос
    $.ajax({
      url: 'select_ans.php?id=' + $('#q').val()
    })
    .done(function(data){
      $('.answers').fadeIn();
      $('.answers').html(data);
      $('.add-answer').show();
      qid = $('#q option:selected').val();
      var radioVal = $('.answers input:radio:checked').val()
      $('.ans-wrapper[data-id="'+radioVal+'"').addClass('ok');
      var old_correct_id = radioVal;
       // переключение правильного варианта ответа
      $('.answers input:radio').on('change', function() {
        $('#submit-id').css('display', 'block');
        $('.answers input:radio').each(function(index, el) {
          radioVal = this.value;
          if ($(el).is(":checked")) {
            $('.ans-wrapper[data-id="'+radioVal+'"').addClass("ok");
          } else {
            $('.ans-wrapper[data-id="'+radioVal+'"').removeClass("ok");
          }
        });
        var new_correct_id = this.value;
        //AJAX-запрос
        $.ajax({
          url: "change_correct.php",
          method: 'post',
          data: {'qid'      : qid,
                'old_id'    : old_correct_id,
                'new_id'    : new_correct_id
                }
        }).done(function(html){
          // Успешное получение ответа
          $(".info").html(html);
          old_correct_id = $('.answers input:radio:checked').val()
        });
      });
      // отобразить рамку для вывода вопроса
      if($('#q option:selected').val() == 0) {
        $('.add-question').fadeIn();
        // скрыть рамку для вывода вопроса
        $('.q-wrapper').hide();
      } else {
        $('.add-question').fadeOut();
        $('.q-wrapper').show();
      }
      if ($('#translate_math').is(":checked")) {
        //console.log('checked');
        MathJax.typeset();
      }
      if($('#q option:selected').val() == 0) {
        //('Добавить/удалить вопрос');
        $('.answers').hide();
        update_question = 0;
      }
        // меняем уровень сложности вопроса
        $('#q-level').on('change', function() {
          qid = $('#q option:selected').val();
          level = $(this).val();
          if (level == 0) level = 1;
          // AJAX-запрос
          $.ajax({
            url: "change_q_level.php",
            method: 'post',
            data: { 'qid' : qid,
                    'level': level
                  }
          }).done(function(html){
            // Успешное получение ответа
            $(".info").html(html);
          });
        });
    });
  });

  // Кнопка "Добавить вопрос"
  $('.question').on('click', '.add-question', function() {
    $('#submit-id').css('display', 'block');
    $('.q-wrapper').hide();
    $(".edit-q").css("border", "1px dotted green");
    $('.edit-q').fadeIn();
    $('#q-content').val('');
    $('#q-level-add-q').val('1');
    $('.answers').fadeIn();
    $('.ans-wrapper').remove();
    $('#submit-id').html('Добавить вопрос');
    update_question = 0;
    $('.add-question').css('display', 'none');
    $('.add-answer').css('display', 'block');
  });

  // Редактировать вопрос
  $('.answers').on('click', '.fa-edit', function() {
    $('#submit-id').css('display', 'block');
    $('.q-wrapper').hide();
    $('.add-answer').show();
    $(".edit-q").css("border", "1px dotted green");
    $('.edit-q').fadeIn();
    //$('#q-content').val($('#q option:selected').text());
    $('#q-content').val($('.q-wrapper > p').text().trim());
    $('.answers').fadeIn();
    $('.ans-wrapper').remove();
    $('#submit-id').html('Редактировать вопрос');
    if($('#q option:selected').val() == 0) {
      update_question = 0;
    } else {
      update_question = 1;
    }
  });


  // кликаем по варианту ответа - появляется возможность редактирования
  $('.answers').on('click', '.fa-pencil-square-o',function(){
    var ans_id = $(this).siblings().data('id');
    $('#submit-id').css('display', 'block');
    $('#ans-'+ans_id+'-wrapper.ans-wrapper').css("border", "1px dotted green");
    $('#a-'+ans_id).val($(this).siblings('li').text());
    $('#a-'+ans_id).fadeIn();
    $('#submit-id').html('Добавить вариант ответа');
  });

  // Удалить картинку варианта
  $('.answers').on('click', '.fa-remove', function() {
   $('#submit-id').css('display', 'block');
   var ans_id = $(this).parent().parent().data('id');
   $(this).siblings('img').fadeOut();
   $(this).fadeOut();
   $('#submit-id').html('Удалить изображение');
   //console.log(ans_id);
});

  // Удалить картинку вопроса
  $('.answers').on('click', '.fa-close', function(){
    remove_q_img = 1;
    $('#submit-id').css('display', 'block');
    var q_id = $('#q option:selected').val();
    $(this).siblings('img').fadeOut();
    $(this).fadeOut();
    $('#submit-id').html('Удалить изображение');
    //console.log(q_id);
  });

  // Удалить вариант ответа
  $('.answers').on('click', '.remove-answer',function(){
    var ans_id = $(this).siblings().data('id');
    $('#submit-id').val('Удалить вариант ответа');
    $('#ans-'+ ans_id +'-wrapper').fadeOut();
    $('#submit-id').css('display', 'block');
    $('#submit-id').html('Удалить вариант ответа');
    //console.log('close' + ans_id);
  });
  

  // кнопка добавления варианта
  $('.answers').on('click', '.add-answer', function() { //$('.btn-add').on('click', function() {   $('.answers').on('click', '.btn-add', function() {
    $('#submit-id').css('display', 'block');
    $('#submit-id').html('Добавить вариант ответа');
    num++;
    qid = $('#q option:selected').val();
    var block = "<div class=\"ans-wrapper\" data-id=\""+num+"\" id=ans-"+num+"-wrapper>";
    block += "<p><textarea rows='5' data-id="+num+" id=\"a-"+num+"\" class='edit-a visible' type='text'></textarea></p>\n";
    block += "<input type=\"radio\" name=\"q-"+qid+"\" value=\""+num+"\">\n";
    block += "<input type=\"file\" name=\"filename[]\" class=\"btn btn-img\" data-id=\""+num+"\" id=\"img-btn-"+num+"\">";
    block += "<button class=\"btn btn-rem\" data-id=\""+num+"\">Удалить вариант</button>\n";
    block += "</div>";
    if(qid == 0 || !($('.ans-wrapper').is(":visible"))) {
      $(block).appendTo('.answers ul');
    } else {
      $(block).insertAfter('.ans-wrapper:last');
    }
 });

 

  // кнопка удаления вопроса
  $('.answers').on('click', '.remove-question', function() {
    update_question = 0;
    $('#q-content').data('remove', '1');
    $('.edit-q').fadeOut();
    $('.add-answer').hide();
    $('.q-wrapper').hide();
    $('.answers').fadeIn();
    $('.ans-wrapper').remove();

    $('#submit-id').css('display', 'block');
    $('#submit-id').html('Удалить вопрос');
  });

  // переключение режима MathJax
  $('#translate_math').on('change', function() {
    // AJAX-запрос
    $.ajax({
      url: 'select_ans.php?id=' + $('#q').val()
    })
    .done(function(data){
      $('.answers').fadeIn();
      $('.answers').html(data);
      qid = $('#q option:selected').val();
      var radioVal = $('.answers input:radio:checked').val()
      $('.ans-wrapper[data-id="'+radioVal+'"').addClass('ok');
      var old_correct_id = radioVal;
       // переключение правильного варианта ответа
      $('.answers input:radio').on('change', function() {
        $('#submit-id').css('display', 'block');
        $('.answers input:radio').each(function(index, el) {
          radioVal = this.value;
          if ($(el).is(":checked")) {
            $('.ans-wrapper[data-id="'+radioVal+'"').addClass("ok");
          } else {
            $('.ans-wrapper[data-id="'+radioVal+'"').removeClass("ok");
          }
        });
        var new_correct_id = this.value;
        //AJAX-запрос
        $.ajax({
          url: "change_correct.php",
          method: 'post',
          data: {'qid'      : qid,
                'old_id'    : old_correct_id,
                'new_id'    : new_correct_id
                }
        }).done(function(html){
          // Успешное получение ответа
          $(".info").html(html);
          old_correct_id = $('.answers input:radio:checked').val()
        });
      });
      // отобразить рамку для вывода вопроса
      if($('#q option:selected').val() == 0) {
        // скрыть рамку для вывода вопроса
        $('.q-wrapper').hide();
      } else {
        $('.q-wrapper').show();
      }
      if ($('#translate_math').is(":checked")) {
        //console.log('checked');
        MathJax.typeset();
      }
      if($('#q option:selected').val() == 0) {
        //$('.question').text('Добавить/удалить вопрос');
        $('.answers').hide();
        update_question = 0;
      }
    });
  });

  // отправка всех данных на сервер для редактирования БД
  $("#submit-id").on("click", function() {
    //console.log('clicked...');
    var test_id = $('#test option:selected').val();
    var new_test_name = '';
    var question = '';
    var q_id = $('#q option:selected').val();
    var correct_id = $('input[name="q-' + q_id + '"'+']:checked').val();
    var ans = {};
    var rem_ans = {};
    var img = {};
    var rem_img = {};
    var rem_q_img_id = 0;
    var remove_q = 0;
    var q_level = $('#q-level-add-q').val();

  $('#q-content').is(":visible") ? question = $('#q-content').val() : '';
  // 1 - удалить вопрос
  $('#q-content').data('remove') == '1' ? remove_q = 1 : remove_q = 0;

  if ($('#test-name').is(":visible")) {
    new_test_name = $('#test-name').val();
  }
  // если видно поле возле ответа - значит он отредактирован
  $('.edit-a').each(function() {
    if ($(this).is(":visible")) {
      var ans_id = $(this).data('id');
      ans[ans_id] = $(this).val();
    }
  });
  
  // скрыта картинка - удаляем
  $('.ans-wrapper').each(function() {
    if($(this).find('img').is(":hidden")) {
      var img_id = $(this).data('id');
      rem_img[img_id] = img_id;
    }
  });
  
  // скрыта картинка вопроса? Удаляем
  if($('.q-wrapper').find('img').is(":hidden") && remove_q_img) {
    rem_q_img_id = q_id;
    remove_q_img = 0;
  }

   // если ответ скрыт - значит его нужно удалить
  $('.ans-wrapper').each(function() {
    if ($(this).is(":hidden") && $(this).data('id') < 10000) {
      var ans_id = $(this).data('id');
      rem_ans[ans_id] = ans_id;
    } else {
      //
    }
  });
    
  // кнопка добавления картинки к варианту ответа 
  $('.btn-img').each(function() {
    var img_id = $(this).data('id');
    // добавить изображение в массив, если оно есть
    if ($(this).prop('files').length > 0) {
      img[img_id] = $(this).prop('files')[0];
    }
  });

  // Блокируемотправки кнопку 
  //$("#submit-id").prop("disabled", true);
  // Создаем новый объект формы
  var form_data = new FormData();
  // Формируем массив FILES
  if (!remove_q) {
    for (var key in img) {
      form_data.append("file["+key+"]", img[key])
    }
  }

  // добавляем остальные данные в POST
  form_data.append("test_id", test_id);
  form_data.append("new_test_name", new_test_name);
  form_data.append("q_id", q_id);
  form_data.append("question", question);
  form_data.append("correct_answer", correct_id);
  form_data.append("remove_q", remove_q);
  form_data.append("update_q", update_question);
  form_data.append("remove_test", remove_test);
  for (var key in ans) {
    form_data.append("ans["+key+"]", ans[key])
  }
  for (var key in rem_ans) {
    form_data.append("rem_ans["+key+"]", rem_ans[key])
  }
  for (var key in rem_img) {
    form_data.append("rem_img["+key+"]", rem_img[key])
  }
  form_data.append("rem_q_img_id", rem_q_img_id);
  form_data.append("level", q_level);
  // AJAX-запрос
  $.ajax({
    url: "add_field.php",
    dataType: 'script',
    cache: false,
    contentType: false,
    processData: false,
    processData : false,
    data: form_data,
    type: 'post',
    success: function(html) {
      // Успешное получение ответа
      $(".info").html(html);
      $('.info').addClass('success');
      $('.edit-t').fadeOut();
      $('.edit-q').fadeOut();
      $('.question').show();
      $("#submit-id").prop("disabled", false);
      // текст кнопки отправки запроса по умолчанию
      $('#submit-id').html('Добавить в БД');
      $('#q-content').data('remove', '0');
      $('.btn-img').each(function() { $(this).val(''); });
      remove_test = 0;
      update_question = 0;
      rem_q_img_id = 0;
          // обновим список вопросов
          // AJAX-запрос
          $.ajax({
            url: 'select_test.php?id=' + $('#test').val()
          })
          .done(function(data){
            $('#q').html(data);
            $('#all_q_count').text($('#q option').length - 1);
          });
      // заполнить список тестов динамически
      // AJAX-запрос
      $.ajax({
        url: 'load_test_list.php'
      })
      .done(function(data){
        $('#test').html(data);
        $('#test').val(test_list_selected);
        $('#q').val(q_id);
        // AJAX-запрос
        $.ajax({
          url: 'select_ans.php?id=' + $('#q').val()
        })
        .done(function(data){
          if($('#q option:selected').val() == 0) {
            $('.add-question').fadeIn();
            $('.answers').hide();
            // скрыть рамку для вывода вопроса
            $('.q-wrapper').hide();
          } else {
            $('.add-question').fadeOut();
            $('.q-wrapper').show();
            $('.answers').fadeIn();
          }
          $('.answers').html(data);
          $('.add-answer').show();
          qid = $('#q option:selected').val();
          var radioVal = $('.answers input:radio:checked').val()
          $('.ans-wrapper[data-id="'+radioVal+'"').addClass('ok');
          var old_correct_id = radioVal;
          // переключение правильного варианта ответа
          $('.answers input:radio').on('change', function() {
            $('#submit-id').css('display', 'block');
            $('.answers input:radio').each(function(index, el) {
              radioVal = this.value;
              if ($(el).is(":checked")) {
                $('.ans-wrapper[data-id="'+radioVal+'"').addClass("ok");
              } else {
                $('.ans-wrapper[data-id="'+radioVal+'"').removeClass("ok");
              }
            });
            var new_correct_id = this.value;
            //AJAX-запрос
            $.ajax({
              url: "change_correct.php",
              method: 'post',
              data: {'qid'      : qid,
                    'old_id'    : old_correct_id,
                    'new_id'    : new_correct_id
                    }
            }).done(function(html){
              // Успешное получение ответа
              $(".info").html(html);
              old_correct_id = $('.answers input:radio:checked').val()
            });
          });
          // отобразить рамку для вывода вопроса
          if($('#q option:selected').val() == 0) {
            // скрыть рамку для вывода вопроса
            $('.q-wrapper').hide();
          } else {
            $('.q-wrapper').show();
          }
          if ($('#translate_math').is(":checked")) {
            //console.log('checked');
            MathJax.typeset();
          }
          if($('#q option:selected').val() == 0) {
            //$('.question').text('Добавить/удалить вопрос');
            $('.answers').hide();
            update_question = 0;
          }
          $('#q').val(q_id);
          $('.answers').fadeIn();
          $('.q-wrapper').css('display', 'block');
          
          if($('#q option:selected').val() == 0) {
            $('.add-question').fadeIn();
            $('.answers').hide();
            // скрыть рамку для вывода вопроса
            $('.q-wrapper').hide();
          } else {
            $('.add-question').fadeOut();
            $('.q-wrapper').show();
          }
        });
      });
    }
  });
});


});

$body = $("body");

$(document).on({
    ajaxStart: function() { 
      $body.addClass("loading");    
    },
    ajaxStop: function() { 
      $body.removeClass("loading"); 
    }    
});