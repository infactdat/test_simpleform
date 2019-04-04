jQuery(document).ready(function () {

    jQuery('.wpforms-page-1 .wpforms-field.wpforms-field-pagebreak').unwrap();

    // Section to check autokana functions
    if (jQuery('.wpforms-field-row').hasClass('enable_autokana')) {
        if (jQuery('.wpforms-field-name-first').length) {
            jQuery.fn.autoKana('.enable_autokana .wpforms-field-name-first', '.enable_autokana .wpforms-field-name-autokana_first', {katakana: true});
        }
        if (jQuery('.wpforms-field-name-last').length) {
            jQuery.fn.autoKana('.enable_autokana .wpforms-field-name-last', '.enable_autokana .wpforms-field-name-autokana_last', {katakana: true});
        }
    }

    // Disbale email confirm field
    if (jQuery('.wpforms-field-email-secondary').length) {
        jQuery('.wpforms-field-email-secondary').on("cut copy paste", function (e) {
            e.preventDefault();
        });
    }

    jQuery('.wpforms-page-next').click(function () {
        if (jQuery('.name_error').length) {
            jQuery('.name_error').remove();
        }
    });

    // Section to check limit characters
    jQuery('.wpforms-field').each(function () {
        if (!jQuery(this).hasClass('wpforms-field-date-time')) {
            if (jQuery(this).hasClass('limit_character')) {
                var className = jQuery(this).attr('class');
                var lstClasses = className.split(" ");

                var arrayLength = lstClasses.length;
                var limitNumber = 0;
                for (var i = 0; i < arrayLength; i++) {
                    className = lstClasses[i];
                    if (className.includes('limit_number')) {
                        limitNumber = className.replace('limit_number_', '');
                    }
                }
                if (limitNumber !== 0) {
                    jQuery(this).find('input').attr('maxlength', limitNumber);
                    jQuery(this).find('textarea').attr('maxlength', limitNumber);
                    jQuery(this).find('email').attr('maxlength', limitNumber);
                }
            }
        }


    });

    //Section to validate characters using regex expression


    jQuery(".wpforms-field").find('input').change(function () {
        jQuery(this).validateregex(jQuery(this));
    });

    jQuery(".wpforms-field").find('textarea').change(function () {
        jQuery(this).validateregex(jQuery(this));
    });


    jQuery('.wpforms-field-container').find('input[type=radio]').each(function () {
        jQuery(this).click(function () {
            var other_choice_element = jQuery(this).closest('.wpforms-field-radio').find('.other_choice');
            if (other_choice_element.length) {
                var other_radios = other_choice_element.parents('.wpforms-field-radio').find('input[type=radio]');
                if (jQuery(this).attr('value') === jQuery.trim('その他')) {
                    other_choice_element.removeClass('hidden');
                    other_choice_element.attr('name', jQuery(this).attr('name'));
                    other_choice_element.prop('disabled', false);
                    if (jQuery(this).prop('required')) {
                        other_radios.attr('aria-required', false);
                        other_radios.attr('required', false);
                        other_choice_element.attr('aria-required', true);
                        other_choice_element.prop('required', true);
                        other_choice_element.addClass('wpforms-field-required');
                    }
                    jQuery(this).attr('name', '');
                }
                else {
                    if (!other_choice_element.hasClass('hidden')) {
                        jQuery(this).attr('name', other_choice_element.attr('name'));
                        var previous_radio = jQuery(this).closest('.wpforms-field-radio').find("input[type=radio]");
                        previous_radio.attr('name', other_choice_element.attr('name'));
                        other_choice_element.attr('name', '');
                    }
                    other_choice_element.addClass('hidden');
                    other_choice_element.prop('disabled', true);

                    if (other_choice_element.prop('required')) {
                        other_radios.attr('aria-required', true);
                        other_radios.attr('required', true);
                        other_choice_element.attr('aria-required', false);
                        other_choice_element.prop('required', false);
                        other_choice_element.removeClass('wpforms-field-required');
                    }

                    jQuery(this).prop('checked', true);
                }
            }
        });

    });



});
jQuery.fn.validateregex = function (element) {
    // English elements
    var is_english_alphabet = "a-zA-Z '.!";
    var is_english_number = "0-9";


    // Fullsize elements
    var full_width = [
        "ぁ-んァ-ン", "０-９", "Ａ-ｚ", "\u4E00-\u9FFF"
    ];

    var katana_full_size = [
        "ァ-ン"
    ]


    var name_condition = [
        "ァ-ン", "ぁ-ん", "一-龯"
    ]

    // Halfsize elements
    var half_width = [
        "ｧ-ﾝﾞﾟ", "ｦ-ﾟ", "0-9", "a-zA-Z"
    ];

    var lstConditions = [];
    var regex_condition = '';
    var error_message = '';


    if (element.closest('.wpforms-field').hasClass('enable_full_size')) {
        lstConditions.push(full_width);
    }

    if (element.closest('.wpforms-field').hasClass('enable_half_size')) {
        lstConditions.push(half_width);
    }

    if (element.closest('.wpforms-field').hasClass('enable_number')) {
        lstConditions.push(is_english_number);
    }

    if (element.closest('.wpforms-field').hasClass('enable_english_character')) {
        lstConditions.push(is_english_alphabet);
    }

    if (element.hasClass('wpforms-field-name')) {
        lstConditions.push(name_condition);
    }

    if (element.hasClass('wpforms-field-name-autokana_first')) {
        lstConditions.push(katana_full_size);
    }

    if (element.hasClass('wpforms-field-name-autokana_last')) {
        lstConditions.push(katana_full_size);
    }


    var arrayLength = lstConditions.length;
    if (arrayLength > 0) {
        regex_condition += "[";
        for (var i = 0; i < arrayLength; i++) {
            regex_condition += lstConditions[i];
        }
        regex_condition += "]+$";


        if (element.validateInputField(element.val(), regex_condition) === false) {
            element.toggleClass("had_name_error");
            if (element.hasClass("had_name_error") && !element.next().hasClass("wpforms-error")) {
                element.after(jQuery('<label class="wpforms-error name_error">入力内容に誤りがあります</label>'));
            }


            element.addClass('wpforms-error');
            element.val('');
        }
        else {
            element.removeClass('wpforms-error');
            element.removeClass("had_name_error");
            if (element.next().hasClass("wpforms-error")) {
                element.next().remove();
            }
        }

    }
};
jQuery.fn.validateInputField =
    function (value, regex_arg) {

        var regex = new RegExp(regex_arg);

        if (!regex.test(value)) {
            return false;
        }

    };
