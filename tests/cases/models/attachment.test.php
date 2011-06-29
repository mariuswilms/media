<?php
/**
 * Attachment Test Case File
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
 * @subpackage media.tests.cases.models
 * @copyright  2007-2011 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Model', 'Media.Attachment');
require_once 'models.php';
require_once dirname(dirname(dirname(__FILE__))) . DS . 'fixtures' . DS . 'test_data.php';

if (!defined('MEDIA')) {
	define('MEDIA', TMP . 'tests' . DS);
} elseif (MEDIA != TMP . 'tests' . DS) {
	trigger_error('MEDIA constant already defined and not pointing to tests directory.', E_USER_ERROR);
}
require_once dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'config' . DS . 'core.php';
require_once 'Media/Process.php';
require_once 'Media/Info.php';

/**
 * Attachment Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.models
 */
class AttachmentTestCase extends CakeTestCase {

	var $fixtures = array(
		'plugin.media.movie', 'plugin.media.actor',
		'plugin.media.attachment', 'plugin.media.pirate'
	);

	function setUp() {
		$this->Data = new TestData();
		$this->Folder = new Folder(TMP . 'tests' . DS, true);
		new Folder($this->Folder->pwd() . 'transfer' . DS, true);
		new Folder($this->Folder->pwd() . 'static' . DS, true);
		new Folder($this->Folder->pwd() . 'filter' . DS, true);
	}

	function tearDown() {
		$this->Data->flushFiles();
		$this->Folder->delete();
		ClassRegistry::flush();
	}

	function testHasOne() {
		$Model = $this->_model('hasOne');

		$file = $this->Data->getFile(array('image-jpg.jpg' => 'ta.jpg'));
		$data = array(
			'Movie' => array('title' => 'Weekend', 'director' => 'Jean-Luc Godard'),
			'Attachment' => array('file' => $file, 'model' => 'Movie')
		);

		$Model->create();
		$this->assertTrue($Model->saveAll($data, array('validate' => 'first')));
		$file = $Model->Attachment->transferred();
		$this->assertTrue(file_exists($file));

		$result = $Model->find('first', array('conditions' => array('title' => 'Weekend')));
		$expected = array(
			'id' => 1,
			'model' => 'Movie',
			'foreign_key' => 4,
			'dirname' => 'img',
			'basename' => 'ta.jpg',
			'checksum' => '1920c29e7fbe4d1ad2f9173ef4591133',
			'group' => null,
			'alternative' => null,
		);
		$this->assertEqual($result['Attachment'], $expected);

		$result = $Model->delete($Model->getLastInsertID());
		$this->assertTrue($result);
		$this->assertFalse(file_exists($this->Folder->pwd() . 'transfer' .  DS . 'ta.jpg'));
	}

	function testHasMany() {
		$Model = $this->_model('hasMany');

		$fileA = $this->Data->getFile(array('image-jpg.jpg' => 'ta.jpg'));
		$fileB = $this->Data->getFile(array('image-png.png' => 'tb.png'));
		$data = array(
			'Movie' => array('title' => 'Weekend', 'director' => 'Jean-Luc Godard'),
			'Attachment' => array(
				array('file' => $fileA, 'model' => 'Movie'),
				array('file' => $fileB, 'model' => 'Movie')
		));

		$Model->create();
		$result = $Model->saveAll($data, array('validate' => 'first'));
		$this->assertTrue($result);
		$this->assertTrue(file_exists($this->Folder->pwd() . 'transfer' . DS . 'img' . DS . 'ta.jpg'));
		$this->assertTrue(file_exists($this->Folder->pwd() . 'transfer' . DS . 'img' . DS . 'tb.png'));

		$result = $Model->find('first', array('conditions' => array('title' => 'Weekend')));
		$expected = array(
			0 => array(
				'id' => 1,
				'model' => 'Movie',
				'foreign_key' => 4,
				'dirname' => 'img',
				'basename' => 'ta.jpg',
				'checksum' => '1920c29e7fbe4d1ad2f9173ef4591133',
				'group' => null,
				'alternative' => null,
			),
			1 => array(
				'id' => 2,
				'model' => 'Movie',
				'foreign_key' => 4,
				'dirname' => 'img',
				'basename' => 'tb.png',
				'checksum' => '7f9af648b511f2c83b1744f42254983f',
				'group' => null,
				'alternative' => null,
		));
		$this->assertEqual($result['Attachment'], $expected);

		$result = $Model->delete($Model->getLastInsertID());
		$this->assertTrue($result);
		$this->assertFalse(file_exists($this->Folder->pwd() . 'transfer' .  DS . 'ta.jpg'));
		$this->assertFalse(file_exists($this->Folder->pwd() . 'transfer' .  DS . 'tb.jpg'));
	}

	function testHasManyWithMissingMediaAdapters() {
		$_backupConfig = Configure::read('Media');
		$_backupProcess = Media_Process::config();
		$_backupInfo = Media_Info::config();

		$s = array('convert' => 'image/png', 'zoomCrop' => array(100, 100));
		$m = array('convert' => 'image/png', 'fitCrop' => array(300, 300));
		$l = array('convert' => 'image/png', 'fit' => array(600, 440));

		Configure::write('Media.filter', array(
			'audio' => compact('s', 'm'),
			'document' => compact('s', 'm'),
			'generic' => array(),
			'image' => compact('s', 'm', 'l'),
			'video' => compact('s', 'm')
		));

		Media_Process::config(array('image' => null));
		Media_Info::config(array('image' => null));

		$Model = $this->_model('hasMany');

		$file = $this->Data->getFile(array('image-jpg.jpg' => 'ta.jpg'));
		$data = array(
			'Movie' => array('title' => 'Weekend', 'director' => 'Jean-Luc Godard'),
			'Attachment' => array(
				array('file' => $file, 'model' => 'Movie'),
		));

		$this->expectError();
		$this->expectError();
		$this->expectError();

		$Model->create();
		$result = $Model->saveAll($data, array('validate' => 'first'));
		$this->assertTrue($result);
		$this->assertTrue(file_exists($this->Folder->pwd() . 'transfer' . DS . 'img' . DS . 'ta.jpg'));

		$result = $Model->find('first', array('conditions' => array('title' => 'Weekend')));
		$expected = array(
			0 => array(
				'id' => 1,
				'model' => 'Movie',
				'foreign_key' => 4,
				'dirname' => 'img',
				'basename' => 'ta.jpg',
				'checksum' => '1920c29e7fbe4d1ad2f9173ef4591133',
				'group' => null,
				'alternative' => null,
			)
		);
		$this->assertEqual($result['Attachment'], $expected);

		Media_Process::config($_backupProcess);
		Media_Info::config($_backupInfo);
		Configure::write('Media', $_backupConfig);
	}

	function testGroupedHasMany() {
		$assoc = array(
			'Photo' => array(
				'className' => 'Media.Attachment',
				'foreignKey' => 'foreign_key',
				'conditions' => array('Photo.model' => 'Movie', 'Photo.group' => 'photo'),
				'dependent' => true
		));
		$Model = $this->_model('hasMany', $assoc);

		$fileA = $this->Data->getFile(array('image-png.png' => 'ta.png'));
		$fileB = $this->Data->getFile(array('image-png.png' => 'tb.png'));
		$data = array(
			'Movie' => array('title' => 'Weekend', 'director' => 'Jean-Luc Godard'),
			'Photo' => array(
				array('file' => $fileA, 'model' => 'Movie', 'group' => 'photo'),
				array('file' => $fileB, 'model' => 'Movie', 'group' => 'photo'),
		));

		$Model->create();
		$result = $Model->saveAll($data, array('validate' => 'first'));
		$this->assertTrue($result);
		$this->assertTrue(file_exists($this->Folder->pwd() . 'transfer' . DS . 'img' . DS . 'ta.png'));
		$this->assertTrue(file_exists($this->Folder->pwd() . 'transfer' . DS . 'img' . DS . 'tb.png'));

		$result = $Model->find('first', array('conditions' => array('title' => 'Weekend')));
		$expected = array(
			'Movie' => array(
				'id' => 4,
				'title' => 'Weekend',
				'director' => 'Jean-Luc Godard',
			),
			'Actor' => array(),
			'Photo' => array(
				0 => array(
					'id' => 1,
					'model' => 'Movie',
					'foreign_key' => 4,
					'dirname' => 'img',
					'basename' => 'ta.png',
					'checksum' => '7f9af648b511f2c83b1744f42254983f',
					'group' => 'photo',
					'alternative' => null,
				),
				1 => array(
					'id' => 2,
					'model' => 'Movie',
					'foreign_key' => 4,
					'dirname' => 'img',
					'basename' => 'tb.png',
					'checksum' => '7f9af648b511f2c83b1744f42254983f',
					'group' => 'photo',
					'alternative' => null,
		)));
		$this->assertEqual($result, $expected);

		$result = $Model->delete($Model->getLastInsertID());
		$this->assertTrue($result);
		$this->assertFalse(file_exists($this->Folder->pwd() . 'transfer' . DS . 'photo' . DS . 'ta.png'));
		$this->assertFalse(file_exists($this->Folder->pwd() . 'transfer' . DS . 'photo' . DS . 'tb.png'));
	}

	function _model($assocType, $assoc = null) {
		$Model = ClassRegistry::init('Movie');

		if ($assoc === null) {
			$assoc = array(
				'Attachment' => array(
					'className' => 'Media.Attachment',
					'foreignKey' => 'foreign_key',
					'conditions' => array('Attachment.model' => 'Movie'),
					'dependent' => true,
			));
		}
		$Model->bindModel(array($assocType => $assoc), false);
		$assocModelName = key($assoc);

		$Model->{$assocModelName}->Behaviors->attach('Media.Transfer', array(
			'transferDirectory' => $this->Folder->pwd() . 'transfer' . DS
		));
		$Model->{$assocModelName}->Behaviors->attach('Media.Generator', array(
			'baseDirectory' => $this->Folder->pwd() . 'transfer' . DS,
			'filterDirectory' => $this->Folder->pwd() . 'filter' . DS
		));
		$Model->{$assocModelName}->Behaviors->attach('Media.Coupler');
		$Model->{$assocModelName}->Behaviors->attach('Media.Polymorphic');
		$Model->{$assocModelName}->Behaviors->attach('Media.Meta', array(
			'level' => 2
		));
		return $Model;
	}
}
?>
