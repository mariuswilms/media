<?php
/**
 * Image Medium Test Case File
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
 * @subpackage media.tests.cases.libs.medium
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor','Media.ImageMedium', array('file' => 'medium' . DS . 'image.php'));
App::import('Vendor','Media.DocumentMedium', array('file' => 'medium' . DS . 'document.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Image Medium Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium
 */
class ImageMediumTest extends CakeTestCase {
	function setUp() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function testInformation() {
		$file = $this->TestData->getFile('image-jpg.jpg');
		$Medium = new ImageMedium($file);
		$result = $Medium->width();
		$expecting = 70;
		$this->assertEqual($result,$expecting);

		$result = $Medium->height();
		$expecting = 47;
		$this->assertEqual($result,$expecting);

		$result = $Medium->quality();
		$expecting = 1;
		$this->assertEqual($result,$expecting);

		$result = $Medium->ratio();
		$expecting = '3:2';
		$this->assertEqual($result,$expecting);

		$result = $Medium->megapixel();
		$expecting = 0;
		$this->assertEqual($result,$expecting);
	}

	function testTransitions() {
		exec('which gs 2>&1', $output, $return);
		$this->skipUnless($return === 0, 'gs command not available');

		$Medium = new DocumentMedium($this->TestData->getFile('application-pdf.pdf'));
		$Medium = $Medium->convert('image/png');
		if ($this->assertIsA($Medium, 'ImageMedium')) {
			$tmpFile = $Medium->store(TMP . uniqid('test_suite_'));
			$this->assertEqual(MimeType::guessType($tmpFile), 'image/png');
			unlink($tmpFile);
		}

		$Medium = new DocumentMedium($this->TestData->getFile('application-pdf.pdf'));
		$Medium = $Medium->convert('image/png');
		if ($this->assertIsA($Medium, 'ImageMedium')) {
			$result = $Medium->fit(10, 10);
			$this->assertTrue($result);
			$this->assertTrue($Medium->width() <= 10);
			$this->assertTrue($Medium->height() <= 10);
		}
	}
}
?>
