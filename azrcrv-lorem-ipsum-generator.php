<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name:		Lorem Ipsum Generator
 * Description:		Generate lorem ipsum placeholder text.
 * Version:			1.0.0
 * Requires CP:		1.0
 * Requires PHP:	7.4
 * Author:			azurecurve
 * Author URI:		https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI:		https://development.azurecurve.co.uk/classicpress-plugins/link-managements/
 * Donate link:		https://development.azurecurve.co.uk/support-development/
 * Text Domain:		azrcrv-lig
 * Domain Path:		/languages
 * License:			GPLv2 or later
 * License URI:		http://www.gnu.org/licenses/gpl-2.0.html
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/rrl-2.0.html.
 * ------------------------------------------------------------------------------
 */

// Declare the namespace.
namespace azurecurve\LoremIpsumGenerator;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// include plugin menu.
require_once dirname( __FILE__ ) . '/pluginmenu/menu.php';
add_action( 'admin_init', 'azrcrv_create_plugin_menu_lig' );

// include update client
require_once dirname( __FILE__ ) . '/libraries/updateclient/UpdateClient.class.php';

/**
 * Setup registration activation hook, actions, filters and shortcodes.
 *
 * @since 1.0.0
 */

// add actions.
add_action( 'admin_menu', __NAMESPACE__ . '\\create_admin_menu' );
add_action( 'admin_init', __NAMESPACE__ . '\\register_admin_styles' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_styles' );
add_action( 'admin_init', __NAMESPACE__ . '\\register_admin_scripts' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_scripts' );
add_action( 'init', __NAMESPACE__ . '\\register_frontend_styles' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_frontend_styles' );
add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_languages' );
add_action( 'admin_post_azrcrv_lig_save_options', __NAMESPACE__ . '\\save_options' );

// add filters.
add_filter( 'plugin_action_links', __NAMESPACE__ . '\\add_plugin_action_link', 10, 2 );

// add shortcodes
add_shortcode( 'lorem-ipsum-generator', __NAMESPACE__ . '\\display_form' );

/**
 * Register admin styles.
 *
 * @since 1.0.0
 */
function register_admin_styles() {
	wp_register_style( 'azrcrv-lig-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), '1.0.0' );
	wp_register_style( 'azrcrv-pluginmenu-admin-styles', plugins_url( 'pluginmenu/css/style.css', __FILE__ ), array(), '1.0.0' );
}

/**
 * Enqueue admin styles.
 *
 * @since 1.0.0
 */
function enqueue_admin_styles() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['page'] ) && ( $_GET['page'] === 'azrcrv-lig' ) ) {
		wp_enqueue_style( 'azrcrv-lig-admin-styles' );
		wp_enqueue_style( 'azrcrv-pluginmenu-admin-styles' );
	}
}

/**
 * Register admin scripts.
 *
 * @since 1.0.0
 */
function register_admin_scripts() {
	wp_register_script( 'azrcrv-lig-admin-jquery', plugins_url( 'assets/jquery/admin.js', __FILE__ ), array(), '1.0.0', true );
}

/**
 * Enqueue admin scripts.
 *
 * @since 1.0.0
 */
function enqueue_admin_scripts() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['page'] ) && ( $_GET['page'] === 'azrcrv-lig' ) ) {
		wp_enqueue_script( 'azrcrv-lig-admin-jquery' );
	}
}

/**
 * Register frontend styles.
 *
 * @since 1.0.0
 */
function register_frontend_styles() {
	wp_register_style( 'azrcrv-lig-styles', plugins_url( 'assets/css/styles.css', __FILE__ ), array(), '1.0.0' );
}

/**
 * Enqueue frontend styles.
 *
 * @since 1.0.0
 */
function enqueue_frontend_styles() {
	wp_enqueue_style( 'azrcrv-lig-styles' );
}

/**
 * Load language files.
 *
 * @since 1.0.0
 */
function load_languages() {
	$plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages';
	load_plugin_textdomain( 'azrcrv-lig', false, $plugin_rel_path );
}

/**
 * Get options including defaults.
 *
 * @since 1.0.0
 */
function get_option_with_defaults( $option_name ) {

	$defaults = array(
		'text'        => array(
			'before' => esc_html__( 'To generate lorem ipsum text, set your options and click the generate button.', 'azrcrv-lig' ),
			'after'  => '',
		),
		'labels'      => array(
			'word-count'      => esc_html__( 'Number of Words', 'azrcrv-lig' ),
			'paragraph-count' => esc_html__( 'Number of Paragraphs', 'azrcrv-lig' ),
		),
		'lorem-ipsum' => array(
			'start-with-lorem'        => 1,
			'word-count'              => 50,
			'paragraph-count'         => 3,
			'maximum-word-count'      => 500,
			'maximum-paragraph-count' => 20,
		),
	);

	$options = get_option( $option_name, $defaults );
	$options = recursive_parse_args( $options, $defaults );

	return $options;

}

/**
 * Recursively parse options to merge with defaults.
 *
 * @since 1.0.0
 */
function recursive_parse_args( $args, $defaults ) {
	$new_args = (array) $defaults;

	foreach ( $args as $key => $value ) {
		if ( is_array( $value ) && isset( $new_args[ $key ] ) ) {
			$new_args[ $key ] = recursive_parse_args( $value, $new_args[ $key ] );
		} else {
			$new_args[ $key ] = $value;
		}
	}

	return $new_args;
}

/**
 * Add action link on plugins page.
 *
 * @since 1.0.0
 */
function add_plugin_action_link( $links, $file ) {
	static $this_plugin;

	if ( ! $this_plugin ) {
		$this_plugin = plugin_basename( __FILE__ );
	}

	if ( $file === $this_plugin ) {
		$settings_link = '<a href="' . esc_url_raw( admin_url( 'admin.php?page=azrcrv-lig' ) ) . '"><img src="' . esc_url_raw( plugins_url( '/pluginmenu/images/logo.svg', __FILE__ ) ) . '" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />' . esc_html__( 'Settings', 'azrcrv-lig' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}

/**
 * Add to menu.
 *
 * @since 1.0.0
 */
function create_admin_menu() {

	add_submenu_page(
		'azrcrv-plugin-menu',
		esc_html__( 'Lorem Ipsum Generator Settings', 'azrcrv-lig' ),
		esc_html__( 'Lorem Ipsum Generator', 'azrcrv-lig' ),
		'manage_options',
		'azrcrv-lig',
		__NAMESPACE__ . '\\display_options'
	);

}

/**
 * Display Settings page.
 *
 * @since 1.0.0
 */
function display_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'azrcrv-lig' ) );
	}

	// Retrieve plugin configuration options from database.
	$options = get_option_with_defaults( 'azrcrv-lig' );

	echo '<div id="azrcrv-lig-general" class="wrap">';

	?>
		<h1>
			<?php
				echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/"><img src="' . esc_attr( plugins_url( '/pluginmenu/images/logo.svg', __FILE__ ) ) . '" style="padding-right: 6px; height: 20px; width: 20px;" alt="azurecurve | Development" /></a>';
				echo esc_html( get_admin_page_title() );
			?>
		</h1>
		<?php

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['settings-updated'] ) ) {
			echo '<div class="notice notice-success is-dismissible">
					<p><strong>' . esc_html__( 'Settings have been saved.', 'azrcrv-lig' ) . '</strong></p>
				</div>';
		}

		$tab_1_label = esc_html__( 'Lorem Ipsum Options', 'azrcrv-lig' );
		$tab_1       = '<table class="form-table azrcrv-lig">

				<tr>
					<th scope="row" colspan=2 class="section-heading">
						<h2 class="azrcrv-lig">' . esc_html__( 'Lorem Ipsum', 'azrcrv-lig' ) . '</h2>
					</th>
				</tr>

				<tr>
					<th scope="row">
						' . esc_html__( 'Start with "Lorem ipsum dolor sit amet"', 'azrcrv-lig' ) . '
					</th>
					<td>
						<input name="start-with-lorem" type="checkbox" id="start-with-lorem" value="1" ' . checked( '1', esc_attr( $options['lorem-ipsum']['start-with-lorem'] ), false ) . ' />
					</td>
				</tr>

				<tr>
					<th scope="row">
						' . esc_html__( 'Default Number of Words', 'azrcrv-lig' ) . '
					</th>
					<td>
						<input name="word-count" type="number" min=1 max=1000 step=1 id="word-count" value="' . esc_attr( $options['lorem-ipsum']['word-count'] ) . '" class="small-text" />
					</td>
				</tr>

				<tr>
					<th scope="row">
						' . esc_html__( 'Maximum Number of Words', 'azrcrv-lig' ) . '
					</th>
					<td>
						<input name="maximum-word-count" type="number" min=1 step=1 id="maximum-word-count" value="' . esc_attr( $options['lorem-ipsum']['maximum-word-count'] ) . '" class="small-text" />
					</td>
				</tr>

				<tr>
					<th scope="row">
						' . esc_html__( 'Default Number of Paragraphs', 'azrcrv-lig' ) . '
					</th>
					<td>
						<input name="paragraph-count" type="number" min=1 max=50 step=1 id="paragraph-count" value="' . esc_attr( $options['lorem-ipsum']['paragraph-count'] ) . '" class="small-text" />
					</td>
				</tr>

				<tr>
					<th scope="row">
						' . esc_html__( 'Maximum Number of Paragraphs', 'azrcrv-lig' ) . '
					</th>
					<td>
						<input name="maximum-paragraph-count" type="number" min=1 step=1 id="maximum-paragraph-count" value="' . esc_attr( $options['lorem-ipsum']['maximum-paragraph-count'] ) . '" class="small-text" />
					</td>
				</tr>

			</table>';

		$tab_2_label = esc_html__( 'Text Options', 'azrcrv-lig' );
		$tab_2       = '<table class="form-table azrcrv-lig">

				<tr>
					<th scope="row" colspan=2 class="section-heading">
						<h2 class="azrcrv-lig">' . esc_html__( 'Text', 'azrcrv-lig' ) . '</h2>
					</th>
				</tr>

				<tr>
					<th scope="row">
						' . esc_html__( 'Before Text', 'azrcrv-lig' ) . '
					</th>
					<td>
						<textarea name="text-before" id="text-before" rows="5" cols="50">' . esc_textarea( $options['text']['before'] ) . '</textarea>
					</td>
				</tr>

				<tr>
					<th scope="row">
						' . esc_html__( 'After Text', 'azrcrv-lig' ) . '
					</th>
					<td>
						<textarea name="text-after" id="text-after" rows="5" cols="50">' . esc_textarea( $options['text']['after'] ) . '</textarea>
					</td>
				</tr>

				<tr>
					<th scope="row" colspan=2 class="section-heading">
						<h2 class="azrcrv-lig">' . esc_html__( 'Labels', 'azrcrv-lig' ) . '</h2>
					</th>
				</tr>

				<tr>
					<th scope="row">
						' . esc_html__( 'Word Count Label', 'azrcrv-lig' ) . '
					</th>
					<td>
						<input name="label-word-count" type="text" id="label-word-count" value="' . esc_attr( $options['labels']['word-count'] ) . '" class="regular-text" />
					</td>
				</tr>

				<tr>
					<th scope="row">
						' . esc_html__( 'Paragraph Count Label', 'azrcrv-lig' ) . '
					</th>
					<td>
						<input name="label-paragraph-count" type="text" id="label-paragraph-count" value="' . esc_attr( $options['labels']['paragraph-count'] ) . '" class="regular-text" />
					</td>
				</tr>

			</table>';

		$tab_3_label = esc_html__( 'Shortcode Usage', 'azrcrv-lig' );
		$tab_3       = '<table class="form-table azrcrv-lig">

				<tr>
					<td scope="row" colspan=2>
						<p>' .
						sprintf( esc_html__( 'Lorem Ipsum forms are placed using the %s shortcode and can have a number of parameters supplied to override the defaults from the options page; each shortcode must have an %s parameter supplied. Available parameters are:', 'azrcrv-lig' ), '<code>&lsqb;lorem-ipsum-generator&rsqb;</code>', '<code>id</code>' ) . '
							<ul>
								<li><code>word-count</code> - ' . esc_html__( 'number of words to generate.', 'azrcrv-lig' ) . '</li>
								<li><code>maximum-word-count</code> - ' . esc_html__( 'maximum number of words the user can generate.', 'azrcrv-lig' ) . '</li>
								<li><code>paragraph-count</code> - ' . esc_html__( 'number of paragraphs to generate.', 'azrcrv-lig' ) . '</li>
								<li><code>maximum-paragraph-count</code> - ' . esc_html__( 'maximum number of paragraphs the user can generate.', 'azrcrv-lig' ) . '</li>
								<li><code>start-with-lorem</code> - ' . esc_html__( 'whether to start with "Lorem ipsum dolor sit amet" (1 or 0).', 'azrcrv-lig' ) . '</li>
								<li><code>text-before</code> - ' . esc_html__( 'text to display before the form.', 'azrcrv-lig' ) . '</li>
								<li><code>text-after</code> - ' . esc_html__( 'text to display after the form.', 'azrcrv-lig' ) . '</li>
								<li><code>label-word-count</code> - ' . esc_html__( 'label for the number of words field.', 'azrcrv-lig' ) . '</li>
								<li><code>label-paragraph-count</code> - ' . esc_html__( 'label for the number of paragraphs field.', 'azrcrv-lig' ) . '</li>
							</ul>
							<p>' . esc_html__( 'Example shortcode usage:', 'azrcrv-lig' ) . '</p>
							<p><code>[lorem-ipsum-generator id="lorem-1" word-count=100 paragraph-count=5]</code></p>
					</p>
					</td>
				</tr>

			</table>';

		$plugin_array = get_option( 'azrcrv-plugin-menu' );

		$tab_4_plugins = '';
		foreach ( $plugin_array as $plugin_name => $plugin_details ) {
			if ( $plugin_details['retired'] == 0 ) {
				$alternative_color = '';
				if ( isset( $plugin_details['bright'] ) && $plugin_details['bright'] == 1 ) {
					$alternative_color = 'bright-';
				}
				if ( isset( $plugin_details['premium'] ) && $plugin_details['premium'] == 1 ) {
					$alternative_color = 'premium-';
				}
				if ( is_plugin_active( $plugin_details['plugin_link'] ) ) {
					$tab_4_plugins .= "<a href='" . esc_url( $plugin_details['admin_URL'] ) . "' class='azrcrv-" . esc_attr( $alternative_color ) . "plugin-index'>" . esc_html( $plugin_name ) . "</a>";
				} else {
					$tab_4_plugins .= "<a href='" . esc_url( $plugin_details['dev_URL'] ) . "' class='azrcrv-" . esc_attr( $alternative_color ) . "plugin-index'>" . esc_html( $plugin_name ) . "</a>";
				}
			}
		}

		$tab_4_label = esc_html__( 'Other Plugins', 'azrcrv-lig' );
		$tab_4       = '<table class="form-table azrcrv-lig">

				<tr>
					<td scope="row" colspan=2>
						<p>' .
						sprintf( esc_html__( '%1$s was one of the first plugin developers to start developing for ClassicPress; all plugins are available from %2$s and are integrated with the %3$s plugin for fully integrated, no hassle, updates.', 'azrcrv-lig' ), '<strong>azurecurve | Development</strong>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve | Development</a>', '<a href="https://directory.classicpress.net/plugins/update-manager/">Update Manager</a>' )
						. '</p>
						<p>' .
						sprintf( esc_html__( 'Other plugins available from %s are:', 'azrcrv-lig' ), '<strong>azurecurve | Development</strong>' )
						. '</p>
					</td>
				</tr>

				<tr>
					<td scope="row" colspan=2>
						' . $tab_4_plugins . '
					</td>
				</tr>

			</table>';

		?>
		<form method="post" action="admin-post.php">

				<input type="hidden" name="action" value="azrcrv_lig_save_options" />

				<?php
					// <!-- Adding security through hidden referer field -->.
					wp_nonce_field( 'azrcrv-lig', 'azrcrv-lig-nonce' );
				?>

				<div id="tabs" class="azrcrv-ui-tabs">
					<ul class="azrcrv-ui-tabs-nav azrcrv-ui-widget-header" role="tablist">
						<li class="azrcrv-ui-state-default azrcrv-ui-state-active" aria-controls="tab-panel-1" aria-labelledby="tab-1" aria-selected="true" aria-expanded="true" role="tab">
							<a id="tab-1" class="azrcrv-ui-tabs-anchor" href="#tab-panel-1"><?php echo esc_html( $tab_1_label ); ?></a>
						</li>
						<li class="azrcrv-ui-state-default" aria-controls="tab-panel-2" aria-labelledby="tab-2" aria-selected="false" aria-expanded="false" role="tab">
							<a id="tab-2" class="azrcrv-ui-tabs-anchor" href="#tab-panel-2"><?php echo esc_html( $tab_2_label ); ?></a>
						</li>
						<li class="azrcrv-ui-state-default" aria-controls="tab-panel-3" aria-labelledby="tab-3" aria-selected="false" aria-expanded="false" role="tab">
							<a id="tab-3" class="azrcrv-ui-tabs-anchor" href="#tab-panel-3"><?php echo esc_html( $tab_3_label ); ?></a>
						</li>
						<li class="azrcrv-ui-state-default" aria-controls="tab-panel-4" aria-labelledby="tab-4" aria-selected="false" aria-expanded="false" role="tab">
							<a id="tab-4" class="azrcrv-ui-tabs-anchor" href="#tab-panel-4"><?php echo esc_html( $tab_4_label ); ?></a>
						</li>
					</ul>
					<div id="tab-panel-1" class="azrcrv-ui-tabs-scroll" role="tabpanel" aria-hidden="false">
						<fieldset>
							<legend class='screen-reader-text'>
								<?php echo esc_html( $tab_1_label ); ?>
							</legend>
							<?php echo $tab_1; ?>
						</fieldset>
					</div>
					<div id="tab-panel-2" class="azrcrv-ui-tabs-scroll azrcrv-ui-tabs-hidden" role="tabpanel" aria-hidden="true">
						<fieldset>
							<legend class='screen-reader-text'>
								<?php echo esc_html( $tab_2_label ); ?>
							</legend>
							<?php echo $tab_2; ?>
						</fieldset>
					</div>
					<div id="tab-panel-3" class="azrcrv-ui-tabs-scroll azrcrv-ui-tabs-hidden" role="tabpanel" aria-hidden="true">
						<fieldset>
							<legend class='screen-reader-text'>
								<?php echo esc_html( $tab_3_label ); ?>
							</legend>
							<?php echo $tab_3; ?>
						</fieldset>
					</div>
					<div id="tab-panel-4" class="azrcrv-ui-tabs-scroll azrcrv-ui-tabs-hidden" role="tabpanel" aria-hidden="true">
						<fieldset>
							<legend class='screen-reader-text'>
								<?php echo esc_html( $tab_4_label ); ?>
							</legend>
							<?php echo $tab_4; ?>
						</fieldset>
					</div>
				</div>

			<input type="submit" name="btn_save" value="<?php esc_html_e( 'Save Settings', 'azrcrv-lig' ); ?>" class="button-primary"/>
		</form>
		<div class='azrcrv-lig-donate'>
			<?php esc_html_e( 'Support', 'azrcrv-lig' ); ?>
			azurecurve | Development
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="MCJQN9SJZYLWJ">
				<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
			</form>
			<span>
				<?php esc_html_e( 'You can help support the development of our free plugins by donating a small amount of money.', 'azrcrv-lig' ); ?>
			</span>
		</div>
	</div>
	<?php

}

/**
 * Save settings.
 *
 * @since 1.0.0
 */
function save_options() {
	// Check that user has proper security level.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action', 'azrcrv-lig' ) );
	}

	// Check that nonce field created in configuration form is present.
	if ( ! empty( $_POST ) && check_admin_referer( 'azrcrv-lig', 'azrcrv-lig-nonce' ) ) {

		// Retrieve original plugin options array.
		$options = get_option( 'azrcrv-lig' );

		/*
		Lorem Ipsum
		*/
		$option_name = 'start-with-lorem';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options['lorem-ipsum']['start-with-lorem'] = 1;
		} else {
			$options['lorem-ipsum']['start-with-lorem'] = 0;
		}

		$option_name = 'word-count';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options['lorem-ipsum']['word-count'] = (int) sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
		}
		$option_name = 'maximum-word-count';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options['lorem-ipsum']['maximum-word-count'] = (int) sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
		}
		$option_name = 'paragraph-count';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options['lorem-ipsum']['paragraph-count'] = (int) sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
		}
		$option_name = 'maximum-paragraph-count';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options['lorem-ipsum']['maximum-paragraph-count'] = (int) sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
		}

		/*
		Text and Labels
		*/
		$option_name = 'label-word-count';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options['labels']['word-count'] = sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
		}
		$option_name = 'label-paragraph-count';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options['labels']['paragraph-count'] = sanitize_text_field( wp_unslash( $_POST[ $option_name ] ) );
		}
		$option_name = 'text-before';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options['text']['before'] = wp_kses( wp_unslash( $_POST[ $option_name ] ), wp_kses_allowed_html( 'post' ) );
		}
		$option_name = 'text-after';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options['text']['after'] = wp_kses( wp_unslash( $_POST[ $option_name ] ), wp_kses_allowed_html( 'post' ) );
		}

		// Store updated options array to database.
		update_option( 'azrcrv-lig', $options );

		// Redirect the page to the configuration form that was processed.
		wp_safe_redirect( add_query_arg( array( 'page' => 'azrcrv-lig', 'settings-updated' => 'true' ), admin_url( 'admin.php' ) ) );
		exit;
	}
}


/**
 * Display form.
 *
 * @since 1.0.0
 */
function display_form( $atts, $content = null ) {

	global $wp;

	if ( isset( $_POST['lorem-ipsum-form-id'] ) ) {
		$responses = process_form();
	}

	// get options with defaults.
	$options = get_option_with_defaults( 'azrcrv-lig' );

	// get shortcode attributes.
	$args = shortcode_atts(
		array(
			'id'                      => '',
			'word-count'              => $options['lorem-ipsum']['word-count'],
			'maximum-word-count'      => $options['lorem-ipsum']['maximum-word-count'],
			'paragraph-count'         => $options['lorem-ipsum']['paragraph-count'],
			'maximum-paragraph-count' => $options['lorem-ipsum']['maximum-paragraph-count'],
			'start-with-lorem'        => $options['lorem-ipsum']['start-with-lorem'],
			'text-before'             => $options['text']['before'],
			'text-after'              => $options['text']['after'],
			'label-word-count'        => $options['labels']['word-count'],
			'label-paragraph-count'   => $options['labels']['paragraph-count'],
		),
		$atts
	);

	// sanitize shortcode attributes.
	$id                      = sanitize_text_field( wp_unslash( $args['id'] ) );
	$word_count              = (int) sanitize_text_field( wp_unslash( $args['word-count'] ) );
	$maximum_word_count      = (int) sanitize_text_field( wp_unslash( $args['maximum-word-count'] ) );
	$paragraph_count         = (int) sanitize_text_field( wp_unslash( $args['paragraph-count'] ) );
	$maximum_paragraph_count = (int) sanitize_text_field( wp_unslash( $args['maximum-paragraph-count'] ) );
	$start_with_lorem        = (int) sanitize_text_field( wp_unslash( $args['start-with-lorem'] ) );
	$text_before             = sanitize_text_field( wp_unslash( $args['text-before'] ) );
	$text_after              = sanitize_text_field( wp_unslash( $args['text-after'] ) );
	$label_word_count        = sanitize_text_field( wp_unslash( $args['label-word-count'] ) );
	$label_paragraph_count   = sanitize_text_field( wp_unslash( $args['label-paragraph-count'] ) );

	// is this a valid lorem ipsum form?
	if ( $id === '' ) {
		return '<div class="azrcrv-lig-form">
			<div class="azrcrv-lig-error">
				' . esc_html__( 'Lorem Ipsum Generator form cannot be displayed; an id must be provided.', 'azrcrv-lig' ) . '
			</div>
		</div>';
	}

	$messages = '';

	if ( isset( $responses ) && is_array( $responses ) ) {
		if ( isset( $responses['id'] ) && $id === $responses['id'] ) {
			if ( isset( $responses['fields']['word-count'] ) ) {
				$word_count = sanitize_text_field( wp_unslash( $responses['fields']['word-count'] ) );
			}
			if ( isset( $responses['fields']['paragraph-count'] ) ) {
				$paragraph_count = sanitize_text_field( wp_unslash( $responses['fields']['paragraph-count'] ) );
			}
			if ( isset( $responses['fields']['start-with-lorem'] ) ) {
				$start_with_lorem = (int) $responses['fields']['start-with-lorem'];
			}

			if ( is_array( $responses['messages'] ) ) {
				foreach ( $responses['messages'] as $response ) {
					if ( $response === 'error-invalid-nonce' ) {
						$messages .= '<div class="azrcrv-lig-notice azrcrv-lig-notice--error">' . esc_html__( 'Lorem ipsum text could not be generated.', 'azrcrv-lig' ) . '</div>';
					}
					if ( $response === 'error-processing' ) {
						$messages .= '<div class="azrcrv-lig-notice azrcrv-lig-notice--error">' . esc_html__( 'There was an error processing your request. Wait a moment and try again.', 'azrcrv-lig' ) . '</div>';
					}
					if ( $response === 'success-lorem-ipsum-generated' ) {
						$messages .= '<div class="azrcrv-lig-notice azrcrv-lig-notice--success">' . esc_html__( 'Lorem ipsum text generated successfully.', 'azrcrv-lig' ) . '</div>';
					}
				}
			}
		}
	}

	$lorem_ipsum_form = render_form(
		$id,
		$messages,
		$text_before,
		$text_after,
		$label_word_count,
		$word_count,
		$maximum_word_count,
		$label_paragraph_count,
		$paragraph_count,
		$maximum_paragraph_count,
		$start_with_lorem,
		isset( $responses ) && is_array( $responses ) ? $responses : array()
	);

	if ( isset( $_POST['lorem-ipsum-form-id'] ) ) {
		$lorem_ipsum_form .= '<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>';
	}

	return $lorem_ipsum_form;

}


/**
 * Render the lorem ipsum form HTML.
 *
 * @since 1.0.0
 */
function render_form(
	$id,
	$messages,
	$text_before,
	$text_after,
	$label_word_count,
	$word_count,
	$maximum_word_count,
	$label_paragraph_count,
	$paragraph_count,
	$maximum_paragraph_count,
	$start_with_lorem,
	$responses
) {
	global $wp;

	$current_url = home_url( add_query_arg( array(), $wp->request ) );

	$field_start_with_lorem = '<div class="azrcrv-lig-field">
		<label class="azrcrv-lig-field__label" for="start-with-lorem">' . esc_html__( 'Start with "Lorem ipsum dolor sit amet"', 'azrcrv-lig' ) . '</label>
		<div class="azrcrv-lig-field__input">
			<input name="start-with-lorem" type="checkbox" id="start-with-lorem" value="1" ' . checked( 1, (int) $start_with_lorem, false ) . ' />
		</div>
	</div>';

	$field_word_count = '<div class="azrcrv-lig-field">
		<label class="azrcrv-lig-field__label" for="word-count">' . esc_html( wp_unslash( $label_word_count ) ) . '</label>
		<div class="azrcrv-lig-field__input">
			<input name="word-count" type="number" id="word-count" min="1" max="' . esc_attr( $maximum_word_count ) . '" step="1" value="' . esc_attr( wp_unslash( $word_count ) ) . '" />
		</div>
	</div>';

	$field_paragraph_count = '<div class="azrcrv-lig-field">
		<label class="azrcrv-lig-field__label" for="paragraph-count">' . esc_html( wp_unslash( $label_paragraph_count ) ) . '</label>
		<div class="azrcrv-lig-field__input">
			<input name="paragraph-count" type="number" id="paragraph-count" min="1" max="' . esc_attr( $maximum_paragraph_count ) . '" step="1" value="' . esc_attr( wp_unslash( $paragraph_count ) ) . '" />
		</div>
	</div>';

	$text_before_html = '';
	if ( strlen( $text_before ) > 0 ) {
		$text_before_html = '<p class="azrcrv-lig-text">' . esc_html( $text_before ) . '</p>';
	}
	$text_after_html = '';
	if ( strlen( $text_after ) > 0 ) {
		$text_after_html = '<p class="azrcrv-lig-text">' . esc_html( $text_after ) . '</p>';
	}

	$lorem_ipsum_output = '';
	if ( ! empty( $responses['paragraphs'] ) ) {
		$count   = count( $responses['paragraphs'] );
		$heading = $count === 1
			? esc_html__( 'Your lorem ipsum text', 'azrcrv-lig' )
			: esc_html__( 'Your lorem ipsum paragraphs', 'azrcrv-lig' );
		$paragraph_rows = '';
		foreach ( $responses['paragraphs'] as $index => $paragraph ) {
			$paragraph_rows .= '<div class="azrcrv-lig-paragraph-row">
				<p class="azrcrv-lig-paragraph-text" id="azrcrv-lig-para-' . $index . '">' . esc_html( $paragraph ) . '</p>
			</div>';
		}
		$lorem_ipsum_output = '<div class="azrcrv-lig-paragraph">
			<div class="azrcrv-lig-paragraph-header">
				<h4 class="azrcrv-lig-paragraph-heading">' . $heading . '
				<button type="button" class="azrcrv-lig-copy-btn" id="azrcrv-lig-copy-all" onclick="azrcrvLigCopyAll(this)" aria-label="' . esc_attr__( 'Copy all lorem ipsum text', 'azrcrv-lig' ) . '">&#x1F4CB;</button></h4>
			</div>
			<div id="azrcrv-lig-all-paragraphs">
				' . $paragraph_rows . '
			</div>
		</div>
		<script>
		function azrcrvLigCopyAll( btn ) {
			var container = document.getElementById( "azrcrv-lig-all-paragraphs" );
			var paras = container.querySelectorAll( ".azrcrv-lig-paragraph-text" );
			var texts = [];
			paras.forEach( function( p ) {
				texts.push( p.innerText );
			} );
			var fullText = texts.join( "\n\n" );
			navigator.clipboard.writeText( fullText ).then( function() {
				btn.innerHTML = "&#x2713; ' . esc_js( __( 'Copied!', 'azrcrv-lig' ) ) . '";
				btn.classList.add( "azrcrv-lig-copy-btn--copied" );
				setTimeout( function() {
					btn.innerHTML = "&#x1F4CB; ' . esc_js( __( 'Copy All', 'azrcrv-lig' ) ) . '";
					btn.classList.remove( "azrcrv-lig-copy-btn--copied" );
				}, 2000 );
			} );
		}
		</script>';
	}

	$messages_html = ! empty( $messages ) ? $messages : '';

	return '<div class="azrcrv-lig-form">

		' . $messages_html . '

		' . $lorem_ipsum_output . '

		<form method="post" id="azrcrv-lorem-ipsum-form" action="' . esc_attr( $current_url ) . '">

			' . $text_before_html . '

			<input name="lorem-ipsum-form-id" type="hidden" value="' . esc_attr( $id ) . '" />' .
			wp_nonce_field( 'azrcrv-lig-lorem-ipsum-form', 'azrcrv-lig-lorem-ipsum-form-nonce', true, false )
			. '<div class="azrcrv-lig-fields">
				' . $field_start_with_lorem . '
				' . $field_word_count . '
				' . $field_paragraph_count . '
			</div>

			' . $text_after_html . '

			<div class="azrcrv-lig-actions">
				<input type="submit" name="submit" value="' . esc_html__( 'Generate', 'azrcrv-lig' ) . '" class="button-primary" />
			</div>

		</form>

	</div>';
}


/**
 * Process lorem ipsum form after submit.
 *
 * @since 1.0.0
 */
function process_form() {
	if ( ! isset( $_POST['lorem-ipsum-form-id'] ) ) {
		return;
	}

	if ( ! isset( $_POST['azrcrv-lig-lorem-ipsum-form-nonce'] ) || ! wp_verify_nonce( $_POST['azrcrv-lig-lorem-ipsum-form-nonce'], 'azrcrv-lig-lorem-ipsum-form' ) ) {
		return array(
			'id'         => sanitize_text_field( wp_unslash( $_POST['lorem-ipsum-form-id'] ) ),
			'messages'   => array( 'error-invalid-nonce' ),
			'paragraphs' => array(),
			'fields'     => array(),
		);
	}

	$options = get_option_with_defaults( 'azrcrv-lig' );

	$responses = array(
		'id'         => sanitize_text_field( wp_unslash( $_POST['lorem-ipsum-form-id'] ) ),
		'messages'   => array(),
		'paragraphs' => array(),
	);

	if ( isset( $_POST['word-count'] ) && $_POST['word-count'] >= 1 && $_POST['word-count'] <= $options['lorem-ipsum']['maximum-word-count'] ) {
		$responses['fields']['word-count'] = (int) sanitize_text_field( wp_unslash( $_POST['word-count'] ) );
	} else {
		$responses['fields']['word-count'] = (int) sanitize_text_field( wp_unslash( $options['lorem-ipsum']['word-count'] ) );
	}
	if ( isset( $_POST['paragraph-count'] ) && $_POST['paragraph-count'] >= 1 && $_POST['paragraph-count'] <= $options['lorem-ipsum']['maximum-paragraph-count'] ) {
		$responses['fields']['paragraph-count'] = (int) sanitize_text_field( wp_unslash( $_POST['paragraph-count'] ) );
	} else {
		$responses['fields']['paragraph-count'] = (int) sanitize_text_field( wp_unslash( $options['lorem-ipsum']['paragraph-count'] ) );
	}
	$responses['fields']['start-with-lorem'] = isset( $_POST['start-with-lorem'] ) ? 1 : 0;

	if ( count( $responses['messages'] ) === 0 ) {
		$responses['paragraphs'] = generate_lorem_ipsum(
			$responses['fields']['word-count'],
			$responses['fields']['paragraph-count'],
			$responses['fields']['start-with-lorem']
		);
		$responses['messages'][] = 'success-lorem-ipsum-generated';
	}

	return $responses;

}

/**
 * Generate lorem ipsum text from a pseudo-latin word list.
 *
 * @since 1.0.0
 */
function generate_lorem_ipsum( $word_count, $paragraph_count, $start_with_lorem ) {

	// Pseudo-latin word list (~1000 words).
	$word_list = array(
		'lorem','ipsum','dolor','sit','amet','consectetur','adipiscing','elit','sed','do',
		'eiusmod','tempor','incididunt','ut','labore','et','dolore','magna','aliqua','enim',
		'ad','minim','veniam','quis','nostrud','exercitation','ullamco','laboris','nisi','aliquip',
		'ex','ea','commodo','consequat','duis','aute','irure','in','reprehenderit','voluptate',
		'velit','esse','cillum','eu','fugiat','nulla','pariatur','excepteur','sint','occaecat',
		'cupidatat','non','proident','sunt','culpa','qui','officia','deserunt','mollit','anim',
		'id','est','laborum','perspiciatis','unde','omnis','natus','error','voluptatem','accusantium',
		'doloremque','laudantium','totam','rem','aperiam','eaque','ipsa','quae','ab','illo',
		'inventore','veritatis','quasi','architecto','beatae','vitae','dicta','explicabo','nemo',
		'ipsam','voluptas','aspernatur','aut','odit','fugit','magni','dolores','ratione',
		'sequi','nesciunt','neque','porro','quisquam','dolorem','adipisci','numquam','eius',
		'modi','tempora','incidunt','magnam','aliquam','quaerat','voluptatibus','maiores',
		'alias','perferendis','doloribus','asperiores','repellat','minima','nostrum','exercitationem',
		'ullam','corporis','suscipit','laboriosam','aliquid','commodi','consequatur','quidem',
		'maxime','placeat','facere','possimus','assumenda','repellendus',
		'temporibus','autem','quibusdam','officiis','debitis','rerum','necessitatibus','saepe',
		'eveniet','voluptates','repudiandae','recusandae','itaque','earum','hic','tenetur',
		'sapiente','delectus','reiciendis','harum','facilis',
		'expedita','distinctio','libero','tempore','cumque','soluta','nobis','eligendi','optio',
		'nihil','impedit','minus','quod','vero','praesentium','voluptatum','deleniti','atque',
		'corrupti','quos','molestias','excepturi','occaecati','cupiditate','provident','similique',
		'mollitia','blanditiis','illum','quo','dignissimos','ducimus','nam','accusamus','iusto',
		'odio','molestiae','vitiosus','fuga','semper','quia','consequuntur','eos','vitae',
		'inventore','beatae','perspiciatis','accusantium','laudantium','aperiam','eaque',
		'aspernatur','odit','magni','sequi','nesciunt','porro','quisquam','adipisci','numquam',
		'modi','tempora','incidunt','magnam','quaerat','alias','perferendis','asperiores',
		'repellat','exercitationem','ullam','corporis','laboriosam','aliquid','consequatur',
		'maxime','repellendus','autem','quibusdam','officiis','necessitatibus','saepe',
		'eveniet','repudiandae','recusandae','itaque','earum','tenetur','sapiente','delectus',
		'reiciendis','maiores','harum','facilis','expedita','distinctio','libero','cumque',
		'nihil','minus','voluptatum','atque','excepturi','cupiditate','accusamus','odio',
		'dignissimos','ducimus','blanditiis','deleniti','corrupti','molestias','provident',
		'mollitia','dolorem','quo','nulla','accusamus','iusto','ducimus','nam','libero',
		'soluta','nobis','eligendi','cumque','maxime','facere','possimus','assumenda','vitiosus',
		'praesentium','similique','deserunt','mollitia','laborum','rerum','expedita',
		'vitae','nemo','ipsam','aspernatur','fugit','ratione','nesciunt','porro',
		'adipisci','numquam','modi','labore','aliquam','voluptatibus','maiores',
		'doloribus','asperiores','repellat','minima','corporis',
		'laboriosam','commodi','quidem','placeat','repellendus','temporibus','officiis',
		'debitis','necessitatibus','eveniet','repudiandae','recusandae','earum',
		'sapiente','reiciendis','voluptatibus','facilis','expedita','distinctio','tempore',
		'soluta','nobis','optio','impedit','quod','vero','accusamus','iusto','dignissimos',
		'voluptatum','corrupti','molestias','occaecati','similique','mollitia','blanditiis',
		'illum','pariatur','ducimus','molestiae','nobis','eligendi','impedit','vitiosus',
		'omnis','assumenda','praesentium','similique','culpa','officia','mollitia','laborum',
		'fuga','harum','rerum','expedita','distinctio','vitae','nemo','voluptas',
		'aspernatur','odit','fugit','magni','sequi','quisquam','adipisci','eius','tempora',
		'incidunt','magnam','quaerat','alias','perferendis','repellat','nostrum',
		'exercitationem','ullam','suscipit','laboriosam','aliquid','consequatur',
		'maxime','repellendus','temporibus','autem','officiis',
		'necessitatibus','eveniet','voluptates','itaque','hic','tenetur','sapiente','delectus',
		'maiores','facilis','libero','tempore','soluta','eligendi','optio','nihil','minus',
		'quod','eos','vero','odio','dignissimos','ducimus','blanditiis','voluptatum','corrupti',
		'molestias','excepturi','cupiditate','provident','mollitia','dolorem','quo','nulla',
		'voluptas','minima','sed','eiusmod','incididunt','labore','dolore','magna',
		'aliqua','minim','veniam','nostrud','ullamco','laboris','aliquip','commodo',
		'irure','reprehenderit','voluptate','velit','cillum','fugiat','excepteur',
		'occaecat','cupidatat','proident','culpa','officia','deserunt','mollit',
		'anim','laborum','perspiciatis','natus','accusantium','laudantium','totam',
		'aperiam','eaque','ipsa','inventore','architecto','beatae','explicabo','nemo',
		'quasi','veritatis','enim','minim','veniam','exercitation','nostrud','ullamco',
		'laboris','aliquip','commodo','consequat','aute','irure','voluptate','velit',
		'cillum','sint','occaecat','cupidatat','proident','deserunt','mollit','anim',
		'perspiciatis','error','voluptatem','laudantium','totam','aperiam','eaque','ipsa',
		'inventore','beatae','dicta','explicabo','ipsam','aspernatur','odit','fugit',
		'magni','sequi','nesciunt','neque','porro','quisquam','adipisci','numquam','modi',
		'tempora','incidunt','aliquam','quaerat','voluptatibus','alias','perferendis',
		'doloribus','asperiores','repellat','minima','exercitationem','ullam','corporis',
		'suscipit','laboriosam','aliquid','commodi','consequatur','quidem','placeat',
		'repellendus','temporibus','autem','quibusdam','officiis','debitis','rerum',
		'necessitatibus','saepe','eveniet','voluptates','repudiandae','recusandae','itaque',
		'earum','hic','tenetur','sapiente','delectus','reiciendis','voluptatibus','maiores',
	);

	$total_words         = count( $word_list );
	$paragraphs          = array();
	$words_per_paragraph = max( 1, (int) ceil( $word_count / $paragraph_count ) );

	for ( $p = 0; $p < $paragraph_count; $p++ ) {
		// Last paragraph gets remainder of words.
		$words_in_this_paragraph = ( $p === $paragraph_count - 1 )
			? max( 1, $word_count - ( $words_per_paragraph * ( $paragraph_count - 1 ) ) )
			: $words_per_paragraph;

		$para_words = array();

		// Optionally open first paragraph with classic incipit.
		if ( $p === 0 && $start_with_lorem ) {
			$opening = array( 'Lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit' );
			$use     = min( count( $opening ), $words_in_this_paragraph );
			for ( $i = 0; $i < $use; $i++ ) {
				$para_words[] = $opening[ $i ];
			}
			$remaining = $words_in_this_paragraph - count( $para_words );
			for ( $i = 0; $i < $remaining; $i++ ) {
				$para_words[] = $word_list[ wp_rand( 0, $total_words - 1 ) ];
			}
		} else {
			for ( $i = 0; $i < $words_in_this_paragraph; $i++ ) {
				$para_words[] = $word_list[ wp_rand( 0, $total_words - 1 ) ];
			}
		}

		// Capitalise first word of paragraph.
		if ( ! empty( $para_words ) ) {
			$para_words[0] = ucfirst( $para_words[0] );
		}

		// Break into sentences of 8-15 words each.
		$sentence       = array();
		$sentence_texts = array();
		$word_index     = 0;
		$total_in_para  = count( $para_words );

		foreach ( $para_words as $word ) {
			$sentence[] = $word;
			$word_index++;
			$is_last = ( $word_index === $total_in_para );

			if ( $is_last || count( $sentence ) >= wp_rand( 8, 15 ) ) {
				$s                = ucfirst( implode( ' ', $sentence ) );
				$sentence_texts[] = $s . '.';
				$sentence         = array();
			}
		}

		$paragraphs[] = implode( ' ', $sentence_texts );
	}

	return $paragraphs;

}
