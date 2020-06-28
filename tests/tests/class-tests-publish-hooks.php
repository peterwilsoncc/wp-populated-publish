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

use WP_REST_Request;
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
	 * Editor ID.
	 *
	 * @var int
	 */
	static $editor_id;

	/**
	 * Create posts to use throughout the test suite.
	 *
	 * @param WP_UnitTest_Factory $factory Test suite factory.
	 * @return void
	 */
	static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) : void {
		self::$posts = $factory->post->create_many( 5 );

		self::$editor_id = $factory->user->create( [ 'role' => 'editor' ] );
	}

	/**
	 * Ensure populated functions run correctly outside of rest.
	 *
	 * This uses the standard PHP functions used in the classic editor
	 * to create, update and publish a post and ensures the number
	 * of times each action fires matches.
	 *
	 * Note: attachments are not tested.
	 */
	function test_all_hooks_fire_for_wp_insert_post() : void {
		$fired_actions_count = [];

		$closure = function( $one ) use ( &$fired_actions_count ) {
			$fired_actions_count[ current_action() ]++;
			return $one;
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

	/**
	 * Ensure populated functions run correctly inside of rest.
	 *
	 * This uses the WP API to create, update and publish a post and
	 * ensures the number of times each action fired matches.
	 *
	 * Note: attachments are not tested.
	 */
	function test_all_hooks_fire_for_rest_posts() : void {
		wp_set_current_user( self::$editor_id );
		$fired_actions_count = [];

		$closure = function( $one ) use ( &$fired_actions_count ) {
			if ( ! doing_filter( 'rest_request_after_callbacks' ) && strpos( current_action(), 'populated.' ) === 0 ) {
				// Populated post that isn't throwing in rest. Doesn't count.
				return $one;
			}
			$fired_actions_count[ current_action() ]++;
			return $one;
		};

		foreach ( self::$actions as $action ) {
			$fired_actions_count[ $action ] = 0;
			$fired_actions_count[ "populated.$action" ] = 0;
			add_action( "populated.$action", $closure );
			add_action( $action, $closure );
		}

		// First create the post.
		$request = new WP_REST_Request( 'POST', '/wp/v2/posts' );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$params = [
			'title' => 'Post Title',
			'content' => 'Post content',
			'excerpt' => 'Post excerpt',
			'name' => 'test',
			'status' => 'draft',
			'author' => get_current_user_id(),
			'type' => 'post',
		];
		$request->set_body_params( $params );
		$response = rest_get_server()->dispatch( $request );

		$post_id = $response->data['id'];

		// Update the post to fire update actions.
		$request = new WP_REST_Request( 'PUT', sprintf( '/wp/v2/posts/%d', $post_id ) );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$request->set_body_params( [ 'status' => 'private' ] );
		$response = rest_get_server()->dispatch( $request );

		// Publish the post.
		$request = new WP_REST_Request( 'PUT', sprintf( '/wp/v2/posts/%d', $post_id ) );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$request->set_body_params( [ 'status' => 'publish' ] );
		$response = rest_get_server()->dispatch( $request );

		foreach ( self::$actions as $action ) {
			$this->assertSame( $fired_actions_count[ $action ], $fired_actions_count[ "populated.$action" ] );
		}
	}
}
