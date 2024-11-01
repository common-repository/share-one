<?php
/*
Plugin Name: Share.one
Description: Show professional video testimonials by Share.one in an easy way
Version: 1.0.1
Author: Share.one
Author URI: https://www.share.one/
License: GPL3
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

const SHARE_ONE_PLUGIN_VERSION = '1.0.1';

// Define the option name for the UUID and script toggle
const SHARE_ONE_OPTION_CATCH_SERVER  = 'share_one_catch_server';
const SHARE_ONE_OPTION_UUID          = 'share_one_uuid';
const SHARE_ONE_OPTION_ENABLE_WIDGET = 'share_one_enable_widget';

// Include the admin page
add_action( 'admin_menu', 'share_one_add_admin_page' );
add_action( 'admin_init', 'share_one_register_settings' );

function share_one_add_admin_page() {
	add_options_page(
		'Share.one Settings',
		'Share.one',
		'manage_options',
		'share-one',
		'share_one_admin_page'
	);
}

function share_one_register_settings() {
	register_setting( 'share_one_settings_group', SHARE_ONE_OPTION_CATCH_SERVER, [
			'default' => 'https://catch.share.one',
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw'
		] );
	register_setting( 'share_one_settings_group', SHARE_ONE_OPTION_UUID, [
			'default' => '',
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field'
	] );
	register_setting( 'share_one_settings_group', SHARE_ONE_OPTION_ENABLE_WIDGET, [
			'default' => 0,
			'type' => 'integer',
			'sanitize_callback' => 'absint'
	] );
}

function share_one_admin_page() {
	?>
	<div class="wrap">
		<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/share-one-logo.png' ) ?>" alt="Share.one"
		     style="height: 50px; margin-bottom: 20px;"/>
		<h1>Settings</h1>
		<p>
			This plugin allows you to easily integrate your Share.one testimonials into your WordPress website.
		</p>
		<p>
			If you are not a Share.one user yet, you can get more information at <a href="https://share.one"
			                                                                        target="_blank">share.one</a>.
		</p>
		<p>
			To get started, enter your Share.one UUID below. This UUID will be provided by Share.one.
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'share_one_settings_group' ); ?>
			<?php do_settings_sections( 'share_one_settings_group' ); ?>

			<input type="hidden" name="<?php echo esc_attr(SHARE_ONE_OPTION_CATCH_SERVER); ?>" value="https://catch.share.one"/>
			<div class="share-one-settings">
				<div class="share-one-setting">
					<label for="<?php echo esc_attr(SHARE_ONE_OPTION_UUID); ?>"><strong>Your UUID</strong></label>
					<input type="text" id="<?php echo esc_attr(SHARE_ONE_OPTION_UUID); ?>"
					       name="<?php echo esc_attr(SHARE_ONE_OPTION_UUID); ?>"
					       value="<?php echo esc_attr( get_option( SHARE_ONE_OPTION_UUID ) ); ?>"/>
				</div>

				<div class="share-one-setting">
					<label for="<?php echo esc_attr(SHARE_ONE_OPTION_ENABLE_WIDGET); ?>"><strong>Enable Catch
							Widget</strong></label>
					<div class="share-one-setting-with-checkbox">
						<input type="checkbox" id="<?php echo esc_attr(SHARE_ONE_OPTION_ENABLE_WIDGET); ?>"
						       name="<?php echo esc_attr(SHARE_ONE_OPTION_ENABLE_WIDGET); ?>"
						       value="1" <?php checked( 1, get_option( SHARE_ONE_OPTION_ENABLE_WIDGET ), true ); ?> />
						Enable the Catch Widget to show a small widget on your website that allows visitors to record a
						video testimonial for you. The settings for this widget are managed by Share.one. Please contact
						us to carry out any change.
					</div>
				</div>

				<div class="share-one-setting">
					<label>Wall of Love</label>
					<p>
						After saving your settings, you can use the following shortcode to render the Wall of Love on
						your website:
					</p>
					<code>[share_one_wall_of_love]</code>
					<p>
						You can also use the Share.one Wall of Love Gutenberg block to add the Wall of Love to your
						pages and posts.
					</p>
				</div>

				<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_enqueue_scripts', 'share_one_enqueue_admin_styles' );

function share_one_enqueue_admin_styles() {
	wp_enqueue_style( 'share-one-admin-styles', plugin_dir_url( __FILE__ ) . 'assets/admin-styles.css', array(), SHARE_ONE_PLUGIN_VERSION );
}


add_action( 'wp_enqueue_scripts', 'share_one_enqueue_catch_widget' );

function share_one_enqueue_catch_widget() {
	if ( get_option( SHARE_ONE_OPTION_ENABLE_WIDGET ) ) {
		$uuid         = esc_attr( get_option( SHARE_ONE_OPTION_UUID ) );
		$catch_server = esc_attr( get_option( SHARE_ONE_OPTION_CATCH_SERVER ) );
		if ( ! empty( $uuid ) ) {
			wp_enqueue_script( 'share-one-catch-widget', $catch_server . '/' . $uuid . '.js', array(), SHARE_ONE_PLUGIN_VERSION, array( 'strategy' => 'defer' ) );
		}
	}
}

// Shortcode to render the Wall of Love JS snippet
function share_one_render_wall_of_love_shortcode() {
	$uuid         = esc_attr( get_option( SHARE_ONE_OPTION_UUID ) );
	$catch_server = esc_attr( get_option( SHARE_ONE_OPTION_CATCH_SERVER ) );

	$div_id = uniqid('share-one-wall-of-love-', true);

	if ( $uuid ) {
		wp_enqueue_script( $div_id, $catch_server . '/walloflove/' . $uuid . '.js?div_id=' . $div_id, array(), SHARE_ONE_PLUGIN_VERSION, array( 'strategy' => 'defer' ) );
		return "<div id='" . $div_id . "'></div>";
	}

	return '';
}

add_shortcode( 'share_one_wall_of_love', 'share_one_render_wall_of_love_shortcode' );

// Register Gutenberg block
function share_one_register_gutenberg_block() {
	// Automatically load dependencies and version
	$asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php' );

	wp_register_script(
		'share-one-wall-of-love-block',
		plugins_url( 'build/index.js', __FILE__ ),
		$asset_file['dependencies'],
		$asset_file['version'],
		['in_footer' => true]
	);

	// Get the UUID from the plugin's settings
	$server_url = get_option( SHARE_ONE_OPTION_CATCH_SERVER );
	$uuid       = get_option( SHARE_ONE_OPTION_UUID );

	// Pass the UUID to the block script
	wp_localize_script( 'share-one-wall-of-love-block', 'shareOneData', array(
		'serverUrl' => $server_url,
		'uuid'      => $uuid,
	) );

	register_block_type( 'share-one/wall-of-love', array(
		'editor_script' => 'share-one-wall-of-love-block',
	) );
}

add_action( 'init', 'share_one_register_gutenberg_block' );

// Include the Elementor widget file
include_once plugin_dir_path( __FILE__ ) . 'wall-of-love-elementor-widget.php';

function share_one_register_elementor_widget() {
	\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Share_One_Wall_of_Love_Widget() );
}

add_action( 'elementor/widgets/widgets_registered', 'share_one_register_elementor_widget' );

