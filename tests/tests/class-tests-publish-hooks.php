<?php
/**
 * WP Populated Publish Tests
 *
 * @package           wp-populated-publish
 * @subpackage        tests
 * @author            Peter Wilson
 * @copyright         20202 Peter Wilson
 * @license           GPL-2.0-or-later
 */

namespace PWCC\PopulatedPublish\Tests;

use WP_UnitTestCase;
use WP_UnitTest_Factory;

/**
 * Test Publishing hooks run as expected.
 */
class Tests_Publish_Hooks extends WP_UnitTestCase {

	/**
	 * An array of posts.
	 *
	 * @var array
	 */
	static $posts;

	/**
	 * Array of actions to check.
	 *
	 * @var array
	 */
	static $actions = [
		'transition_post_status',
		'post_updated',
		'wp_insert_post',
		'add_attachment',
	];

	/**
	 * Create posts to use throughout the test suite.
	 *
	 * @param WP_UnitTest_Factory $factory Test suite factory.
	 * @return void
	 */
	static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) : void {
		self::$posts = $factory->post->create_many( 5 );
	}

	/**
	 * Ensure populated functions run correctly outside of rest.
	 *
	 * This uses the standard PHP functions used in the classic editor
	 * to create, update and publish a post and ensures the number
	 * of times each action fires matches.
	 *
	 * Note: attachments are not tested.
	 *
	 * @return void
	 */
	function test_all_hooks_fire_for_wp_insert_post() : void {
		$fired_actions_count = [];

		$closure = function( $one ) use ( &$fired_actions_count ) {
			$fired_actions_count[ current_action() ]++;
		};

		foreach ( self::$actions as $action ) {
			$fired_actions_count[ $action ] = 0;
			$fired_actions_count[ "populated.$action" ] = 0;
			add_action( "populated.$action", $closure );
			add_action( $action, $closure );
		}

		// First create the post.
		$post_id = $this->factory->post->create( [
			'post_status' => 'draft',
		] );

		// Update the post to fire update actions.
		wp_update_post([
			'ID' => $post_id,
			'post_status' => 'private',
		]);

		wp_publish_post( $post_id );

		foreach ( self::$actions as $action ) {
			$this->assertSame( $fired_actions_count[ $action ], $fired_actions_count[ "populated.$action" ] );
		}
	}
}
