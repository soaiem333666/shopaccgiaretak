<?php

namespace EasyWPSMTP\Providers\Mail;

use EasyWPSMTP\Providers\OptionsAbstract;

/**
 * Class Option.
 *
 * @since 2.0.0
 */
class Options extends OptionsAbstract {

	/**
	 * Mail constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$description = wp_kses(
			__( 'The Default (none) mailer uses the default PHP mail function and will not improve email deliverability. Please select one of our compatible mailers to start sending emails with Easy WP SMTP.', 'easy-wp-smtp' ),
			[
				'strong' => [],
				'a'      => [
					'href'   => [],
					'rel'    => [],
					'target' => [],
				],
			]
		);

		parent::__construct(
			array(
				'logo_url'    => easy_wp_smtp()->assets_url . '/images/providers/php.svg',
				'slug'        => 'mail',
				'title'       => esc_html__( 'Default (none)', 'easy-wp-smtp' ),
				'description' => $description,
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_options() {}
}
