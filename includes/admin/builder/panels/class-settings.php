<?php

/**
 * Settings management panel.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
 */
class WPForms_Builder_Panel_Settings extends WPForms_Builder_Panel
{

    /**
     * All systems go.
     *
     * @since 1.0.0
     */
    public function init()
    {

        // Define panel information
        $this->name = __('Settings', 'wpforms');
        $this->slug = 'settings';
        $this->icon = 'fa-sliders';
        $this->order = 10;
        $this->sidebar = true;
    }

    /**
     * Enqueue assets for the Setting panel.
     *
     * @since 1.0.0
     */
    public function enqueues()
    {

        // CSS
        wp_enqueue_style(
            'wpforms-builder-settings',
            WPFORMS_PLUGIN_URL . 'assets/css/admin-builder-settings.css',
            null,
            WPFORMS_VERSION
        );
    }

    /**
     * Outputs the Settings panel sidebar.
     *
     * @since 1.0.0
     */
    public function panel_sidebar()
    {

        // Sidebar contents are not valid unless we have a form
        if (!$this->form) {
            return;
        }

        $sections = array(
            'custom_content' => __('Custom content', 'wpforms'),
            'general' => __('General', 'wpforms'),
            'notifications' => __('Notifications', 'wpforms'),
            'confirmation' => __('Confirmation', 'wpforms'),

        );
        $sections = apply_filters('wpforms_builder_settings_sections', $sections, $this->form_data);
        foreach ($sections as $slug => $section) {
            $this->panel_sidebar_section($section, $slug);
        }
    }

    /**
     * Outputs the Settings panel primary content.
     *
     * @since 1.0.0
     */
    public function panel_content()
    {

        // Check if there is a form created
        if (!$this->form) {
            echo '<div class="wpforms-alert wpforms-alert-info">';
            _e('You need to <a href="#" class="wpforms-panel-switch" data-panel="setup">setup your form</a> before you can manage the settings.', 'wpforms');
            echo '</div>';
            return;
        }




        // --------------------------------------------------------------------//
        // Notifications
        // --------------------------------------------------------------------//
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-custom_content">';
        echo '<div class="wpforms-panel-content-section-title">';
        _e('Custom content', 'wpforms');
        echo '</div>';
        wpforms_panel_field(
            'tinymce',
            'settings',
            'custom_content_editor',
            $this->form_data,
            __('', 'wpforms'),
            array(
                'default' => __('', 'wpforms'),
                'tinymce' => array(
                    'editor_height' => '400',
                    'media_buttons' => true
                ),
            )
        );
        echo '</div>';

        // --------------------------------------------------------------------//
        // General
        // --------------------------------------------------------------------//
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-general">';
        echo '<div class="wpforms-panel-content-section-title">';
        _e('General', 'wpforms');
        echo '</div>';
        wpforms_panel_field(
            'select',
            'settings',
            'enable_archive',
            $this->form_data,
            __('Enable archives', 'wpforms'),
            array(
                'default' => '1',
                'options' => array(
                    '1' => __('On', 'wpforms'),
                    '0' => __('Off', 'wpforms'),
                ),
                'tooltip' => __('This options to allow all data of form can be stored in Archives ', 'wpforms'),
            )
        );
        wpforms_panel_field(
            'text',
            'settings',
            'form_title',
            $this->form_data,
            __('Form Name', 'wpforms'),
            array(
                'default' => $this->form->post_title,
            )
        );
        wpforms_panel_field(
            'textarea',
            'settings',
            'form_desc',
            $this->form_data,
            __('Form Description', 'wpforms')
        );
        wpforms_panel_field(
            'text',
            'settings',
            'form_class',
            $this->form_data,
            __('Form CSS Class', 'wpforms'),
            array(
                'tooltip' => __('Enter CSS class names for the form wrapper. Multiple class names should be separated with spaces.', 'wpforms'),
            )
        );
        wpforms_panel_field(
            'text',
            'settings',
            'submit_text',
            $this->form_data,
            __('Submit Button Text', 'wpforms'),
            array(
                'default' => __('Submit', 'wpforms'),
            )
        );
        wpforms_panel_field(
            'text',
            'settings',
            'submit_text_processing',
            $this->form_data,
            __('Submit Button Processing Text', 'wpforms'),
            array(
                'tooltip' => __('Enter the submit button text you would like the button display while the form submit is processing.', 'wpforms'),
            )
        );
        wpforms_panel_field(
            'text',
            'settings',
            'submit_class',
            $this->form_data,
            __('Submit Button CSS Class', 'wpforms'),
            array(
                'tooltip' => __('Enter CSS class names for the form submit button. Multiple names should be separated with spaces.', 'wpforms'),
            )
        );
        wpforms_panel_field(
            'checkbox',
            'settings',
            'honeypot',
            $this->form_data,
            __('Enable anti-spam honeypot', 'wpforms')
        );
        $recaptcha_key = wpforms_setting('recaptcha-site-key');
        $recaptcha_secret = wpforms_setting('recaptcha-secret-key');
        $recaptcha_type = wpforms_setting('recaptcha-type');
        if (!empty($recaptcha_key) && !empty($recaptcha_secret)) {
            wpforms_panel_field(
                'checkbox',
                'settings',
                'recaptcha',
                $this->form_data,
                'invisible' === $recaptcha_type ? __('Enable Google invisible reCAPTCHA', 'wpforms') : __('Enable Google reCAPTCHA (v2)', 'wpforms')
            );
        }
        do_action('wpforms_form_settings_general', $this);
        echo '</div>';

        // --------------------------------------------------------------------//
        // Notifications
        // --------------------------------------------------------------------//
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-notifications">';
        do_action('wpforms_form_settings_notifications', $this);
        echo '</div>';

        // --------------------------------------------------------------------//
        // Confirmation
        // --------------------------------------------------------------------//
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-confirmation">';
        echo '<div class="wpforms-panel-content-section-title">';
        _e('Confirmation', 'wpforms');
        echo '</div>';
        wpforms_panel_field(
            'select',
            'settings',
            'confirmation_type',
            $this->form_data,
            __('Confirmation Type', 'wpforms'),
            array(
                'default' => 'message',
                'options' => array(
                    'message' => __('Message', 'wpforms'),
                    'page' => __('Show Page', 'wpforms'),
                    'redirect' => __('Go to URL (Redirect)', 'wpforms'),
                ),
            )
        );
        wpforms_panel_field(
            'tinymce',
            'settings',
            'confirmation_message',
            $this->form_data,
            __('Confirmation Message', 'wpforms'),
            array(
                'default' => __('Thanks for contacting us! We will be in touch with you shortly.', 'wpforms'),
                'tinymce' => array(
                    'editor_height' => '200',
                ),
            )
        );
        wpforms_panel_field(
            'checkbox',
            'settings',
            'confirmation_message_scroll',
            $this->form_data,
            __('Automatically scroll to the confirmation message', 'wpforms')
        );
        $p = array();
        $pages = get_pages();
        foreach ($pages as $page) {
            $depth = count($page->ancestors);
            $p[$page->ID] = str_repeat('-', $depth) . ' ' . $page->post_title;
        }
        wpforms_panel_field(
            'select',
            'settings',
            'confirmation_page',
            $this->form_data,
            __('Confirmation Page', 'wpforms'),
            array(
                'options' => $p,
            )
        );
        wpforms_panel_field(
            'text',
            'settings',
            'confirmation_redirect',
            $this->form_data,
            __('Confirmation Redirect URL', 'wpforms')
        );
        do_action('wpforms_form_settings_confirmation', $this);
        echo '</div>';

        do_action('wpforms_form_settings_panel_content', $this);
    }
}

new WPForms_Builder_Panel_Settings;
