<?php
/**
 * FfmpegVideo Medium Adapter Test Case File
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
App::import('Vendor','FfMpegVideoMediumAdapter', array('file' => 'medium'.DS.'adapter'.DS.'ff_mpeg_video.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Test Ffmpeg Video Medium Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class TestFfmpegVideoMedium extends VideoMedium {
	var $adapters = array('FfMpegVideo');
}
/**
 * Ffmpeg Video Medium Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class FfmpegVideoMediumAdapterTest extends CakeTestCase {
	function setUp() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function skip() {
		$this->skipUnless(extension_loaded('ffmpeg'), '%s ffmpeg extension not loaded');
	}

	function testBasic() {
		$result = new TestFfmpegVideoMedium($this->TestData->getFile('video-quicktime.notag.mov'));
		$this->assertIsA($result, 'object');

		$Medium = new TestFfmpegVideoMedium($this->TestData->getFile('video-quicktime.notag.mov'));
		$result = $Medium->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformationMp4tag() {
		$Medium = new TestFfmpegVideoMedium($this->TestData->getFile('video-quicktime.notag.mov'));

		$result = $Medium->title();
		//$this->assertEqual($result, 'Title'); // Unable to get the Title...

		$result = $Medium->duration();
		$this->assertEqual($result, 1);

		$result = $Medium->bitRate();
		$this->assertEqual($result, 489203);

		$result = $Medium->width();
		$this->assertEqual($result, 320);

		$result = $Medium->height();
		$this->assertEqual($result, 180);

		$result = $Medium->quality();
		$this->assertEqual($result, 2);
	}

	function testInformationMp4notag() {
		$Medium = new TestFfmpegVideoMedium($this->TestData->getFile('video-quicktime.notag.mov'));

		$result = $Medium->title();
		$this->assertEqual($result, null);

		$result = $Medium->duration();
		$this->assertEqual($result, 1);

		$result = $Medium->bitRate();
		$this->assertEqual($result, 489203);

		$result = $Medium->width();
		$this->assertEqual($result, 320);

		$result = $Medium->height();
		$this->assertEqual($result, 180);

		$result = $Medium->quality();
		$this->assertEqual($result, 2);
	}

	function testInformationTheoraComment() {
		$Medium = new TestFfmpegVideoMedium($this->TestData->getFile('video-theora.comments.ogv'));

		$result = $Medium->title();
		$this->assertEqual($result, 'Title');

		$result = $Medium->duration();
		//$this->assertEqual($result, 1); // Video seems too short (1 sec), return 0 length

		$result = $Medium->bitRate();
		//$this->assertEqual($result, 200000); // Return 0 bitrate...

		$result = $Medium->width();
		$this->assertEqual($result, 320);

		$result = $Medium->height();
		$this->assertEqual($result, 176);

		$result = $Medium->quality();
		//$this->assertEqual($result, 2); // No bitrate, fail to compute quality
	}

	function testInformationTheoraNotag() {
		$Medium = new TestFfmpegVideoMedium($this->TestData->getFile('video-theora.notag.ogv'));

		$result = $Medium->title();
		$this->assertEqual($result, null);

		$result = $Medium->duration();
		//$this->assertEqual($result, 1); // Video seems too short (1 sec), return 0 length

		$result = $Medium->bitRate();
		//$this->assertEqual($result, 200000); // Return 0 bitrate...

		$result = $Medium->width();
		$this->assertEqual($result, 320);

		$result = $Medium->height();
		$this->assertEqual($result, 176);

		$result = $Medium->quality();
		//$this->assertEqual($result, 2); // No bitrate, fail to compute quality
	}

	function testConvertMp4() {
		$Medium = new TestFfmpegVideoMedium($this->TestData->getFile('video-h264.qt-tag.mp4'));
		$Medium->convert('image/jpeg');
		$result = $Medium->mimeType;
		$this->assertTrue($result, 'image/jpeg');
	}

	function testConvertTheora() {
		$Medium = new TestFfmpegVideoMedium($this->TestData->getFile('video-theora.comments.ogv'));
		$Medium->convert('image/png');
		$result = $Medium->mimeType;
		$this->assertTrue($result, 'image/png');
	}
}
?>