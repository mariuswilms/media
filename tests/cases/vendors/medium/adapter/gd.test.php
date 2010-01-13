<?php
/**
 * Gd Medium Adapter Test Case File
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
App::import('Vendor','Media.ImageMedium', array('file' => 'medium'.DS.'image.php'));
App::import('Vendor','GdMediumAdapter', array('file' => 'medium'.DS.'adapter'.DS.'gd.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Test Gd Medium Adapter Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class TestGdImageMedium extends ImageMedium {
	var $adapters = array('Gd');
}
/**
 * Gd Medium Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class GdMediumAdapterTest extends CakeTestCase {
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
		$result = new TestGdImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$this->assertIsA($result, 'object');

		$Medium = new TestGdImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$result = $Medium->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformation() {
		$Medium = new TestGdImageMedium($this->TestData->getFile('image-jpg.jpg'));

		$result = $Medium->width();
		$this->assertEqual($result, 70);

		$result = $Medium->height();
		$this->assertEqual($result, 47);
	}

	function testManipulation() {
		$Medium = new TestGdImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$Medium->fit(10, 10);
		$this->assertTrue($Medium->width() <= 10);
		$this->assertTrue($Medium->height() <= 10);

		$Medium = new TestGdImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$Medium->convert('image/png');
		$result = $Medium->mimeType;
		$this->assertTrue($result, 'image/png');
	}

	function testManipulationWithAlphaTransparency8bit() {
		$source = $this->TestData->getFile('image-png.transparent.8bit.png');
		$target = $this->TestData->getFile('test8bit_man.jpg');

		$Medium = new TestGdImageMedium($source);
		$Medium->convert('image/jpeg');
		$Medium->fit(15, 15);
		$Medium->store($target, true);
		$this->assertEqual(md5_file($target), 'fc5a49bb265ea1baa6094d289a4823b0');

		$source = $this->TestData->getFile('image-png.transparent.8bit.png');
		$target = $this->TestData->getFile('test8bit_man.png');

		$Medium = new TestGdImageMedium($source);
		$Medium->fit(15, 15);
		$result = $Medium->mimeType;
		$Medium->store($target, true);
		$this->assertEqual(md5_file($target), '6230d343b932bfcf0996c7e0a291b677');
	}

	function testManipulationWithAlphaTransparency16bit() {
		$source = $this->TestData->getFile('image-png.transparent.16bit.png');
		$target = $this->TestData->getFile('test16bit_man.jpg');

		$Medium = new TestGdImageMedium($source);
		$Medium->convert('image/jpeg');
		$Medium->fit(15, 15);
		$Medium->store($target, true);
		$this->assertEqual(md5_file($target), '1719836a4b39bc56bf119975785292f8');

		$source = $this->TestData->getFile('image-png.transparent.16bit.png');
		$target = $this->TestData->getFile('test16bit_man.png');

		$Medium = new TestGdImageMedium($source);
		$Medium->fit(15, 15);
		$result = $Medium->mimeType;
		$Medium->store($target, true);
		$this->assertEqual(md5_file($target), 'f8c84870bd6cf656e9dcaba1cbf26636');
	}

	function testCompress() {
		$Medium = new TestGdImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$resultCompress = $Medium->compress(1.5);
		$resultStore = $Medium->store($this->TestData->getFile('test-compress-1.5.jpg'), true);
		$this->assertTrue($resultCompress);
		$this->assertTrue(file_exists($resultStore));

		$Medium = new TestGdImageMedium($this->TestData->getFile('image-png.png'));
		$resultCompress = $Medium->compress(1.5);
		$resultStore = $Medium->store($this->TestData->getFile('test-compress-1.5.png'), true);
		$this->assertTrue($resultCompress);
		$this->assertTrue($resultStore);
	}
}
?>