<?php
/**
 * Plugin Name: Signalfire Quick Notes
 * Plugin URI: https://wordpress.org/plugins/signalfire-quick-notes/
 * Description: Adds a simple "Quick Notes" widget to the WordPress dashboard for administrators and editors to leave short notes.
 * Version: 1.0.0
 * Author: Signalfire
 * Author URI: https://signalfire.com
 * Text Domain: signalfire-quick-notes
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
	exit;
}

class SignalfireQuickNotes {
	
	/**
	 * Option name for storing notes data
	 *
	 * @var string
	 */
	private $option_name = 'sqn_notes_data';
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action('init', array($this, 'init'));
		add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
		add_action('wp_ajax_sqn_save_notes', array($this, 'ajax_save_notes'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
		register_activation_hook(__FILE__, array($this, 'activate'));
	}
	
	/**
	 * Initialize the plugin
	 */
	public function init() {
		load_plugin_textdomain('signalfire-quick-notes', false, dirname(plugin_basename(__FILE__)) . '/languages');
	}
	
	/**
	 * Add dashboard widget
	 */
	public function add_dashboard_widget() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		
		wp_add_dashboard_widget(
			'sqn_quick_notes_widget',
			__('Quick Notes', 'signalfire-quick-notes'),
			array($this, 'display_dashboard_widget')
		);
	}
	
	/**
	 * Display the dashboard widget content
	 */
	public function display_dashboard_widget() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die(esc_html__('You do not have sufficient permissions to access this feature.', 'signalfire-quick-notes'));
		}
		
		$notes_data = get_option($this->option_name, array());
		$notes_content = isset($notes_data['content']) ? $notes_data['content'] : '';
		$last_updated = isset($notes_data['last_updated']) ? $notes_data['last_updated'] : '';
		$updated_by = isset($notes_data['updated_by']) ? $notes_data['updated_by'] : '';
		$updated_by_name = isset($notes_data['updated_by_name']) ? $notes_data['updated_by_name'] : '';
		
		$nonce = wp_create_nonce('sqn_save_notes_nonce');
		?>
		<div class="sqn-quick-notes-container">
			<div class="sqn-notes-form">
				<textarea 
					id="sqn-notes-textarea" 
					name="sqn_notes_content" 
					rows="8" 
					class="large-text code"
					placeholder="<?php echo esc_attr__('Add your quick notes here... (HTML allowed)', 'signalfire-quick-notes'); ?>"
				><?php echo esc_textarea($notes_content); ?></textarea>
				
				<div class="sqn-notes-actions">
					<button type="button" id="sqn-save-notes-btn" class="button button-primary">
						<?php echo esc_html__('Save Notes', 'signalfire-quick-notes'); ?>
					</button>
					<span id="sqn-save-status" class="sqn-save-status"></span>
				</div>
			</div>
			
			<?php if ($last_updated): ?>
			<div class="sqn-notes-meta">
				<small class="description">
					<?php
					echo sprintf(
						/* translators: 1: formatted date and time, 2: user display name */
						esc_html__('Last updated: %1$s by %2$s', 'signalfire-quick-notes'),
						esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_updated))),
						esc_html($updated_by_name)
					);
					?>
				</small>
			</div>
			<?php endif; ?>
		</div>
		
		<input type="hidden" id="sqn-nonce" value="<?php echo esc_attr($nonce); ?>">
		
		<style>
		.sqn-quick-notes-container {
			margin: 0;
		}
		
		.sqn-notes-form {
			margin-bottom: 10px;
		}
		
		#sqn-notes-textarea {
			width: 100%;
			resize: vertical;
			font-family: Consolas, Monaco, monospace;
		}
		
		.sqn-notes-actions {
			margin-top: 10px;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		
		.sqn-save-status {
			font-style: italic;
			color: #666;
		}
		
		.sqn-save-status.success {
			color: #46b450;
		}
		
		.sqn-save-status.error {
			color: #dc3232;
		}
		
		.sqn-notes-meta {
			border-top: 1px solid #ddd;
			padding-top: 8px;
			margin-top: 10px;
		}
		</style>
		<?php
	}
	
	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		if ( 'index.php' !== $hook_suffix ) {
			return;
		}
		
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		
		wp_enqueue_script('jquery');
		
		$inline_script = "
		jQuery(document).ready(function($) {
			var saveTimeout;
			var isManualSave = false;
			
			// Auto-save functionality
			$('#sqn-notes-textarea').on('input', function() {
				if (isManualSave) {
					return;
				}
				
				clearTimeout(saveTimeout);
				$('#sqn-save-status').removeClass('success error').text('" . esc_js(__('Typing...', 'signalfire-quick-notes')) . "');
				
				saveTimeout = setTimeout(function() {
					saveNotes(false);
				}, 2000);
			});
			
			// Manual save button
			$('#sqn-save-notes-btn').on('click', function(e) {
				e.preventDefault();
				isManualSave = true;
				clearTimeout(saveTimeout);
				saveNotes(true);
				setTimeout(function() {
					isManualSave = false;
				}, 500);
			});
			
			function saveNotes(isManual) {
				var content = $('#sqn-notes-textarea').val();
				var nonce = $('#sqn-nonce').val();
				
				if (!isManual) {
					$('#sqn-save-status').removeClass('success error').text('" . esc_js(__('Auto-saving...', 'signalfire-quick-notes')) . "');
				} else {
					$('#sqn-save-status').removeClass('success error').text('" . esc_js(__('Saving...', 'signalfire-quick-notes')) . "');
				}
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'sqn_save_notes',
						content: content,
						nonce: nonce
					},
					success: function(response) {
						if (response.success) {
							$('#sqn-save-status').removeClass('error').addClass('success').text(
								isManual ? '" . esc_js(__('Saved!', 'signalfire-quick-notes')) . "' : '" . esc_js(__('Auto-saved', 'signalfire-quick-notes')) . "'
							);
							
							// Update timestamp display
							if (response.data && response.data.timestamp) {
								var metaHtml = '<small class=\"description\">' + 
									'" . esc_js(__('Last updated: ', 'signalfire-quick-notes')) . "' + 
									response.data.timestamp + 
									'" . esc_js(__(' by ', 'signalfire-quick-notes')) . "' + 
									response.data.user_name + 
									'</small>';
								
								if ($('.sqn-notes-meta').length) {
									$('.sqn-notes-meta').html(metaHtml);
								} else {
									$('.sqn-quick-notes-container').append('<div class=\"sqn-notes-meta\" style=\"border-top: 1px solid #ddd; padding-top: 8px; margin-top: 10px;\">' + metaHtml + '</div>');
								}
							}
							
							setTimeout(function() {
								$('#sqn-save-status').text('');
							}, 3000);
						} else {
							$('#sqn-save-status').removeClass('success').addClass('error').text('" . esc_js(__('Save failed', 'signalfire-quick-notes')) . "');
						}
					},
					error: function() {
						$('#sqn-save-status').removeClass('success').addClass('error').text('" . esc_js(__('Save error', 'signalfire-quick-notes')) . "');
					}
				});
			}
		});
		";
		
		wp_add_inline_script('jquery', $inline_script);
	}
	
	/**
	 * Handle AJAX save notes request
	 */
	public function ajax_save_notes() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die(esc_html__('You do not have sufficient permissions to perform this action.', 'signalfire-quick-notes'));
		}
		
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'sqn_save_notes_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed.', 'signalfire-quick-notes' ) );
		}
		
		$content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';
		
		$current_user = wp_get_current_user();
		$notes_data   = array(
			'content'         => $content,
			'last_updated'    => current_time( 'mysql' ),
			'updated_by'      => $current_user->ID,
			'updated_by_name' => $current_user->display_name,
		);
		
		$saved = update_option( $this->option_name, $notes_data );
		
		if ( $saved ) {
			wp_send_json_success(
				array(
					'message'   => __( 'Notes saved successfully.', 'signalfire-quick-notes' ),
					'timestamp' => wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $notes_data['last_updated'] ) ),
					'user_name' => $current_user->display_name,
				)
			);
		} else {
			wp_send_json_error( __( 'Failed to save notes.', 'signalfire-quick-notes' ) );
		}
	}
	
	/**
	 * Plugin activation hook
	 */
	public function activate() {
		// Initialize empty notes data
		if ( ! get_option( $this->option_name ) ) {
			add_option(
				$this->option_name,
				array(
					'content'         => '',
					'last_updated'    => '',
					'updated_by'      => '',
					'updated_by_name' => '',
				)
			);
		}
	}
}

new SignalfireQuickNotes();