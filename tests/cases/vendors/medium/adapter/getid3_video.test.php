<?php
/**
 * Getid3Video Medium Adapter Test Case File
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor','Media.VideoMedium', array('file' => 'medium'.DS.'video.php'));
App::import('Vendor','GetId3VideoMediumAdapter', array('file' => 'medium'.DS.'adapter'.DS.'get_id3_video.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Test Getid3 Video Medium Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class TestGetid3VideoMedium extends VideoMedium {
	var $adapters = array('Getid3Video');
}
/**
 * Getid3 Video Medium Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class Getid3VideoMediumAdapterTest extends CakeTestCase {
	function setUp() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function skip() {
		$this->skipUnless(App::import(array(
			'type' => 'Vendor',
			'name'=> 'getID3',
			'file' => 'getid3/getid3.php'
			)), 'Getid3 not in vendor');
	}

	function testBasic() {
		$result = new TestGetid3VideoMedium($this->TestData->getFile('video-h264.qt-tag.mp4'));
		$this->assertIsA($result, 'object');

		$Medium = new TestGetid3VideoMedium($this->TestData->getFile('video-h264.qt-tag.mp4'));
		$result = $Medium->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformationMp4tag() {
		$Medium = new TestGetid3VideoMedium($this->TestData->getFile('video-h264.qt-tag.mp4'));

		/* Fails because info->tags->quicktime->field
		$result = $Medium->title();
		$this->assertEqual($result, 'Title');

		$result = $Medium->year();
		$this->assertEqual($result, 2009);
		 */
		$result = $Medium->duration();
		$this->assertEqual($result, 1);

		$result = $Medium->bitRate();
		$this->assertEqual($result, 243006);

		$result = $Medium->width();
		$this->assertEqual($result, 320);

		$result = $Medium->height();
		$this->assertEqual($result, 180);

		$result = $Medium->quality();
		$this->assertEqual($result, 2);
	}

	function testInformationMp4notag() {
		$Medium = new TestGetid3VideoMedium($this->TestData->getFile('video-h264.notag.mp4'));

		$result = $Medium->title();
		$this->assertEqual($result, null);

		$result = $Medium->year();
		$this->assertEqual($result, null);

		$result = $Medium->duration();
		$this->assertEqual($result, 1);

		$result = $Medium->bitRate();
		$this->assertEqual($result, 241671);

		$result = $Medium->width();
		$this->assertEqual($result, 320);

		$result = $Medium->height();
		$this->assertEqual($result, 180);

		$result = $Medium->quality();
		$this->assertEqual($result, 2);
	}
}
?>
