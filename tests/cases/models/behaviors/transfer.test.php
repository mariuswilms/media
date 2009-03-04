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

	function setUp() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
		ClassRegistry::flush();
	}

	function skip() {
		$this->skipUnless(@fsockopen('cakephp.org', 80), 'Remote server not available at cakephp.org.');
	}

	function testDestinationFile() {
		$Model =& ClassRegistry::init('Image');

		$Model->Behaviors->attach('Media.Transfer',array('destinationFile' => ':TMP::Source.basename:'));
		$file = $this->TestData->getFile(array('image-jpg.jpg' => TMP . 'wei?rd$Ö- FILE_name_'));
		$item = array('name' => 'Image xy','file' => $file);

		$Model->create();
		$this->assertTrue($Model->save($item));

		$file = $Model->getLastTransferredFile();
		unlink($file);
		$this->assertEqual($file, TMP . 'wei_rd_oe_file_name');
		$Model->Behaviors->detach('Transfer');

		$Model->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP::Idont.exist:'));
		$file = $this->TestData->getFile('image-jpg.jpg');
		$item = array('name' => 'Image xy', 'file' => $file);

		$Model->create();
		$this->expectError();
		$this->assertFalse($Model->save($item));
	}

	function testGetLastTransferredFile() {
		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP::Source.basename:'));

		$this->assertFalse($Model->getLastTransferredFile());

		$file = $this->TestData->getFile('image-jpg.jpg');
		$Model->prepare($file);
		$Model->perform();
		$file = $Model->getLastTransferredFile();
		$this->assertTrue($file);
		$this->assertTrue(file_exists($file));
		unlink($file);
	}

	function testFileLocalToFileLocal() {
		$Model =& ClassRegistry::init('Image');
		$Model->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP::Source.basename:'));

		$file = $this->TestData->getFile('image-jpg.jpg');
		$item = array('name' => 'Image xy', 'file' => $file);

		$Model->create();
		$this->assertTrue($Model->save($item));
		$this->assertTrue(file_exists($file));

		$file = $Model->getLastTransferredFile();
		$this->assertTrue(file_exists($file));
		unlink($file);

		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP::Source.basename:'));

		$file = $this->TestData->getFile('image-jpg.jpg');
		$this->assertTrue($Model->prepare($file));
		$this->assertTrue($Model->perform());
		$this->assertTrue(file_exists($file));

		$file = $Model->getLastTransferredFile();
		$this->assertTrue(file_exists($file));
		unlink($file);
	}

	function testUrlRemoteToFileLocal() {
		$Model =& ClassRegistry::init('Image');
		$Model->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP::Source.basename:'));

		$item = array('name' => 'Image xy', 'file' => 'http://cakephp.org/img/cake-logo.png');

		$Model->create();
		$this->assertTrue($Model->save($item));

		$file = $Model->getLastTransferredFile();
		$this->assertTrue(file_exists($file));

		if (file_exists($file)) {
			unlink($file);
		}

		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP::Source.basename:'));

		$file = 'http://cakephp.org/img/cake-logo.png';
		$this->assertTrue($Model->prepare($file));
		$this->assertTrue($Model->perform());

		$file = $Model->getLastTransferredFile();
		$this->assertTrue(file_exists($file));

		if (file_exists($file)) {
			unlink($file);
		}
	}

	function testTrustClient() {
		$Model =& ClassRegistry::init('TheVoid');
		$config = array('destinationFile' => ':TMP::Source.basename:');
		$configTrust = array('destinationFile' => ':TMP::Source.basename:', 'trustClient' => true);

		$Model->Behaviors->attach('Media.Transfer', $config);

		$file = $this->TestData->getFile('image-jpg.jpg');
		$Model->prepare($file);
		$Model->perform();
		unlink($Model->getLastTransferredFile());
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['source']['mimeType'];
		$this->assertIdentical($result, 'image/jpeg');
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['destination']['mimeType'];
		$this->assertNull($result);

		$file = 'http://cakephp.org/img/cake-logo.png';
		$Model->prepare($file);
		$Model->perform();
		unlink($Model->getLastTransferredFile());
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['source']['mimeType'];
		$this->assertNull($result);
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['destination']['mimeType'];
		$this->assertNull($result);

		$Model->Behaviors->attach('Media.Transfer', $configTrust);

		$file = $this->TestData->getFile('image-jpg.jpg');
		$Model->prepare($file);
		$Model->perform();
		unlink($Model->getLastTransferredFile());
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['source']['mimeType'];
		$this->assertIdentical($result, 'image/jpeg');
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['destination']['mimeType'];
		$this->assertIdentical($result, 'image/jpeg');

		$file = 'http://cakephp.org/img/cake-logo.png';
		$Model->prepare($file);
		$Model->perform();
		unlink($Model->getLastTransferredFile());
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['source']['mimeType'];
		$this->assertIdentical($result, 'image/png');
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['destination']['mimeType'];
		$this->assertIdentical($result, 'image/png');
	}
}
?>