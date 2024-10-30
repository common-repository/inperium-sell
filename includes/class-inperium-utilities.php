<?php
/**
 * Utility functions and helpers.
 *
 * @link       https://inperium.com
 * @since      1.0.0
 *
 * @package    Inperium
 * @subpackage Inperium/includes
 */

/**
 * Utility functions and helpers.
 *
 * @package    Inperium
 * @subpackage Inperium/includes
 * @author     Artyom Gerus <artyom@inperium.com>
 */
class Inperium_Utilities {

	/**
	 * A recursive `sanitize_text_field` implementation for arrays.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array $array  Array of text fields to sanitize.
	 * @return array  Resulting array of sanitized text fields.
	 */
	public static function recursive_sanitize_text_field( $array ) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
					$value = self::recursive_sanitize_text_field( $value );
			} else {
					$value = sanitize_text_field( $value );
			}
		}
		return $array;
	}

}
