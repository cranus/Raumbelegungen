/**
 * Created by JetBrains PhpStorm.
 * User: johannesstichler
 * Date: 11.10.11
 * Time: 15:46
 * To change this template use File | Settings | File Templates.
 */
$(document).ready(function(){
    $('#termine tr:odd').css('background-color','#FFFFF');
    $('#termine tr:even').css('background-color','#fcf4e9');
    $('#termine tr:first').css('background-color','#f3ae00');
    $('#raumbelegungen_anzeigen').button()
    .click(function(){
            $('#raumbelegungen_formular').submit();
        });
    $('#raumbelegungen_buttonhilfe').button({ })
    .button("disable")
    .button("enable")
    .click(function(){
        $('#raumbelegungen_hilfe').dialog({
            show: "slide",
            hide: "slide",
            minWidth: 600,
            buttons: { "Ok": function() {
                $(this).dialog("close");
                }
            }
        });
    });
    $('#raumbelegungen_buttonwochenende').button({ })
        .click(function(){
            $.getJSON('/plugins_packages/neo/raumbelegungen/ajax.php', {id: 'we'},function(data){
                  $('#raumbelegungen_bis').val(data.bis);
                  $('#raumbelegungen_von').val(data.von);
            });

        });
});