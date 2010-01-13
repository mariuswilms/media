<?php
/**
 * Media Test Case File
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
 * @subpackage media.tests.cases.libs.media
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.Media');
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';

/**
 * Banana Media Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media
 */
class BananaMediaAdapter extends MediaAdapter {}

/**
 * Cherry Media Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media
 */
class CherryMediaAdapter extends MediaAdapter {}

/**
 * Sweet Media Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media
 */
class SweetMedia extends Media {
	var $adapters = array('Banana', 'Cherry');
}

/**
 * Media Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media
 */
class MediaTest extends CakeTestCase {
	function setUp() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function testMediaFactory() {
		$file = $this->TestData->getFile('image-jpg.jpg');
		$result = Media::factory($file);
		$this->assertIsA($result,'ImageMedia');

		$file = $this->TestData->getFile('image-png.png');
		$result = Media::factory($file);
		$this->assertIsA($result,'ImageMedia');

		$file = $this->TestData->getFile('image-gif.gif');
		$result = Media::factory($file);
		$this->assertIsA($result,'ImageMedia');

		$file = $this->TestData->getFile('text-plain.txt');
		$result = Media::factory($file);
		$this->assertIsA($result,'TextMedia');

		$file = $this->TestData->getFile('application-pdf.pdf');
		$result = Media::factory($file);
		$this->assertIsA($result,'DocumentMedia');
	}

	function testMediaNameAndShort() {
		$file = $this->TestData->getFile('image-jpg.jpg');
		$result = Media::factory($file);
		$this->assertEqual($result->name,'Image');
		$this->assertEqual($result->short,'img');

		$file = $this->TestData->getFile('image-png.png');
		$result = Media::factory($file);
		$this->assertEqual($result->name,'Image');
		$this->assertEqual($result->short,'img');

		$file = $this->TestData->getFile('image-gif.gif');
		$result = Media::factory($file);
		$this->assertEqual($result->name,'Image');
		$this->assertEqual($result->short,'img');

		$file = $this->TestData->getFile('text-plain.txt');
		$result = Media::factory($file);
		$this->assertEqual($result->name,'Text');
		$this->assertEqual($result->short,'txt');

		$file = $this->TestData->getFile('application-pdf.pdf');
		$result = Media::factory($file);
		$this->assertEqual($result->name,'Document');
		$this->assertEqual($result->short,'doc');
	}

	function testMediaAdapterCollection() {
	}

	function testMake() {
		$instructions = array('convert' => 'image/png', 'zoomCrop' => array(10, 10));
		$Media = Media::make($this->TestData->getFile('image-jpg.jpg'), $instructions);
		$this->assertIsA($Media, 'Media');
	}
}
?>