<?php

namespace EasyWPSMTP\Admin\Pages;

use EasyWPSMTP\Admin\ConnectionSettings;
use EasyWPSMTP\Admin\PageAbstract;
use EasyWPSMTP\Options;
use EasyWPSMTP\WP;

/**
 * Class SettingsTab is part of Area, displays general settings of the plugin.
 *
 * @since 2.0.0
 */
class SettingsTab extends PageAbstract {

	/**
	 * Slug of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $slug = 'settings';

	/**
	 * Link label of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Settings', 'easy-wp-smtp' );
	}

	/**
	 * Title of a tab.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->get_label();
	}

	/**
	 * Settings tab content.
	 *
	 * @since 2.0.0
	 */
	public function display() {
		?>

		<form method="POST" action="" autocomplete="off" class="easy-wp-smtp-connection-settings-form">
			<?php $this->wp_nonce_field(); ?>

			<?php ob_start(); ?>

			<?php
			$connection          = easy_wp_smtp()->get_connections_manager()->get_primary_connection();
			$connection_settings = new ConnectionSettings( $connection );

			// Display connection settings.
			$connection_settings->display();
			?>

			<?php
			$settings_content = apply_filters( 'easy_wp_smtp_admin_settings_tab_display', ob_get_clean() );
			echo $settings_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>

			<?php $this->display_save_btn(); ?>
		</form>
		<?php
	}

	/**
	 * Process tab form submission ($_POST).
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Post data specific for the plugin.
	 */
	public function process_post( $data ) {

		$this->check_admin_referer();

		$connection          = easy_wp_smtp()->get_connections_manager()->get_primary_connection();
		$connection_settings = new ConnectionSettings( $connection );

		$old_data = $connection->get_options()->get_all();

		$data = $connection_settings->process( $data, $old_data );

		/**
		 * Filters mail settings before save.
		 *
		 * @since 2.0.0
		 *
		 * @param array $data Settings data.
		 */
		$data = apply_filters( 'easy_wp_smtp_settings_tab_process_post', $data );

		// All the sanitization is done in Options class.
		Options::init()->set( $data, false, false );

		$connection_settings->post_process( $data, $old_data );

		if ( $connection_settings->get_scroll_to() !== false ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			wp_safe_redirect( sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ) . $connection_settings->get_scroll_to() );
			exit;
		}

		WP::add_admin_notice(
			esc_html__( 'Settings were successfully saved.', 'easy-wp-smtp' ),
			WP::ADMIN_NOTICE_SUCCESS
		);
	}
}
