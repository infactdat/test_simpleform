jQuery(document).ready(function () {
    var infact_wpforms_color_picker = jQuery('.infact_wpforms-color-picker');
    if (infact_wpforms_color_picker.length){
        infact_wpforms_color_picker.minicolors();
    }
    var $ = jQuery.noConflict();
    $( ".range_time_input" ).change(function() {
        var max = parseInt($(this).attr('max'));
        var min = parseInt($(this).attr('min'));
        if ($(this).val() > max)
        {
            alert('Length is not valid, maximum ' + max + ' allowed.');
            $(this).val(max);
        }
        else if ($(this).val() < min)
        {
            $(this).val(min);
            alert('Length is short, minimum ' + min + ' required.');
        }
    });
    jQuery('.wpforms-field-option-row.wpforms-field-option-row-limit_character ').find('input').prop('type', 'number').attr('min', '1');
});
