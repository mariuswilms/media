<?php
/**
 * Getid3Video Media Adapter Test Case File
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Lib','Media.VideoMedia', array('file' => 'media' . DS . 'video.php'));
App::import('Lib','GetId3VideoMediaAdapter', array('file' => 'media' . DS . 'adapter' . DS . 'get_id3_video.php'));
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'fixtures' . DS . 'test_data.php';

/**
 * Test Getid3 Video Media Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class TestGetid3VideoMedia extends VideoMedia {
	var $adapters = array('Getid3Video');
}

/**
 * Getid3 Video Media Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class Getid3VideoMediaAdapterTest extends CakeTestCase {
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
		$result = new TestGetid3VideoMedia($this->TestData->getFile('video-h264.qt-tag.mp4'));
		$this->assertIsA($result, 'object');

		$Media = new TestGetid3VideoMedia($this->TestData->getFile('video-h264.qt-tag.mp4'));
		$result = $Media->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformationMp4tag() {
		$Media = new TestGetid3VideoMedia($this->TestData->getFile('video-h264.qt-tag.mp4'));

		/* Fails because info->tags->quicktime->field
		$result = $Media->title();
		$this->assertEqual($result, 'Title');

		$result = $Media->year();
		$this->assertEqual($result, 2009);
		 */
		$result = $Media->duration();
		$this->assertEqual($result, 1);

		$result = $Media->bitRate();
		$this->assertEqual($result, 243006);

		$result = $Media->width();
		$this->assertEqual($result, 320);

		$result = $Media->height();
		$this->assertEqual($result, 180);

		$result = $Media->quality();
		$this->assertEqual($result, 2);
	}

	function testInformationMp4notag() {
		$Media = new TestGetid3VideoMedia($this->TestData->getFile('video-h264.notag.mp4'));

		$result = $Media->title();
		$this->assertEqual($result, null);

		$result = $Media->year();
		$this->assertEqual($result, null);

		$result = $Media->duration();
		$this->assertEqual($result, 1);

		$result = $Media->bitRate();
		$this->assertEqual($result, 241671);

		$result = $Media->width();
		$this->assertEqual($result, 320);

		$result = $Media->height();
		$this->assertEqual($result, 180);

		$result = $Media->quality();
		$this->assertEqual($result, 2);
	}
}
?>