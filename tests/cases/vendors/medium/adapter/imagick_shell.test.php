<?php
/**
 * Imagick Shell Medium Adapter Test Case File
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
App::import('Vendor','Media.DocumentMedium', array('file' => 'medium'.DS.'document.php'));
App::import('Vendor','ImagickShellMediumAdapter', array('file' => 'medium'.DS.'adapter'.DS.'imagick_shell.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Test Imagick Shell Image Medium Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class TestImagickShellImageMedium extends ImageMedium {
	var $adapters = array('ImagickShell');
}
/**
 * Test Imagick Shell Document Medium Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class TestImagickShellDocumentMedium extends DocumentMedium {
	var $adapters = array('ImagickShell');
}
/**
 * Imagick Shell Medium Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 */
class ImagickShellMediumAdapterTest extends CakeTestCase {
	function setUp() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function skip()	{
		exec('which convert 2>&1', $output, $return);
		$this->skipUnless($return === 0, 'convert command not available');
		exec('which identify 2>&1', $output, $return);
		$this->skipUnless($return === 0, 'identify command not available');
		exec('which gs 2>&1', $output, $return);
		$this->skipUnless($return === 0, 'gs command not available');
	}

	function showImage($string, $mimeType = null) {
		echo '<img src="data:'.$mimeType.';base64,'.base64_encode($string).'" />';
	}

	function testBasic() {
		$result = new TestImagickShellImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$this->assertIsA($result, 'object');

		$Medium = new TestImagickShellImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$result = $Medium->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformation() {
		$Medium = new TestImagickShellImageMedium($this->TestData->getFile('image-jpg.jpg'));

		$result = $Medium->width();
		$this->assertEqual($result, 70);

		$result = $Medium->height();
		$this->assertEqual($result, 47);
	}

	function testManipulation() {
		$Medium = new TestImagickShellImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$Medium->fit(10,10);
		$this->assertTrue($Medium->width() <= 10);
		$this->assertTrue($Medium->height() <= 10);

		$Medium = new TestImagickShellImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$Medium->convert('image/png');
		$result = $Medium->mimeType;
		$this->assertTrue($result, 'image/png');
	}

	function testCompress() {
		$Medium = new TestImagickShellImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$resultCompress = $Medium->compress(1.5);
		$resultStore = $Medium->store($this->TestData->getFile('test-compress-1.5.jpg'), true);
		$this->assertTrue($resultCompress);
		$this->assertTrue($resultStore);

		$Medium = new TestImagickShellImageMedium($this->TestData->getFile('image-png.png'));
		$resultCompress = $Medium->compress(1.5);
		$resultStore = $Medium->store($this->TestData->getFile('test-compress-1.5.png'), true);
		$this->assertTrue($resultCompress);
		$this->assertTrue($resultStore);
	}

	function testTransitions() {
		$Medium = new DocumentMedium($this->TestData->getFile('application-pdf.pdf'));
		$Medium->Adapters->detach(array_diff($Medium->adapters, array('ImagickShell')));

		$Medium = $Medium->convert('image/png');
		$Medium->Adapters->detach(array_diff($Medium->adapters, array('ImagickShell')));
		$this->assertIsA($Medium, 'ImageMedium');

		$tmpFile = $Medium->store(TMP . uniqid('test_suite_'));
		$this->assertEqual(MimeType::guessType($tmpFile), 'image/png');
		unlink($tmpFile);

		$Medium = new DocumentMedium($this->TestData->getFile('application-pdf.pdf'));
		$Medium->Adapters->detach(array_diff($Medium->adapters, array('ImagickShell')));
		$Medium = $Medium->convert('image/png');
		$Medium->Adapters->detach(array_diff($Medium->adapters, array('ImagickShell')));
		$result = $Medium->fit(10, 10);
		$this->assertTrue($result);
		$this->assertTrue($Medium->width() <= 10);
		$this->assertTrue($Medium->height() <= 10);
	}
}
?>