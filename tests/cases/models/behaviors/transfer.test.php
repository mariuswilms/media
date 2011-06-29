<?php
/**
 * Transfer Behavior Test Case File
 *
 * Copyright (c) 2007-2011 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.tests.cases.models.behaviors
 * @copyright  2007-2011 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
require_once dirname(__FILE__) . DS . 'base.test.php';
App::import('Behavior', 'Media.Transfer');

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
class TransferBehaviorTestCase extends BaseBehaviorTestCase {

	var $fixtures = array('plugin.media.movie', 'plugin.media.actor');

	function setUp() {
		parent::setUp();
		$this->_transferDirectory = $this->Folder->pwd() . 'transfer' . DS;
		$this->_behaviorSettings['Transfer'] = array(
			'transferDirectory' => $this->_transferDirectory
		);
		$this->_remoteAvailable = @fsockopen('cakephp.org', 80);
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
		$Model->Behaviors->attach('Media.Transfer', $this->_behaviorSettings['Transfer']);

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
		$Model->Behaviors->attach('Media.Transfer', $this->_behaviorSettings['Transfer']);

		$file = $this->Data->getFile(array('image-jpg.jpg' => 'wei?rd$Ö- FILE_name_'));
		$item = array('title' => 'Spiderman I', 'file' => $file);
		$Model->create();
		$this->assertTrue($Model->save($item));
		$expected = $this->_transferDirectory . 'img' . DS . 'wei_rd_oe_file_name';
		$this->assertEqual($Model->transferred(), $expected);
	}

	function testTransferred() {
		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', $this->_behaviorSettings['Transfer']);

		$this->assertFalse($Model->transferred());

		$file = $this->Data->getFile('image-jpg.jpg');
		$Model->transfer($file);
		$file = $Model->transferred();

		$this->assertTrue($file);
		$this->assertTrue(file_exists($file));
	}

	function testFileLocalToFileLocal() {
		$Model =& ClassRegistry::init('Movie');
		$Model->Behaviors->attach('Media.Transfer', $this->_behaviorSettings['Transfer']);

		$file = $this->Data->getFile(array('image-jpg.jpg' => 'ta.jpg'));
		$item = array('title' => 'Spiderman I', 'file' => $file);
		$Model->create();
		$this->assertTrue($Model->save($item));
		$this->assertTrue(file_exists($file));
		$expected = $this->_transferDirectory . 'img' . DS . 'ta.jpg';
		$this->assertEqual($Model->transferred(), $expected);

		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', $this->_behaviorSettings['Transfer']);

		$file = $this->Data->getFile(array('image-jpg.jpg' => 'tb.jpg'));
		$this->assertTrue($Model->transfer($file));
		$this->assertTrue(file_exists($file));
		$expected = $this->_transferDirectory . 'img' . DS . 'tb.jpg';
		$this->assertEqual($Model->transferred(), $expected);

		ClassRegistry::flush();

		$Model =& ClassRegistry::init('Movie');
		$Model->Actor->Behaviors->attach('Media.Transfer', $this->_behaviorSettings['Transfer']);
		$file = $this->Data->getFile(array('image-jpg.jpg' => 'tc.jpg'));
		$data = array(
			'Movie' => array('title' => 'Changeling'),
			'Actor' => array(array('name' => 'John Malkovich', 'file' => $file)),
		);
		$this->assertTrue($Model->saveAll($data));
		$this->assertTrue(file_exists($file));
		$expected = $this->_transferDirectory . 'img' . DS . 'tc.jpg';
		$this->assertEqual($Model->Actor->transferred(), $expected);
	}

	function testUrlRemoteToFileLocal() {
		if ($this->skipIf(!$this->_remoteAvailable, 'Remote server not available. %s')) {
			return;
		}

		$Model =& ClassRegistry::init('Movie');
		$Model->Behaviors->attach('Media.Transfer', $this->_behaviorSettings['Transfer']);

		$item = array('title' => 'Spiderman I', 'file' => 'http://cakephp.org/img/cake-logo.png');
		$Model->create();
		$this->assertTrue($Model->save($item));
		$expected = $this->_transferDirectory . 'img' . DS . 'cake_logo.png';
		$this->assertEqual($Model->transferred(), $expected);

		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', $this->_behaviorSettings['Transfer']);

		$file = 'http://cakephp.org/img/cake-logo.png';
		$this->assertTrue($Model->transfer($file));
		$expected = $this->_transferDirectory . 'img' . DS . 'cake_logo_2.png';
		$this->assertEqual($Model->transferred(), $expected);
	}

	function testTrustClient() {
		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', $this->_behaviorSettings['Transfer']);

		$file = $this->Data->getFile('image-jpg.jpg');
		$Model->transfer($file);
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['source']['mimeType'];
		$this->assertIdentical($result, 'image/jpeg');
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['destination']['mimeType'];
		$this->assertNull($result);

		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', array(
			'trustClient' => true
		) + $this->_behaviorSettings['Transfer']);

		$file = $this->Data->getFile('image-jpg.jpg');
		$Model->transfer($file);
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['source']['mimeType'];
		$this->assertIdentical($result, 'image/jpeg');
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['destination']['mimeType'];
		$this->assertIdentical($result, 'image/jpeg');
	}

	function testTrustClientRemote() {
		if ($this->skipIf(!$this->_remoteAvailable, 'Remote server not available. %s')) {
			return;
		}

		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', $this->_behaviorSettings['Transfer']);

		$file = 'http://cakephp.org/img/cake-logo.png';
		$Model->transfer($file);
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['source']['mimeType'];
		$this->assertNull($result);
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['destination']['mimeType'];
		$this->assertNull($result);

		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Transfer', array(
			'trustClient' => true
		) + $this->_behaviorSettings['Transfer']);

		$file = 'http://cakephp.org/img/cake-logo.png';
		$Model->transfer($file);
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['source']['mimeType'];
		$this->assertIdentical($result, 'image/png');
		$result = $Model->Behaviors->Transfer->runtime['TheVoid']['destination']['mimeType'];
		$this->assertIdentical($result, 'image/png');
	}

	function testAlternativeFile() {
		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.TestTransfer', $this->_behaviorSettings['Transfer']);
		$file = $this->Folder->pwd() . 'file.jpg';

		$result = $Model->Behaviors->TestTransfer->alternativeFile($file);
		$expected = $this->Folder->pwd() . 'file.jpg';
		$this->assertEqual($result, $expected);

		touch($this->Folder->pwd() . 'file.jpg');

		$result = $Model->Behaviors->TestTransfer->alternativeFile($file);
		$expected = $this->Folder->pwd() . 'file_2.jpg';
		$this->assertEqual($result, $expected);

		touch($this->Folder->pwd() . 'file_2.jpg');

		$result = $Model->Behaviors->TestTransfer->alternativeFile($file);
		$expected = $this->Folder->pwd() . 'file_3.jpg';
		$this->assertEqual($result, $expected);

		touch($this->Folder->pwd() . 'file_3.png');

		$result = $Model->Behaviors->TestTransfer->alternativeFile($file);
		$expected = $this->Folder->pwd() . 'file_4.jpg';
		$this->assertEqual($result, $expected);

		touch($this->Folder->pwd() . 'file_80.jpg');

		$result = $Model->Behaviors->TestTransfer->alternativeFile($file);
		$expected = $this->Folder->pwd() . 'file_4.jpg';
		$this->assertEqual($result, $expected);

		touch($this->Folder->pwd() . 'file_4.jpg');

		$result = $Model->Behaviors->TestTransfer->alternativeFile($file, 4);
		$this->assertFalse($result);
	}
}
?>
