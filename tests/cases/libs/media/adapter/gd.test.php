<?php
/**
 * Gd Media Adapter Test Case File
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
App::import('Lib','Media.ImageMedia', array('file' => 'media' . DS . 'image.php'));
App::import('Lib','GdMediaAdapter', array('file' => 'media' . DS . 'adapter' . DS . 'gd.php'));
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'fixtures' . DS . 'test_data.php';

/**
 * Test Gd Media Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class TestGdImageMedia extends ImageMedia {
	var $adapters = array('Gd');
}

/**
 * Gd Media Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class GdMediaAdapterTest extends CakeTestCase {
	function setUp() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function skip()	{
		$this->skipUnless(extension_loaded('gd'), '%s GD extension not loaded');
	}

	function testBasic() {
		$result = new TestGdImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$this->assertIsA($result, 'object');

		$Media = new TestGdImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$result = $Media->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformation() {
		$Media = new TestGdImageMedia($this->TestData->getFile('image-jpg.jpg'));

		$result = $Media->width();
		$this->assertEqual($result, 70);

		$result = $Media->height();
		$this->assertEqual($result, 47);
	}

	function testManipulation() {
		$Media = new TestGdImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$Media->fit(10, 10);
		$this->assertTrue($Media->width() <= 10);
		$this->assertTrue($Media->height() <= 10);

		$Media = new TestGdImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$Media->convert('image/png');
		$result = $Media->mimeType;
		$this->assertTrue($result, 'image/png');
	}

	function testManipulationWithAlphaTransparency8bit() {
		$source = $this->TestData->getFile('image-png.transparent.8bit.png');
		$target = $this->TestData->getFile('test8bit_man.jpg');

		$Media = new TestGdImageMedia($source);
		$Media->convert('image/jpeg');
		$Media->fit(15, 15);
		$Media->store($target, true);
		$this->assertEqual(md5_file($target), 'fc5a49bb265ea1baa6094d289a4823b0');

		$source = $this->TestData->getFile('image-png.transparent.8bit.png');
		$target = $this->TestData->getFile('test8bit_man.png');

		$Media = new TestGdImageMedia($source);
		$Media->fit(15, 15);
		$result = $Media->mimeType;
		$Media->store($target, true);
		$this->assertEqual(md5_file($target), '6230d343b932bfcf0996c7e0a291b677');
	}

	function testManipulationWithAlphaTransparency16bit() {
		$source = $this->TestData->getFile('image-png.transparent.16bit.png');
		$target = $this->TestData->getFile('test16bit_man.jpg');

		$Media = new TestGdImageMedia($source);
		$Media->convert('image/jpeg');
		$Media->fit(15, 15);
		$Media->store($target, true);
		$this->assertEqual(md5_file($target), '1719836a4b39bc56bf119975785292f8');

		$source = $this->TestData->getFile('image-png.transparent.16bit.png');
		$target = $this->TestData->getFile('test16bit_man.png');

		$Media = new TestGdImageMedia($source);
		$Media->fit(15, 15);
		$result = $Media->mimeType;
		$Media->store($target, true);
		$this->assertEqual(md5_file($target), 'f8c84870bd6cf656e9dcaba1cbf26636');
	}

	function testCompress() {
		$Media = new TestGdImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$resultCompress = $Media->compress(1.5);
		$resultStore = $Media->store($this->TestData->getFile('test-compress-1.5.jpg'), true);
		$this->assertTrue($resultCompress);
		$this->assertTrue(file_exists($resultStore));

		$Media = new TestGdImageMedia($this->TestData->getFile('image-png.png'));
		$resultCompress = $Media->compress(1.5);
		$resultStore = $Media->store($this->TestData->getFile('test-compress-1.5.png'), true);
		$this->assertTrue($resultCompress);
		$this->assertTrue($resultStore);
	}
}
?>