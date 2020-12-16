<?php
/*
Plugin Name: Enable/Disable plugins when doing local dev
Plugin URL: https://gist.github.com/pbiron/52bb63042cf220256ece89bc07fb57b0
Description: If the WP_LOCAL_DEV constant is true, enables/disables plugins that you specify
Version: 0.1
License: GPL version 2 or any later version
Author: Paul V. Biron/Sparrow Hawk Computing
Author URI: https://sparrowhawkcomputing.com
*/

class CWS_Disable_Plugins {
	static $instance;
	private $disabled = array();
	/**
	 * Sets up the options filter, and optionally handles an array of plugins to disable
	 *
	 * @param array $disables Optional array of plugin filenames to disable
	 */
	public function __construct( array $disables = null ) {
		// Handle what was passed in
		if ( is_array( $disables ) ) {
			foreach ( $disables as $disable ) {
				$this->disable( $disable );
			}
		}
		// Add the filters
		add_filter( 'option_active_plugins', array( $this, 'do_disabling' ) );
		add_filter( 'site_option_active_sitewide_plugins', array( $this, 'do_network_disabling' ) );
		// Allow other plugins to access this instance
		self::$instance = $this;
	}
	/**
	 * Adds a filename to the list of plugins to disable
	 */
	public function disable( $file ) {
		$this->disabled[] = $file;
	}
	/**
	 * Hooks in to the option_active_plugins filter and does the disabling
	 *
	 * @param array $plugins WP-provided list of plugin filenames
	 * @return array The filtered array of plugin filenames
	 */
	public function do_disabling( $plugins ) {
		if ( count( $this->disabled ) ) {
			foreach ( (array) $this->disabled as $plugin ) {
				$key = array_search( $plugin, $plugins );
				if ( false !== $key ) {
					unset( $plugins[ $key ] );
				}
			}
		}
		return $plugins;
	}

	/**
	 * Hooks in to the site_option_active_sitewide_plugins filter and does the disabling
	 *
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function do_network_disabling( $plugins ) {
		if ( count( $this->disabled ) ) {
			foreach ( (array) $this->disabled as $plugin ) {
				if ( isset( $plugins[ $plugin ] ) ) {
					unset( $plugins[ $plugin ] );
				}
			}
		}
		return $plugins;
	}
}

/**
 * Inspired by https://gist.github.com/Rarst/4402927, which was inspired
 * by https://gist.github.com/markjaquith/1044546.
 *
 * The main difference between this plugin and those is how you call the
 * constructor and the enable() method.
 *
 * For the constructor, the array you pass should be an assoc array, with
 * keys 'network' and/or 'non-network', whose values are arrays of plugins
 * to enable.
 *
 * For the enable() method, there is an 2nd parameter, specifying whether you
 * want to enable the plugins network-wide or not (default 'non-network').
 *
 * In both cases, if installed in a non-multisite setup, any plugins specified
 * to be 'network' enabled will be 'non-network' enabled, so that if you use
 * a given network plugin on both multisite and non-multisite setups you can
 * use the same version of this mu-plugin across them all.
 */
class SHC_Enable_Plugins {
	static $instance;
	private $enabled = array(
		'non-network' => array(),
		'network'     => array(),
	);
	/**
	 * Sets up the options filter, and optionally handles an array of plugins to enable
	 *
	 * @param array $enables Optional array of plugin filenames to enable
	 */
	public function __construct( array $enables = null ) {
		// Handle what was passed in
		if ( is_array( $enables ) ) {
			foreach ( $enables as $which => $_enables ) {
				if ( is_array( $_enables ) ) {
					foreach ( $_enables as $enable ) {
						$this->enable( $enable, $which );
					}
				}
			}
		}
		// Add the filters
		add_filter( 'option_active_plugins', array( $this, 'do_enabling' ) );
		add_filter( 'site_option_active_sitewide_plugins', array( $this, 'do_network_enabling' ) );

		// Allow other plugins to access this instance
		self::$instance = $this;
	}
	/**
	 * Adds a filename to the list of plugins to enable
	 */
	public function enable( $file, $which = 'non-network' ) {
		if ( ! is_multisite() || ! in_array( $which, array( 'non-network', 'network' ) ) ) {
			$which = 'non-network';
		}
		$this->enabled[ $which ][] = $file;
	}
	/**
	 * Hooks in to the option_active_plugins filter and does the enabling
	 *
	 * @param array $plugins WP-provided list of plugin filenames
	 * @return array The filtered array of plugin filenames
	 */
	public function do_enabling( $plugins ) {
		return array_merge( $plugins, $this->enabled['non-network'] );
	}

	/**
	 * Hooks in to the site_option_active_sitewide_plugins filter and does the enabling
	 *
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function do_network_enabling( $plugins ) {
		foreach ( $this->enabled['network'] as $file ) {
			$plugins[ $file ] = time();
		}
		return $plugins;
	}
}

/* Begin customization */
if ( defined( 'WORDPRESS_ENV' ) && WORDPRESS_ENV === 'development' ) {
	new CWS_Disable_Plugins(
		array(
			// 'post-smtp/postman-smtp.php',
			// 'wp-rocket/wp-rocket.php',
		)
	);

	new SHC_Enable_Plugins(
		array(
			'non-network' => array(
				// 'disable-emails/disable-emails.php',
			),
		)
	);

	if ( ! defined( 'SITE_DOMAIN' ) || SITE_DOMAIN !== 'zomerkampen.test' ) {
		new SHC_Enable_Plugins(
			array(
				'non-network' => array(
					// 'redirect-to-login-if-not-logged-in/redirect-to-login-if-not-logged-in.php',
				),
			)
		);
	}
} elseif ( defined( 'WORDPRESS_ENV' ) && WORDPRESS_ENV === 'production' ) {
	new CWS_Disable_Plugins(
		array(
			// 'disable-emails/disable-emails.php',
		)
	);

	new SHC_Enable_Plugins(
		array(
			// 'post-smtp/postman-smtp.php',
		)
	);
}
