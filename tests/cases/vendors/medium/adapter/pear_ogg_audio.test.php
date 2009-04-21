<?php
/**
 * PearOggAudio Medium Adapter Test Case File
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
App::import('Vendor','PearOggAudioMediumAdapter', array('file' => 'medium'.DS.'adapter'.DS.'pear_ogg_audio.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Test PearOgg Audio Medium Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class TestPearOggAudioMedium extends AudioMedium {
	var $adapters = array('PearOggAudio');
}
/**
 * PearOgg Audio Medium Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class PearOggAudioMediumAdapterTest extends CakeTestCase {
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
			'name'=> 'File_Ogg',
			'file' => 'File/Ogg.php'
			)), 'PearOgg not in vendor');
	}

	function testBasic() {
		$result = new TestPearOggAudioMedium($this->TestData->getFile('audio-vorbis.comments.ogg'));
		$this->assertIsA($result, 'object');

		$Medium = new TestPearOggAudioMedium($this->TestData->getFile('audio-vorbis.comments.ogg'));
		$result = $Medium->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformationVorbisComment() {
		$Medium = new TestPearOggAudioMedium($this->TestData->getFile('audio-vorbis.comments.ogg'));

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
		$this->assertEqual($result, 66736); // Nominal Bitrate = 36666

		$result = $Medium->samplingRate();
		$this->assertEqual($result, 24000);

		$result = $Medium->quality();
		$this->assertEqual($result, 1);
	}

	function testInformationVorbisNotag() {
		$Medium = new TestPearOggAudioMedium($this->TestData->getFile('audio-vorbis.notag.ogg'));

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
		$this->assertEqual($result, 56813); // Nominal Bitrate = 36666

		$result = $Medium->samplingRate();
		$this->assertEqual($result, 24000);

		$result = $Medium->quality();
		$this->assertEqual($result, 1);
	}
}
?>