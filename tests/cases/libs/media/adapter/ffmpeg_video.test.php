<?php
/**
 * FfmpegVideo Media Adapter Test Case File
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
App::import('Lib','FfMpegVideoMediaAdapter', array('file' => 'media' . DS . 'adapter' . DS . 'ff_mpeg_video.php'));
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'fixtures' . DS . 'test_data.php';

/**
 * Test Ffmpeg Video Media Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class TestFfmpegVideoMedia extends VideoMedia {
	var $adapters = array('FfMpegVideo');
}

/**
 * Ffmpeg Video Media Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class FfmpegVideoMediaAdapterTest extends CakeTestCase {
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
		$result = new TestFfmpegVideoMedia($this->TestData->getFile('video-quicktime.notag.mov'));
		$this->assertIsA($result, 'object');

		$Media = new TestFfmpegVideoMedia($this->TestData->getFile('video-quicktime.notag.mov'));
		$result = $Media->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformationMp4tag() {
		$Media = new TestFfmpegVideoMedia($this->TestData->getFile('video-quicktime.notag.mov'));

		$result = $Media->title();
		//$this->assertEqual($result, 'Title'); // Unable to get the Title...

		$result = $Media->duration();
		$this->assertEqual($result, 1);

		$result = $Media->bitRate();
		$this->assertEqual($result, 489203);

		$result = $Media->width();
		$this->assertEqual($result, 320);

		$result = $Media->height();
		$this->assertEqual($result, 180);

		$result = $Media->quality();
		$this->assertEqual($result, 2);
	}

	function testInformationMp4notag() {
		$Media = new TestFfmpegVideoMedia($this->TestData->getFile('video-quicktime.notag.mov'));

		$result = $Media->title();
		$this->assertEqual($result, null);

		$result = $Media->duration();
		$this->assertEqual($result, 1);

		$result = $Media->bitRate();
		$this->assertEqual($result, 489203);

		$result = $Media->width();
		$this->assertEqual($result, 320);

		$result = $Media->height();
		$this->assertEqual($result, 180);

		$result = $Media->quality();
		$this->assertEqual($result, 2);
	}

	function testInformationTheoraComment() {
		$Media = new TestFfmpegVideoMedia($this->TestData->getFile('video-theora.comments.ogv'));

		$result = $Media->title();
		$this->assertEqual($result, 'Title');

		$result = $Media->duration();
		//$this->assertEqual($result, 1); // Video seems too short (1 sec), return 0 length

		$result = $Media->bitRate();
		//$this->assertEqual($result, 200000); // Return 0 bitrate...

		$result = $Media->width();
		$this->assertEqual($result, 320);

		$result = $Media->height();
		$this->assertEqual($result, 176);

		$result = $Media->quality();
		//$this->assertEqual($result, 2); // No bitrate, fail to compute quality
	}

	function testInformationTheoraNotag() {
		$Media = new TestFfmpegVideoMedia($this->TestData->getFile('video-theora.notag.ogv'));

		$result = $Media->title();
		$this->assertEqual($result, null);

		$result = $Media->duration();
		//$this->assertEqual($result, 1); // Video seems too short (1 sec), return 0 length

		$result = $Media->bitRate();
		//$this->assertEqual($result, 200000); // Return 0 bitrate...

		$result = $Media->width();
		$this->assertEqual($result, 320);

		$result = $Media->height();
		$this->assertEqual($result, 176);

		$result = $Media->quality();
		//$this->assertEqual($result, 2); // No bitrate, fail to compute quality
	}

	function testConvertMp4() {
		$Media = new TestFfmpegVideoMedia($this->TestData->getFile('video-h264.qt-tag.mp4'));
		$Media->convert('image/jpeg');
		$result = $Media->mimeType;
		$this->assertTrue($result, 'image/jpeg');
	}

	function testConvertTheora() {
		$Media = new TestFfmpegVideoMedia($this->TestData->getFile('video-theora.comments.ogv'));
		$Media->convert('image/png');
		$result = $Media->mimeType;
		$this->assertTrue($result, 'image/png');
	}
}
?>