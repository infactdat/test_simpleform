<?php

/**
 * Pagebreak field.
 *
 * @package    WPForms
 * @author     WPForms
 * @since      1.0.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2016, WPForms LLC
 */
class WPForms_Field_Page_Break extends WPForms_Field
{

    /** @var bool|array */
    protected $pagebreak;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function init()
    {

        // Define field type information.
        $this->name = __('Page Break', 'wpforms');
        $this->type = 'pagebreak';
        $this->icon = 'fa-files-o';
        $this->order = 17;
//		$this->group = 'fancy';

        add_filter('wpforms_field_preview_class', array($this, 'preview_field_class'), 10, 2);
        add_filter('wpforms_field_new_class', array($this, 'preview_field_class'), 10, 2);
        add_action('wpforms_frontend_output', array($this, 'display_page_indicator'), 9, 5);
        add_action('wpforms_display_fields_before', array($this, 'display_fields_before'), 20, 2);
        add_action('wpforms_display_fields_after', array($this, 'display_fields_after'), 5, 2);
        add_action('wpforms_display_field_after', array($this, 'display_field_after'), 20, 2);
    }

    /**
     * Adds class to the builder field preview.
     *
     * @since 1.2.0
     *
     * @param string $css
     * @param array $field
     *
     * @return string
     */
    public function preview_field_class($css, $field)
    {

        if ('pagebreak' === $field['type']) {
            if (!empty($field['position']) && 'top' === $field['position']) {
                $css .= ' wpforms-field-stick wpforms-pagebreak-top';
            } elseif (!empty($field['position']) && 'bottom' === $field['position']) {
                $css .= ' wpforms-field-stick wpforms-pagebreak-bottom';
            } else {
                $css .= ' wpforms-pagebreak-normal';
            }
        }

        return $css;
    }

    /**
     * This displays if the form contains pagebreaks and is configured to show
     * a page indicator in the top pagebreak settings.
     *
     * This function was moved from class-frontend.php in v1.3.7.
     *
     * @since 1.2.1
     *
     * @param array $form_data
     */
    public function display_page_indicator($form_data)
    {

        $top = !empty(wpforms()->frontend->pages['top']) ? wpforms()->frontend->pages['top'] : false;

        if (empty($top['indicator'])) {
            return;
        }

        $pagebreak = array(
            'indicator' => sanitize_html_class($top['indicator']),
            'color' => wpforms_sanitize_hex_color($top['indicator_color']),
            'pages' => array_merge(array(wpforms()->frontend->pages['top']), wpforms()->frontend->pages['pages']),
        );
        $p = 1;

        printf(
            '<div class="wpforms-page-indicator %s" data-indicator="%s" data-indicator-color="%s">',
            esc_attr($pagebreak['indicator']),
            esc_attr($pagebreak['indicator']),
            esc_attr($pagebreak['color'])
        );

        if ('circles' === $pagebreak['indicator']) {

            // Circles theme.
            foreach ($pagebreak['pages'] as $page) {
                $class = (1 === $p) ? 'active' : '';
                $bg = (1 === $p) ? 'style="background-color:' . esc_attr($pagebreak['color']) . '"' : '';
                printf('<div class="wpforms-page-indicator-page %s wpforms-page-indicator-page-%d">', $class, $p);
                printf('<span class="wpforms-page-indicator-page-number" %s>%d</span>', $bg, $p);
                if (!empty($page['title'])) {
                    printf('<span class="wpforms-page-indicator-page-title">%s<span>', esc_html($page['title']));
                }
                echo '</div>';
                $p++;
            }
        } elseif ('connector' === $pagebreak['indicator']) {

            // Connector theme.
            foreach ($pagebreak['pages'] as $page) {
                $class = (1 === $p) ? 'active ' : '';
                $bg = (1 === $p) ? 'style="background-color:' . esc_attr($pagebreak['color']) . '"' : '';
                $border = (1 === $p) ? 'style="border-top-color:' . esc_attr($pagebreak['color']) . '"' : '';
                $width = 100 / (count($pagebreak['pages'])) . '%';
                printf('<div class="wpforms-page-indicator-page %s wpforms-page-indicator-page-%d" style="width:%s;">', $class, $p, $width);
                printf('<span class="wpforms-page-indicator-page-number" %s>%d<span class="wpforms-page-indicator-page-triangle" %s></span></span>', $bg, $p, $border);
                if (!empty($page['title'])) {
                    printf('<span class="wpforms-page-indicator-page-title">%s<span>', esc_html($page['title']));
                }
                echo '</div>';
                $p++;
            }
        } elseif ('progress' === $pagebreak['indicator']) {

            // Progress theme.
            $p1 = !empty($pagebreak['pages'][0]['title']) ? esc_html($pagebreak['pages'][0]['title']) : '';
            $sep = empty($p1) ? 'style="display:none;"' : '';
            $width = 100 / (count($pagebreak['pages'])) . '%';
            $prog = 'style="width:' . $width . ';background-color:' . $pagebreak['color'] . ';"';
            $names = '';

            foreach ($pagebreak['pages'] as $page) {
                if (!empty($page['title'])) {
                    $names .= sprintf('data-page-%d-title="%s" ', $p, esc_attr($page['title']));
                }
                $p++;
            }
            printf('<span class="wpforms-page-indicator-page-title" %s>%s</span>', $names, $p1);
            printf('<span class="wpforms-page-indicator-page-title-sep" %s> - </span>', $sep);
            printf(
            /* translators: %1$s - current step in multi-page form; %2$d - total number of pages. */
                '<span class="wpforms-page-indicator-steps">' . __('Step %1$s of %2$d', 'wpforms') . '</span>',
                '<span class="wpforms-page-indicator-steps-current">1</span>',
                count($pagebreak['pages'])
            );

            printf('<div class="wpforms-page-indicator-page-progress-wrap"><div class="wpforms-page-indicator-page-progress" %s></div></div>', $prog);
        } // End if().

        do_action('wpforms_pagebreak_indicator', $pagebreak, $form_data);

        echo '</div>';
    }

    /**
     * Display frontend markup for the beginning of the first pagebreak.
     *
     * @since 1.3.7
     *
     * @param array $form_data
     */
    public function display_fields_before($form_data)
    {

        // Check if we have an opening pagebreak, if not then bail.
        $top = !empty(wpforms()->frontend->pages['top']) ? wpforms()->frontend->pages['top'] : false;

        if (!$top) {
            return;
        }

        $css = !empty($top['css']) ? $top['css'] : '';

        echo '<div class="wpforms-page wpforms-page-1 ' . wpforms_sanitize_classes($css) . '">';
    }

    /**
     * Display frontend markup for the end of the last pagebreak.
     *
     * @since 1.3.7
     *
     * @param array $form_data
     */
    public function display_fields_after($form_data)
    {
        // Check if the current form utilizes pagebreaks, if not bail.
        $pages = !empty(wpforms()->frontend->pages) ? true : false;
        if ($pages) {
            // If we don't have a bottom pagebreak, the form is pre-v1.2.1 and
            // this is for backwards compatibility.
            $bottom = !empty(wpforms()->frontend->pages['bottom']) ? wpforms()->frontend->pages['top'] : false;
            if (!$bottom) {
                $prev = !empty($form_data['settings']['pagebreak_prev']) ? $form_data['settings']['pagebreak_prev'] : __('Previous', 'wpforms');
                echo '<div class="wpforms-field wpforms-field-pagebreak">';
                printf(
                    '<button class="wpforms-page-button wpforms-page-prev" data-action="prev" data-page="%d" data-formid="%d">%s</button>',
                    absint(wpforms()->frontend->pages['current'] + 1),
                    absint($form_data['id']),
                    esc_html($prev)
                );
                echo '</div>';
            }

            echo '</div>';
        }
    }

    /**
     * Display frontend markup to end current page and begin the next.
     *
     * @since 1.3.7
     *
     * @param array $field
     * @param array $form_data
     */
    public function display_field_after($field, $form_data)
    {

        if ('pagebreak' === $field['type']) {

            $total = wpforms()->frontend->pages['total'];
            $current = wpforms()->frontend->pages['current'];

            if ((empty($field['position']) || 'top' !== $field['position']) && $current !== $total) {

                $next = $current + 1;
                $last = $next === $total ? 'last' : '';
                $css = !empty($field['css']) ? $field['css'] : '';

                printf(
                    '</table></div><div class="wpforms-page wpforms-page-%s %s %s" style="display:none;">',
                    absint($next),
                    esc_html($last),
                    wpforms_sanitize_classes($css)
                );

                // Increase count for next page.
                wpforms()->frontend->pages['current']++;
            }
        }
    }

    /**
     * Field options panel inside the builder.
     *
     * @since 1.0.0
     *
     * @param array $field
     */
    public function field_options($field)
    {

        $position = !empty($field['position']) ? esc_attr($field['position']) : '';
        $position_class = !empty($field['position']) ? 'wpforms-pagebreak-' . $position : '';

        // Hidden field indicating the position.
        $this->field_element('text', $field, array(
            'type' => 'hidden',
            'slug' => 'position',
            'value' => $position,
            'class' => 'position',
        ));

        // -------------------------------------------------------------------//
        // Basic field options.
        // -------------------------------------------------------------------//

        // Options open markup.
        $this->field_option('basic-options', $field, array(
            'markup' => 'open',
            'class' => $position_class,
        ));

        // Next label.
        if (empty($position)) {
            $lbl = $this->field_element(
                'label',
                $field,
                array(
                    'slug' => 'next',
                    'value' => __('Next Label', 'wpforms'),
                    'tooltip' => __('Enter text for Next page navigation button.', 'wpforms'),
                ),
                false
            );
            $fld = $this->field_element(
                'text',
                $field,
                array(
                    'slug' => 'next',
                    'value' => !empty($field['next']) ? esc_attr($field['next']) : __('Next', 'wpforms'),
                ),
                false
            );
            $this->field_element('row', $field, array(
                'slug' => 'next',
                'content' => $lbl . $fld,
            ));
        }

        // Options not available to top pagebreaks.
        if ('bottom' === $position) {

            // Previous button toggle.
            $lbl = $this->field_element(
                'label',
                $field,
                array(
                    'slug' => 'prev_toggle',
                    'value' => __('Display Previous', 'wpforms'),
                    'tooltip' => __('Toggle displaying the Previous page navigation button.', 'wpforms'),

                ),
                false
            );
            $fld = $this->field_element(
                'toggle',
                $field,
                array(
                    'slug' => 'prev_toggle',
                    'value' => !empty($field['prev_toggle']) || !empty($field['prev']) ? true : false,
                ),
                false
            );
            $this->field_element('row', $field, array(
                'slug' => 'prev_toggle',
                'content' => $lbl . $fld,
            ));

            // Previous button label.
            $lbl = $this->field_element(
                'label',
                $field,
                array(
                    'slug' => 'prev',
                    'value' => __('Previous Label', 'wpforms'),
                    'tooltip' => __('Enter text for Previous page navigation button.', 'wpforms'),
                ),
                false
            );
            $fld = $this->field_element(
                'text',
                $field,
                array(
                    'slug' => 'prev',
                    'value' => !empty($field['prev']) ? esc_attr($field['prev']) : '',
                ),
                false
            );
            $this->field_element('row', $field, array(
                'slug' => 'prev',
                'content' => $lbl . $fld,
                'class' => empty($field['prev_toggle']) ? 'wpforms-hidden' : '',
            ));
        } // End if().

        // Options close markup.
        $this->field_option('basic-options', $field, array(
            'markup' => 'close',
        ));

        // -------------------------------------------------------------------//
        // Advanced field options.
        // -------------------------------------------------------------------//

        // Advanced options are not available to bottom pagebreaks.
//		if ( 'bottom' !== $position ) {
//
//			// Options open markup.
//			$this->field_option( 'advanced-options', $field, array(
//				'markup' => 'open',
//				'class'  => $position_class,
//			) );
//
//			// Custom CSS classes.
//			$this->field_option( 'css', $field );
//
//			// Options close markup.
//			$this->field_option( 'advanced-options', $field, array(
//				'markup' => 'close',
//			) );
//		} // End if().
    }

    /**
     * Field preview inside the builder.
     *
     * @since 1.0.0
     *
     * @param array $field
     */
    public function field_preview($field)
    {

        $nav_align = 'wpforms-pagebreak-buttons-left';
        $prev = !empty($field['prev']) ? esc_html($field['prev']) : __('Previous', 'wpforms');
        $prev_class = empty($field['prev']) && empty($field['prev_toggle']) ? 'wpforms-hidden' : '';
        $next = !empty($field['next']) ? esc_html($field['next']) : __('Next', 'wpforms');
        $next_class = empty($next) ? 'wpforms-hidden' : '';
        $position = !empty($field['position']) ? esc_html($field['position']) : 'normal';
        $title = !empty($field['title']) ? '(' . esc_html($field['title']) . ')' : '';
        $label = 'top' === $position ? __('First Page', 'wpforms') : '';
        $label = 'normal' === $position && empty($label) ? __('Page Break', 'wpforms') : $label;

        if ('top' !== $position) {
            if (empty($this->form_data)) {
                $this->form_data = wpforms()->form->get($this->form_id, array(
                    'content_only' => true,
                ));
            }

            if (empty($this->pagebreak)) {
                $this->pagebreak = wpforms_get_pagebreak_details($this->form_data);
            }

            if (!empty($this->pagebreak['top']['nav_align'])) {
                $nav_align = 'wpforms-pagebreak-buttons-' . sanitize_html_class($this->pagebreak['top']['nav_align']);
            }

            echo '<div class="wpforms-pagebreak-buttons ' . $nav_align . '">';
            printf(
                '<button class="wpforms-pagebreak-button wpforms-pagebreak-prev %s">%s</button>',
                $prev_class,
                $prev
            );
            if ('bottom' !== $position) {
                printf(
                    '<button class="wpforms-pagebreak-button wpforms-pagebreak-next %s">%s</button>',
                    $next_class,
                    $next
                );
            }
            echo '</div>';
        }

        // Visual divider.
        echo '<div class="wpforms-pagebreak-divider">';
        if ('bottom' !== $position) {
            printf(
                '<span class="pagebreak-label">%s <span class="wpforms-pagebreak-title">%s</span></span>',
                $label,
                $title
            );
        }
        echo '<span class="line"></span>';
        echo '</div>';
    }

    /**
     * Field display on the form front-end.
     *
     * @since 1.0.0
     *
     * @param array $field
     * @param array $field_atts
     * @param array $form_data
     */
    public function field_display($field, $field_atts, $form_data)
    {

        // Top pagebreaks don't display.
        if (!empty($field['position']) && 'top' === $field['position']) {
            return;
        }

        $settings = $form_data['settings'];

        // Setup and sanitize the necessary data.
        $field = apply_filters('wpforms_pagedivider_field_display', $field, $field_atts, $form_data);
        $total = wpforms()->frontend->pages['total'];
        $current = wpforms()->frontend->pages['current'];
        $top = wpforms()->frontend->pages['top'];
        $next = !empty($field['next']) ? esc_html($field['next']) : '';
        $prev = !empty($field['prev']) ? esc_html($field['prev']) : '';
        $align = 'wpforms-pagebreak-center';

        if (!empty($top['nav_align'])) {
            $align = 'wpforms-pagebreak-' . $top['nav_align'];
        }

        echo '<div class="wpforms-clear ' . sanitize_html_class($align) . '">';

        if (!empty($settings['theme_of_button'])) {
            $classes = wpforms_sanitize_classes($settings['theme_of_button']);
        }

        if ($current > 1 && !empty($prev)) {
            printf(
                '<button class="wpforms-page-button wpforms-handle-button wpforms-page-prev %s" data-action="prev" data-page="%d" data-formid="%s">%s</button>',
                $classes,
                $current,
                $form_data['id'],
                $prev
            );
        }

        if ($current < $total && !empty($next)) {
            printf(
                '<button class="wpforms-page-button wpforms-handle-button wpforms-page-next %s" data-action="next" data-page="%d" data-formid="%s">%s</button>',
                $classes,
                $current,
                $form_data['id'],
                $next
            );
        }

        echo '</div>';
    }

    /**
     * Formats field.
     *
     * @since 1.0.0
     *
     * @param int $field_id
     * @param array $field_submit
     * @param array $form_data
     */
    public function format($field_id, $field_submit, $form_data)
    {
    }
}

new WPForms_Field_Page_Break;
