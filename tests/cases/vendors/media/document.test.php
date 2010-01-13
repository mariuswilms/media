<?php
/**
 * Document Media Test Case File
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
App::import('Vendor', 'Media.DocumentMedia', array('file' => 'media' . DS . 'document.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';

/**
 * Document Media Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs.media
 */
class DocumentMediaTest extends CakeTestCase {
	function setUp() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function skip()	{
		exec('which gs 2>&1', $output, $return);
		$this->skipUnless($return === 0, 'gs command not available');
	}

	function testInformation() {
		$file = $this->TestData->getFile('application-pdf.pdf');
		$Media = new DocumentMedia($file);
		$result = $Media->width();
		$expecting = 595;
		$this->assertEqual($result, $expecting);

		$result = $Media->height();
		$expecting = 842;
		$this->assertEqual($result, $expecting);

		$result = $Media->quality();
		$expecting = 0;
		$this->assertEqual($result, $expecting);

		$result = $Media->ratio();
		$expecting = '1:√2';
		$this->assertEqual($result, $expecting);
	}
}
?>