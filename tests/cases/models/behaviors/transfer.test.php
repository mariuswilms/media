<?php
/**
 * Transfer Behavior Test Case File
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
 * @subpackage media.tests.cases.models.behaviors
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::Import('Model', 'App');
require_once CORE_TEST_CASES . DS . 'libs' . DS . 'model' .DS . 'models.php';
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
require_once APP . 'plugins' . DS . 'media' . DS . 'config' . DS . 'core.php';
/**
 * Transfer Behavior Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.models.behaviors
 */
class TransferBehaviorTestCase extends CakeTestCase {
	var $fixtures = array('core.image');

	function setup() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
		ClassRegistry::flush();
	}

	function testDestinationFile() {
		$Model =& ClassRegistry::init('Image');

		$Model->Behaviors->attach('Media.Transfer',array('destinationFile' => ':TMP::Source.basename:'));
		$file = $this->TestData->getFile(array('image-jpg.jpg' => TMP . 'wei?rd$Ö- FILE_name_'));
		$item = array('name' => 'Image xy','file' => $file);

		$Model->create();
		$result = $Model->save($item);
		$this->assertTrue($result);

		$file = $Model->getLastTransferredFile();
		unlink($file);
		$this->assertEqual($file, TMP . 'wei_rd_oe_file_name');
		$Model->Behaviors->detach('Transfer');

		$Model->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP::Idont.exist:'));
		$file = $this->TestData->getFile('image-jpg.jpg');
		$item = array('name' => 'Image xy', 'file' => $file);

		$Model->create();
		$this->expectError();
		$result = $Model->save($item);
		$this->assertFalse($result);
		$Model->Behaviors->detach('Transfer');
	}

	function testFileLocalToFileLocal() {
		$Model =& ClassRegistry::init('Image');
		$Model->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP::Source.basename:'));

		$file = $this->TestData->getFile('image-jpg.jpg');
		$item = array('name' => 'Image xy', 'file' => $file);

		$Model->create();
		$result = $Model->save($item);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($file));

		$file = $Model->getLastTransferredFile();
		$this->assertTrue(file_exists($file));
		unlink($file);
	}

	function testFileLocalToFileLocalTableless() {
		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP::Source.basename:'));

		$file = $this->TestData->getFile('image-jpg.jpg');
		$Model->prepare($file);
		$result = $Model->perform();
		$this->assertTrue($result);
		$this->assertTrue(file_exists($file));

		$file = $Model->getLastTransferredFile();
		$this->assertTrue($file);
		$this->assertTrue(file_exists($file));
		unlink($file);
	}

	function testUrlRemoteToFileLocal() {
		$this->skipUnless(@fsockopen('www.cakephp.org', 80), 'Remote server not available.');

		$Model =& ClassRegistry::init('Image');
		$Model->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP::Source.basename:'));

		$item = array('name' => 'Image xy', 'file' => 'http://www.cakephp.org/img/cake-logo.png');

		$Model->create();
		$result = $Model->save($item);
		$this->assertTrue($result);

		$file = $Model->getLastTransferredFile();
		$this->assertTrue($file);
		$this->assertTrue(file_exists($file));

		if (file_exists($file)) {
			unlink($file);
		}
	}
}
?>