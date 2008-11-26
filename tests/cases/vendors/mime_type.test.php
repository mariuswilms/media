<?php
/**
 * Mime Type Test Case File
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
 * @subpackage media.tests.cases.libs
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2008 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.MimeType');
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Mime Type Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs
 */
class MimeTypeTest extends CakeTestCase {
	function setup() {
		$this->TestData = new MediumTestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}
/**
 * method
 *
 * @access public
 * @return void
 */
	function testSimplify() {
		$result = MimeType::simplify('application/x-pdf');
		$this->assertEqual($result, 'application/pdf');

		$result = MimeType::simplify('x-inode/x-directory');
		$this->assertEqual($result, 'inode/directory');

		$result = MimeType::simplify('application/octet-stream; encoding=compress');
		$this->assertEqual($result, 'application/octet-stream');

		$result = MimeType::simplify('application/x-test; encoding=compress');
		$this->assertEqual($result, 'application/test');

		$result = MimeType::simplify('text/plain; charset=iso-8859-1');
		$this->assertEqual($result, 'text/plain');

		$result = MimeType::simplify('text/plain charset=us-ascii');
		$this->assertEqual($result, 'text/plain');
	}
/**
 * method
 *
 * @access public
 * @return void
 */
	function testGuessType() {
		$file = $this->TestData->getFile('image-jpeg.snippet.jpg');
		$result = MimeType::guessType($file);
		$this->assertEqual($result, 'image/jpeg');

		$file = $this->TestData->getFile('generic-binary');
		$result = MimeType::guessType($file);
		$this->assertEqual($result, 'application/octet-stream');

		$file = $this->TestData->getFile('generic-text');
		$result = MimeType::guessType($file, array('simplify' => true));
		$this->assertEqual($result, 'text/plain');
	}
/**
 * method
 *
 * @access public
 * @return void
 */
	function testGuessExtension() {
		$file = $this->TestData->getFile('image-jpeg.snippet.jpg');
		$result = MimeType::guessExtension($file);
		$this->assertEqual($result, 'jpg');

		$result = MimeType::guessExtension('text/plain');
		$this->assertEqual($result, 'txt');

		$result = MimeType::guessExtension('i-m-not-a-mime-type');
		$this->assertFalse($result);

		$result = MimeType::guessExtension('/tmp/i-m-do-not-exist.txt');
		$this->assertFalse($result);
	}
}
?>