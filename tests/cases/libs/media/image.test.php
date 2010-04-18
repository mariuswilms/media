<?php
/**
 * Image Media Test Case File
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
 * @subpackage media.tests.cases.libs.media
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Lib', 'Media.ImageMedia', array('file' => 'media' . DS . 'image.php'));
App::import('Lib', 'Media.DocumentMedia', array('file' => 'media' . DS . 'document.php'));
require_once dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'fixtures' . DS . 'test_data.php';

/**
 * Test Image Media Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.image
 */
class TestImageMedia extends ImageMedia {

	function testBoxify($width, $height, $gravity = 'center') {
		return parent::_boxify($width, $height, $gravity);
	}

}

/**
 * Image Media Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media
 */
class ImageMediaTest extends CakeTestCase {
	function setUp() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function testInformation() {
		$file = $this->TestData->getFile('image-jpg.jpg');
		$Media = new ImageMedia($file);
		$result = $Media->width();
		$expected = 70;
		$this->assertEqual($result, $expected);

		$result = $Media->height();
		$expected = 47;
		$this->assertEqual($result, $expected);

		$result = $Media->quality();
		$expected = 1;
		$this->assertEqual($result, $expected);

		$result = $Media->ratio();
		$expected = '3:2';
		$this->assertEqual($result, $expected);

		$result = $Media->megapixel();
		$expected = 0;
		$this->assertEqual($result, $expected);
	}

	function testTransitions() {
		exec('which gs 2>&1', $output, $return);
		$this->skipUnless($return === 0, 'gs command not available');

		$Media = new DocumentMedia($this->TestData->getFile('application-pdf.pdf'));
		$Media = $Media->convert('image/png');
		if ($this->assertIsA($Media, 'ImageMedia')) {
			$tmpFile = $Media->store(TMP . uniqid('test_suite_'));
			$this->assertEqual(MimeType::guessType($tmpFile), 'image/png');
			unlink($tmpFile);
		}

		$Media = new DocumentMedia($this->TestData->getFile('application-pdf.pdf'));
		$Media = $Media->convert('image/png');
		if ($this->assertIsA($Media, 'ImageMedia')) {
			$result = $Media->fit(10, 10);
			$this->assertTrue($result);
			$this->assertTrue($Media->width() <= 10);
			$this->assertTrue($Media->height() <= 10);
		}
	}

	function testBoxify() {
		$Media = new TestImageMedia($this->TestData->getFile('image-jpg.jpg'));
		// media has 70px width and 47px height

		$result = $Media->testBoxify(20, 20, 'center');
		$expected = array(25, 13.5);
		$this->assertEqual($result, $expected);

		$result = $Media->testBoxify(20, 20, 'topleft');
		$expected = array(0, 0);
		$this->assertEqual($result, $expected);

		$result = $Media->testBoxify(20, 20, 'topright');
		$expected = array(50, 0);
		$this->assertEqual($result, $expected);

		$result = $Media->testBoxify(20, 20, 'bottomleft');
		$expected = array(0, 27);
		$this->assertEqual($result, $expected);

		$result = $Media->testBoxify(20, 20, 'bottomright');
		$expected = array(50, 27);
		$this->assertEqual($result, $expected);

		$this->expectError();
		$result = $Media->testBoxify(20, 20, 'XXXXXXX');
		$expected = array(25, 13.5);
		$this->assertEqual($result, $expected);
	}

}
?>