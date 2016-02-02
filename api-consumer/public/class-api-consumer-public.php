<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.creative-area.net
 * @since      0.0.1
 *
 * @package    API_Consumer
 * @subpackage API_Consumer/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    API_Consumer
 * @subpackage API_Consumer/public
 * @author     CREATIVE AREA
 */
class API_Consumer_Public
{
	/**
	 * The ID of this plugin.
	 *
	 * @since  0.0.1
	 * @access private
	 * @var    string $plugin_name The ID of this plugin.
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
	 * @since  0.0.1
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The shortcode of this plugin.
	 *
	 * @since  0.0.1
	 * @access private
	 * @var    string $shortcode_name The shortcode name of this plugin.
	 */
	private $shortcode_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 0.0.1
	 * @param string $plugin_name The name of the plugin.
	 * @param string $prefix The Namespace of the plugin.
	 * @param string $shortcode_name The shortcode name of the plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $prefix, $shortcode_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->prefix = $prefix;
		$this->version = $version;
		$this->shortcode_name = $shortcode_name;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since		0.0.1
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/api-consumer-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since		0.0.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/api-consumer-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Fetch data from given API.
	 *
	 * @since  0.0.1
	 * @access private
	 * @param  integer $limit Max data items to fetch.
	 */
	private function fetch_api_data( $limit = 10 ) {

		$api_uri = get_option( $this->prefix . '_uri' );
		$username = get_option( $this->prefix . '_username' );
		$password = get_option( $this->prefix . '_password' );
		$root = get_option( $this->prefix . '_root' );
		$custom_fields = get_option( $this->prefix . '_customfields' );

		if ( empty( $api_uri ) ) {
			return false;
		}

		$http_context = array( 'method' => 'GET', 'timeout' => 15 );
		$header  = "Content-Type: application/json\r\n";
		if ( ! empty( $username ) || ! empty( $password ) ) {
			$header .= 'Authorization: Basic ' . base64_encode( "$username:$password" ) . "\r\n";
		}
		$http_context['header'] = $header;
		$context = stream_context_create( array( 'http' => $http_context ) );

		$json_data = file_get_contents( $api_uri, false, $context );
		if ( false === $json_data ) {
			return false;
		}

		$data = json_decode( $json_data, true );
		$items = ( $root ) ? $data[ $root ] : $data;

		$result = array();
		if ( count( $items ) > 0 ) {
			$i = 0;
			foreach ( $items as $item ) {
				if ( $i < $limit ) {
					$tmp = array();
					foreach ( $custom_fields as $field_key => $field_value ) {
						if ( key_exists( $field_value, $item ) ) {
							$tmp[ $field_key ] = $item[ $field_value ];
						}
					}
					$result[] = $tmp;
					$i++;
				}
			}
		}

		return $result;
	}

	/**
	 * Register a shortcode
	 *
	 * @since		0.0.1
	 */
	public function register_shortcode() {
		add_shortcode( $this->shortcode_name, array( $this, 'shortcode' ) );
	}

	/**
	 * Shortcode callback
	 *
	 * @since 0.0.1
	 * @param array  $atts Shortcode attributes.
	 * @param string $content Shortcode content.
	 */
	public function shortcode( $atts, $content = null ) {

		$output = '';

		$shortcode_atts = shortcode_atts( array(
			'limit' => 10,
			'wrapper-class' => 'api-block-wrapper',
		), $atts );

		$limit = (int) esc_attr( $shortcode_atts['limit'] );
		$data = $this->fetch_api_data( $limit );

		$output .= '<div class="' . esc_attr( $shortcode_atts['wrapper-class'] ) . '">';
		foreach ( $data as $item ) {
			$output .= $this->parse_shortcode_content( $content, $item );
		}
		$output .= '</div>';

		return $output;
	}

	/**
	 * Parse shortcode content
	 *
	 * @since 0.0.1
	 * @param string $content Shortcode content.
	 * @param array  $data The data to be used.
	 */
	private function parse_shortcode_content( $content, $data ) {
		foreach ( $data as $key => $value ) {
			$content = str_replace( '{{' . $key . '}}', $value, $content );
		}
		return wp_kses_post( $content );
	}
}
