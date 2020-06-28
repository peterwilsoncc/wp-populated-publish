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
	 * Create posts to use throughout the test suite.
	 *
	 * @param WP_UnitTest_Factory $factory Test suite factory.
	 * @return void
	 */
	static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) : void {
		self::$posts = $factory->post->create_many( 5 );
	}

	/**
	 * Test the tests.
	 *
	 * @return void
	 */
	function test_dummy() : void {
		$this->assertTrue( true );
	}
}
