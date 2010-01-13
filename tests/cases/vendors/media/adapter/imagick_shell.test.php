<?php
/**
 * Imagick Shell Media Adapter Test Case File
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
App::import('Vendor','Media.ImageMedia', array('file' => 'media'.DS.'image.php'));
App::import('Vendor','Media.DocumentMedia', array('file' => 'media'.DS.'document.php'));
App::import('Vendor','ImagickShellMediaAdapter', array('file' => 'media'.DS.'adapter'.DS.'imagick_shell.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Test Imagick Shell Image Media Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class TestImagickShellImageMedia extends ImageMedia {
	var $adapters = array('ImagickShell');
}
/**
 * Test Imagick Shell Document Media Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class TestImagickShellDocumentMedia extends DocumentMedia {
	var $adapters = array('ImagickShell');
}
/**
 * Imagick Shell Media Adapter Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media.adapter
 */
class ImagickShellMediaAdapterTest extends CakeTestCase {
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
		$result = new TestImagickShellImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$this->assertIsA($result, 'object');

		$Media = new TestImagickShellImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$result = $Media->toString();
		$this->assertTrue(!empty($result));
	}

	function testInformation() {
		$Media = new TestImagickShellImageMedia($this->TestData->getFile('image-jpg.jpg'));

		$result = $Media->width();
		$this->assertEqual($result, 70);

		$result = $Media->height();
		$this->assertEqual($result, 47);
	}

	function testManipulation() {
		$Media = new TestImagickShellImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$Media->fit(10,10);
		$this->assertTrue($Media->width() <= 10);
		$this->assertTrue($Media->height() <= 10);

		$Media = new TestImagickShellImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$Media->convert('image/png');
		$result = $Media->mimeType;
		$this->assertTrue($result, 'image/png');
	}

	function testCompress() {
		$Media = new TestImagickShellImageMedia($this->TestData->getFile('image-jpg.jpg'));
		$resultCompress = $Media->compress(1.5);
		$resultStore = $Media->store($this->TestData->getFile('test-compress-1.5.jpg'), true);
		$this->assertTrue($resultCompress);
		$this->assertTrue($resultStore);

		$Media = new TestImagickShellImageMedia($this->TestData->getFile('image-png.png'));
		$resultCompress = $Media->compress(1.5);
		$resultStore = $Media->store($this->TestData->getFile('test-compress-1.5.png'), true);
		$this->assertTrue($resultCompress);
		$this->assertTrue($resultStore);
	}

	function testTransitions() {
		$Media = new DocumentMedia($this->TestData->getFile('application-pdf.pdf'));
		$Media->Adapters->detach(array_diff($Media->adapters, array('ImagickShell')));

		$Media = $Media->convert('image/png');
		$Media->Adapters->detach(array_diff($Media->adapters, array('ImagickShell')));
		$this->assertIsA($Media, 'ImageMedia');

		$tmpFile = $Media->store(TMP . uniqid('test_suite_'));
		$this->assertEqual(MimeType::guessType($tmpFile), 'image/png');
		unlink($tmpFile);

		$Media = new DocumentMedia($this->TestData->getFile('application-pdf.pdf'));
		$Media->Adapters->detach(array_diff($Media->adapters, array('ImagickShell')));
		$Media = $Media->convert('image/png');
		$Media->Adapters->detach(array_diff($Media->adapters, array('ImagickShell')));
		$result = $Media->fit(10, 10);
		$this->assertTrue($result);
		$this->assertTrue($Media->width() <= 10);
		$this->assertTrue($Media->height() <= 10);
	}
}
?>