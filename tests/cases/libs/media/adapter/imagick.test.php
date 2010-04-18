<?php
/**
 * Imagick Media Adapter Test Case File
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
App::import('Lib','Media.DocumentMedia', array('file' => 'media' . DS . 'document.php'));
App::import('Lib','ImagickMediaAdapter', array('file' => 'media' . DS . 'adapter' . DS . 'imagick.php'));
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'fixtures' . DS . 'test_data.php';

/**
 * Test Imagick Image Media Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class TestImagickImageMedia extends ImageMedia {
	var $adapters = array('Imagick');
}
/**
 * Test Imagick Document Media Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class TestImagickDocumentMedia extends DocumentMedia {
	var $adapters = array('Imagick');
}
/**
 * Imagick Media Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class ImagickMediaAdapterTest extends CakeTestCase {
	function setUp() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function skip() {
		$this->skipUnless(extension_loaded('imagick'), 'Imagick extension not loaded');
	}

	function showImage($string, $mimeType = null) {
		echo '<img src="data:'.$mimeType.';base64,'.base64_encode($string).'" />';
	}

	function testBasic() {
		$result = new TestImagickImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$this->assertIsA($result, 'object');

		$Media = new TestImagickImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$result = $Media->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformation() {
		$file = $this->TestData->getFile('image-jpg.jpg');
		$Media = new TestImagickImageMedia($file);

		$result = $Media->width();
		$this->assertEqual($result, 70);

		$result = $Media->height();
		$this->assertEqual($result, 47);
	}

	function testManipulation() {
		$Media = new TestImagickImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$Media->fit(10, 10);
		$this->assertTrue($Media->width() <= 10);
		$this->assertTrue($Media->height() <= 10);

		$Media = new TestImagickImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$Media = $Media->convert('image/png');
		if ($this->assertIsA($Media, 'ImageMedia')) {
			$result = $Media->mimeType;
			$this->assertEqual($result, 'image/png');
		}

		$Media = new TestImagickImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$Media = $Media->convert('image/png');
		if ($this->assertIsA($Media, 'ImageMedia')) {
			$tmpFile = TMP . uniqid('test_suite_');
			$tmpFile = $Media->store($tmpFile);
			$this->assertEqual(MimeType::guessType($tmpFile), 'image/png');
			unlink($tmpFile);
		}
	}

	function testCompress() {
		$Media = new TestImagickImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$resultCompress = $Media->compress(1.5);
		$resultStore = $Media->store($this->TestData->getFile('test-compress-1.5.jpg'), true);
		$this->assertTrue($resultCompress);
		$this->assertTrue($resultStore);

		$Media = new TestImagickImageMedia($this->TestData->getFile('image-png.png'));
		$resultCompress = $Media->compress(1.5);
		$resultStore = $Media->store($this->TestData->getFile('test-compress-1.5.png'), true);
		$this->assertTrue($resultCompress);
		$this->assertTrue($resultStore);
	}
}
?>