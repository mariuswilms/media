<?php
/**
 * Transfer Validation Test Case File
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
 * @subpackage media.tests.cases.libs
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor','Media.TransferValidation');
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Transfer Validation Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs
 */
class TransferValidationTest extends CakeTestCase {
	function setup() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}
}
?>