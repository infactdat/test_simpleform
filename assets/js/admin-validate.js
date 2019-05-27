jQuery(document).ready(function () {
    $(function () {
        $("input[type=number]").keydown(function () {
            // Save old value.
            if (!$(this).val() || (parseInt($(this).val()) <= 9999 && parseInt($(this).val()) >0))
                $(this).data("old", $(this).val());
        });
        $("input[type=number]").keyup(function () {
            // Check correct, else revert back to old value.
            if (!$(this).val() || (parseInt($(this).val()) <= 9999 && parseInt($(this).val()) >0))
                ;
            else
                $(this).val($(this).data("old"));
        });
    });
    jQuery('.wpforms-field-option-row.wpforms-field-option-row-limit_character ').find('input').prop('type','number').attr('min','1');
});
