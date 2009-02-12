<?php
/**
 * Document Medium Test Case File
 *
 * Copyright (c) 2007-2009 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor','Media.DocumentMedium', array('file' => 'medium'.DS.'document.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Document Medium Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.medium
 */
class DocumentMediumTest extends CakeTestCase {
	function setup() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function testInformation() {
		$file = $this->TestData->getFile('application-pdf.pdf');
		$Medium = new DocumentMedium($file);
		$result = $Medium->width();
		$expecting = 595;
		$this->assertEqual($result,$expecting);

		$result = $Medium->height();
		$expecting = 842;
		$this->assertEqual($result,$expecting);

		$result = $Medium->quality();
		$expecting = 0;
		$this->assertEqual($result,$expecting);

		$result = $Medium->ratio();
		$expecting = '1:√2';
		$this->assertEqual($result,$expecting);
	}
}
?>