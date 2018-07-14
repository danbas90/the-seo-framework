<?php
/**
 * @package The_SEO_Framework\Compat\Plugin\WPML
 */
namespace The_SEO_Framework;

defined( 'THE_SEO_FRAMEWORK_PRESENT' ) and $_this = \the_seo_framework_class() and $this instanceof $_this or die;

/**
 * Warns homepage global title and description about receiving input.
 *
 * @since 2.8.0
 */
\add_filter( 'the_seo_framework_warn_homepage_global_title', '__return_true' );
\add_filter( 'the_seo_framework_warn_homepage_global_description', '__return_true' );

\add_action( 'current_screen', __NAMESPACE__ . '\\_wpml_do_current_screen_action' );
/**
 * Adds WPML filters based on current screen.
 *
 * @since 2.8.0
 * @access private
 *
 * @param object $current_screen
 */
function _wpml_do_current_screen_action( $current_screen = '' ) {

	if ( \the_seo_framework()->is_seo_settings_page() ) {
		\add_filter( 'wpml_admin_language_switcher_items', __NAMESPACE__ . '\\_wpml_remove_all_languages' );
	}
}

/**
 * Removes "All languages" option from WPML admin switcher.
 *
 * @since 2.8.0
 * @access private
 *
 * @param array $languages_links
 * @return array
 */
function _wpml_remove_all_languages( $languages_links = [] ) {

	unset( $languages_links['all'] );

	return $languages_links;
}

\add_action( 'the_seo_framework_delete_cache_sitemap', __NAMESPACE__ . '\\_wpml_flush_sitemap', 10, 4 );
/**
 * Deletes all sitemap transients, instead of just one.
 *
 * @since 3.1.0
 * @static bool $cleared
 * @TODO Don't use a wpdb LIKE expression, but loop through the languages instead, and let delete_transient() handle it?
 *       Note that we can't adjust the mandatory cache key suffix, which includes a cached language code -- required for performance.
 *       =HACK?
 *
 * @param string $type    The type. Comes in handy if you use a catch-all function.
 * @param int    $id      The post, page or TT ID. Defaults to $this->get_the_real_ID().
 * @param array  $args    Additional arguments. They can overwrite $type and $id.
 * @param bool   $success Whether the action cleared.
 */
function _wpml_flush_sitemap( $type, $id, $args, $success ) {

	static $cleared = false;
	if ( $cleared ) return;

	if ( $success ) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
				'_transient_tsf_sitemap_%'
			)
		); // No cache OK. DB call ok.

		//? We didn't use a wildcard after "_transient_" to reduce scans.
		//  A second query is faster on saturated sites.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
				'_transient_timeout_tsf_sitemap_%'
			)
		); // No cache OK. DB call ok.

		$cleared = true;
	}
}

// add_action( 'pre_post_update', __NAMESPACE__ . '\\_wpml_fix_locale', 10, 2 );
// /**
//  * Does the dirty work for updating a locale on post update.
//  * WARNING: This will probably destroy the post, given that WPML seems to rely on the base language.
//  *
//  * @since 3.1.0
//  *
//  * @param int      $id The updated post ID.
//  * @param \WP_Post $post The updated post.
//  */
// function _wpml_fix_locale( $id, $post ) {
// 	$info = function_exists( 'wpml_get_language_information' ) ? wpml_get_language_information( $id ) : [];
// 	if ( ! empty( $info['locale'] ) ) {
// 		// filter 'locale' here...
// 	}
// }
