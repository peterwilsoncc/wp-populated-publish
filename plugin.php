<?php
/**
 * WP Populated Publish
 *
 * @package           wp-populated-publish
 * @author            Peter Wilson
 * @copyright         20202 Peter Wilson
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WP Populated Publish
 * Plugin URI:        https://peterwilson.cc/projects/wp-populated-publish
 * Description:       Ensure save hooks run after all data is populated.
 * Version:           0.1.0
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            Peter Wilson
 * Author URI:        https://peterwilson.cc
 * Text Domain:       wp-populated-publish
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace PWCC\PopulatedPublish;

require_once __DIR__ . '/inc/namespace.php';

add_action( 'plugins_loaded', __NAMESPACE__ . '\\bootstrap' );
