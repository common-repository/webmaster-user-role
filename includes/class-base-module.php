<?php

abstract class SCD_Base_Module {


	/**
	 * Core modules array
	 */
	protected $core_modules = array(
		'SCD_Plugins',
		'SCD_Themes',
		'SCD_Users',
		'SCD_Tools',
	);

	/**
	 * Check if all capabilities are turned off
	 *
	 * @param  array $capabilities
	 * @return bool
	 */
	protected function are_all_caps_turned_off( $capabilities ) {
		return empty( array_filter( $capabilities ) );
	}

	/**
	 * Check if all capabilities are turned on
	 *
	 * @param  array $capabilities
	 * @return bool
	 */
	protected function are_all_caps_turned_on( $capabilities ) {
		return count( array_filter( $capabilities ) ) === count( $capabilities );
	}

	/**
	 *
	 *
	 * @param  array $section
	 * @return bool
	 */
	protected function filter_section( $section ) {
		if ( $this->should_disable_module() ) {
			$section = $this->inactivate_section( $section );
		}
		return $section;
	}

	private function should_disable_module() {
		// Don't disable core modules
		if ( in_array( get_called_class(), $this->core_modules ) ) {
			return false;
		}

		return Simple_Client_Dashboard::get_edition() === 'basic';
	}

	private function inactivate_section( $section ) {
		$section['active']   = false;
		$section['dashicon'] = 'dashicons dashicons-lock';
		return $section;
	}

	protected function should_register_settings_access_hooks() {
		if ( ! Simple_Client_Dashboard::current_user_is_webmaster() ) {
			return false;
		}

		if ( $this->should_disable_module() ) {
			return false;
		}

		return true;
	}

	protected function should_filter_capabilities() {
		return ! $this->should_disable_module();
	}
}
