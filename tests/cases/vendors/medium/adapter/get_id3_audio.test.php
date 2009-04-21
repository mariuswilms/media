<?php
/**
 * GetId3Audio Medium Adapter Test Case File
 *
 * Copyright (c) 2007-2009 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 * @copyright  2007-2009 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor','Media.AudioMedium', array('file' => 'medium'.DS.'audio.php'));
App::import('Vendor','GetId3AudioMediumAdapter', array('file' => 'medium'.DS.'adapter'.DS.'get_id3_audio.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Test GetId3 Audio Medium Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class TestGetId3AudioMedium extends AudioMedium {
	var $adapters = array('GetId3Audio');
}
/**
 * GetId3 Audio Medium Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class GetId3AudioMediumAdapterTest extends CakeTestCase {
	function setUp() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function skip()
	{
		$this->skipUnless(App::import(array(
			'type' => 'Vendor',
			'name'=> 'getID3',
			'file' => 'getid3/getid3.php'
			)), 'GetId3 not in vendor');
	}

	function testBasic() {
		$result = new TestGetId3AudioMedium($this->TestData->getFile('audio-mpeg.ID3v1.mp3'));
		$this->assertIsA($result, 'object');

		$Medium = new TestGetId3AudioMedium($this->TestData->getFile('audio-mpeg.ID3v1.mp3'));
		$result = $Medium->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformationId3v1() {
		$Medium = new TestGetId3AudioMedium($this->TestData->getFile('audio-mpeg.ID3v1.mp3'));

		$result = $Medium->artist();
		$this->assertEqual($result, 'Artist');

		$result = $Medium->title();
		$this->assertEqual($result, 'Title');

		$result = $Medium->album();
		$this->assertEqual($result, 'Album');

		$result = $Medium->year();
		$this->assertEqual($result, 2009);

		$result = $Medium->track();
		$this->assertEqual($result, null);

		$result = $Medium->duration();
		$this->assertEqual($result, 1);

		$result = $Medium->bitrate();
		$this->assertEqual($result, 64000);

		$result = $Medium->samplingRate();
		$this->assertEqual($result, 24000);

		$result = $Medium->quality();
		$this->assertEqual($result, 1);
	}

	function testInformationId3v2() {
		$Medium = new TestGetId3AudioMedium($this->TestData->getFile('audio-mpeg.ID3v2.mp3'));

		$result = $Medium->artist();
		$this->assertEqual($result, 'Artist');

		$result = $Medium->title();
		$this->assertEqual($result, 'Title');

		$result = $Medium->album();
		$this->assertEqual($result, 'Album');

		$result = $Medium->year();
		$this->assertEqual($result, 2009);

		$result = $Medium->track();
		$this->assertEqual($result, 1);

		$result = $Medium->duration();
		$this->assertEqual($result, 1);

		$result = $Medium->bitrate();
		$this->assertEqual($result, 64000);

		$result = $Medium->samplingRate();
		$this->assertEqual($result, 24000);

		$result = $Medium->quality();
		$this->assertEqual($result, 1);
	}

	function testInformationNotag() {
		$Medium = new TestGetId3AudioMedium($this->TestData->getFile('audio-mpeg.notag.mp3'));

		$result = $Medium->artist();
		$this->assertEqual($result, null);

		$result = $Medium->title();
		$this->assertEqual($result, null);

		$result = $Medium->album();
		$this->assertEqual($result, null);

		$result = $Medium->year();
		$this->assertEqual($result, null);

		$result = $Medium->track();
		$this->assertEqual($result, null);

		$result = $Medium->duration();
		$this->assertEqual($result, 1);

		$result = $Medium->bitrate();
		$this->assertEqual($result, 64000);

		$result = $Medium->samplingRate();
		$this->assertEqual($result, 24000);

		$result = $Medium->quality();
		$this->assertEqual($result, 1);
	}

	function testInformationVorbisComment() {
		$Medium = new TestGetId3AudioMedium($this->TestData->getFile('audio-vorbis.comments.ogg'));

		$result = $Medium->artist();
		$this->assertEqual($result, 'Artist');

		$result = $Medium->title();
		$this->assertEqual($result, 'Title');

		$result = $Medium->album();
		$this->assertEqual($result, 'Album');

		$result = $Medium->year();
		$this->assertEqual($result, 2009);

		$result = $Medium->track();
		$this->assertEqual($result, 1);

		$result = $Medium->duration();
		$this->assertEqual($result, 1);

		$result = $Medium->bitrate();
		$this->assertEqual($result, 36666);

		$result = $Medium->samplingRate();
		$this->assertEqual($result, 24000);

		$result = $Medium->quality();
		$this->assertEqual($result, 1);
	}

	function testInformationVorbisNotag() {
		$Medium = new TestGetId3AudioMedium($this->TestData->getFile('audio-vorbis.notag.ogg'));

		$result = $Medium->artist();
		$this->assertEqual($result, null);

		$result = $Medium->title();
		$this->assertEqual($result, null);

		$result = $Medium->album();
		$this->assertEqual($result, null);

		$result = $Medium->year();
		$this->assertEqual($result, null);

		$result = $Medium->track();
		$this->assertEqual($result, null);

		$result = $Medium->duration();
		$this->assertEqual($result, 1);

		$result = $Medium->bitrate();
		$this->assertEqual($result, 36666);

		$result = $Medium->samplingRate();
		$this->assertEqual($result, 24000);

		$result = $Medium->quality();
		$this->assertEqual($result, 1);
	}

	function testConvertMp3() {
		$Medium = new TestGetId3AudioMedium($this->TestData->getFile('audio-mpeg.ID3v2.mp3'));
		$Medium->convert('image/jpeg');
		$result = $Medium->mimeType;
		$this->assertTrue($result, 'image/jpeg');
	}

	function testConvertOgg() {
		$Medium = new TestGetId3AudioMedium($this->TestData->getFile('audio-vorbis.comments.ogg'));
		$Medium->convert('image/png');
		$result = $Medium->mimeType;
		$this->assertTrue($result, 'image/png');
	}
}
?>