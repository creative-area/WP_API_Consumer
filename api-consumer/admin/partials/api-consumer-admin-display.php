<?php
/**
 * Provide the admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    api_consumer
 * @subpackage api_consumer/admin/partials
 */

?>

<div class="wrap">
	<h1><?php esc_html_e( 'API Consumer', 'load_plugin_textdomain' ) ?></h1>
	<form action='options.php' method='post'>
		<?php
		settings_fields( $this->prefix . '_optionpage' );
		do_settings_sections( $this->prefix . '_optionpage' );
		submit_button();
		?>
	</form>
</div>
