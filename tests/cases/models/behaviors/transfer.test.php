<?php
/**
 * Transfer Behavior Test Case File
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
 * @subpackage media.tests.cases.models.behaviors
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::Import('Model', 'App');
App::import('Behavior', 'Media.Transfer');
require_once dirname(dirname(__FILE__)) . DS . 'models.php';
require_once CORE_TEST_CASES . DS . 'libs' . DS . 'model' .DS . 'models.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'fixtures' . DS . 'test_data.php';
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'config' . DS . 'core.php';
/**
 * Test Transfer Behavior Class
 *
 * @package    media
 * @subpackage media.tests.cases.models.behaviors
 */
class TestTransferBehavior extends TransferBehavior {
	function alternativeFile($file, $tries = 100) {
		return $this->_alternativeFile($file, $tries);
	}
}
/**
 * Transfer Behavior Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.models.behaviors
 */
class TransferBehaviorTestCase extends CakeTestCase {
	var $fixtures = array('plugin.media.movie', 'plugin.media.actor');

	function setUp() {
		$this->TestData = new TestData();
		$this->TestFolder = new Folder(TMP . 'test_suite' . DS . 'transfer' . DS, true);
	}

	function tearDown() {
		$this->TestData->flushFiles();
		$this->TestFolder->delete();
		ClassRegistry::flush();
	}

	function skip() {
		$this->skipUnless(@fsockopen('cakephp.org', 80), 'Remote server not available at cakephp.org.');
	}

	function testSetupValidation() {
		$Model =& ClassRegistry::init('Movie');
		$Model->validate['file'] = array(
			'resource' => array('rule' => 'checkResource')
		);
		$Model->Behaviors->attach('Media.Transfer');

		$expected = array(
			'resource' => array(
				'rule' => 'checkResource',
				'allowEmpty' => true,
				'required' => false,
				'last' => true
		));
		$this->assertEqual($Model->validate['file'], $expected);

		$Model =& ClassRegistry::init('Movie');
		$Model->validate['file'] = array(
			'resource' => array(
				'rule' => 'checkResource',
				'required' => true,
		));
		$Model->Behaviors->attach('Media.Transfer');

		$expected = array(
			'resource' => array(
				'rule' => 'checkResource',
				'allowEmpty' => true,
				'required' => true,
				'last' => true
		));
		$this->assertEqual($Model->validate['file'], $expected);
	}

	function testFailOnNoResource() {
		$Model =& ClassRegistry::init('Movie');
		$Model->validate['file'] = array(
			'resource' => array(
				'rule' => 'checkResource',
				'required' => true,
				'allowEmpty' => false,
		));
		$Model->Behaviors->attach('Media.Transfer', array(
			'baseDirectory' => TMP,
			'destinationFile' => 'test_suite:DS:transfer:DS::Source.basename:'
		));

		$item = array('title' => 'Spiderman I', 'file' => '');
		$Model->create($item);
		$this->assertFalse($Model->save());

		$item = array('title' => 'Spiderman I', 'file' => array());
		$Model->create($item);
		$this->assertFalse($Model->save());

		$item = array(
			'title' => 'Spiderman I',
			'file' => array(
				'name' => '',
				'type' => '',
				'tmp_name' => '',
				'error' => UPLOAD_ERR_NO_FILE,
				'size' => 0,
		));
		$Model->create($item);
		$this->assertFalse($Model->save());
	}

	function testDestinationFile() {
		$Model =& ClassRegistry::init('Movie');
		$Model->Behaviors->attach('Media.Transfer', array(
			'baseDirectory' => TMP,
			'destinationFile' => 'test_suite:DS:transfer:DS::Source.basename:'
		));

		$file = $this->TestData->getFile(array('image-jpg.jpg' => 'wei?rd$Ö- FILE_name_'));
		$item = array('title' => 'Spiderman I', 'file' => $file);
		$Model->create();
		$this->assertTrue($Model->save($item));
		$this->assertEqual($Model->getLastTransferredFile(), $this->TestFolder->pwd() . 'wei_rd_oe_file_name');

		$Model->Behaviors->detach('Transfer');
		$Model->Behaviors->attach('Media.Transfer', array(
			'baseDirectory' => TMP,
			'destinationFile' => 'please:raiseanerror:'
		));

		$file = $this->TestData->getFile('image-jpg.jpg');
		$item = array('title' => 'Spiderman II', 'file' => $file);
		$Model->create();
		$this->expectError();
		$this->assertFalse($Model->save($item));
	}

	function testGetLastTransferredFile() {
		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', array(
			'baseDirectory' => TMP,
			'destinationFile' => 'test_suite:DS::Source.basename:'
		));

		$this->assertFalse($Model->getLastTransferredFile());

		$file = $this->TestData->getFile('image-jpg.jpg');
		$Model->prepare($file);
		$Model->perform();
		$file = $Model->getLastTransferredFile();

		$this->assertTrue($file);
		$this->assertTrue(file_exists($file));
	}

	function testFileLocalToFileLocal() {
		$Model =& ClassRegistry::init('Movie');
		$Model->Behaviors->attach('Media.Transfer', array(
			'baseDirectory' => TMP,
			'destinationFile' => 'test_suite:DS:transfer:DS::Source.basename:'
		));

		$file = $this->TestData->getFile(array('image-jpg.jpg' => 'ta.jpg'));
		$item = array('title' => 'Spiderman I', 'file' => $file);
		$Model->create();
		$this->assertTrue($Model->save($item));
		$this->assertTrue(file_exists($file));
		$this->assertEqual($Model->getLastTransferredFile(), $this->TestFolder->pwd() . 'ta.jpg');

		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', array(
			'baseDirectory' => TMP,
			'destinationFile' => 'test_suite:DS:transfer:DS::Source.basename:'
		));

		$file = $this->TestData->getFile(array('image-jpg.jpg' => 'tb.jpg'));
		$this->assertTrue($Model->prepare($file));
		$this->assertTrue($Model->perform());
		$this->assertTrue(file_exists($file));
		$this->assertEqual($Model->getLastTransferredFile(), $this->TestFolder->pwd() . 'tb.jpg');

		ClassRegistry::flush();

		$Model =& ClassRegistry::init('Movie');
		$Model->Actor->Behaviors->attach('Media.Transfer', array(
			'baseDirectory' => TMP,
			'destinationFile' => 'test_suite:DS:transfer:DS::Source.basename:'
		));
		$file = $this->TestData->getFile(array('image-jpg.jpg' => 'tc.jpg'));
		$data = array(
			'Movie' => array('title' => 'Changeling'),
			'Actor' => array(array('name' => 'John Malkovich', 'file' => $file)),
		);
		$this->assertTrue($Model->saveAll($data));
		$this->assertTrue(file_exists($file));
		$this->assertEqual($Model->Actor->getLastTransferredFile(), $this->TestFolder->pwd() . 'tc.jpg');
	}

	function testUrlRemoteToFileLocal() {
		$Model =& ClassRegistry::init('Movie');
		$Model->Behaviors->attach('Media.Transfer', array(
			'baseDirectory' => TMP,
			'destinationFile' => 'test_suite:DS:transfer:DS::Source.basename:'
		));

		$item = array('title' => 'Spiderman I', 'file' => 'http://cakephp.org/img/cake-logo.png');
		$Model->create();
		$this->assertTrue($Model->save($item));
		$this->assertEqual($Model->getLastTransferredFile(), $this->TestFolder->pwd() . 'cake_logo.png');

		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', array(
			'baseDirectory' => TMP,
			'destinationFile' => 'test_suite:DS:transfer:DS::Source.basename:'
		));

		$file = 'http://cakephp.org/img/cake-logo.png';
		$this->assertTrue($Model->prepare($file));
		$this->assertTrue($Model->perform());
		$this->assertEqual($Model->getLastTransferredFile(), $this->TestFolder->pwd() . 'cake_logo_2.png');
	}

	function testTrustClient() {
		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', array(
			'baseDirectory' => TMP,
			'destinationFile' => 'test_suite:DS::Source.basename:'
		));

		$file = $this->TestData->getFile('image-jpg.jpg');
		$Model->prepare($file);
		$Model->perform();
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['source']['mimeType'];
		$this->assertIdentical($result, 'image/jpeg');
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['destination']['mimeType'];
		$this->assertNull($result);

		$file = 'http://cakephp.org/img/cake-logo.png';
		$Model->prepare($file);
		$Model->perform();
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['source']['mimeType'];
		$this->assertNull($result);
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['destination']['mimeType'];
		$this->assertNull($result);

		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', array(
			'trustClient' => true,
			'baseDirectory' => TMP,
			'destinationFile' => 'test_suite:DS::Source.basename:'
		));

		$file = $this->TestData->getFile('image-jpg.jpg');
		$Model->prepare($file);
		$Model->perform();
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['source']['mimeType'];
		$this->assertIdentical($result, 'image/jpeg');
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['destination']['mimeType'];
		$this->assertIdentical($result, 'image/jpeg');

		$file = 'http://cakephp.org/img/cake-logo.png';
		$Model->prepare($file);
		$Model->perform();
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['source']['mimeType'];
		$this->assertIdentical($result, 'image/png');
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['destination']['mimeType'];
		$this->assertIdentical($result, 'image/png');
	}

	function testAlternativeFile() {
		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('TestTransfer', array(
			'baseDirectory' => TMP,
			'destinationFile' => 'test_suite:DS:transfer:DS::Source.basename:'
		));
		$file = $this->TestFolder->pwd() . 'file.jpg';

		$result = $Model->Behaviors->TestTransfer->alternativeFile($file);
		$expected = $this->TestFolder->pwd() . 'file.jpg';
		$this->assertEqual($result, $expected);

		touch($this->TestFolder->pwd() . 'file.jpg');

		$result = $Model->Behaviors->TestTransfer->alternativeFile($file);
		$expected = $this->TestFolder->pwd() . 'file_2.jpg';
		$this->assertEqual($result, $expected);

		touch($this->TestFolder->pwd() . 'file_2.jpg');

		$result = $Model->Behaviors->TestTransfer->alternativeFile($file);
		$expected = $this->TestFolder->pwd() . 'file_3.jpg';
		$this->assertEqual($result, $expected);

		touch($this->TestFolder->pwd() . 'file_3.png');

		$result = $Model->Behaviors->TestTransfer->alternativeFile($file);
		$expected = $this->TestFolder->pwd() . 'file_4.jpg';
		$this->assertEqual($result, $expected);

		touch($this->TestFolder->pwd() . 'file_80.jpg');

		$result = $Model->Behaviors->TestTransfer->alternativeFile($file);
		$expected = $this->TestFolder->pwd() . 'file_4.jpg';
		$this->assertEqual($result, $expected);

		touch($this->TestFolder->pwd() . 'file_4.jpg');

		$result = $Model->Behaviors->TestTransfer->alternativeFile($file, 4);
		$this->assertFalse($result);
	}
}
?>