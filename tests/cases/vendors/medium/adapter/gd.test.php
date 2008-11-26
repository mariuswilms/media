<?php
/**
 * Gd Medium Adapter Test Case File
 *
 * Copyright (c) 2007-2008 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium.adapter
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2008 David Persson <davidpersson@qeweurope.org>
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
	function setup() {
		$this->TestData = new MediumTestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function skip()
	{
		$this->skipUnless(extension_loaded('gd'), 'GD extension not loaded');
	}

	function _showImage($string, $mimeType = null) {
		echo '<img src="data:'.$mimeType.';base64,'.base64_encode($string).'" />';
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
		$Medium->fit(10,10);
		$this->assertTrue($Medium->width() <= 10);
		$this->assertTrue($Medium->height() <= 10);

		$Medium = new TestGdImageMedium($this->TestData->getFile('image-jpg.jpg'));
		$Medium->convert('image/png');
		$result = $Medium->mimeType;
		$this->assertTrue($result, 'image/png');
	}
}
?>