<?php

/**
 * Setup panel.
 *
 * @package    Infactform
 * @author     Infact
 * @since      1.0.0
 */
class WPForms_Builder_Panel_Setup extends WPForms_Builder_Panel {

	/**
	 * All systems go.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Define panel information
		$this->name  = __( 'Setup', 'wpforms' );
		$this->slug  = 'setup';
		$this->icon  = 'fa-cog';
		$this->order = 5;
	}

	/**
	 * Enqueue assets for the builder.
	 *
	 * @since 1.0.0
	 */
	public function enqueues() {

		// CSS
		wp_enqueue_style(
			'wpforms-builder-setup',
			WPFORMS_PLUGIN_URL . 'assets/css/admin-builder-setup.css',
			null,
			WPFORMS_VERSION
		);
	}

	/**
	 * Outputs the Settings panel primary content.
	 *
	 * @since 1.0.0
	 */
	public function panel_content() {

		$core_templates       = apply_filters( 'wpforms_form_templates_core', array() );
		$additional_templates = apply_filters( 'wpforms_form_templates', array() );
		$additional_count     = count( $additional_templates );
		?>
      

		<?php $this->template_select_options( $core_templates, 'core' ); ?>


		<?php
		do_action( 'wpforms_setup_panel_after' );
	}

	/**
	 * Generate a block of templates to choose from.
	 *
	 * @since 1.4.0
	 *
	 * @param array $templates
	 * @param string $slug
	 */
	public function template_select_options( $templates, $slug ) {

		if ( ! empty( $templates ) ) {

			echo '<div id="wpforms-setup-templates-' . $slug . '" class="wpforms-setup-templates ' . $slug . ' wpforms-clear">';
            echo '<h4>'.__('Choose a template of new form, up to your purpose!','wpforms').'.</h4>';
			echo '<div class="list">';

			// Loop through each available template.
			foreach ( $templates as $template ) {

				$selected = ! empty( $this->form_data['meta']['template'] ) && $this->form_data['meta']['template'] === $template['slug'] ? true : false;
				?>
                <div class="wpforms-template <?php echo $selected ? 'selected' : ''; ?>"
                     id="wpforms-template-<?php echo sanitize_html_class( $template['slug'] ); ?>">

                    <div class="wpforms-template-inner">

                        <div class="wpforms-template-name wpforms-clear">
							<?php echo esc_html( $template['name'] ); ?>
							<?php echo $selected ? '<span class="selected">' . __( 'Selected', 'wpforms' ) . '</span>' : ''; ?>
                        </div>

						<?php if ( ! empty( $template['description'] ) ) : ?>
                            <div class="wpforms-template-details">
                                <p class="desc"><?php echo esc_html( $template['description'] ); ?></p>
                            </div>
						<?php endif; ?>

                        <div class="wpforms-template-overlay">
                            <a href="#" class="wpforms-template-select"
                               data-template-name-raw="<?php echo esc_attr( $template['name'] ); ?>"
                               data-template-name="<?php printf( _x( '%s template', 'Template name', 'wpforms' ), esc_attr( $template['name'] ) ); ?>"
                               data-template="<?php echo esc_attr( $template['slug'] ); ?>"><?php printf( _x( 'Create a %s', 'Template name', 'wpforms' ), esc_html( $template['name'] ) ); ?></a>
                        </div>

                    </div>

                </div>
				<?php
			}

			echo '</div>';

			echo '</div>';
		}
	}
}

new WPForms_Builder_Panel_Setup;
