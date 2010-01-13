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
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor','Media.ImageMedia', array('file' => 'media' . DS . 'image.php'));
App::import('Vendor','Media.DocumentMedia', array('file' => 'media' . DS . 'document.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';

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
}
?>