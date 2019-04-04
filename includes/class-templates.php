<?php

/**
 * Pre-configured packaged templates.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
 */
class WPForms_Templates
{


    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {

        $this->init();
    }

    /**
     * Load and init the base form template class.
     *
     * @since 1.2.8
     */
    public function init()
    {
        // Parent class template
        require_once WPFORMS_PLUGIN_DIR . 'includes/templates/class-base.php';

        // Load default templates on WP init
        add_action('init', array($this, 'load'), 20);
    }

    /**
     * Load default form templates.
     *
     * @since 1.0.0
     */
    public function load()
    {

        $templates = apply_filters('wpforms_load_templates', array(
            'blank',
            'contact',
            'request-quote',
            'donation',
            'order',
            'subscribe',
            'suggestion',
            'review'
        ));

        foreach ($templates as $template) {

            if (file_exists(WPFORMS_PLUGIN_DIR . 'includes/templates/class-' . $template . '.php')) {
                require_once WPFORMS_PLUGIN_DIR . 'includes/templates/class-' . $template . '.php';
            } elseif (file_exists(WPFORMS_PLUGIN_DIR . 'advance/includes/templates/class-' . $template . '.php')) {
                require_once WPFORMS_PLUGIN_DIR . 'advance/includes/templates/class-' . $template . '.php';
            }
        }
    }
}

new WPForms_Templates;
