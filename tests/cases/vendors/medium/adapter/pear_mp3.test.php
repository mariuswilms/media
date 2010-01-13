<?php
/**
 * PearMp3Audio Medium Adapter Test Case File
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
App::import('Vendor','Media.AudioMedium', array('file' => 'medium'.DS.'audio.php'));
App::import('Vendor','PearMp3MediumAdapter', array('file' => 'medium'.DS.'adapter'.DS.'pear_mp3.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Test PearMp3 Audio Medium Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class TestPearMp3Medium extends AudioMedium {
	var $adapters = array('PearMp3');
}
/**
 * PearMp3 Audio Medium Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class PearMp3MediumAdapterTest extends CakeTestCase {
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
		$result = new TestPearMp3Medium($this->TestData->getFile('audio-mpeg.ID3v1.mp3'));
		$this->assertIsA($result, 'object');

		$Medium = new TestPearMp3Medium($this->TestData->getFile('audio-mpeg.ID3v1.mp3'));
		$result = $Medium->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformationId3v1() {
		$Medium = new TestPearMp3Medium($this->TestData->getFile('audio-mpeg.ID3v1.mp3'));

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

		$result = $Medium->bitRate();
		$this->assertEqual($result, 64000);

		$result = $Medium->samplingRate();
		$this->assertEqual($result, 24000);

		$result = $Medium->quality();
		$this->assertEqual($result, 1);
	}

	function testInformationNotag() {
		$Medium = new TestPearMp3Medium($this->TestData->getFile('audio-mpeg.notag.mp3'));

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

		$result = $Medium->bitRate();
		$this->assertEqual($result, 64000);

		$result = $Medium->samplingRate();
		$this->assertEqual($result, 24000);

		$result = $Medium->quality();
		$this->assertEqual($result, 1);
	}
}
?>