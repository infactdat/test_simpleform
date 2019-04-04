<?php

/**
 * Scribe to Email list form template.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
 */
class WPForms_Template_Review extends WPForms_Template
{

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function init()
    {

        $cat_args = array(
            'orderby' => 'term_id',
            'order' => 'ASC',
            'hide_empty' => false,
            'taxonomy' => 'infact_review_cat'
        );

        $terms = get_terms($cat_args);


        $cat_args2 = array(
            'orderby' => 'term_id',
            'order' => 'ASC',
            'hide_empty' => false,
            'taxonomy' => 'infact_review_cat2'
        );

        $terms2 = get_terms($cat_args2);


        if (!is_wp_error($terms) || !is_wp_error($terms2)) {

            $lstTerms = null;
            foreach ($terms as $term) {
                $term_id = $term->term_id;
                $term_slug = $term->name;
                $lstTerms[] = [
                    'label' => $term_slug,
                    'value' => $term_id
                ];
            }

            $lstTerms2 = null;
            foreach ($terms2 as $term) {
                $term_id = $term->term_id;
                $term_slug = $term->name;
                $lstTerms2[] = [
                    'label' => $term_slug,
                    'value' => $term_id
                ];
            }
            $this->name = __('Review template', 'wpforms');
            $this->slug = 'review_template';
            $this->description = __('', 'wpforms');
            $this->includes = '';
            $this->icon = '';
            $this->core = true;
            $this->data = array(
                'field_id' => '3',
                'fields' => array(
                    '0' => array(
                        'id' => '101',
                        'type' => 'pagebreak',
                        'position' => 'top'
                    ),
                    '1' => array(
                        'id' => '120',
                        'type' => 'select',
                        'label' => __('商品名', 'wpforms'),
                        'choices' => $lstTerms,
                        'required' => 1,
                        'show_values' => 1
                    ),
                    '2' => array(
                        'id' => '103',
                        'type' => 'text',
                        'label' => __('お名前', 'wpforms'),
                        'required' => '1',
                    ),
                    '3' => array(
                        'id' => '104',
                        'type' => 'text',
                        'label' => __('ペンネーム', 'wpforms'),
                        'required' => '1',
                    ),

                    '4' => array(
                        'id' => '105',
                        'type' => 'email',
                        'label' => __('メールアドレス', 'wpforms'),
                        'required' => '1',
                        'confirmation' => 1,
                        'placeholder' => 'abc@xyz.jp'
                    ),

                    '5' => array(
                        'id' => '107',
                        'type' => 'text',
                        'label' => __('年齢', 'wpforms'),
                        'required' => '0',
                        'enable_english_number' => 1
                    ),
                    '6' => array(
                        'id' => '108',
                        'type' => 'text',
                        'label' => __('職業', 'wpforms'),
                        'required' => 0,
                    ),
                    '7' => array(
                        'id' => '109',
                        'type' => 'file-upload',
                        'label' => __('画像アップロード', 'wpforms'),
                        'required' => 0,
                    ),
                    '8' => array(
                        'id' => '130',
                        'type' => 'select',
                        'label' => __('お悩み', 'wpforms'),
                        'required' => 1,
                        'choices' => $lstTerms2,
                        'show_values' => 1
                    ),
                    '9' => array(
                        'id' => '111',
                        'type' => 'textarea',
                        'label' => __('本文', 'wpforms'),
                        'required' => 1,
                    ),

                    '10' => array(
                        'id' => '112',
                        'type' => 'pagebreak',
                    ),
                    '11' => array(
                        'id' => '113',
                        'type' => 'pagebreak',
                        'position' => 'bottom',
                        'prev_toggle' => true,
                        'prev' => __('Previous', 'wpforms')
                    )
                ),
                'settings' => array(
                    'honeypot' => '1',
                    'confirmation_message_scroll' => '1',
                    'submit_text_processing' => __('Sending...', 'wpforms'),
                ),
                'meta' => array(
                    'template' => $this->slug,
                ),
            );
        }

    }

    /**
     * Conditional to determine if the template informational modal screens
     * should display.
     *
     * @since 1.0.0
     *
     * @param array $form_data
     *
     * @return boolean
     */
    function template_modal_conditional($form_data)
    {

        // If we do not have provider data, then we can assume a provider
        // method has not yet been configured, so we display the modal to
        // remind the user they need to set it up for the form to work
        // correctly.
        if (empty($form_data['providers'])) {
            return true;
        } else {
            return false;
        }
    }
}

new WPForms_Template_Review;
