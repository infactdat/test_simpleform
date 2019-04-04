<?php

/**
 * Sanitizes textarea. Tries to use wp sanitize_textarea_field() function. If that's now available, uses its own methods
 * @return string
 */


function swpsmtp_sanitize_textarea($str)
{
    if (function_exists('sanitize_textarea_field')) {
        return sanitize_textarea_field($str);
    }
    $filtered = wp_check_invalid_utf8($str);

    if (strpos($filtered, '<') !== false) {
        $filtered = wp_pre_kses_less_than($filtered);
        // This will strip extra whitespace for us.
        $filtered = wp_strip_all_tags($filtered, false);

        // Use html entities in a special case to make sure no later
        // newline stripping stage could lead to a functional tag
        $filtered = str_replace("<\n", "&lt;\n", $filtered);
    }

    $filtered = trim($filtered);

    $found = false;
    while (preg_match('/%[a-f0-9]{2}/i', $filtered, $match)) {
        $filtered = str_replace($match[0], '', $filtered);
        $found = true;
    }

    if ($found) {
        // Strip out the whitespace that may now exist after removing the octets.
        $filtered = trim(preg_replace('/ +/', ' ', $filtered));
    }

    return $filtered;
}

/**
 * Renders the admin settings menu of the plugin.
 * @return void
 */
function swpsmtp_settings()
{
    echo '<div class="wrap" id="swpsmtp-mail"><div id="wpforms-settings" class="wrap wpforms-admin-wrap">';
    echo '<div id="post-body">';

    $display_add_options = $message = $error = $result = '';

    $swpsmtp_options = get_option('swpsmtp_options');
    $smtp_test_mail = get_option('smtp_test_mail');
    $gag_password = '#easywpsmtpgagpass#';
    if (empty($smtp_test_mail)) {
        $smtp_test_mail = array('swpsmtp_to' => '', 'swpsmtp_subject' => '', 'swpsmtp_message' => '',);
    }

    if (isset($_POST['swpsmtp_form_submit'])) {
        // check nounce
        if (!check_admin_referer(plugin_basename(__FILE__), 'swpsmtp_nonce_name')) {
            $error .= " " . __("Nonce check failed.", 'wpforms');
        }
        /* Update settings */
        $swpsmtp_options['from_name_field'] = isset($_POST['swpsmtp_from_name']) ? sanitize_text_field(wp_unslash($_POST['swpsmtp_from_name'])) : '';
        $swpsmtp_options['force_from_name_replace'] = isset($_POST['swpsmtp_force_from_name_replace']) ? 1 : false;

        if (isset($_POST['swpsmtp_from_email'])) {
            if (is_email($_POST['swpsmtp_from_email'])) {
                $swpsmtp_options['from_email_field'] = sanitize_email($_POST['swpsmtp_from_email']);
            } else {
                $error .= " " . __("Please enter a valid email address in the 'FROM' field.", 'wpforms');
            }
        }
        if (isset($_POST['swpsmtp_reply_to_email'])) {
            $swpsmtp_options['reply_to_email'] = sanitize_email($_POST['swpsmtp_reply_to_email']);
        }

        if (isset($_POST['swpsmtp_email_ignore_list'])) {
            $swpsmtp_options['email_ignore_list'] = sanitize_text_field($_POST['swpsmtp_email_ignore_list']);
        }

        $swpsmtp_options['smtp_settings']['host'] = sanitize_text_field($_POST['swpsmtp_smtp_host']);
        $swpsmtp_options['smtp_settings']['type_encryption'] = (isset($_POST['swpsmtp_smtp_type_encryption'])) ? sanitize_text_field($_POST['swpsmtp_smtp_type_encryption']) : 'none';
        $swpsmtp_options['smtp_settings']['autentication'] = (isset($_POST['swpsmtp_smtp_autentication'])) ? sanitize_text_field($_POST['swpsmtp_smtp_autentication']) : 'yes';
        $swpsmtp_options['smtp_settings']['username'] = sanitize_text_field($_POST['swpsmtp_smtp_username']);
        $smtp_password = $_POST['swpsmtp_smtp_password'];
        if ($smtp_password !== $gag_password) {
            $swpsmtp_options['smtp_settings']['password'] = base64_encode($smtp_password);
        }
        $swpsmtp_options['smtp_settings']['enable_debug'] = isset($_POST['swpsmtp_enable_debug']) ? 1 : false;
        $swpsmtp_options['smtp_settings']['insecure_ssl'] = isset($_POST['swpsmtp_insecure_ssl']) ? 1 : false;
        $swpsmtp_options['enable_domain_check'] = isset($_POST['swpsmtp_enable_domain_check']) ? 1 : false;
        if (isset($_POST['swpsmtp_allowed_domains'])) {
            $swpsmtp_options['block_all_emails'] = isset($_POST['swpsmtp_block_all_emails']) ? 1 : false;
            $swpsmtp_options['allowed_domains'] = base64_encode(sanitize_text_field($_POST['swpsmtp_allowed_domains']));
        } else if (!isset($swpsmtp_options['allowed_domains'])) {
            $swpsmtp_options['allowed_domains'] = '';
        }

        /* Check value from "SMTP port" option */
        if (isset($_POST['swpsmtp_smtp_port'])) {
            if (empty($_POST['swpsmtp_smtp_port']) || 1 > intval($_POST['swpsmtp_smtp_port']) || (!preg_match('/^\d+$/', $_POST['swpsmtp_smtp_port']))) {
                $swpsmtp_options['smtp_settings']['port'] = '25';
                $error .= " " . __("Please enter a valid port in the 'SMTP Port' field.", 'wpforms');
            } else {
                $swpsmtp_options['smtp_settings']['port'] = sanitize_text_field($_POST['swpsmtp_smtp_port']);
            }
        }

        /* Update settings in the database */
        if (empty($error)) {
            update_option('swpsmtp_options', $swpsmtp_options);
            $message .= __("Settings saved.", 'wpforms');
        } else {
            $error .= " " . __("Settings are not saved.", 'wpforms');
        }
    }

    if( array_key_exists( 'clear_setting', $_POST ) ){
        swpsmtp_send_uninstall();
    }

    /* Send test letter */
    $swpsmtp_to = '';
    if (isset($_POST['swpsmtp_test_submit']) && check_admin_referer(plugin_basename(__FILE__), 'swpsmtp_nonce_name')) {
        if (isset($_POST['swpsmtp_to'])) {
            $to_email = sanitize_text_field($_POST['swpsmtp_to']);
            if (is_email($to_email)) {
                $swpsmtp_to = $to_email;
            } else {
                $error .= __("Please enter a valid email address in the recipient email field.", 'wpforms');
            }
        }
        $swpsmtp_subject = isset($_POST['swpsmtp_subject']) ? sanitize_text_field($_POST['swpsmtp_subject']) : '';
        $swpsmtp_message = isset($_POST['swpsmtp_message']) ? swpsmtp_sanitize_textarea($_POST['swpsmtp_message']) : '';

        //Save the test mail details so it doesn't need to be filled in everytime.
        $smtp_test_mail['swpsmtp_to'] = $swpsmtp_to;
        $smtp_test_mail['swpsmtp_subject'] = $swpsmtp_subject;
        $smtp_test_mail['swpsmtp_message'] = $swpsmtp_message;
        update_option('smtp_test_mail', $smtp_test_mail);

        if (!empty($swpsmtp_to)) {
            $result = swpsmtp_test_mail($swpsmtp_to, $swpsmtp_subject, $swpsmtp_message);
        }
    }
    ?>
    <style>


        #swpsmtp-save-settings-notice {
            padding: 10px 0;
        }

        #swpsmtp-save-settings-notice span {
            background-color: #ffff76;
            padding: 7px;
            border: 1px dashed red;
            display: block;
        }

        .swpsmtp-stars-container {
            text-align: center;
            margin-top: 10px;
        }

        .swpsmtp-stars-container span {
            vertical-align: text-top;
            color: #ffb900;
        }

        .swpsmtp-stars-container a {
            text-decoration: none;
        }

        .swpsmtp-settings-grid {
            display: inline-block;
        }

        .swpsmtp-settings-main-cont {
            width: 80%;
        }

        .swpsmtp-settings-sidebar-cont {
            width: 19%;
            float: right;
        }

        @media (max-width: 782px) {
            .swpsmtp-settings-grid {
                display: block;
                float: none;
                width: 100%;
            }
        }
    </style>
    <div class="updated fade" <?php if (empty($message)) {
        echo "style=\"display:none\"";
    } ?>>
        <p><strong><?php echo $message; ?></strong></p>
    </div>
    <div class="error" <?php if (empty($error)) {
        echo "style=\"display:none\"";
    } ?>>
        <p><strong><?php echo $error; ?></strong></p>
    </div>

    <div class="swpsmtp-settings-container">
        <div class="swpsmtp-settings-grid swpsmtp-settings-main-cont wpforms-admin-content wpforms-admin-settings">
            <div class="wpforms-setting-row wpforms-setting-row-content wpforms-clear section-heading no-desc"
                 id="wpforms-setting-row-email-heading"><span class="wpforms-setting-field"><h4>SMTP Settings</h4></span></div>
            <form id="swpsmtp_settings_form" method="post" action="">

                <input type="hidden" id="swpsmtp-urlHash" name="swpsmtp-urlHash" value="">

                <div class="swpsmtp-tab-container" data-tab-name="smtp">
                    <div class="postbox">
                        <div class="inside">


                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row"><?php _e("From Email Address", 'wpforms'); ?></th>
                                    <td>
                                        <input id="swpsmtp_from_email" type="text" name="swpsmtp_from_email"
                                               value="<?php echo isset($swpsmtp_options['from_email_field']) ? esc_attr($swpsmtp_options['from_email_field']) : ''; ?>"/><br/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><?php _e("From Name", 'wpforms'); ?></th>
                                    <td>
                                        <input id="swpsmtp_from_name" type="text" name="swpsmtp_from_name"
                                               value="<?php echo isset($swpsmtp_options['from_name_field']) ? esc_attr($swpsmtp_options['from_name_field']) : ''; ?>"/><br/>
                                        <p>
                                            <label><input type="checkbox" style="display:none;"
                                                          id="swpsmtp_force_from_name_replace"
                                                          name="swpsmtp_force_from_name_replace"
                                                          value="1"<?php echo (isset($swpsmtp_options['force_from_name_replace']) && ($swpsmtp_options['force_from_name_replace'])) ? ' checked' : ''; ?>/></label>
                                        </p>

                                    </td>
                                </tr>

                                <tr class="ad_opt swpsmtp_smtp_options">
                                    <th><?php _e('SMTP Host', 'wpforms'); ?></th>
                                    <td>
                                        <input id='swpsmtp_smtp_host' type='text' name='swpsmtp_smtp_host'
                                               value='<?php echo isset($swpsmtp_options['smtp_settings']['host']) ? esc_attr($swpsmtp_options['smtp_settings']['host']) : ''; ?>'/><br/>
                                    </td>
                                </tr>
                                <tr class="ad_opt swpsmtp_smtp_options">
                                    <th><?php _e('Type of Encryption', 'wpforms'); ?></th>
                                    <td>
                                        <label for="swpsmtp_smtp_type_encryption_1"><input type="radio"
                                                                                           id="swpsmtp_smtp_type_encryption_1"
                                                                                           name="swpsmtp_smtp_type_encryption"
                                                                                           value='none' <?php if (isset($swpsmtp_options['smtp_settings']['type_encryption']) && 'none' == $swpsmtp_options['smtp_settings']['type_encryption']) {
                                                echo 'checked="checked"';
                                            } ?> /> <?php _e('None', 'wpforms'); ?></label>
                                        <label for="swpsmtp_smtp_type_encryption_2"><input type="radio"
                                                                                           id="swpsmtp_smtp_type_encryption_2"
                                                                                           name="swpsmtp_smtp_type_encryption"
                                                                                           value='ssl' <?php if (isset($swpsmtp_options['smtp_settings']['type_encryption']) && 'ssl' == $swpsmtp_options['smtp_settings']['type_encryption']) {
                                                echo 'checked="checked"';
                                            } ?> /> <?php _e('SSL', 'wpforms'); ?></label>
                                        <label for="swpsmtp_smtp_type_encryption_3"><input type="radio"
                                                                                           id="swpsmtp_smtp_type_encryption_3"
                                                                                           name="swpsmtp_smtp_type_encryption"
                                                                                           value='tls' <?php if (isset($swpsmtp_options['smtp_settings']['type_encryption']) && 'tls' == $swpsmtp_options['smtp_settings']['type_encryption']) {
                                                echo 'checked="checked"';
                                            } ?> /> <?php _e('TLS', 'wpforms'); ?></label><br/>
                                    </td>
                                </tr>
                                <tr class="ad_opt swpsmtp_smtp_options">
                                    <th><?php _e('SMTP Port', 'wpforms'); ?></th>
                                    <td>
                                        <input id='swpsmtp_smtp_port' type='text' name='swpsmtp_smtp_port'
                                               value='<?php echo isset($swpsmtp_options['smtp_settings']['port']) ? esc_attr($swpsmtp_options['smtp_settings']['port']) : ''; ?>'/><br/>
                                    </td>
                                </tr>
                                <tr class="ad_opt swpsmtp_smtp_options">
                                    <th><?php _e('SMTP Authentication', 'wpforms'); ?></th>
                                    <td>
                                        <label for="swpsmtp_smtp_autentication"><input type="radio"
                                                                                       id="swpsmtp_smtp_autentication_1"
                                                                                       name="swpsmtp_smtp_autentication"
                                                                                       value='no' <?php if (isset($swpsmtp_options['smtp_settings']['autentication']) && 'no' == $swpsmtp_options['smtp_settings']['autentication']) {
                                                echo 'checked="checked"';
                                            } ?> /> <?php _e('No', 'wpforms'); ?></label>
                                        <label for="swpsmtp_smtp_autentication"><input type="radio"
                                                                                       id="swpsmtp_smtp_autentication_2"
                                                                                       name="swpsmtp_smtp_autentication"
                                                                                       value='yes' <?php if (isset($swpsmtp_options['smtp_settings']['autentication']) && 'yes' == $swpsmtp_options['smtp_settings']['autentication']) {
                                                echo 'checked="checked"';
                                            } ?> /> <?php _e('Yes', 'wpforms'); ?></label><br/>
                                    </td>
                                </tr>
                                <tr class="ad_opt swpsmtp_smtp_options">
                                    <th><?php _e('SMTP Username', 'wpforms'); ?></th>
                                    <td>
                                        <input id='swpsmtp_smtp_username' type='text' name='swpsmtp_smtp_username'
                                               value='<?php echo isset($swpsmtp_options['smtp_settings']['username']) ? esc_attr($swpsmtp_options['smtp_settings']['username']) : ''; ?>'/><br/>
                                    </td>
                                </tr>
                                <tr class="ad_opt swpsmtp_smtp_options">
                                    <th><?php _e('SMTP Password', 'wpforms'); ?></th>
                                    <td>
                                        <input id='swpsmtp_smtp_password' type='password' name='swpsmtp_smtp_password'
                                               value='<?php echo(swpsmtp_get_password() !== '' ? $gag_password : ''); ?>'/><br/>
                                    </td>
                                </tr>
                            </table>
                            <p class="submit">

                                <input type="submit" id="settings-form-submit" class=" wpforms-btn wpforms-btn-md wpforms-btn-orange"
                                       value="<?php _e('Save Changes', 'wpforms') ?>"/>
                                <input type="submit" id="clear-smtp-submit" name="clear_setting" class=" wpforms-btn wpforms-btn-md wpforms-btn-orange"
                                       value="<?php _e('Clear SMTP Settings', 'wpforms') ?>"/>
                                <input type="hidden" name="swpsmtp_form_submit" value="submit"/>
                                <?php wp_nonce_field(plugin_basename(__FILE__), 'swpsmtp_nonce_name'); ?>
                            </p>
                        </div><!-- end of inside -->
                    </div><!-- end of postbox -->
                </div>

            </form>


        </div>
    </div>


    <script>

        jQuery(function ($) {
            $('#swpsmtp-mail input').not('.ignore-change').change(function () {
                $('#swpsmtp-save-settings-notice').show();

            });
            $('#swpsmtp_enable_domain_check').change(function () {
                $('input[name="swpsmtp_allowed_domains"]').prop('disabled', !$(this).is(':checked'));
                $('input[name="swpsmtp_block_all_emails"]').prop('disabled', !$(this).is(':checked'));
            });
            $('#swpsmtp_clear_log_btn').click(function (e) {
                e.preventDefault();
                if (confirm("<?php _e('Are you sure want to clear log?', 'wpforms'); ?>")) {
                    var req = jQuery.ajax({
                        url: ajaxurl,
                        type: "post",
                        data: {action: "swpsmtp_clear_log"}
                    });
                    req.done(function (data) {
                        if (data === '1') {
                            alert("<?php _e('Log cleared.', 'wpforms'); ?>");
                        } else {
                            alert("Error occured: " + data);
                        }
                    });
                }
            });
        });

    </script>

    <?php
    echo '</div>'; //<!-- end of #poststuff and #post-body -->
    echo '</div></div>'; //<!--  end of .wrap #swpsmtp-mail .swpsmtp-mail -->
}

/**
 * Plugin functions for init
 * @return void
 */
function swpsmtp_admin_init()
{
    /* Internationalization, first(!) */

    add_action('wp_ajax_swpsmtp_clear_log', 'swpsmtp_clear_log');
//view log file
    if (isset($_GET['swpsmtp_action'])) {
        if ($_GET['swpsmtp_action'] === 'view_log') {
            $swpsmtp_options = get_option('swpsmtp_options');
            $log_file_name = $swpsmtp_options['smtp_settings']['log_file_name'];
            if (!file_exists(plugin_dir_path(__FILE__) . $log_file_name)) {
                if (swpsmtp_write_to_log("Easy WP SMTP debug log file\r\n\r\n") === false) {
                    wp_die('Can\'t write to log file. Check if plugin directory  (' . plugin_dir_path(__FILE__) . ') is writeable.');
                };
            }
            $logfile = fopen(plugin_dir_path(__FILE__) . $log_file_name, 'rb');
            if (!$logfile) {
                wp_die('Can\'t open log file.');
            }
            header('Content-Type: text/plain');
            fpassthru($logfile);
            die;
        }
    }
}
