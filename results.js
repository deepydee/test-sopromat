$(function() {
  
  $('#select_date').on('change', function() {
    //date = $('#select_date option:selected').val();
    //console.log(date);
    $.ajax({
      type: "POST",
      url: "get_result_table_by_date.php",
      data: {
              'date' : $('#select_date option:selected').val()
            },
      success: function(html) {
        //console.log(html);
        $('.result-table').html(html);
        $('#resTable').tablesorter();

        $('.table-row').each(function() {
          if($(this).data('id') > 50) {
            $(this).addClass('ok');
          } else {
            $(this).addClass('error');
          }
        });

        $('.sirname').on('click', function() {
          //console.log($(this).data('id'));
          //console.log($(this).data('tid'));
          var res = {'uid': $(this).data('id'), 'tid': $(this).data('tid')};
          $.ajax({
            type: "POST",
            url: "get_test_result.php",
            data: res,
            success: function(html) {
              $('.data').html(html);
              MathJax.typeset();
            },
            error: function() {
              alert('Error!');
            }
          });
        });

        $('.tid').on('click', function() {
          console.log($(this).attr('title'));
          $('.res_header').html($(this).attr('title'));
        });

        $('#remove_all_results').on('change', function() {
          $('.remove_results').each(function() {
            if(this.checked) {
              $(this).prop('checked', false);
              $('#submit-id').prop('disabled', true);
            } else {
              $(this).prop('checked', true);
              $('#submit-id').prop('disabled', false);
            }
          });
        });
      
        $('.remove_results').on('change', function() {
          $('#submit-id').css('display', 'block');
          $('#submit-id').prop('disabled', false);
        });

        $('.group').on('click', function() {
          var gr = $(this).data('group');
          //console.log($(this).data('group'));
          
          $('.group').each(function() {
            if(gr != $(this).data('group')) {
              $(this).closest("tr").remove();
            }
          });
        });
        
      },
      error: function() {
        alert('Error!');
      }
    });
  });

  $('#group, #select_test_res').on('change', function() {
    date = $('#select_date option:selected').val();
    //console.log(date);
    tid = $('#select_test_res option:selected').val();
    $('.test_name').html($('#select_test_res option:selected').text());
    $('.res_header > span').html(' студентов группы ' + $('#group option:selected').val());
    $.ajax({
      type: "POST",
      url: "get_result_table_by_group.php",
      data: {
              'group': $('#group option:selected').val(),
              'tid' : tid
            },
      success: function(html) {
        console.log($('.info span').text());
        $('.result-table').html(html);
        $('#resTable').tablesorter();
        avg = 0;
        cnt = 0;
        sum = 0;
        $('.table-row').each(function() {
          cnt++;
          sum = sum + $(this).data('id');
          if($(this).data('id') >= 50) {
            $(this).addClass('ok');
          } else {
            $(this).addClass('error');
          }
        });
        avg = Math.round(sum / cnt);
        $('.inf > span').html(avg);
        
        // Порог - среднее по группе

        // $('.table-row').each(function() {
        //   if($(this).data('id') >= avg) {
        //     $(this).addClass('ok');
        //   } else {
        //     $(this).addClass('error');
        //   }
        // });

        $('.sirname').on('click', function() {
          var res = {'uid': $(this).data('id'), 'tid': $(this).data('tid')};
          $.ajax({
            type: "POST",
            url: "get_test_result.php",
            data: res,
            success: function(html) {
              $('.data').html(html);
              MathJax.typeset();
            },
            error: function() {
              alert('Error!');
            }
          });
        });

        $('.tid').on('click', function() {
          console.log($(this).attr('title'));
          $('.res_header').html($(this).attr('title'));
        });

        $('#remove_all_results').on('change', function() {
          $('.remove_results').each(function() {
            if(this.checked) {
              $(this).prop('checked', false);
              $('#submit-id').prop('disabled', true);
            } else {
              $(this).prop('checked', true);
              $('#submit-id').prop('disabled', false);
            }
          });
        });
      
        $('.remove_results').on('change', function() {
          $('#submit-id').css('display', 'block');
          $('#submit-id').prop('disabled', false);
        });

        $('.group').on('click', function() {
          var gr = $(this).data('group');
          //console.log($(this).data('group'));
          
          $('.group').each(function() {
            if(gr != $(this).data('group')) {
              $(this).closest("tr").remove();
            }
          });
        });
        
      },
      error: function() {
        alert('Error!');
      }
    });
  });

  $('#sirname').on('input', function() {
    sirname = $(this).val();
    sirname.toLowerCase();
    //console.log(date);
    $.ajax({
      type: "POST",
      url: "get_result_table_by_sirname.php",
      data: {
              'sirname': sirname,
            },
      success: function(html) {
        //console.log(html);
        $('.result-table').html(html);
        $('#resTable').tablesorter();

        $('.table-row').each(function() {
          if($(this).data('id') > 50) {
            $(this).addClass('ok');
          } else {
            $(this).addClass('error');
          }
        });

        $('.sirname').on('click', function() {
          //console.log($(this).data('id'));
          //console.log($(this).data('tid'));
          var res = {'uid': $(this).data('id'), 'tid': $(this).data('tid')};
          $.ajax({
            type: "POST",
            url: "get_test_result.php",
            data: res,
            success: function(html) {
              $('.data').html(html);
              MathJax.typeset();
            },
            error: function() {
              alert('Error!');
            }
          });
        });

        $('.tid').on('click', function() {
          console.log($(this).attr('title'));
          $('.res_header').html($(this).attr('title'));
        });

        $('#remove_all_results').on('change', function() {
          $('.remove_results').each(function() {
            if(this.checked) {
              $(this).prop('checked', false);
              $('#submit-id').prop('disabled', true);
            } else {
              $(this).prop('checked', true);
              $('#submit-id').prop('disabled', false);
            }
          });
        });
      
        $('.remove_results').on('change', function() {
          $('#submit-id').css('display', 'block');
          $('#submit-id').prop('disabled', false);
        });

        $('.group').on('click', function() {
          var gr = $(this).data('group');
          //console.log($(this).data('group'));
          
          $('.group').each(function() {
            if(gr != $(this).data('group')) {
              $(this).closest("tr").remove();
            }
          });
        });
        
      },
      error: function() {
        alert('Error!');
      }
    });
  });

 

  $('#submit-id').on('click', function() {
    var remove_ids = {};
    $('.remove_results').each(function() {
      if(this.checked) {
        remove_ids[$(this).data('id')] = $(this).data('id');
        $(this).closest("tr").remove();
      }
    });
    //console.log(remove_ids);
    $.ajax({
      type: "POST",
      url: "remove_results.php",
      data: {'remove_ids' : remove_ids},
      success: function(html) {
        //console.log(html);
        $('.data').html(html);
      },
      error: function() {
        alert('Error!');
      }
    });
  });
 
  

});