;(function($) {

	var s;

	var WPFormsBuilder = {

		settings: {
			spinner: '<i class="fa fa-spinner fa-spin"></i>',
			spinnerInline: '<i class="fa fa-spinner fa-spin wpforms-loading-inline"></i>',
			pagebreakTop: false,
			pagebreakBottom: false
		},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			wpforms_panel_switch = true;
			s = this.settings;

			// Document ready
			$(document).ready(WPFormsBuilder.ready);

			// Page load
			$(window).on('load', WPFormsBuilder.load);

			WPFormsBuilder.bindUIActions();
		},

		/**
		 * Page load.
		 *
		 * @since 1.0.0
		 */
		load: function() {

			// Remove Loading overlay
			$('#wpforms-builder-overlay').fadeOut();

			// Maybe display informational informational modal
			if ( wpforms_builder.template_modal_display == '1' && 'fields' == wpf.getQueryString('view') ) {
				$.alert({
					title: wpforms_builder.template_modal_title,
					content: wpforms_builder.template_modal_msg,
					icon: 'fa fa-info-circle',
					type: 'blue',
					buttons: {
						confirm: {
							text: wpforms_builder.close,
							btnClass: 'btn-confirm',
							keys: ['enter']
						}
					}
				})
			}
		},

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready: function() {

			// Trigger initial save for new forms
			var newForm = wpf.getQueryString('newform');
			if (newForm) {
				WPFormsBuilder.formSave(false);
			}

			// Setup/cache some vars not available before
			s.formID          = $('#wpforms-builder-form').data('id');
			s.pagebreakTop    = $('.wpforms-pagebreak-top').length;
			s.pagebreakBottom = $('.wpforms-pagebreak-bottom').length;
			s.templateList    = new List('wpforms-setup-templates-additional', {
				valueNames: [ 'wpforms-template-name' ]
			});

			// If there is a section configured, display it. Otherwise
			// we show the first panel by default.
			$('.wpforms-panel').each(function(index, el) {
				var $this       = $(this);
					$configured = $this.find('.wpforms-panel-sidebar-section.configured').first();

				if ( $configured.length ) {
					var section = $configured.data('section');
					$configured.addClass('active').find('.wpforms-toggle-arrow').toggleClass('fa-angle-down fa-angle-right');
					$this.find('.wpforms-panel-content-section-'+section).show().addClass('active');
				} else {
					$this.find('.wpforms-panel-content-section:first-of-type').show().addClass('active');
					$this.find('.wpforms-panel-sidebar-section:first-of-type').addClass('active').find('.wpforms-toggle-arrow').toggleClass('fa-angle-down fa-angle-right');
				}
			});

			// Drag and drop sortable elements
			WPFormsBuilder.fieldSortable();
			WPFormsBuilder.fieldChoiceSortable('select');
			WPFormsBuilder.fieldChoiceSortable('radio');
			WPFormsBuilder.fieldChoiceSortable('checkbox');
			WPFormsBuilder.fieldChoiceSortable('payment-multiple');
			WPFormsBuilder.fieldChoiceSortable('payment-select');

			// Load match heights
			$('.wpforms-setup-templates.core .wpforms-template-inner').matchHeight({
				property: 'min-height',
				byRow: false
			});
			$('.wpforms-setup-templates.additional .wpforms-template-inner').matchHeight({
				property: 'min-height',
				byRow: false
			});

			// Set field group visibility
			$('.wpforms-add-fields-group').each(function(index, el) {
				WPFormsBuilder.fieldGroupToggle($(this),'load');
			});

			// Trim long form titles
			WPFormsBuilder.trimFormTitle();

			// Load Tooltips
			WPFormsBuilder.loadTooltips();

			// Load Tooltips
			WPFormsBuilder.loadColorPickers();

			// Hide/Show reCAPTCHA in form
			WPFormsBuilder.recaptchaToggle();

			// Confirmation settings
			WPFormsBuilder.confirmationToggle();

			// Notification settings
			WPFormsBuilder.notificationToggle();

			// Secret builder hotkeys.
			WPFormsBuilder.builderHotkeys();

			// Clone form title to setup page
			$('#wpforms-setup-name').val($('#wpforms-panel-field-settings-form_title').val());

			// jquery-confirmd defaults
			jconfirm.defaults = {
				closeIcon: true,
				backgroundDismiss: true,
				escapeKey: true,
				animationBounce: 1,
				useBootstrap: false,
				theme: 'modern',
				boxWidth: '400px'
			};
		},

		/**
		 * Element bindings.
		 *
		 * @since 1.0.0
		 */
		bindUIActions: function() {

			// General Panels
			WPFormsBuilder.bindUIActionsPanels();

			// Setup Panel
			WPFormsBuilder.bindUIActionsSetup();

			// Fields Panel
			WPFormsBuilder.bindUIActionsFields();

			// Settings Panel
			WPFormsBuilder.bindUIActionsSettings();

			// Save and Exit
			WPFormsBuilder.bindUIActionsSaveExit();

			// General/ global
			WPFormsBuilder.bindUIActionsGeneral();
		},

		//--------------------------------------------------------------------//
		// General Panels
		//--------------------------------------------------------------------//

		/**
		 * Element bindings for general panel tasks.
		 *
		 * @since 1.0.0
		 */
		bindUIActionsPanels: function() {

			// Panel switching
			$(document).on('click', '#wpforms-panels-toggle button, .wpforms-panel-switch', function(e) {
				e.preventDefault();
				WPFormsBuilder.panelSwitch($(this).data('panel'));
			});

			// Panel sections switching
			$(document).on('click', '.wpforms-panel .wpforms-panel-sidebar-section', function(e) {
				WPFormsBuilder.panelSectionSwitch(this, e);
			});
		},

		/**
		 * Switch Panels.
		 *
		 * @since 1.0.0
		 */
		panelSwitch: function(panel) {

			var $panel    = $('#wpforms-panel-'+panel),
				$panelBtn = $('.wpforms-panel-'+panel+'-button');

			if (!$panel.hasClass('active')) {

				$(document).trigger('wpformsPanelSwitch', panel);

				if (!wpforms_panel_switch) {
					return false;
				}

				$('#wpforms-panels-toggle').find('button').removeClass('active');
				$('.wpforms-panel').removeClass('active');
				$panelBtn.addClass('active');
				$panel.addClass('active');

				history.replaceState({}, null, wpf.updateQueryString('view', panel));
			}
		},

		/**
		 * Switch Panel section.
		 *
		 * @since 1.0.0
		 */
		panelSectionSwitch: function(el, e) {
			e.preventDefault();

			var $this           = $(el),
				$panel          = $this.parent().parent(),
				section         = $this.data('section'),
				$sectionButtons = $panel.find('.wpforms-panel-sidebar-section'),
				$sectionButton  = $panel.find('.wpforms-panel-sidebar-section-'+section);

			if ( ! $sectionButton.hasClass('active') ) {
				$sectionButtons.removeClass('active');
				$sectionButtons.find('.wpforms-toggle-arrow').removeClass('fa-angle-down').addClass('fa-angle-right');
				$sectionButton.addClass('active');
				$sectionButton.find('.wpforms-toggle-arrow').toggleClass('fa-angle-right fa-angle-down');
				$panel.find('.wpforms-panel-content-section').hide();
				$panel.find('.wpforms-panel-content-section-'+section).show();
			}
		},

		//--------------------------------------------------------------------//
		// Setup Panel
		//--------------------------------------------------------------------//

		/**
		 * Element bindings for Setup panel.
		 *
		 * @since 1.0.0
		 */
		bindUIActionsSetup: function() {

			// Focus on the form title field when displaying setup panel
			$(window).load(function(e) {
				WPFormsBuilder.setupTitleFocus(e, wpf.getQueryString('view'));
			});
			$(document).on('wpformsPanelSwitch', WPFormsBuilder.setupTitleFocus);

			// Select and apply a template
			$(document).on('click', '.wpforms-template-select', function(e) {
				WPFormsBuilder.templateSelect(this, e);
			});

			// "Blank form" text should trigger template selection
			$(document).on('click', '.wpforms-trigger-blank', function(e) {
				e.preventDefault();
				$('#wpforms-template-blank .wpforms-template-select').trigger('click');
			});

			// Keep Setup title and settings title instances the same
			$(document).on('input ', '#wpforms-panel-field-settings-form_title', function() {
				$('#wpforms-setup-name').val($('#wpforms-panel-field-settings-form_title').val());
			});
			$(document).on('input', '#wpforms-setup-name', function() {
				$('#wpforms-panel-field-settings-form_title').val($('#wpforms-setup-name').val());
			});

			// Additional template searching
			$(document).on('keyup', '#wpforms-setup-template-search' , function() {
				s.templateList.search( $(this).val() );
			});
		},

		/**
		 * Force focus on the form title field when the Setup panel is displaying.
		 *
		 * @since 1.0.0
		 */
		setupTitleFocus: function(e, view) {

			if (typeof view !== 'undefined' && view == 'setup') {
				setTimeout(function (){
					$('#wpforms-setup-name').focus();
				}, 100);
			}
		},

		/**
		 * Select template.
		 *
		 * @since 1.0.0
		 */
		templateSelect: function(el, e) {
			e.preventDefault();

			var $this         = $(el),
				$parent       = $this.parent().parent();
				$formName     = $('#wpforms-setup-name'),
				$templateBtns = $('.wpforms-template-select'),
				formName      = '',
				labelOriginal = $this.html();
                template      = $this.data('template'),
                templateName  = $this.data('template-name-raw'),
				title         = '',
				action        = '';

			// Don't do anything for selects that trigger modal
			if ($parent.hasClass('pro-modal')){
				return;
			}

			// Disable all template buttons
			$templateBtns.prop('disabled', true);

			// Display loading indicator
			$this.html(s.spinner+' '+ wpforms_builder.loading);

			// This is an existing form
			if (s.formID) {

				$.confirm({
					title: wpforms_builder.heads_up,
					content: wpforms_builder.template_confirm,
					backgroundDismiss: false,
					closeIcon: false,
					icon: 'fa fa-exclamation-circle',
					type: 'orange',
					buttons: {
						confirm: {
							text: wpforms_builder.ok,
							btnClass: 'btn-confirm',
							action: function(){
								// Ajax update form
								var data = {
									title   : $formName.val(),
									action  : 'wpforms_update_form_template',
									template: template,
									form_id : s.formID,
									nonce   : wpforms_builder.nonce
								}
								$.post(wpforms_builder.ajax_url, data, function(res) {
									if (res.success){
										window.location.href = res.data.redirect;
									} else {
										console.log(res);
									}
								}).fail(function(xhr, textStatus, e) {
									console.log(xhr.responseText);
								});
							}
						},
						cancel: {
							text: wpforms_builder.cancel,
							action: function(){
								$templateBtns.prop('disabled', false);
								$this.html(labelOriginal);
							}
						}
					}
				});

			// This is a new form
			} else {

				// Check that form title is provided
				if (!$formName.val()) {
					formName = templateName;
				} else {
					formName = $formName.val();
				}

				// Ajax create new form
				var data = {
					title   : formName,
					action  : 'wpforms_new_form',
					template: template,
					form_id : s.formID,
					nonce   : wpforms_builder.nonce
				}
				$.post(wpforms_builder.ajax_url, data, function(res) {
					if (res.success){
						window.location.href = res.data.redirect;
					} else {
						console.log(res);
					}
				}).fail(function(xhr, textStatus, e) {
					console.log(xhr.responseText);
				});
			}
		},


		//--------------------------------------------------------------------//
		// Fields Panel
		//--------------------------------------------------------------------//

		/**
		 * Element bindings for Fields panel.
		 *
		 * @since 1.0.0
		 */
		bindUIActionsFields: function() {

			// Field sidebar tab toggle
			$(document).on('click', '.wpforms-tab a', function(e) {
				e.preventDefault();
				WPFormsBuilder.fieldTabToggle($(this).parent().attr('id'));
			});

			// Field sidebar group toggle
			$(document).on('click', '.wpforms-add-fields-heading', function(e) {
				e.preventDefault();
				WPFormsBuilder.fieldGroupToggle($(this), 'click');
			});

			// Form field preview clicking
			$(document).on('click', '.wpforms-field', function(e) {
				WPFormsBuilder.fieldTabToggle($(this).data('field-id'));
			});

			// Field delete
			$(document).on('click', '.wpforms-field-delete', function(e) {
				e.preventDefault();
				e.stopPropagation();
				WPFormsBuilder.fieldDelete($(this).parent().data('field-id'));
			});

			// Field duplicate
			$(document).on('click', '.wpforms-field-duplicate', function(e) {
				e.preventDefault();
				WPFormsBuilder.fieldDuplicate($(this).parent().data('field-id'));
			});

			// Field add
			$(document).on('click', '.wpforms-add-fields-button', function(e) {
				e.preventDefault();
				WPFormsBuilder.fieldAdd($(this).data('field-type'));
			});

			// New field choices should be sortable
			$(document).on('wpformsFieldAdd', function(event, id, type) {
				if (type === 'select' || type === 'radio'  || type === 'checkbox' || type === 'payment-multiple' || type === 'payment-select' ) {
					WPFormsBuilder.fieldChoiceSortable(type,'#wpforms-field-option-row-' + id + '-choices ul');
				}
			 });

			// Field choice add new
			$(document).on('click', '.wpforms-field-option-row-choices .add', function(e) {
				WPFormsBuilder.fieldChoiceAdd(e, $(this));
			});

			// Field choice delete
			$(document).on('click', '.wpforms-field-option-row-choices .remove', function(e) {
				WPFormsBuilder.fieldChoiceDelete(e, $(this));
			});

			// Field choices defaults
			$(document).on('change', '.wpforms-field-option-row-choices input[type=radio]', function(e) {
				var $this = $(this),
					list  = $this.parent().parent();
				$this.parent().parent().find('input[type=radio]').not(this).prop('checked',false);
				WPFormsBuilder.fieldChoiceUpdate(list.data('field-type'),list.data('field-id') );
			});

			// Field choices update preview area
			$(document).on('change', '.wpforms-field-option-row-choices input[type=checkbox]', function(e) {
				var list = $(this).parent().parent();
				WPFormsBuilder.fieldChoiceUpdate(list.data('field-type'),list.data('field-id') );
			});

			// Field choices display value toggle
			$(document).on('change', '.wpforms-field-option-row-show_values input', function(e) {
				$(this).closest('.wpforms-field-option').find('.wpforms-field-option-row-choices ul').toggleClass('show-values');
			});

			// Updates field choices text in almost real time
			$(document).on('focusout', '.wpforms-field-option-row-choices input.label', function(e) {
				var list = $(this).parent().parent();
				WPFormsBuilder.fieldChoiceUpdate(list.data('field-type'),list.data('field-id'));
			});

			// Field Choices Bulk Add
			$(document).on('click', '.toggle-bulk-add-display', function(e) {
				e.preventDefault();
				WPFormsBuilder.fieldChoiceBulkAddToggle(this);
			});
			$(document).on('click', '.toggle-bulk-add-presets', function(e) {
				e.preventDefault();
				var $presetList = $(this).closest('.bulk-add-display').find('ul');
				if ( $presetList.css('display') === 'block' ) {
					$(this).text(wpforms_builder.bulk_add_presets_show);
				} else {
					$(this).text(wpforms_builder.bulk_add_presets_hide);
				}
				$presetList.slideToggle();
			});
			$(document).on('click', '.bulk-add-preset-insert', function(e) {
				e.preventDefault();
				var $this         = $(this),
					preset        = $this.data('preset'),
					$container    = $this.closest('.bulk-add-display'),
					$presetList   = $container.find('ul'),
					$presetToggle = $container.find('.toggle-bulk-add-presets'),
					$textarea     = $container.find('textarea');
				$textarea.val('');
				$textarea.insertAtCaret(wpforms_preset_choices[preset].choices.join("\n"));
				$presetToggle.text(wpforms_builder.bulk_add_presets_show);
				$presetList.slideUp();
			});
			$(document).on('click', '.bulk-add-insert', function(e) {
				e.preventDefault();
				WPFormsBuilder.fieldChoiceBulkAddInsert(this);
			});

			// Field Options group toggle
			$(document).on('click', '.wpforms-field-option-group-toggle', function(e) {
				e.preventDefault();
				var $this = $(this);
				$this.parent().toggleClass('wpforms-hide').find('.wpforms-field-option-group-inner').slideToggle();
				$this.find('i').toggleClass('fa-angle-down fa-angle-right');
			});

			// Display toggle for Address field hide address line 2 option
			$(document).on('change', '.wpforms-field-option-address input.hide', function(e) {
				var $this    = $(this),
					id       = $this.parent().parent().data('field-id'),
					subfield = $this.parent().parent().data('subfield');
				$('#wpforms-field-'+id).find('.wpforms-'+subfield).toggleClass('wpforms-hide');
			});

			// Real-time updates for "Show Label" field option
			$(document).on('input', '.wpforms-field-option-row-label input', function(e) {
				var $this = $(this),
					value = $this.val(),
					id    = $this.parent().data('field-id');
				$('#wpforms-field-'+id).find('.label-title .text').text(value);
			});

			// Real-time updates for "Description" field option
			$(document).on('input', '.wpforms-field-option-row-description textarea', function(e) {
				var $this = $(this),
					value = $this.val(),
					id    = $this.parent().data('field-id');
				$('#wpforms-field-'+id).find('.description').html(value);
			});

			// Real-time updates for "Required" field option
			$(document).on('change', '.wpforms-field-option-row-required input', function(e) {
				var id = $(this).parent().data('field-id');
				$('#wpforms-field-'+id).toggleClass('required');
			});

			// Real-time updates for "Confirmation" field option
			$(document).on('change', '.wpforms-field-option-row-confirmation input', function(e) {
				var id = $(this).parent().data('field-id');
				$('#wpforms-field-'+id).find('.wpforms-confirm').toggleClass('wpforms-confirm-enabled wpforms-confirm-disabled');
				$('#wpforms-field-option-'+id).toggleClass('wpforms-confirm-enabled wpforms-confirm-disabled');
			});

			// Real-time updates for "Size" field option
			$(document).on('change', '.wpforms-field-option-row-size select', function(e) {
				var $this = $(this),
					value = $this.val(),
					id    = $this.parent().data('field-id');
				$('#wpforms-field-'+id).removeClass('size-small size-medium size-large').addClass('size-'+value);
			});

			// Real-time updates for "Placeholder" field option
			$(document).on('input', '.wpforms-field-option-row-placeholder input', function(e) {
				var $this   = $(this),
					value   = $this.val(),
					id      = $this.parent().data('field-id'),
					$primary = $('#wpforms-field-'+id).find('.primary-input');

				if ($primary.is('select')) {
					if (!value.length) {
						$primary.find('.placeholder').remove();
					} else {
						if ($primary.find('.placeholder').length) {
							$primary.find('.placeholder').text(value);
						} else {
							$primary.prepend('<option class="placeholder" selected>'+value+'</option>')
						}
					}
				} else {
					$primary.attr('placeholder', value);
				}
			});

			// Real-time updates for "Confirmation Placeholder" field option
			$(document).on('input', '.wpforms-field-option-row-confirmation_placeholder input', function(e) {
				var $this   = $(this),
					value   = $this.val(),
					id      = $this.parent().data('field-id');
				$('#wpforms-field-'+id).find('.secondary-input').attr('placeholder', value);
			});

			// Real-time updates for "Hide Label" field option
			$(document).on('change', '.wpforms-field-option-row-label_hide input', function(e) {
				var id = $(this).parent().data('field-id');
				$('#wpforms-field-'+id).toggleClass('label_hide');
			});

			// Real-time updates for Sub Label visbility field option
			$(document).on('change', '.wpforms-field-option-row-sublabel_hide input', function(e) {
				var id = $(this).parent().data('field-id');
				$('#wpforms-field-'+id).toggleClass('sublabel_hide');
			});

			// Real-time updates for Date/Time and Name "Format" option
			$(document).on('change', '.wpforms-field-option-row-format select', function(e) {
				var $this = $(this),
					value = $this.val(),
					id    = $this.parent().data('field-id');
				$('#wpforms-field-'+id).find('.format-selected').removeClass().addClass('format-selected format-selected-'+value);
				$('#wpforms-field-option-'+id).find('.format-selected').removeClass().addClass('format-selected format-selected-'+value);
			})

			// Real-time updates specific for Address "Scheme" option
			$(document).on('change', '.wpforms-field-option-row-scheme select', function(e) {
				var $this = $(this),
					value = $this.val(),
					id    = $this.parent().data('field-id');
				$('#wpforms-field-'+id).find('.wpforms-address-scheme').addClass('wpforms-hide');
				$('#wpforms-field-'+id).find('.wpforms-address-scheme-'+value).removeClass('wpforms-hide');

				if ( $('#wpforms-field-'+id).find('.wpforms-address-scheme-'+value+' .wpforms-country' ).children().length == 0 ) {
					$('#wpforms-field-option-'+id).find('.wpforms-field-option-row-country').addClass('wpforms-hidden');
				} else {
					$('#wpforms-field-option-'+id).find('.wpforms-field-option-row-country').removeClass('wpforms-hidden');
				}
			})

			// Real-time updates for Address, Date/Time, and Name "Placeholder" field options
			$(document).on('input', '.wpforms-field-option .format-selected input.placeholder, .wpforms-field-option-address input.placeholder', function(e) {
				var $this    = $(this),
					value    = $this.val(),
					id       = $this.parent().parent().data('field-id'),
					subfield = $this.parent().parent().data('subfield');
				$('#wpforms-field-'+id).find('.wpforms-'+ subfield+' input' ).attr('placeholder', value);
			});

			// Real-time updates for Date/Time date type
			$(document).on('change', '.wpforms-field-option-row-date .type select', function(e) {
				var $this = $(this),
					value = $this.val(),
					id    = $(this).parent().parent().data('field-id');
				$('#wpforms-field-'+id).find('.wpforms-date').toggleClass('wpforms-date-type-datepicker wpforms-date-type-dropdown');
				$('#wpforms-field-option-'+id).toggleClass('wpforms-date-type-datepicker wpforms-date-type-dropdown');
			});

			// Real-time updates for Date/Time date select format
			$(document).on('change', '.wpforms-field-option-row-date .format select', function(e) {
				var $this = $(this),
					value = $this.val(),
					id    = $(this).parent().parent().data('field-id');
				if ( value === 'm/d/Y' ) {
					$('#wpforms-field-'+id).find('.wpforms-date-dropdown .first option').text(wpforms_builder.date_select_month);
					$('#wpforms-field-'+id).find('.wpforms-date-dropdown .second option').text(wpforms_builder.date_select_day);
				} else if ( value == 'd/m/Y' ) {
					$('#wpforms-field-'+id).find('.wpforms-date-dropdown .first option').text(wpforms_builder.date_select_day);
					$('#wpforms-field-'+id).find('.wpforms-date-dropdown .second option').text(wpforms_builder.date_select_month);
				}
			});

			// Consider the field active when a disabled nav button is clicked
			$(document).on('click', '.wpforms-pagebreak-button', function(e) {
				e.preventDefault();
				$(this).closest('.wpforms-field').trigger('click');
			});

			// Real-time updates for "Next" and "Prev" pagebreak field option
			$(document).on('input', '.wpforms-field-option-row-next input', function(e) {
				var $this = $(this),
					value = $this.val(),
					id    = $this.parent().data('field-id');
				if (value) {
					$('#wpforms-field-'+id).find('.wpforms-pagebreak-next').css('display','inline-block').text(value);
				} else {
					$('#wpforms-field-'+id).find('.wpforms-pagebreak-next').css('display','none').empty();
				}
			});
			$(document).on('input', '.wpforms-field-option-row-prev input', function(e) {
				var $this = $(this),
					value = $this.val(),
					id    = $this.parent().data('field-id');
				if (value) {
					$('#wpforms-field-'+id).find('.wpforms-pagebreak-prev').css('display','inline-block').text(value);
				} else {
					$('#wpforms-field-'+id).find('.wpforms-pagebreak-prev').css('display','none').empty();
				}
			});

			// Real-time updates for "Page Title" pagebreak field option
			$(document).on('input', '.wpforms-field-option-row-title input', function(e) {
				var $this = $(this),
					value = $this.val(),
					id    = $this.parent().data('field-id');
				if (value) {
					$('#wpforms-field-'+id).find('.wpforms-pagebreak-title').text('('+value+')');
				} else {
					$('#wpforms-field-'+id).find('.wpforms-pagebreak-title').empty();
				}
			});

			// Real-time updates for "Page Navigation Alignment" pagebreak field option
			$(document).on('change', '.wpforms-field-option-row-nav_align select', function(e) {
				var $this = $(this),
					value = $this.val();
				if (!value) {
					value = 'center';
				}
				$('.wpforms-pagebreak-buttons').removeClass('wpforms-pagebreak-buttons-center wpforms-pagebreak-buttons-left wpforms-pagebreak-buttons-right wpforms-pagebreak-buttons-split').addClass('wpforms-pagebreak-buttons-'+value);
			});

			// Real-time updates for "Display Previous" pagebreak field option
			$(document).on('change', '.wpforms-field-option-row-prev_toggle input', function(e) {
				var $this      = $(this),
					$group     = $this.closest('.wpforms-field-option-group-inner'),
					$prev      = $group.find('.wpforms-field-option-row-prev'),
					$prevLabel = $prev.find('input');

				$prev.toggleClass('wpforms-hidden');

				if ( $(this).prop('checked') && !$prevLabel.val() ) {
					$prevLabel.val(wpforms_builder.previous);
				} else {
					$prevLabel.val('');
				}
				$prevLabel.trigger('input');
			});

			// Real-time updates for Single Item field "Item Price" option
			$(document).on('input', '.wpforms-field-option-row-price input', function(e) {
				var $this      = $(this),
					value      = $this.val(),
					id         = $this.parent().data('field-id'),
					sanitized  = wpf.amountSanitize(value),
					formatted  = wpf.amountFormat(sanitized),
					singleItem;
				if ( wpforms_builder.currency_symbol_pos == 'right' ) {
					singleItem = formatted+' '+wpforms_builder.currency_symbol;
				} else {
					singleItem = wpforms_builder.currency_symbol+' '+formatted;
				}
				$('#wpforms-field-'+id).find('.primary-input').val(formatted);
				$('#wpforms-field-'+id).find('.price').text(singleItem);
			});

			// Real-time updates for payment CC icons
			$(document).on('change', '.wpforms-field-option-credit-card .payment-icons input', function(e) {
				var $this = $(this),
					card  = $this.data('card')
					id    = $this.parent().data('field-id');
				$('#wpforms-field-'+id).find('img.icon-'+card).toggleClass('card_hide');
			});

			// Generic updates for various additional placeholder fields
			$(document).on('input', '.wpforms-field-option input.placeholder-update', function(e) {
				var $this    = $(this),
					value    = $this.val(),
					id       = $this.data('field-id'),
					subfield = $this.data('subfield');
				$('#wpforms-field-'+id).find('.wpforms-'+ subfield+' input' ).attr('placeholder', value);
			});

			// Toggle Choice Layout advanced field option
			$(document).on('change', '.wpforms-field-option-row-input_columns select', function(e) {
				var $this    = $(this),
					value    = $this.val(),
					cls      = '',
					id       = $this.parent().data('field-id');
				if ( value === '2' ) {
					cls = 'wpforms-list-2-columns';
				} else if ( value === '3' ) {
					cls = 'wpforms-list-3-columns';
				}
				$('#wpforms-field-'+id).removeClass('wpforms-list-2-columns wpforms-list-3-columns').addClass(cls);
			});

			// Toggle the toggle field
			$(document).on('click', '.wpforms-field-option-row .wpforms-toggle-icon', function(e) {
				var $this  = $(this),
					$check = $this.find('input[type=checkbox]'),
					$label = $this.find('.wpforms-toggle-icon-label');

				$this.toggleClass('wpforms-off wpforms-on');
				$this.find('i').toggleClass('fa-toggle-off fa-toggle-on');

				if ($this.hasClass('wpforms-on')) {
					$label.text(wpforms_builder.on);
					$check.prop('checked', true);
				} else {
					$label.text(wpforms_builder.off);
					$check.prop('checked', false);
				}
				$check.trigger('change');
			});

			// Watch for pagebreak field being added and deleted
			$(document).on('wpformsFieldAdd', WPFormsBuilder.fieldPagebreakAdd);
			$(document).on('wpformsFieldDelete', WPFormsBuilder.fieldPagebreakDelete);

			// Real-time updates for "Dynamic Choices" field option, for Dropdown,
			// Checkboxes, and Multiple choice fields
			$(document).on('change', '.wpforms-field-option-row-dynamic_choices select', function(e) {
				WPFormsBuilder.fieldDynamicChoiceToggle($(this));
			});

			// Real-time updates for "Dynamic [type] Source" field option, for Dropdown,
			// Checkboxes, and Multiple choice fields
			$(document).on('change', '.wpforms-field-option-row-dynamic_taxonomy select, .wpforms-field-option-row-dynamic_post_type select', function(e) {
				WPFormsBuilder.fieldDynamicChoiceSource($(this));
			});

			// Toggle Layout selector
			$(document).on('click', '.toggle-layout-selector-display', function(e) {
				e.preventDefault();
				WPFormsBuilder.fieldLayoutSelectorToggle(this);
			});
			$(document).on('click', '.layout-selector-display-layout', function(e) {
				e.preventDefault();
				WPFormsBuilder.fieldLayoutSelectorLayout(this);
			});
			$(document).on('click', '.layout-selector-display-columns span', function(e) {
				e.preventDefault();
				WPFormsBuilder.fieldLayoutSelectorInsert(this);
			});
		},

		/**
		 * Toggle field group visibility in the field sidebar.
		 *
		 * @since 1.0.0
		 */
		fieldGroupToggle: function(el, action) {

			if ( 'click' === action ) {

				var $this      = $(el),
					$buttons   = $this.next('.wpforms-add-fields-buttons'),
					$group     = $buttons.parent(),
					$icon      = $this.find('i'),
					groupName  = $this.data('group'),
					cookieName = 'wpforms_field_group_'+groupName;

				if ($group.hasClass('wpforms-hide')) {
					wpCookies.remove(cookieName);
				} else {
					wpCookies.set(cookieName,'true',2592000); // 1 month
				}
				$icon.toggleClass('fa-angle-down fa-angle-right');
				$buttons.slideToggle();
				$group.toggleClass('wpforms-hide');

			} else if ( 'load' === action ) {

				var $this      = $(el),
					$buttons   = $this.find('.wpforms-add-fields-buttons'),
					$icon      = $this.find('.wpforms-add-fields-heading i'),
					groupName  = $this.find('.wpforms-add-fields-heading').data('group'),
					cookieName = 'wpforms_field_group_'+groupName;

				if (wpCookies.get(cookieName) == 'true') {
					$icon.toggleClass('fa-angle-down fa-angle-right');
					$buttons.hide();
					$this.toggleClass('wpforms-hide');
				}
			}
		},

		/**
		 * Delete field
		 *
		 * @since 1.0.0
		 */
		fieldDelete: function(id) {

			var $field = $('#wpforms-field-'+id),
				type   = $field.data('field-type');

			if ($field.hasClass('no-delete')) {
				$.alert({
					title: wpforms_builder.field_locked,
					content: wpforms_builder.field_locked_msg,
					icon: 'fa fa-info-circle',
					type: 'blue',
					buttons: {
						confirm: {
							text: wpforms_builder.close,
							btnClass: 'btn-confirm',
							keys: ['enter']
						}
					}
				});
			} else {
				$.confirm({
					title: false,
					content: wpforms_builder.delete_confirm,
					backgroundDismiss: false,
					closeIcon: false,
					icon: 'fa fa-exclamation-circle',
					type: 'orange',
					buttons: {
						confirm: {
							text: wpforms_builder.ok,
							btnClass: 'btn-confirm',
							keys: ['enter'],
							action: function(){
								$('#wpforms-field-'+id).fadeOut(400, function(){
									$(this).remove();
									$('#wpforms-field-option-'+id).remove();
									$('.wpforms-field, .wpforms-title-desc').removeClass('active');
									WPFormsBuilder.fieldTabToggle('add-fields');
									$(document).trigger('wpformsFieldDelete', [id, type ]);
								});
							}
						},
						cancel: {
							text: wpforms_builder.cancel
						}
					}
				});
			}
		},

		/**
		 * Duplicate field
		 *
		 * @since 1.2.9
		 */
		fieldDuplicate: function(id) {

			var $field = $('#wpforms-field-'+id),
				type   = $field.data('field-type');

			if ($field.hasClass('no-duplicate')) {
				$.alert({
					title: wpforms_builder.field_locked,
					content: wpforms_builder.field_locked_msg,
					icon: 'fa fa-info-circle',
					type: 'blue',
					buttons : {
						confirm : {
							text: wpforms_builder.close,
							btnClass: 'btn-confirm',
							keys: ['enter']
						}
					}
				});
			} else {
				$.confirm({
					title: false,
					content: wpforms_builder.duplicate_confirm,
					backgroundDismiss: false,
					closeIcon: false,
					icon: 'fa fa-exclamation-circle',
					type: 'orange',
					buttons: {
						confirm: {
							text: wpforms_builder.ok,
							btnClass: 'btn-confirm',
							keys: ['enter'],
							action: function(){
								var $newField            = $field.clone(),
									$fieldOptions        = $('#wpforms-field-option-'+id),
									newFieldOptions 	 = $fieldOptions.html(),
									newFieldID           = $('#wpforms-field-id').val(),
									newFieldLabel        = $('#wpforms-field-option-'+id+'-label').val()+' '+wpforms_builder.duplicate_copy,
									nextID               = Number(newFieldID)+1,
									regex_fieldOptionsID = new RegExp( 'ID #'+id, "g"),
									regex_fieldID        = new RegExp( 'fields\\['+id+'\\]', "g"),
									regex_dataFieldID    = new RegExp( 'data-field-id="'+id+'"', "g"),
									regex_referenceID    = new RegExp( 'data-reference="'+id+'"', "g"),
									regex_elementID      = new RegExp( '\\b(id|for)="wpforms-(.*?)'+id+'(.*?)"', "ig");

								// Toggle visibility states
								$field.after($newField);
								$field.removeClass('active');
								$newField.addClass('active').attr({
									'id'           : 'wpforms-field-'+newFieldID,
									'data-field-id': newFieldID
								});

								// Various regex to adjust the field options to work with
								// the new field ID
								function regex_elementID_replace(match, p1, p2, p3, offset, string) {
									return p1+'="wpforms-'+p2+newFieldID+p3+'"';
								}
								newFieldOptions = newFieldOptions.replace(regex_fieldOptionsID, 'ID #'+newFieldID);
								newFieldOptions = newFieldOptions.replace(regex_fieldID, 'fields['+newFieldID+']');
								newFieldOptions = newFieldOptions.replace(regex_dataFieldID, 'data-field-id="'+newFieldID+'"');
								newFieldOptions = newFieldOptions.replace(regex_referenceID, 'data-reference="'+newFieldID+'"');
								newFieldOptions = newFieldOptions.replace(regex_elementID, regex_elementID_replace);

								// Add new field options panel
								$fieldOptions.hide().after('<div class="wpforms-field-option wpforms-field-option-'+type+'" id="wpforms-field-option-'+newFieldID+'" data-field-id="'+newFieldID+'">'+newFieldOptions+'</div>');
								var $newFieldOptions = $('#wpforms-field-option-'+newFieldID);

								// Copy over values
								$fieldOptions.find(':input').each(function(index, el) {
									var $this = $(this);
										name    = $this.attr('name'),
										newName = name.replace(regex_fieldID, 'fields['+newFieldID+']'),
										type    = $this.attr('type');

									if ( type === 'checkbox' || type === 'radio' ) {
										if ($this.is(':checked')){
											$newFieldOptions.find('[name="'+newName+'"]').prop('checked', true).attr('checked','checked');
										} else {
											$newFieldOptions.find('[name="'+newName+'"]').prop('checked', false).attr('checked',false);
										}
									} else if ($this.is('select')) {
										if ($this.find('option:selected').length) {
											var optionVal = $this.find('option:selected').val();
											$newFieldOptions.find('[name="'+newName+'"]').find('[value="'+optionVal+'"]').prop('selected',true);
										}
									} else {
										if ($this.val() != '') {
											$newFieldOptions.find('[name="'+newName+'"]').val( $this.val() );
										}
									}
								});

								// ID adjustments
								$('#wpforms-field-option-'+newFieldID).find('.wpforms-field-option-hidden-id').val(newFieldID);
								$('#wpforms-field-id').val(nextID);

								// Adjust label to indicate this is a copy
								$('#wpforms-field-option-'+newFieldID+'-label').val(newFieldLabel);
								$newField.find('.label-title .text').text(newFieldLabel);

								// Fire field add custom event
								$(document).trigger('wpformsFieldAdd', [newFieldID, type]);

								// Lastly, update the next ID stored in database
								$.post(wpforms_builder.ajax_url, {form_id : s.formID, nonce : wpforms_builder.nonce, action : 'wpforms_builder_increase_next_field_id'});
							}
						},
						cancel: {
							text: wpforms_builder.cancel
						}
					}
				});
			}
		},

		/**
		 * Add new field.
		 *
		 * @since 1.0.0
		 */
		fieldAdd: function(type, options) {

			var defaults = {
				position   : 'bottom',
				placeholder: false,
				scroll     : true,
				defaults   : false,
			};
			options = $.extend( {}, defaults, options);

			var data = {
				action  : 'wpforms_new_field_'+type,
				id      : s.formID,
				type    : type,
				defaults: options.defaults,
				nonce   : wpforms_builder.nonce
			}
			return $.post(wpforms_builder.ajax_url, data, function(res) {
				if (res.success) {

					var totalFields = $('.wpforms-field').length,
						$preview    = $('#wpforms-panel-fields .wpforms-panel-content-wrap'),
						$lastField  = $('.wpforms-field').last(),
						$newField   = $(res.data.preview),
						$newOptions = $(res.data.options);

					$newField.css('display', 'none');

					if (options.placeholder) {
						options.placeholder.remove();
					}

					// Determine where field gets placed
					if ( 'bottom' === options.position ) {

						if ( $lastField.length && $lastField.hasClass('wpforms-field-stick')) {
							// Check to see if the last field we have is configured to
							// be stuck to the bottom, if so add the field above it.
							$('.wpforms-field-wrap').children(':eq('+(totalFields-1)+')').before($newField);
							$('.wpforms-field-options').children(':eq('+(totalFields-1)+')').before($newOptions);

						} else {
							// Add field to bottom
							$('.wpforms-field-wrap').append($newField);
							$('.wpforms-field-options').append($newOptions);
						}

						if (options.scroll) {
							$preview.animate({ scrollTop: $preview.prop('scrollHeight') - $preview.height() }, 1000);
						}

					} else if ( 'top' === options.position ) {

						// Add field to top, scroll to
						$('.wpforms-field-wrap').prepend($newField);
						$('.wpforms-field-options').prepend($newOptions);

						if (options.scroll) {
							$preview.animate({ scrollTop: 0 }, 1000);
						}

					} else {

						if ( options.position === totalFields && $lastField.length && $lastField.hasClass('wpforms-field-stick') ) {
							// Check to see if the user tried to add the field at
							// the end BUT the last field we have is configured to
							// be stuck to the bottom, if so add the field above it.
							$('.wpforms-field-wrap').children(':eq('+(totalFields-1)+')').before($newField);
							$('.wpforms-field-options').children(':eq('+(totalFields-1)+')').before($newOptions);

						} else if ($('.wpforms-field-wrap').children(':eq('+options.position+')').length) {
							// Add field to a specific location
							$('.wpforms-field-wrap').children(':eq('+options.position+')').before($newField);
							$('.wpforms-field-options').children(':eq('+options.position+')').before($newOptions);

						} else {
							// Something's wrong, just add the field. This should never occur.
							$('.wpforms-field-wrap').append($newField);
							$('.wpforms-field-options').append($newOptions);
						}
					}

					$newField.fadeIn();

					$('#wpforms-builder-form .no-fields, #wpforms-builder-form .no-fields-preview').remove();
					$('#wpforms-field-id').val(res.data.field.id+1);

					WPFormsBuilder.loadTooltips();
					WPFormsBuilder.loadColorPickers();

					$(document).trigger('wpformsFieldAdd', [res.data.field.id, type ]);

				} else {
					console.log(res);
				}
			}).fail(function(xhr, textStatus, e) {
				console.log(xhr.responseText);
			});
		},

		/**
		 * Sortable fields in the builder form preview area.
		 *
		 * @since 1.0.0
		 */
		fieldSortable: function() {

			var fieldOptions = $('.wpforms-field-options'),
				fieldReceived = false,
				fieldIndex,
				fieldIndexNew,
				field,
				fieldNew;

			$('.wpforms-field-wrap').sortable({
				items  : '> .wpforms-field:not(.wpforms-field-stick)',
				axis   : 'y',
				delay  : 100,
				opacity: 0.75,
				start:function(e,ui){
					fieldIndex = ui.item.index();
					field      = fieldOptions[0].children[fieldIndex];
				},
				stop:function(e,ui){
					fieldIndexNew = ui.item.index();
					fieldNew      = fieldOptions[0].children[fieldIndexNew];
					if (fieldIndex < fieldIndexNew){
						$(fieldNew).after(field);
					} else {
						$(fieldNew).before(field);
					}
					$(document).trigger('wpformsFieldMove', ui);
					fieldReceived = false;
				},
				over: function(e, ui){
					var $el = ui.item.first();
					$el.addClass('wpforms-field-dragging');

					if ( $el.hasClass('wpforms-field-drag')){
						var width = $('.wpforms-field').first().outerWidth();
						$el.addClass('wpforms-field-drag-over').removeClass('wpforms-field-drag-out').css('width', width).css('height', 'auto');
					}
				},
				out: function(e, ui){
					var $el   = ui.item.first();
					$el.removeClass('wpforms-field-dragging');

					if ( !fieldReceived ) {
						var width = $el.attr('data-original-width');
						if ( $el.hasClass('wpforms-field-drag')){
							$el.addClass('wpforms-field-drag-out').removeClass('wpforms-field-drag-over').css('width', width).css('left', '').css('top', '');
						}
					}
					$el.css({
						'top':     '',
						'left':    '',
						'z-index': ''
					});
				},
				receive: function(e, ui) {
					fieldReceived = true;

					var pos  = $(this).data('ui-sortable').currentItem.index();
						$el  = ui.helper,
						type = $el.attr('data-field-type');

					$el.addClass('wpforms-field-drag-over wpforms-field-drag-pending').removeClass('wpforms-field-drag-out').css('width', '100%');
					$el.append('<i class="fa fa-cog fa-spin"></i>');

					WPFormsBuilder.fieldAdd(type, {position: pos, placeholder: $el});
				   }
			});

			$('.wpforms-add-fields-button').not('.upgrade-modal').draggable({
				connectToSortable: '.wpforms-field-wrap',
				delay: 200,
				helper: function(event) {
					var $this = $(this),
						width = $this.outerWidth(),
						text  = $this.html(),
						type  = $this.data('field-type'),
						$el   = $('<div class="wpforms-field-drag-out wpforms-field-drag">');
					return $el.html(text).css('width',width).attr('data-original-width',width).attr('data-field-type',type);
				},
				revert: 'invalid',
				cancel: false,
				scroll: false,
				opacity: 0.75,
				containment: 'document'
			});
		},

		/**
		 * Add new field choice
		 *
		 * @since 1.0.0
		 */
		fieldChoiceAdd: function(e, el) {

			e.preventDefault();

			var $this   = $(el),
				$parent = $this.parent(),
				checked = $parent.find('input.default').is(':checked'),
				fieldID = $this.closest('.wpforms-field-option-row-choices').data('field-id'),
				id      = $parent.parent().attr('data-next-id'),
				type    = $parent.parent().data('field-type'),
				choice  = $parent.clone().insertAfter($parent);

			choice.attr('data-key', id);
			choice.find('input.label').val('').attr('name', 'fields['+fieldID+'][choices]['+id+'][label]');
			choice.find('input.value').val('').attr('name', 'fields['+fieldID+'][choices]['+id+'][value]');
            choice.find('input.limit').val('').attr('name', 'fields['+fieldID+'][choices]['+id+'][limit]');
			choice.find('input.default').attr('name', 'fields['+fieldID+'][choices]['+id+'][default]').prop('checked', false);

			if ( checked == true ) {
				$parent.find('input.default').prop('checked', true);
			}
			id++;
			$parent.parent().attr('data-next-id', id);
			$(document).trigger('wpformsFieldChoiceAdd');
			WPFormsBuilder.fieldChoiceUpdate(type, fieldID);
		},

		/**
		 * Delete field choice
		 *
		 * @since 1.0.0
		 */
		fieldChoiceDelete: function(e, el) {

			e.preventDefault();

			var $this = $(el),
				$list = $this.parent().parent(),
				total = $list.find('li').length;

			if (total == '1') {
				$.alert({
					title: false,
					content: wpforms_builder.error_choice,
					icon: 'fa fa-info-circle',
					type: 'blue',
					buttons: {
						confirm: {
							text: wpforms_builder.ok,
							btnClass: 'btn-confirm',
							keys: ['enter']
						}
					}
				});
			} else {
				$this.parent().remove();
				WPFormsBuilder.fieldChoiceUpdate($list.data('field-type'), $list.data('field-id'));
				$(document).trigger('wpformsFieldChoiceDelete');
			}
		},

		/**
		 * Make field choices sortable.
		 *
		 * Currently used for select, radio, and checkboxes field types
		 *
		 * @since 1.0.0
		 */
		fieldChoiceSortable: function(type, selector) {

			selector = typeof selector !== 'undefined' ? selector : '.wpforms-field-option-'+type+' .wpforms-field-option-row-choices ul';

			$(selector).sortable({
				items  : 'li',
				axis   : 'y',
				delay  : 100,
				opacity: 0.6,
				handle : '.move',
				stop:function(e,ui){
					var id = ui.item.parent().data('field-id');
					WPFormsBuilder.fieldChoiceUpdate(type, id);
					$(document).trigger('wpformsFieldChoiceMove', ui);
				},
				update:function(e,ui){
				}
			});
		},

		/**
		 * Update field choices in preview area, for the Fields panel.
		 *
		 * Currently used for select, radio, and checkboxes field types
		 *
		 * @since 1.0.0
		 */
		fieldChoiceUpdate: function(type, id) {

			var new_choice;

			// Multiple payment choices are radio buttons
			if ( type === 'payment-multiple') {
				type = 'radio';
			}
			// Dropdown payment choices are selects
			if ( type === 'payment-select') {
				type = 'select';
			}
            if ( type === 'store') {
                type = 'radio';
            }
			if (type === 'select') {
				$('#wpforms-field-'+id+' .primary-input option' ).not('.placeholder').remove();
				new_choice = '<option>{label}</option>';
			} else if (type === 'radio' || type === 'checkbox' ) {
				$('#wpforms-field-'+id+' .primary-input li' ).remove();
				new_choice = '<li><input type="'+type+'" disabled>{label}</li>';
			}
			$('#wpforms-field-option-row-' + id + '-choices .choices-list li').each( function( index ) {
				var $this    = $(this),
					label    = $this.find('input.label').val(),
					selected = $this.find('input.default').is(':checked'),
					choice 	 = $( new_choice.replace('{label}',label) );
				$('#wpforms-field-'+id+' .primary-input').append(choice);
				if ( selected === true ) {
					switch (type) {
						case 'select':
							choice.prop('selected', 'true');
							break;
						case 'radio':
						case 'checkbox':
							choice.find('input').prop('checked', 'true');
							break;
					}
				}
			});
		},

		/**
		 * Field choice bulk add toggling.
		 *
		 * @since 1.3.7
		 */
		fieldChoiceBulkAddToggle: function(el) {

			var $this  = $(el),
				$label = $this.closest('label');

			if ( $this.hasClass('bulk-add-showing') ) {

				// Import details is showing, so hide/remove it
				var $selector = $label.next('.bulk-add-display');
				$selector.slideUp(400, function() {
					$selector.remove();
				});
				$this.find('span').text(wpforms_builder.bulk_add_show);
			} else {

				var importOptions = '<div class="bulk-add-display">';

				importOptions += '<p class="heading">'+wpforms_builder.bulk_add_heading+' <a href="#" class="toggle-bulk-add-presets">'+wpforms_builder.bulk_add_presets_show+'</a></p>';
				importOptions += '<ul>';
					for(var key in wpforms_preset_choices) {
						importOptions += '<li><a href="#" data-preset="'+key+'" class="bulk-add-preset-insert">'+wpforms_preset_choices[key].name+'</a></li>';
					}
				importOptions += '</ul>';
				importOptions += '<textarea placeholder="'+wpforms_builder.bulk_add_placeholder+'"></textarea>';
				importOptions += '<button class="bulk-add-insert">'+wpforms_builder.bulk_add_button+'</button>';
				importOptions += '</div>';

				$label.after(importOptions);
				$label.next('.bulk-add-display').slideDown(400, function() {
					$(this).find('textarea').focus();
				});
				$this.find('span').text(wpforms_builder.bulk_add_hide);
			}

			$this.toggleClass('bulk-add-showing');
		},

		/**
		 * Field choice bulk insert the new choices.
		 *
		 * @since 1.3.7
		 */
		fieldChoiceBulkAddInsert: function(el) {

			var $this          = $(el),
				$container     = $this.closest('.wpforms-field-option-row'),
				$textarea      = $container.find('textarea'),
				$list          = $container.find('.choices-list'),
				$choice        = $list.find('li:first-of-type').clone().wrap('<div>').parent(),
				choice         = '',
				fieldID        = $container.data('field-id'),
				type           = $list.data('field-type'),
				nextID         = Number( $list.attr('data-next-id') ),
				newValues      = $textarea.val().split("\n"),
				newChoices     = '';

			$this.prop('disabled', true).html($this.html()+' '+s.spinner);
			$choice.find('input.value,input.label').attr('value','');
			choice = $choice.html();

			for(var key in newValues) {
				var value     = newValues[key],
					newChoice = choice;
				value = value.trim();
				newChoice = newChoice.replace( /\[choices\]\[(\d+)\]/g ,'[choices]['+nextID+']' );
				newChoice = newChoice.replace( /data-key="(\d+)"/g ,'data-key="'+nextID+'"' );
				newChoice = newChoice.replace( /value="" class="label"/g ,'value="'+value+'" class="label"' );
				newChoices += newChoice;
				nextID++;
			}
			$list.attr('data-next-id', nextID).append(newChoices)

			WPFormsBuilder.fieldChoiceUpdate(type, fieldID);
			$(document).trigger('wpformsFieldChoiceAdd');
			WPFormsBuilder.fieldChoiceBulkAddToggle( $container.find('.toggle-bulk-add-display') );
		},

		/**
		 * Toggle fields tabs (Add Fields, Field Options.
		 *
		 * @since 1.0.0
		 */
		fieldTabToggle: function(id) {

			$('.wpforms-tab a').removeClass('active').find('i').removeClass('fa-angle-down').addClass('fa-angle-right');
			$('.wpforms-field, .wpforms-title-desc').removeClass('active');

			if (id === 'add-fields') {
				$('#add-fields').find('a').addClass('active').find('i').addClass('fa-angle-down');
				$('.wpforms-field-options').hide();
				$('.wpforms-add-fields').show()
			} else {
				$('#field-options').find('a').addClass('active').find('i').addClass('fa-angle-down');
				if (id === 'field-options') {
					$('.wpforms-field').first().addClass('active');
					id = $('.wpforms-field').first().data('field-id');
				} else {
					$('#wpforms-field-'+id).addClass('active');
				}
				$('.wpforms-field-option').hide();
				$('#wpforms-field-option-'+id).show();
				$('.wpforms-add-fields').hide();
				$('.wpforms-field-options').show();
			}
		},

		/**
		 * Watches fields being added and listens for a pagebreak field.
		 *
		 * If a pagebreak field is added, and it's the first one, then we
		 * automatically add the top and bottom pagebreak elements to the
		 * builder.
		 *
		 * @since 1.2.1
		 */
		fieldPagebreakAdd: function(event, id, type) {

			if ( 'pagebreak' !== type )
				return;

			if ( ! s.pagebreakTop ) {

				s.pagebreakTop = true;
				var options = {
					position: 'top',
					scroll: false,
					defaults: {
						position: 'top',
						nav_align: 'left',
					}
				}
				WPFormsBuilder.fieldAdd('pagebreak', options).done(function(res){
					s.pagebreakTop = res.data.field.id;
					//console.log( 'PB top is ' + res.data.field.id);
					var $preview = $('#wpforms-field-'+res.data.field.id),
						$options = $('#wpforms-field-option-'+res.data.field.id);

					$options.find('.wpforms-field-option-group').addClass('wpforms-pagebreak-top');
					$preview.addClass('wpforms-field-stick wpforms-pagebreak-top');
				});

			} else if ( ! s.pagebreakBottom ) {

				s.pagebreakBottom = true;
				var options = {
					position: 'bottom',
					scroll: false,
					defaults: {
						position: 'bottom'
					}
				}
				WPFormsBuilder.fieldAdd('pagebreak', options).done(function(res){
					s.pagebreakBottom = res.data.field.id;
					//console.log( 'PB bottom is ' + res.data.field.id);
					var $preview = $('#wpforms-field-'+res.data.field.id),
						$options = $('#wpforms-field-option-'+res.data.field.id);

					$options.find('.wpforms-field-option-group').addClass('wpforms-pagebreak-bottom');
					$preview.addClass('wpforms-field-stick wpforms-pagebreak-bottom');
				});
			}
		},

		/**
		 * Watches fields being deleted and listens for a pagebreak field.
		 *
		 * If a pagebreak field is added, and it's the first one, then we
		 * automatically add the top and bottom pagebreak elements to the
		 * builder.
		 *
		 * @since 1.2.1
		 */
		fieldPagebreakDelete: function(event, id, type) {

			if ( 'pagebreak' !== type )
				return;

			var pagebreaksRemaining = $('.wpforms-field-pagebreak').not('.wpforms-pagebreak-top, .wpforms-pagebreak-bottom').length;

			// All pagebreaks, excluding top/bottom, are gone so we need to
			// remove the top and bottom pagebreak
			if ( !pagebreaksRemaining ) {
				var $top     = $('.wpforms-preview-wrap').find('.wpforms-pagebreak-top'),
					topID    = $top.data('field-id'),
					$bottom  = $('.wpforms-preview-wrap').find('.wpforms-pagebreak-bottom'),
					bottomID = $bottom.data('field-id');

					// Remove
					$top.remove();
					$('#wpforms-field-option-'+topID).remove();
					s.pagebreakTop = false;
					$bottom.remove();
					$('#wpforms-field-option-'+bottomID).remove();
					s.pagebreakBottom = false;
			}
		},

		/**
		 * Field Dynamic Choice toggle.
		 *
		 * @since 1.2.8
		 */
		fieldDynamicChoiceToggle: function(el) {

			var $this       = $(el),
				$thisOption = $this.parent(),
				value       = $this.val(),
				id          = $thisOption.data('field-id'),
				$field      = $('#wpforms-field-'+id),
				$choices    = $('#wpforms-field-option-row-'+id+'-choices');

			// Loading
			wpf.fieldOptionLoading($thisOption);

			// Remove previous dynamic post type or taxonomy source options
			$('#wpforms-field-option-row-'+id+'-dynamic_post_type').remove();
			$('#wpforms-field-option-row-'+id+'-dynamic_taxonomy').remove();

			if ( '' === value ) {
				// "Off" - no dynamic populating

				// Get original field choices
				var choices = [];
				$('#wpforms-field-option-row-'+id+'-choices .label').each(function(index) {
					choices.push($(this).val());
				});

				// Restore field to display original field choices
				if ($field.hasClass('wpforms-field-select')) {

					$field.find('select option:first').text(choices[0]);

				} else {

					var type  = 'radio',
						$list = $field.find('.primary-input');

					if ($field.hasClass('wpforms-field-checkbox')) {
						type = 'checkbox';
					}

					// Remove previous items
					$list.empty();

					// Add new items to radio or checkbox field
					for(var key in choices) {
						$list.append('<li><input type="'+type+'" disabled> '+choices[key]+'</li>');
					}
				}

				// Toggle elements and hide loading indicator
				$choices.find('ul').removeClass('wpforms-hidden');
				$choices.find('.wpforms-alert').addClass('wpforms-hidden');

				wpf.fieldOptionLoading($thisOption, true);

			} else {
				// Post type or Taxonomy based dynamic populating
				var data = {
					type    : value,
					field_id: id,
					action  : 'wpforms_builder_dynamic_choices',
					nonce   : wpforms_builder.nonce
				};
				$.post(wpforms_builder.ajax_url, data, function(res) {
					if (res.success){
						// New option markup
						$thisOption.after(res.data.markup)
					} else {
						console.log(res);
					}
					// Hide loading indicator
					wpf.fieldOptionLoading($thisOption, true);

					// Re-init tooltips for new field
					WPFormsBuilder.loadTooltips();

					// Trigger Dynamic source updates
					$('#wpforms-field-option-'+id+'-dynamic_'+value).find('option:first').prop('selected', true);
					$('#wpforms-field-option-'+id+'-dynamic_'+value).trigger('change');

				}).fail(function(xhr, textStatus, e) {
					console.log(xhr.responseText);
				});
			}
		},

		/**
		 * Field Dynamic Choice Source toggle.
		 *
		 * @since 1.2.8
		 */
		fieldDynamicChoiceSource: function(el) {

			var $this       = $(el),
				$thisOption = $this.parent(),
				value       = $this.val(),
				id          = $thisOption.data('field-id'),
				form_id     = $('#wpforms-builder-form').data('id'),
				$choices    = $('#wpforms-field-option-row-'+id+'-choices'),
				$field      = $('#wpforms-field-'+id),
				type        = $('#wpforms-field-option-'+id+'-dynamic_choices option:selected').val(),
				limit       = 20;

			// Loading
			wpf.fieldOptionLoading($thisOption);

			var data = {
				type    : type,
				source  : value,
				field_id: id,
				form_id : form_id,
				action  : 'wpforms_builder_dynamic_source',
				nonce   : wpforms_builder.nonce
			};
			$.post(wpforms_builder.ajax_url, data, function(res) {
				if (res.success){

					// Update info box and remove old choices
					$choices.find('.dynamic-name').text(res.data.source_name);
					$choices.find('.dynamic-type').text(res.data.type_name);
					$choices.find('ul').addClass('wpforms-hidden');
					$choices.find('.wpforms-alert').removeClass('wpforms-hidden');

					if ($field.hasClass('wpforms-field-select')) {

						// Add new items to select field
						$field.find('select option:first').text(res.data.items[0]);
						limit = 200;

					} else {

						var type  = 'radio',
							$list = $field.find('.primary-input');

						if ($field.hasClass('wpforms-field-checkbox')) {
							type = 'checkbox';
						}

						// Remove previous items
						$list.empty();

						// Add new items to radio or checkbox field
						for(var key in res.data.items) {
							$list.append('<li><input type="'+type+'" disabled> '+res.data.items[key]+'</li>');
						}
					}

					// If the source has more items than the field type can
					// ideally handle alert the user
					if (Number(res.data.total) > limit) {
						var msg = wpforms_builder.dynamic_choice_limit;
						msg = msg.replace('{source}',res.data.source_name);
						msg = msg.replace('{type}',res.data.type_name);
						msg = msg.replace('{limit}',limit);
						msg = msg.replace('{total}',res.data.total);
						$.alert({
							title: wpforms_builder.heads_up,
							content: msg,
							icon: 'fa fa-info-circle',
							type: 'blue',
							buttons: {
								confirm: {
									text: wpforms_builder.ok,
									btnClass: 'btn-confirm',
									keys: ['enter']
								}
							}
						});
					}
				} else {
					console.log(res);
				}

				// Toggle elements and hide loading indicator
				$choices.find('ul').addClass('wpforms-hidden');
				wpf.fieldOptionLoading($thisOption, true);

			}).fail(function(xhr, textStatus, e) {
				console.log(xhr.responseText);
			});
		},

		/**
		 * Field layout selector toggling.
		 *
		 * @since 1.3.7
		 */
		fieldLayoutSelectorToggle: function(el) {

			var $this   = $(el),
				$label  = $this.closest('label'),
				layouts = {
					'layout-1' : [
						{
							'class': 'one-half',
							'data' : 'wpforms-one-half wpforms-first'
						},
						{
							'class': 'one-half',
							'data' : 'wpforms-one-half'
						}
					],
					'layout-2' : [
						{
							'class': 'one-third',
							'data' : 'wpforms-one-third wpforms-first'
						},
						{
							'class': 'one-third',
							'data' : 'wpforms-one-third'
						},
						{
							'class': 'one-third',
							'data' : 'wpforms-one-third'
						}
					],
					'layout-3' : [
						{
							'class': 'one-fourth',
							'data' : 'wpforms-one-fourth wpforms-first'
						},
						{
							'class': 'one-fourth',
							'data' : 'wpforms-one-fourth'
						},
						{
							'class': 'one-fourth',
							'data' : 'wpforms-one-fourth'
						},
						{
							'class': 'one-fourth',
							'data' : 'wpforms-one-fourth'
						}
					],
					'layout-4' : [
						{
							'class': 'one-third',
							'data' : 'wpforms-one-third wpforms-first'
						},
						{
							'class': 'two-third',
							'data' : 'wpforms-two-thirds'
						}
					],
					'layout-5' : [
						{
							'class': 'two-third',
							'data' : 'wpforms-two-thirds wpforms-first'
						},
						{
							'class': 'one-third',
							'data' : 'wpforms-one-third'
						}
					],
					'layout-6' : [
						{
							'class': 'one-fourth',
							'data' : 'wpforms-one-fourth wpforms-first'
						},
						{
							'class': 'one-fourth',
							'data' : 'wpforms-one-fourth'
						},
						{
							'class': 'two-fourth',
							'data' : 'wpforms-two-fourths'
						}
					],
					'layout-7' : [
						{
							'class': 'two-fourth',
							'data' : 'wpforms-two-fourths wpforms-first'
						},
						{
							'class': 'one-fourth',
							'data' : 'wpforms-one-fourth'
						},
						{
							'class': 'one-fourth',
							'data' : 'wpforms-one-fourth'
						}
					],
					'layout-8' : [
						{
							'class': 'one-fourth',
							'data' : 'wpforms-one-fourth wpforms-first'
						},
						{
							'class': 'two-fourth',
							'data' : 'wpforms-two-fourths'
						},
						{
							'class': 'one-fourth',
							'data' : 'wpforms-one-fourth'
						}
					]
				};

			if ( $this.hasClass('layout-selector-showing') ) {

				// Selector is showing, so hide/remove it
				var $selector = $label.next('.layout-selector-display');
				$selector.slideUp(400, function() {
					$selector.remove();
				});
				$this.find('span').text(wpforms_builder.layout_selector_show);
			} else {

				// Create selector options
				var layoutOptions = '<div class="layout-selector-display">';

				layoutOptions += '<p class="heading">'+wpforms_builder.layout_selector_layout+'</p>';
					for(var key in layouts) {
						var layout = layouts[key];
						layoutOptions += '<div class="layout-selector-display-layout">';
						for(var key in layout) {
							layoutOptions += '<span class="'+layout[key].class+'" data-classes="'+layout[key].data+'"></span>';
						}
						layoutOptions += '</div>';
					}
				layoutOptions += '</div>';

				$label.after(layoutOptions);
				$label.next('.layout-selector-display').slideDown();
				$this.find('span').text(wpforms_builder.layout_selector_hide);
			}

			$this.toggleClass('layout-selector-showing');
		},

		/**
		 * Field layout selector, selecting a layout.
		 *
		 * @since 1.3.7
		 */
		fieldLayoutSelectorLayout: function(el) {

			var $this   = $(el),
				$label  = $this.closest('label');

			$this.parent().find('.layout-selector-display-layout').not($this).remove();
			$this.parent().find('.heading').text(wpforms_builder.layout_selector_column);
			$this.toggleClass('layout-selector-display-layout layout-selector-display-columns')
		},

		/**
		 * Field layout selector, insert into class field.
		 *
		 * @since 1.3.7
		 */
		fieldLayoutSelectorInsert: function(el) {
			var $this     = $(el),
				$selector = $this.closest('.layout-selector-display'),
				$parent   = $selector.parent(),
				$label    = $parent.find('label'),
				$input    = $parent.find('input[type=text]'),
				classes   = $this.data('classes');

			$input.insertAtCaret(classes);

			// remove list, all done!
			$selector.slideUp(400, function() {
				$selector.remove();
			});

			$label.find('.toggle-layout-selector-display').removeClass('layout-selector-showing');
			$label.find('.toggle-layout-selector-display span').text(wpforms_builder.layout_selector_show);
		},

		//--------------------------------------------------------------------//
		// Settings Panel
		//--------------------------------------------------------------------//

		/**
		 * Element bindings for Settings panel.
		 *
		 * @since 1.0.0
		 */
		bindUIActionsSettings: function() {

			// Clicking form title/desc opens Settings panel
			$(document).on('click', '.wpforms-title-desc, .wpforms-field-submit-button, .wpforms-center-form-name', function(e) {
				e.preventDefault();
				WPFormsBuilder.panelSwitch('settings');
			});

			// Clicking form previous page break button
			$(document).on('click', '.wpforms-field-pagebreak-last button', function(e) {
				e.preventDefault();
				WPFormsBuilder.panelSwitch('settings');
				$('#wpforms-panel-field-settings-pagebreak_prev').focus();
			});

			// Clicking form last page break button
			$(document).on('input', '#wpforms-panel-field-settings-pagebreak_prev', function(){
				$('.wpforms-field-pagebreak-last button').text( $(this).val() );
			});

			// Real-time updates for editing the form title
			$(document).on('input', '#wpforms-panel-field-settings-form_title, #wpforms-setup-name', function(){
				var title = $(this).val();
				if (title.length > 38) {
					title = $.trim(title).substring(0, 38).split(" ").slice(0, -1).join(" ") + "..."
				}
				$('.wpforms-form-name').text( title );
			});

			// Real-time updates for editing the form description
			$(document).on('input', '#wpforms-panel-field-settings-form_desc', function(){
				$('.wpforms-form-desc').text( $(this).val() );
			});

			// Real-time updates for editing the form submit button
			$(document).on('input', '#wpforms-panel-field-settings-submit_text', function(){
				$('.wpforms-field-submit input[type=submit]').val( $(this).val() );
			});

			// Toggle form reCAPTCHA setting
			$(document).on('change', '#wpforms-panel-field-settings-recaptcha', function() {
				WPFormsBuilder.recaptchaToggle();
			});

			// Toggle form confirmation setting fields
			$(document).on('change', '#wpforms-panel-field-settings-confirmation_type', function() {
				WPFormsBuilder.confirmationToggle();
			});

			// Toggle form notification setting fields
			$(document).on('change', '#wpforms-panel-field-settings-notification_enable', function() {
				WPFormsBuilder.notificationToggle();
			});

			// Add New notification settings block
			$(document).on('click', '.wpforms-notifications-add', function(e) {
				e.preventDefault();
				WPFormsBuilder.notificationAdd();
			});

            // Edit notification settings block name
            $(document).on('click', '.wpforms-notification-edit', function(e) {
                e.preventDefault();

                var $el = $(this);

                if ( $el.parents('.wpforms-notification-header').find('.wpforms-notification-name').hasClass('editing') ) {
					WPFormsBuilder.notificationNameEditingHide( $el );
				} else {
					WPFormsBuilder.notificationNameEditingShow( $el );
				}
			});

            // Update notification settings block name and close editing interface
            $(document).on('blur', '.wpforms-notification-name-edit input', function(e) {
				// Do not fire if for onBlur user clicked on edit button - it has own event processing.
            	if ( ! $(e.relatedTarget).hasClass('wpforms-notification-edit')) {
					WPFormsBuilder.notificationNameEditingHide( $(this) );
				}
            });

			// Close notifications editing interface with pressed Enter
			$(document).on('keypress', '.wpforms-notification-name-edit input', function(e) {
				// On Enter - hide editing interface.
				if (e.keyCode === 13) {
					WPFormsBuilder.notificationNameEditingHide( $(this) );

					// We need this preventDefault() to stop jumping to form name editing input.
					e.preventDefault();
				}
			});

			// Toggle notification settings block - slide up or down
			$(document).on('click', '.wpforms-notification-toggle', function(e) {
				e.preventDefault();

				WPFormsBuilder.notificationPanelToggle($(this));
			});

			// Remove notification settings block
			$(document).on('click', '.wpforms-notification-delete', function(e) {
				e.preventDefault();
				WPFormsBuilder.notificationDelete($(this));
			});
		},

		/**
		 * Toggle displaying the ReCAPTCHA.
		 *
		 * @since 1.0.0
		 */
		recaptchaToggle: function() {

			if ($('#wpforms-panel-field-settings-recaptcha').is(':checked')) {
				$('.wpforms-field-recaptcha').show();
			} else {
				$('.wpforms-field-recaptcha').hide();
			}
		},

		/**
		 * Toggle the different form Confirmation setting fields.
		 *
		 * @since 1.0.0
		 */
		confirmationToggle: function() {

			var $confirmation = $('#wpforms-panel-field-settings-confirmation_type');
			if ($confirmation.val()){
				var type = $confirmation.val();
				$confirmation.parent().parent().find('.wpforms-panel-field').not($confirmation.parent()).hide();
				$('#wpforms-panel-field-settings-confirmation_'+type+'-wrap').show();
				if (type === 'message') {
					$('#wpforms-panel-field-settings-confirmation_message_scroll-wrap').show();
				}
			}
		},

		/**
		 * Toggle the displaying notification settings depending on if the
		 * notifications are enabled.
		 *
		 * @since 1.1.9
		 */
		notificationToggle: function() {
			var $notification = $('#wpforms-panel-field-settings-notification_enable');
			if ( $notification.find('option:selected').val() === '0'){
				$notification.parent().parent().find('.wpforms-notification').hide();
			} else {
				$notification.parent().parent().find('.wpforms-notification').show();
			}
		},

		/**
		 * Add new notification.
		 *
		 * @since 1.2.3
		 */
		notificationAdd: function() {

			var nextID       = Number($('.wpforms-notifications-add').attr('data-next-id')),
				namePrompt   = wpforms_builder.notification_prompt,
				nameField    = '<input autofocus="" type="text" id="notification-name" placeholder="'+wpforms_builder.notification_ph+'">',
				nameError    = '<p class="error">'+wpforms_builder.notification_error+'</p>',
				modalContent = namePrompt + nameField + nameError;

			var modal = $.confirm({
				title: false,
				content: modalContent,
				icon: 'fa fa-info-circle',
				type: 'blue',
				buttons: {
					confirm: {
						text: wpforms_builder.ok,
						btnClass: 'btn-confirm',
						keys: ['enter'],
						action: function() {
							var notification_name = $.trim(this.$content.find('input#notification-name').val()),
								error = this.$content.find('.error');

							if (notification_name === '') {
								error.show();
								return false;
							} else {
								var $firstNotification = $('.wpforms-notification').first(),
									$newNotification   = $firstNotification.clone(),
									newNotification;

								$newNotification.find('.wpforms-notification-header span').text(notification_name);
								$newNotification.find('input, textarea, select').each(function(index, el) {
									if ($(this).attr('name')) {
										$(this).val('').attr('name', $(this).attr('name').replace(/\[(\d+)\]/, '['+nextID+']'));
										if ($(this).is('select')) {
											$(this).find('option').prop('selected',false).attr('selected',false);
											$(this).find('option:first').prop('selected',true).attr('selected','selected');
										} else if ( $(this).attr('type') === 'checkbox') {
											$(this).prop('checked', false).attr('checked', false).val('1');
										} else {
											$(this).val('').attr('value','');
										}
									}
								});
								$newNotification.find('.wpforms-notification-header input').val(notification_name).attr('value',notification_name);
								$newNotification.find('.email-msg textarea').val('{all_fields}').attr('value','{all_fields}');
								$newNotification.find('.email-recipient input').val('{admin_email}').attr('value','{admin_email}');

								// Conditional logic, if present
								var $conditionalLogic = $newNotification.find('.wpforms-conditional-block');
								if ($conditionalLogic.length) {
									$conditionalLogic.find('.wpforms-conditionals-enable-toggle input').attr('data-name', 'settings[notifications]['+nextID+']');
									$conditionalLogic.find('.wpforms-conditional-groups').remove();
								}

								newNotification = $newNotification.wrap('<div>').parent().html();
								newNotification = newNotification.replace( /wpforms-panel-field-notifications-(\d+)/g, 'wpforms-panel-field-notifications-'+nextID );
								newNotification = newNotification.replace( /\[conditionals\]\[(\d+)\]\[(\d+)\]/g, '[conditionals][0][0]' );

								$firstNotification.before( newNotification );

								$('.wpforms-notifications-add').attr('data-next-id', nextID+1);
							}
						}
					},
					cancel: {
						text: wpforms_builder.cancel
					}
				}
			});

			// We need to process this event here, because we need a confirm modal object defined, so we can intrude into it.
			// Pressing Enter will click the Ok button.
			$(document).on('keypress', '#notification-name', function(e) {
				if (e.keyCode === 13) {
					$(modal.buttons.confirm.el).trigger('click');
				}
			});
		},

		/**
		 * Show notification editing interface.
		 *
		 * @since 1.4.1
		 */
		notificationNameEditingShow: function (el) {

			var header_holder = el.parents('.wpforms-notification-header'),
				name_holder   = header_holder.find('.wpforms-notification-name');

			name_holder
				.addClass('editing')
				.hide();

			// Make the editing interface active and in focus
			header_holder.find('.wpforms-notification-name-edit').addClass('active');
			wpf.focusCaretToEnd(header_holder.find('input'));
		},

		/**
		 * Update notification name and hide editing interface.
		 *
		 * @since 1.4.1
		 */
		notificationNameEditingHide: function (el) {

			var header_holder = el.parents('.wpforms-notification-header'),
				name_holder   = header_holder.find('.wpforms-notification-name'),
				edit_holder   = header_holder.find('.wpforms-notification-name-edit'),
				current_name  = edit_holder.find('input').val().trim();

			// Provide a default value for empty notification name.
			if (! current_name.length) {
				current_name = wpforms_builder.notification_def_name;
			}

			// This is done for sanitizing.
			edit_holder.find('input').val(current_name);
			name_holder.text(current_name);

			// Editing should be hidden, displaying - active.
			name_holder
				.removeClass('editing')
				.show();
			edit_holder.removeClass('active');
		},

		/**
		 * Show or hide notification panel content.
		 *
		 * @since 1.4.1
		 */
		notificationPanelToggle: function(el) {

			var $notification = el.closest('.wpforms-notification'),
				notification_id = /settings\[notifications\]\[(\d+)\]\[notification_name\]/g.exec($notification.find('.wpforms-notification-name-edit input').attr('name'))[1],
				$content = $notification.find('.wpforms-notification-content'),
				is_visible = $content.is(':visible');

			$content.slideToggle({
				duration: 400,
				start: function () {
					// Send early to save fast.
					// It's animation start, so we should save the state for animation end (reversed).
					$.post(wpforms_builder.ajax_url, {
						action: 'wpforms_builder_notification_state_save',
						state: is_visible ? 'closed' : 'opened',
						form_id: s.formID,
						notification_id: notification_id,
						nonce : wpforms_builder.nonce
					});
				},
				always: function() {
					if ($content.is(':visible')) {
						el.html('<i class="fa fa-chevron-up"></i>');
					} else {
						el.html('<i class="fa fa-chevron-down"></i>');
					}
				}
			});
		},

		/**
		 * Delete notification.
		 *
		 * @since 1.2.3
		 */
		notificationDelete: function(el) {

			var $this = $(el);

			$.confirm({
				title: false,
				content: wpforms_builder.notification_delete,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_builder.ok,
						btnClass: 'btn-confirm',
						keys: ['enter'],
						action: function () {
							var notifications = $('.wpforms-notification');

							if ( notifications.length <= 1 ) {
								$.alert({
									title: false,
									content: wpforms_builder.notification_error2,
									icon: 'fa fa-exclamation-circle',
									type: 'orange',
									buttons: {
										confirm: {
											text: wpforms_builder.ok,
											btnClass: 'btn-confirm',
											keys: ['enter']
										}
									}
								});
							} else {
								var $current_notification = $this.closest('.wpforms-notification'),
									notification_id = /settings\[notifications\]\[(\d+)\]\[notification_name\]/g.exec($current_notification.find('.wpforms-notification-name-edit input').attr('name'))[1];

								$.post(wpforms_builder.ajax_url, {
									action: 'wpforms_builder_notification_state_remove',
									nonce: wpforms_builder.nonce,
									notification_id: notification_id,
									form_id: s.formID
								} );

								$current_notification.remove();
							}
						}
					},
					cancel: {
						text: wpforms_builder.cancel
					}
				}
			});
		},

		//--------------------------------------------------------------------//
		// Save and Exit
		//--------------------------------------------------------------------//

		/**
		 * Element bindings for Embed and Save/Exit items.
		 *
		 * @since 1.0.0
		 */
		bindUIActionsSaveExit: function() {

			// Embed form
			$(document).on('click', '#wpforms-embed', function(e) {
				e.preventDefault();
				var content = wpforms_builder.embed_modal;
					content += '<input type=\'text\' value=\'[wpforms id="' + s.formID + '" title="false" description="false"]\' readonly id=\'wpforms-embed-shortcode\'>';
					content += wpforms_builder.embed_modal_2;
				$.alert({
					columnClass: 'modal-wide',
					title: false,
					content: content,
					boxWidth: '600px',
					buttons: {
						confirm: {
							text: wpforms_builder.close,
							btnClass: 'btn-confirm',
							keys: ['enter']
						}
					}
				});
			});

			// Save form
			$(document).on('click', '#wpforms-save', function(e) {
				e.preventDefault();
				WPFormsBuilder.formSave(false);
			});

			// Exit builder
			$(document).on('click', '#wpforms-exit', function(e) {
				e.preventDefault();
				WPFormsBuilder.formExit();
			});
		},

		/**
		 * Save form.
		 *
		 * @since 1.0.0
		 */
		formSave: function(redirect) {

			var $saveBtn = $('#wpforms-save'),
				$icon    = $saveBtn.find('i'),
				$label   = $saveBtn.find('span'),
				text     = $label.text();

			if (typeof tinyMCE !== 'undefined') {
				tinyMCE.triggerSave();
			}

			$label.text(wpforms_builder.saving);
			$icon.toggleClass('fa-check fa-cog fa-spin');

			var data = {
				action: 'wpforms_save_form',
				data  : JSON.stringify($('#wpforms-builder-form').serializeArray()),
				id    : s.formID,
				nonce : wpforms_builder.nonce
			};
			$.post(wpforms_builder.ajax_url, data, function(res) {
				if (res.success) {
					$label.text(text);
					$icon.toggleClass('fa-check fa-cog fa-spin');
					wpf.savedState = wpf.getFormState( '#wpforms-builder-form');
					$(document).trigger('wpformsSaved');
					if (true === redirect ) {
						window.location.href = wpforms_builder.exit_url;
					}
				} else {
					console.log(res);
				}
			}).fail(function(xhr, textStatus, e) {
				console.log(xhr.responseText);
			});
		},

		/**
		 * Exit form builder.
		 *
		 * @since 1.0.0
		 */
		formExit: function() {

			if ( WPFormsBuilder.formIsSaved() ) {
				window.location.href = wpforms_builder.exit_url;
			} else {
				$.confirm({
					title: false,
					content: wpforms_builder.exit_confirm,
					icon: 'fa fa-exclamation-circle',
					type: 'orange',
					backgroundDismiss: false,
					closeIcon: false,
					buttons: {
						confirm: {
							text: wpforms_builder.save_exit,
							btnClass: 'btn-confirm',
							keys: ['enter'],
							action: function(){
								WPFormsBuilder.formSave(true);
							}
						},
						cancel: {
							text: wpforms_builder.exit,
							action: function(){
								window.location.href = wpforms_builder.exit_url;
							}
						}
					}
				});
			}
		},

		/**
		 * Check current form state.
		 *
		 * @since 1.0.0
		 */
		formIsSaved: function() {

			if ( wpf.savedState == wpf.getFormState( '#wpforms-builder-form' ) ) {
				return true;
			} else {
				return false;
			}
		},

		//--------------------------------------------------------------------//
		// General / global
		//--------------------------------------------------------------------//

		/**
		 * Element bindings for general and global items
		 *
		 * @since 1.2.0
		 */
		bindUIActionsGeneral: function() {

			// Toggle Smart Tags
			$(document).on('click', '.toggle-smart-tag-display', function(e) {
				e.preventDefault();
				WPFormsBuilder.smartTagToggle(this);
			});

			$(document).on('click', '.smart-tags-list-display a', function(e) {
				e.preventDefault();
				WPFormsBuilder.smartTagInsert(this);
			});

			// Field map table, update key source
			$(document).on('input', '.wpforms-field-map-table .key-source', function(){
				var value = $(this).val(),
					$dest = $(this).parent().parent().find('.key-destination'),
					name  = $dest.data('name');
					if (value) {
						$dest.attr('name', name.replace('{source}', value.replace(/[^0-9a-zA-Z_-]/gi, '')));
					}
			});

			// Field map table, delete row
			$(document).on('click', '.wpforms-field-map-table .remove', function(e) {
				e.preventDefault();
				WPFormsBuilder.fieldMapTableDeleteRow(e, $(this));
			});

			// Field map table, Add row
			$(document).on('click', '.wpforms-field-map-table .add', function(e) {
				e.preventDefault();
				WPFormsBuilder.fieldMapTableAddRow(e, $(this));
			});

			// Global select field mapping
			jQuery(document).on('wpformsFieldUpdate', WPFormsBuilder.fieldMapSelect);

			// Restrict user money input fields
			$(document).on('input', '.wpforms-money-input', function(event) {
				var $this = $(this),
					amount = $this.val(),
					start  = $this[0].selectionStart,
					end    = $this[0].selectionEnd;
				$this.val(amount.replace(/[^0-9.,]/g, ''));
				$this[0].setSelectionRange(start,end);
			});

			// Format user money input fields
			$(document).on('focusout', '.wpforms-money-input', function(event) {
				var $this     = $(this),
					amount    = $this.val(),
					sanitized = wpf.amountSanitize(amount),
					formatted = wpf.amountFormat(sanitized);
				$this.val(formatted);
			});

			// Don't allow users to enable payments if storing entries has
			// been disabled in the General settings.
			$(document).on('change', '#wpforms-panel-field-stripe-enable, #wpforms-panel-field-paypal_standard-enable', function(event) {
				var $this = $(this);
				if ( $this.prop('checked') ) {
					var disabled = $('#wpforms-panel-field-settings-disable_entries').prop('checked');
					if ( disabled ) {
						$.confirm({
							title: wpforms_builder.heads_up,
							content: wpforms_builder.payments_entries_off,
							backgroundDismiss: false,
							closeIcon: false,
							icon: 'fa fa-exclamation-circle',
							type: 'orange',
							buttons: {
								confirm: {
									text: wpforms_builder.ok,
									btnClass: 'btn-confirm'
								}
							}
						});
						$this.prop('checked',false);
					}
				}
			});
		},

		/**
		 * Smart Tag toggling.
		 *
		 * @since 1.0.1
		 */
		smartTagToggle: function(el) {

			var $this = $( el ),
				$label = $this.closest( 'label' );

			if ( $this.hasClass( 'smart-tag-showing' ) ) {

				// Smart tags are showing, so hide/remove them
				var $list = $label.next( '.smart-tags-list-display' );
				$list.slideUp( 400, function () {
					$list.remove();
				} );
				$this.find( 'span' ).text( wpforms_builder.smart_tags_show );
			}
			else {

				// Show all fields or narrow to specific field types
				var allowed = $this.data( 'fields' ),
					type = $this.data( 'type' ),
					fields = [];
				if ( allowed && allowed.length ) {
					fields = wpf.getFields( allowed.split( ',' ), true );
				}
				else {
					fields = wpf.getFields( false, true );
				}

				// Create smart tags list
				var smartTagList = '<ul class="smart-tags-list-display">';

				if ( type === 'fields' || type === 'all' ) {
					if ( ! fields ) {
						smartTagList += '<li class="heading">' + wpforms_builder.fields_unavailable + '</li>';
					}
					else {
						smartTagList += '<li class="heading">' + wpforms_builder.fields_available + '</li>';
						for ( var field_key in fields ) {
							var label = '';
							if ( fields[ field_key ].label ) {
								label = wpf.sanitizeString( fields[ field_key ].label );
							}
							else {
								label = wpforms_builder.field + ' #' + fields[ field_key ].id;
							}
							smartTagList += '<li><a href="#" data-type="field" data-meta=\'' + fields[ field_key ].id + '\'>' + label + '</a></li>';
						}
					}
				}

				if ( type === 'other' || type === 'all' ) {
					smartTagList += '<li class="heading">' + wpforms_builder.other + '</li>';
					for ( var smarttag_key in wpforms_builder.smart_tags ) {
						smartTagList += '<li><a href="#" data-type="other" data-meta=\'' + smarttag_key + '\'>' + wpforms_builder.smart_tags[ smarttag_key ] + '</a></li>';
					}
				}

				smartTagList += '</ul>';

				$label.after( smartTagList );
				$label.next( '.smart-tags-list-display' ).slideDown();
				$this.find( 'span' ).text( wpforms_builder.smart_tags_hide );
			}

			$this.toggleClass( 'smart-tag-showing' );
		},

		/**
		 * Smart Tag insert.
		 *
		 * @since 1.0.1
		 */
		smartTagInsert: function(el) {

			var $this   = $(el),
				$list   = $this.closest('.smart-tags-list-display'),
				$parent = $list.parent(),
				$label  = $parent.find('label'),
				$input  = $parent.find('input[type=text]'),
				meta    = $this.data('meta'),
				type    = $this.data('type');

			if ( ! $input.length ) {
				$input  = $parent.find('textarea');
			}

			// insert smart tag
			if ( type === 'field' ) {
				$input.insertAtCaret('{field_id="'+meta+'"}');
			} else {
				$input.insertAtCaret('{'+meta+'}');
			}

			// remove list, all done!
			$list.slideUp(400, function() {
				$list.remove();
			});

			$label.find('.toggle-smart-tag-display span').text(wpforms_builder.smart_tags_show);
			$label.find('.toggle-smart-tag-display').removeClass('smart-tag-showing');
		},

		/**
		 * Field map table - Delete row
		 *
		 * @since 1.2.0
		 */
		fieldMapTableDeleteRow: function(e, el) {

			var $this = $(el),
				$row = $this.closest('tr'),
				$table = $this.closest('table'),
				total = $table.find('tr').length;

			if (total > '1') {
				$row.remove();
			}
		},

		/**
		 * Field map table - Add row
		 *
		 * @since 1.2.0
		 */
		fieldMapTableAddRow: function(e, el) {

			var $this   = $(el),
				$row    = $this.closest('tr'),
				$table  = $this.closest('tbody'),
				choice  = $row.clone().insertAfter($row);

			choice.find('input').val('');
			choice.find('select :selected').prop('selected', false);
			choice.find('.key-destination').attr('name','');
		},

		/**
		 * Update field mapped select items on form updates.
		 *
		 * @since 1.2.0
		 */
		fieldMapSelect: function(e, fields) {

			// Apply to all selects with identifier class
			$('.wpforms-field-map-select').each(function(index, el) {

				var $this         = $(this),
					selected      = $this.find('option:selected').val(),
					allowedFields = $this.data('field-map-allowed'),
					placeholder   = $this.data('field-map-placeholder');

				// Check if custom placeholder was provided
				if (typeof placeholder === 'undefined' ||  !placeholder) {
					placeholder = wpforms_builder.select_field;
				}

				// Reset select add placeholder option
				$this.empty().append($('<option>', { value: '', text : placeholder }));

				// If allowed fields are not defined, bail
				if (typeof allowedFields !== 'undefined' && allowedFields) {
					allowedFields = allowedFields.split(' ');
				} else {
					return;
				}

				// If we have no fields for the form, bail
				if ( !fields || $.isEmptyObject(fields) ) {
					return;
				}

				// Loop through the current fields
				for(var field_key in fields) {
					var label = '';
					// Compile the label
					if (typeof fields[field_key].label !== 'undefined' && fields[field_key].label.length) {
						label = wpf.sanitizeString(fields[field_key].label);
					} else {
						label = wpforms_builder.field + ' #' + fields[field_key].val;
					}

					// Add to select if it is a field type allowed
					if ($.inArray(fields[field_key].type, allowedFields) >= 0 || $.inArray('all-fields', allowedFields) >= 0) {
						$this.append($('<option>', { value: fields[field_key].id, text : label }));
					}
				}

				// Restore previous value if found
				if (selected) {
					$this.find('option[value="'+selected+'"]').prop('selected',true);
				}
			});
		},

		//--------------------------------------------------------------------//
		// Other functions
		//--------------------------------------------------------------------//

		/**
		 * Trim long form titles.
		 *
		 * @since 1.0.0
		 */
		trimFormTitle: function() {

			var $title = $('.wpforms-center-form-name');
			if ($title.text().length > 38) {
				var shortTitle = $.trim($title.text()).substring(0, 38).split(" ").slice(0, -1).join(" ") + "...";
				$title.text(shortTitle);
			}
		},

		/**
		 * Load or refresh tooltips.
		 *
		 * @since 1.0.0
		 */
		loadTooltips: function() {

			$('.wpforms-help-tooltip').tooltipster({
				contentAsHTML: true,
				position: 'right',
				maxWidth: 300,
				multiple: true
			});
		},

		/**
		 * Load or refresh tooltips.
		 *
		 * @since 1.2.1
		 */
		loadColorPickers: function() {
			$('.wpforms-color-picker').minicolors();
		},

		/**
		 * Secret preview hotkey.
		 *
		 * @since 1.2.4
		 */
		builderHotkeys: function() {

			var ctrlDown = false;

			$(document).keydown(function(e) {
				if (e.keyCode === 17) {
					ctrlDown = true;
				} else if (ctrlDown && e.keyCode === 80) {
					window.open(wpforms_builder.preview_url);
					ctrlDown = false;
					return false;
				} else if (ctrlDown && e.keyCode === 69) {
					window.open(wpforms_builder.entries_url);
					ctrlDown = false;
					return false;
				}
			}).keyup(function(e) {
				if (e.keyCode === 17) {
					ctrlDown = false;
				}
			});
		}
	};

	WPFormsBuilder.init();

})(jQuery);
