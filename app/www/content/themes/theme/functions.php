<?php

class Jobs_Theme {
	public function __construct() {
		$this->init();
	}

	public function init() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'theme-style', get_stylesheet_directory_uri() . '/style.' . ( ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) ? 'min.' : '' ) . 'css', array(), filemtime(get_stylesheet_directory() . '/style.' . ( ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) ? 'min.' : '' ) . 'css') );
	}
}

new Jobs_Theme();
