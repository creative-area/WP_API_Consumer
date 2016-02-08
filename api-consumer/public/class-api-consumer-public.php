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
	 */
	private function fetch_api_data() {

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

		return $items;
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
			'wrapper-class' => 'api-block-wrapper',
			'limit' => 10,
			'filters' => '',
		), $atts );

		$data = $this->fetch_api_data();

		$output .= '<div class="' . esc_attr( $shortcode_atts['wrapper-class'] ) . '">';
		$limit = (int) esc_attr( $shortcode_atts['limit'] );

		$filters_pattern = '/\{?\s?(?<key>.*?)\s?:\s?(?<val>["].*["]?|.*?)[,\W]\}?/';
		preg_match_all( $filters_pattern, $shortcode_atts['filters'], $filters_matches );
		if ( ! empty( $filters_matches[0] ) ) {
			$filters = array();
			foreach ( $filters_matches['key'] as $key => $filter_key ) {
				$filters[ $filter_key ] = $filters_matches['val'][ $key ];
			}
		}

		$i = 0;
		foreach ( $data as $item ) {
			if ( $limit > $i ) {
				if ( ! empty( $filters ) ) {
					foreach ( $filters as $filter_key => $filter_val ) {
						if ( key_exists( $filter_key, $item ) && $item[ $filter_key ] === $filter_val ) {
							$output .= $this->parse_shortcode_content( $content, $item );
							$i++;
						}
					}
				} else {
					$output .= $this->parse_shortcode_content( $content, $item );
					$i++;
				}
			} else {
				break 1;
			}
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
		$pattern = '/{{([a-zA-Z0-9_]+)((?:\.[a-z_]+\((?:[^)]+)?\))*)}}/';
		$formatter_pattern = '/.([a-z_]+)\((?:([^)]*))?\)/';
		preg_match_all( $pattern, $content, $matches );
		foreach ( $matches[0] as $capture_key => $capture_val ) {
			$formatted_data = $data[ $matches[1][ $capture_key ] ];
			if ( ! empty( $matches[2][ $capture_key ] ) ) {
				preg_match_all( $formatter_pattern, $matches[2][ $capture_key ], $formatter_matches );
				foreach ( $formatter_matches[1] as $formatter_key => $formatter_val ) {
					if ( ! empty( $formatter_matches[2][ $formatter_key ] ) ) {
						$formatted_data = call_user_func( $formatter_val, $formatter_matches[2][ $formatter_key ], $formatted_data );
					} else {
						$formatted_data = call_user_func( $formatter_val, $formatted_data );
					}
				}
			}
			$content = str_replace( $capture_val, $formatted_data, $content );
		}
		return wp_kses_post( $content );
	}
}
