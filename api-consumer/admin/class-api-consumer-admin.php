<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    API_Consumer
 * @subpackage API_Consumer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    api_consumer
 * @subpackage api_consumer/admin
 * @author     CREATIVE AREA
 */
class API_Consumer_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since   0.0.1
	 * @access  private
	 * @var     string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The namespace of this plugin.
	 *
	 * @since   0.0.1
	 * @access  private
	 * @var     string $prefix The namespace of this plugin.
	 */
	private $prefix;

	/**
	 * The version of this plugin.
	 *
	 * @since   0.0.1
	 * @access  private
	 * @var     string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 0.0.1
	 * @param string $plugin_name Plugin name.
	 * @param string $prefix Namespace.
	 * @param string $version Version.
	 */
	public function __construct( $plugin_name, $prefix, $version ) {
		$this->plugin_name = $plugin_name;
		$this->prefix = $prefix;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 0.0.1
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/api-consumer-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 0.0.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/api-consumer-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	 * Render admin menu link.
	 *
	 * @since 0.0.1
	 */
	public function render_admin_menu() {
		add_options_page( 'API Consumer configuration', 'API Consumer', 'manage_options', $this->plugin_name, array( $this, 'render_options_page' ) );
	}

	/**
	 * Init admin page.
	 *
	 * @since 0.0.1
	 */
	public function init_admin_page() {

		add_settings_section(
			$this->prefix . '_optionpage_main',
			__( 'Main settings', 'load_plugin_textdomain' ),
			array( $this, 'fill_main_section' ),
			$this->prefix . '_optionpage'
		);

		add_settings_field(
			$this->prefix . '_uri',
			__( 'Web Service URI', 'load_plugin_textdomain' ),
			array( $this, 'render_settings_field' ),
			$this->prefix . '_optionpage',
			$this->prefix . '_optionpage_main',
			array(
				'field_id' => $this->prefix . '_uri',
				'field_name' => $this->prefix . '_uri',
				'label_for' => $this->prefix . '_uri',
				'option' => $this->prefix . '_uri',
			)
		);

		add_settings_field(
			$this->prefix . '_username',
			__( 'Username', 'load_plugin_textdomain' ),
			array( $this, 'render_settings_field' ),
			$this->prefix . '_optionpage',
			$this->prefix . '_optionpage_main',
			array(
				'field_id' => $this->prefix . '_username',
				'field_name' => $this->prefix . '_username',
				'label_for' => $this->prefix . '_username',
				'option' => $this->prefix . '_username',
			)
		);

		add_settings_field(
			$this->prefix . '_password',
			__( 'Password', 'load_plugin_textdomain' ),
			array( $this, 'render_settings_field' ),
			$this->prefix . '_optionpage',
			$this->prefix . '_optionpage_main',
			array(
				'field_id' => $this->prefix . '_password',
				'field_name' => $this->prefix . '_password',
				'label_for' => $this->prefix . '_password',
				'option' => $this->prefix . '_password',
			)
		);

		add_settings_field(
			$this->prefix . '_root',
			__( 'Data root key', 'load_plugin_textdomain' ),
			array( $this, 'render_settings_field' ),
			$this->prefix . '_optionpage',
			$this->prefix . '_optionpage_main',
			array(
				'field_id' => $this->prefix . '_root',
				'field_name' => $this->prefix . '_root',
				'label_for' => $this->prefix . '_root',
				'option' => $this->prefix . '_root',
			)
		);

		register_setting( $this->prefix . '_optionpage', $this->prefix . '_uri' );
		register_setting( $this->prefix . '_optionpage', $this->prefix . '_username' );
		register_setting( $this->prefix . '_optionpage', $this->prefix . '_password' );
		register_setting( $this->prefix . '_optionpage', $this->prefix . '_root' );

		add_settings_section(
			$this->prefix . '_optionpage_customfields',
			__( 'Custom fields', 'load_plugin_textdomain' ),
			array( $this, 'fill_customfields_section' ),
			$this->prefix . '_optionpage'
		);

		$customfields_option = $this->prefix . '_customfields';

		for ( $i = 1; $i < 11; $i++ ) {

			$str_num = str_pad( $i, 2, '0', STR_PAD_LEFT );
			$slug = $this->prefix . '_field_' . $str_num;
			$field_id = $customfields_option . '_' . $str_num;
			$field_name = $customfields_option . '[' . $slug . ']';

			add_settings_field(
				$slug,
				__( 'Field', 'load_plugin_textdomain' ) . ' #' . $str_num,
				array( $this, 'render_settings_field' ),
				$this->prefix . '_optionpage',
				$this->prefix . '_optionpage_customfields',
				array(
					'slug' => $slug,
					'field_id' => $field_id,
					'field_name' => $field_name,
					'label_for' => $field_id,
					'option' => $customfields_option,
				)
			);
		}

		register_setting( $this->prefix . '_optionpage',  $customfields_option );

	}

	/**
	 * Fill admin page main section content.
	 *
	 * @since 0.0.1
	 */
	public function fill_main_section() {
		esc_html_e( 'Fill your API info below', 'load_plugin_textdomain' );
	}

	/**
	 * Fill admin page custom fields section content.
	 *
	 * @since 0.0.1
	 */
	public function fill_customfields_section() {
		esc_html_e( 'Fill up to 10 field keys you want to fetch from the API above. Please refer the documentation to know how to render those data.', 'load_plugin_textdomain' );
	}

	/**
	 * Render settings field.
	 *
	 * @since 0.0.1
	 * @param array $args Field parameters.
	 */
	public function render_settings_field( $args ) {
		$option = get_option( $args['option'] );
		echo '<input type="text"
			class="regular-text"
			id="' . esc_attr( $args['field_id'] ) . '"
			name="' . esc_attr( $args['field_name'] ) . '"
			value="' . esc_attr( ( is_array( $option ) ? $option[ $args['slug'] ] : $option ) ) . '"
		>';
	}

	/**
	 * Render Admin settings page.
	 *
	 * @since 0.0.1
	 */
	public function render_options_page() {
		include( plugin_dir_path( __FILE__ ) . 'partials/api-consumer-admin-display.php' );
	}
}
