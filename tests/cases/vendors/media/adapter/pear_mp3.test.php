<?php
/**
 * PearMp3Audio Media Adapter Test Case File
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
 * @subpackage media.tests.cases.libs.media.adapter
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor','Media.AudioMedia', array('file' => 'media'.DS.'audio.php'));
App::import('Vendor','PearMp3MediaAdapter', array('file' => 'media'.DS.'adapter'.DS.'pear_mp3.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';

/**
 * Test PearMp3 Audio Media Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class TestPearMp3Media extends AudioMedia {
	var $adapters = array('PearMp3');
}

/**
 * PearMp3 Audio Media Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class PearMp3MediaAdapterTest extends CakeTestCase {
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
			'name'=> 'MP3_Id',
			'file' => 'MP3/Id.php'
			)), 'PearMp3 not in vendor');
	}

	function testBasic() {
		$result = new TestPearMp3Media($this->TestData->getFile('audio-mpeg.ID3v1.mp3'));
		$this->assertIsA($result, 'object');

		$Media = new TestPearMp3Media($this->TestData->getFile('audio-mpeg.ID3v1.mp3'));
		$result = $Media->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformationId3v1() {
		$Media = new TestPearMp3Media($this->TestData->getFile('audio-mpeg.ID3v1.mp3'));

		$result = $Media->artist();
		$this->assertEqual($result, 'Artist');

		$result = $Media->title();
		$this->assertEqual($result, 'Title');

		$result = $Media->album();
		$this->assertEqual($result, 'Album');

		$result = $Media->year();
		$this->assertEqual($result, 2009);

		$result = $Media->track();
		$this->assertEqual($result, 1);

		$result = $Media->duration();
		$this->assertEqual($result, 1);

		$result = $Media->bitRate();
		$this->assertEqual($result, 64000);

		$result = $Media->samplingRate();
		$this->assertEqual($result, 24000);

		$result = $Media->quality();
		$this->assertEqual($result, 1);
	}

	function testInformationNotag() {
		$Media = new TestPearMp3Media($this->TestData->getFile('audio-mpeg.notag.mp3'));

		$result = $Media->artist();
		$this->assertEqual($result, null);

		$result = $Media->title();
		$this->assertEqual($result, null);

		$result = $Media->album();
		$this->assertEqual($result, null);

		$result = $Media->year();
		$this->assertEqual($result, null);

		$result = $Media->track();
		$this->assertEqual($result, null);

		$result = $Media->duration();
		$this->assertEqual($result, 1);

		$result = $Media->bitRate();
		$this->assertEqual($result, 64000);

		$result = $Media->samplingRate();
		$this->assertEqual($result, 24000);

		$result = $Media->quality();
		$this->assertEqual($result, 1);
	}
}
?>