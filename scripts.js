$body = $("body");
$(function() {
  $('#select_test').on('change', function() {
    tid = $('#select_test option:selected').val();
    window.location = location.pathname + '?test=' + tid;
    //$('.testname').text($('#select_test option:selected').text());
  });

  var idle = $('#idle_time_val').val();
  // скрываем кнопку завершения теста
  //console.log('idle ' + idle);
  //console.log('Since term ' + msToTime(Date.now() - $.cookie('terminated')));
  var time_passed = (msToTime(Date.now() - $.cookie('terminated'))).split(":").map(Number);
  //$.removeCookie('timer');
  //console.log('Since term ' + time_passed[1] + " mins " + time_passed[2] + " sec");
  if((idle - time_passed[1]) > 0) {
    //$('.content').hide();
    $('.countdown').text('До новой попытки вам нужно подождать еще ' + ((idle-1) - time_passed[1]) + " мин. " + (60.0-time_passed[2]).toFixed(1) + " сек.");
    //console.log('До новой попытки вам нужно подождать еще ' + ((idle-1) - time_passed[1]) + " мин. " + (60.0-time_passed[2]).toFixed(1) + " сек.");
    $body.addClass("loading");
    // сериализованный массив ключей вопросов
    var keys = $('#keys').val();
    var test = $('#test-id').val();
    var res = {'test':test, 'keys': keys};
    $('.question').each(function() {
      var id = $(this).data('id');
      res[id] = $('input[name=question-'+ id +']:checked').val();
    });
    $.ajax({
      type: "POST",
      url: "index.php",
      data: res,
      success: function(html) {
        //console.log(html);
        $('.content').html(html);
      },
      error: function() {
        alert('Error!');
      }
    });
  } else if ($.cookie('status') == 0) {
    $.removeCookie('timer');
    $.removeCookie('terminated');
    $.removeCookie('status');
    $('.content').show();
    $body.removeClass("loading");
  }
  //console.log($.cookie('terminated'));
  $('.answers').hide();
  // флаг включенного теста
  var test_enabled = 0;
  // флаг удаления текущего теста
  var remove_test = 0;
  // отключить список выбора вопроса, если не выбран тест
  if ($('#test option:selected').val() == 0) {
    $('#q').prop('disabled', true);
  }
  
  $('.test-data').find('div:first').show();
  
  // Отобразить номер текущего вопроса сначала
  $('#curr_q').html($('.pagination > ul > li > a.nav-active').text());
  
  $('.pagination ul > li > a').on('click', function () {
    // Отобразить номер текущего вопроса
    $('#curr_q').html($(this).text());

    if ($(this).attr('class') == 'nav-active') return false;
    
    var link = $(this).attr('href'); // ссылка на запрошенную вкладку
    var prevActive = $('.pagination > ul > li > a.nav-active').attr('href'); // ссылка на текст активной вкладки
    
    $('.pagination > ul > li > a.nav-active').removeClass('nav-active').addClass('visited'); // удалить класс активной ссылки
    $(this).addClass('nav-active'); // добавить класс активной вкладке

    // скрываем/показываем вопросы
    $(prevActive).fadeOut(100, function() {
      $(link).fadeIn();
    });
    return false;
  });

  $('i.nav-prev').on('click', function () {
    var link;
    var prevActive;
    //console.log('prev');
    $('i.nav-next').removeClass('nav-disabled');
    $('.pagination li').each(function() {
      if ($(this).children().hasClass('nav-active')) {
        prevActive = $(this).children().attr('href');
        //console.log(prevActive);
        link = $(this).prev().children().attr('href');
        //console.log('link '+link);
        if (link !== undefined) {
          $('i.nav-prev').removeClass('nav-disabled');
          $(this).children().removeClass('nav-active');
          $(this).children().addClass('visited');
          $(this).prev().children().addClass('nav-active');
          // Отобразить номер текущего вопроса
          $('#curr_q').html($(this).prev().children().text());
        } else {
          $('i.nav-prev').addClass('nav-disabled');
        }
      }
    });
    if (link !== undefined) {
      // скрываем/показываем вопросы
      $(prevActive).fadeOut(0, function() {
        $(link).fadeIn(50);
      });
    }
  });

  $('i.nav-next').on('click', function () {
    //console.log('next');
    var link;
    var prevActive;

    $('i.nav-prev').removeClass('nav-disabled');

    $($('.pagination li').get().reverse()).each(function() {
      if ($(this).children().hasClass('nav-active')) {
        //console.log('active!');
        prevActive = $(this).children().attr('href');
        //console.log(prevActive);
        link = $(this).next().children().attr('href');
        //console.log('link '+link);
        if (link !== undefined) {
          $(this).children().removeClass('nav-active');
          $(this).children().addClass('visited');
          $(this).next().children().addClass('nav-active');
          $('i.nav-next').removeClass('nav-disabled');
          // Отобразить номер текущего вопроса
          $('#curr_q').html($(this).next().children().text());
        } else {
          $('i.nav-next').addClass('nav-disabled');
        }
      }
    });
    if (link != undefined) {
      // скрываем/показываем вопросы
      $(prevActive).fadeOut(0, function() {
        $(link).fadeIn(50);
      });
    }
  });

  // собрать значения отмеченных переключателей
  checked = {};
  // запоминаем состояние переключателей
  $('input:radio, input:text, label').on('click', function() {
    $('.pagination > ul > li > a.nav-active').addClass('answered');
    $('.question').each(function() {
      var id = $(this).data('id');
      if($('input[name=question-'+ id +']').is(':radio')) {
        checked[id] = $('input[name=question-'+ id +']:checked').attr('id');
      } else {
        checked[$('input[name=question-'+ id +']').attr('id')] = $('input[name=question-'+ id +']').val();
      }
    });
    answered = 0;
    questions = Object.keys(checked).length;

    for (k in checked) {
      if ((checked[k] !== undefined) && (checked[k] !== '')) {
        //console.log(checked[k]);
        answered++;
      }
    }
    /*if (answered == questions)*/ $('#btn').removeClass('none');
    $.post(
      'check.php',
      { checked }
    );
  });

  
  // кнопка "Завершить тест"
  $('#btn').click(function() {
    if (confirm('Вы действительно хотите завершить тест?')) {
      // Get the visitor identifier when you need it.
      //console.log('ID in button FP: ' + $('#visitorId').val());
      var time_left = $('.countdown').text();
      if($.trim($("#student_name").val()) === "")
      {
        alert('Пожалуйста заполните поле "Фамилия"');
        return false;
      }
      if($.trim($("#student_group").val()) === "")
      {
        alert('Пожалуйста заполните поле "Группа"');
        return false;
      }
      $.cookie('status', 0, { expires: 1 }); // флаг окончания теста
      $.cookie('terminated', Date.now(), { expires: 1 }); // время окончания
      // сериализованный массив ключей вопросов
      var keys = $('#keys').val();
      var test = $('#test-id').val();
      var sirname = $('#student_name').val();
      var fp = $('#visitorId').val();
      //console.log('ID in button FP: ' + fp);
      var st_group = $('#student_group').val();
      var st_group_replaced = st_group.replace(/\s+/gi, '');
      st_group_replaced = st_group_replaced.replace(/([а-яА-ЯёЁ]{2,3})-*?(\d{2})-*?(\d{1})?/i, '$1-$2-$3');
      st_group_replaced = st_group_replaced.replace(/--/gi, '-');
      st_group_replaced = st_group_replaced.toUpperCase().trim();
      if (st_group_replaced[st_group_replaced.length - 1] == '-') {
        st_group_replaced = st_group_replaced.slice(0, st_group_replaced.length - 1);
      }
      if(typeof($.cookie('sirname')) == 'undefined') {
        $.cookie('sirname', sirname, { expires: 1 });
      }
      if(typeof($.cookie('st_group')) == 'undefined') {
        $.cookie('st_group', st_group, { expires: 1 });
      }
      var ans = {};
      $('.question').each(function() {
        var id = $(this).data('id');
        if($('input[name=question-'+ id +']').is(':radio')) {
          ans[id] = $('input[name=question-'+ id +']:checked').val();
        } else {
          ans[id] = $('input[name=question-'+ id +']').val();
        }
        //res[id] = $('input[name=question-'+ id +']:checked').val();
      });
      var res = {
        'test': test, 
        'answers' : ans,
        'keys': keys, 
        'sirname': sirname, 
        'st_group': st_group_replaced, 
        'time': time_left,
        'fp' : fp,
      };
      $.ajax({
        type: "POST",
        url: "index.php",
        data: res,
        success: function(html) {
          //console.log(html);
          $('.content').html(html);
          MathJax.typeset();
        },
        error: function() {
          alert('Error!');
        }
      });
      $.removeCookie('timer');
    }
  });

  $("#student_name").on('change', function() {
    $.removeCookie('sirname');
    $.cookie('sirname', $("#student_name").val(), { expires: 1 });
  });

  $("#student_name").on('blur', function() {
    if($.trim($("#student_name").val()) === "")
      {
        alert('Пожалуйста заполните поле "Фамилия"');
        return false;
      } else {
          // Валидация имени
          var st_name = $(this).val();
          var re = /([А-Яа-я]{2,}\s*){1,3}/iu;
          var found = st_name.match(re);
          if(found) {
            $('.validity-info').addClass('green');
            $('.validity-info').text('Верный формат имени ('+found[0]+')');
            $('.validity-info').fadeOut(1000);
            $(this).fadeOut(1000);
          } else {
            $('.validity-info').fadeIn();
            $('.validity-info').addClass('red');
            $('.validity-info').text('Введите Имя или ФИО');
          }
      }
  });

  $("#student_group").on('change', function() {
    $.removeCookie('st_group');
    $.cookie('st_group', $("#student_group").val(), { expires: 1 });
  });

  $("#student_group").on('blur', function() {
    if($.trim($("#student_group").val()) === "")
      {
        alert('Пожалуйста заполните поле "Группа"');
        return false;
      } else {
        // Валидация группы
          var st_group = $(this).val();
          st_group = st_group.replace(/\s+/gi, '');
          st_group = st_group.replace(/-+/gi, '-');
          st_group = st_group.toUpperCase().trim();

          var re = /[А-Яа-я]{2,3}-\d{2}(-\d)?/iu;
          var found = st_group.match(re);

          if(found) {
            $('.validity-info').addClass('green');
            $('.validity-info').text('Верный формат группы ('+found[0]+')');
            
            $('.validity-info').fadeOut(1000);
            $("#student_group").val(found[0].toUpperCase());
            $(this).fadeOut(1000);
          } else {
            $('.validity-info').fadeIn();
            $('.validity-info').addClass('red');
            $('.validity-info').text('Формат ГК-13-1, СПС-20 и т.п.');
          }
    }
  });



  // заполнить поля ФИО/группа, если есть в куках
  if($.trim($("#student_name").val()) === '' && typeof($.cookie('sirname')) != 'undefined') {
    $("#student_name").val($.cookie('sirname'));
  }
  if($.trim($("#student_group").val()) === '' && typeof($.cookie('st_group')) != 'undefined') {
    $("#student_group").val($.cookie('st_group'));
  }
  
// таймер
var initial_time = $('#timer_val').val();
//console.log("timer before "+$.cookie('timer'));
//console.log("status "+$.cookie('status'));
if (typeof($.cookie('timer')) == 'undefined' && $.cookie('status') == 1) {
  timer2 = initial_time;
  $body.removeClass("loading");
  //console.log(timer2);
  $.cookie('timer', timer2, { expires: 1 });
  //console.log('ini');
  //console.log("timer after "+$.cookie('timer'));
} else if($.cookie('status') == 1) {
   // console.log('from cook');
  timer2 = $.cookie('timer');
}

if (typeof($.cookie('timer')) == 'undefined' && $.cookie('status') == 0) {
  clearInterval(interval);
  $.cookie('status', 1, { expires: 1 });
  timer2 = initial_time;
  $.cookie('timer', timer2, { expires: 1 });
}

if (typeof($.cookie('status')) == 'undefined') {
  clearInterval(interval);
  $.cookie('status', 1, { expires: 1 });
  timer2 = initial_time;
}


//$.removeCookie('status');
//console.log("timer "+$.cookie('timer'));
//console.log("status "+$.cookie('status'));
//console.log("terminated "+$.cookie('terminated'));

//$.removeCookie('timer');
//$.removeCookie('terminated');
//$.removeCookie('status');


//var test = +$('#test-id').text();
var test_off = +$('#test-off').text();
// выводим котика, если тест выключен
if (test_off == 1 || $.cookie('status') == 0) {
  $body.addClass("loading");
} else {
  $body.removeClass("loading");
}

if ($.cookie('status') == 1) {
  if (test_off == 0) {
    var interval = setInterval(function() {

      // if ($.cookie('status') == 0) {
      //   clearInterval(interval);
      //   $.removeCookie('timer');

      // }

      var timer = timer2.split(':');
      //by parsing integer, I avoid all extra string processing
      var minutes = parseInt(timer[0], 10);
      var seconds = parseInt(timer[1], 10);
      --seconds;
      minutes = (seconds < 0) ? --minutes : minutes;
      // красный шрифт и мерцание на последней минуте
      if (minutes >= 0 && minutes < 1) {
        $('.countdown').removeClass('green').addClass('red');
        $('.countdown').fadeOut(500);
        $('.countdown').fadeIn(500);
      }

      // время вышло
      if (minutes < 0) {
        $.cookie('status', 0, { expires: 1 });
        $.cookie('terminated', Date.now(), { expires: 1 });
        //$.removeCookie('timer');
        clearInterval(interval);

        // сериализованный массив ключей вопросов
        time_left = 0;
        var keys = $('#keys').val();
        var test = $('#test-id').val();
        var sirname = $('#student_name').val();
        var fp = $('#visitorId').val();
        var st_group = $('#student_group').val();
        var st_group_replaced = st_group.replace(/\s+/gi, '');
        st_group_replaced = st_group_replaced.replace(/([а-яА-ЯёЁ]{2,3})-*?(\d{2})-*?(\d{1})?/i, '$1-$2-$3');
        st_group_replaced = st_group_replaced.replace(/--/gi, '-');
        st_group_replaced = st_group_replaced.toUpperCase().trim();
        if (st_group_replaced[st_group_replaced.length - 1] == '-') {
          st_group_replaced = st_group_replaced.slice(0, st_group_replaced.length - 1);
        }
        if(sirname == '' && typeof($.cookie('sirname')) != 'undefined') {
          sirname = $.cookie('sirname');
        }
        if(st_group == '' && typeof($.cookie('st_group')) != 'undefined') {
          st_group = $.cookie('st_group');
        }
        //console.log(st_group_replaced);
        var res = {
                    'test': test, 
                    'keys': keys, 
                    'sirname': sirname, 
                    'st_group': st_group_replaced, 
                    'time': time_left,
                    'fp' : fp
                  };
        
        $('.question').each(function() {
          var id = $(this).data('id');
          res[id] = $('input[name=question-'+ id +']:checked').val();
        });
        $.ajax({
          type: "POST",
          url: "index.php",
          data: res,
          success: function(html) {
            $('.content').html(html);
          },
          error: function() {
            alert('Error!');
          }
        });
      }
      seconds = (seconds < 0) ? 59 : seconds;
      seconds = (seconds < 10) ? '0' + seconds : seconds;
      //minutes = (minutes < 10) ?  minutes : minutes;
      if ($.cookie('status') == 1) {
        $('.countdown').html('Осталось времени: ' + minutes + ':' + seconds);
      }
      timer2 = minutes + ':' + seconds;
      if (minutes >= 0) $.cookie('timer', timer2, { expires: 1 });

      if(minutes < 0) {
        $('.countdown').text('Ваше время истекло!');
        //timer2 = initial_time;
        //$.removeCookie('timer');
      }
    }, 1000);
  }
}
    // Get the visitor identifier when you need it.
    fpPromise
    .then(fp => fp.get({extendedResult: true}))
    .then(result => {
      // This is the visitor identifier:
      const visitorId = result.visitorId
      const isIncognito = result.incognito
      $('#visitorId').val(visitorId);
      console.log('Incognito: ' + isIncognito);
      if (isIncognito) $body.addClass("loading");
    })
});

function msToTime(s) {
  var ms = s % 1000;
  s = (s - ms) / 1000;
  var secs = s % 60;
  s = (s - secs) / 60;
  var mins = s % 60;
  var hrs = (s - mins) / 60;

  return hrs + ':' + mins + ':' + secs + '.' + ms;
  //return mins;
}

