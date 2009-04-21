<?php
/**
 * Attachment Test Case File
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
 * @subpackage media.tests.cases.models
 * @copyright  2007-2009 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Model', 'Media.Attachment');
require_once 'models.php';
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
require_once APP . 'plugins' . DS . 'media' . DS . 'config' . DS . 'core.php';
/**
 * Attachment Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.models
 */
class AttachmentTestCase extends CakeTestCase {
	var $fixtures = array('plugin.media.movie', 'plugin.media.actor', 'plugin.media.attachment');

	function setUp() {
		$this->TestData = new TestData();
		$this->TestFolder = new Folder(TMP . 'test_suite' . DS, true);
		new Folder($this->TestFolder->pwd() . 'transfer' . DS, true);
		new Folder($this->TestFolder->pwd() . 'static' . DS, true);
		new Folder($this->TestFolder->pwd() . 'filter' . DS, true);
	}

	function tearDown() {
		$this->TestData->flushFiles();
		$this->TestFolder->delete();
		ClassRegistry::flush();
	}

	function testHasMany() {
		$Model = ClassRegistry::init('Movie');
		$assoc = array(
					'Attachment' => array(
						'className' => 'Media.Attachment',
						'foreignKey' => 'foreign_key',
						'conditions' => array('model' => 'Movie'),
						'dependent' => true,
						),
					);
		$Model->bindModel(array('hasMany' => $assoc), false);
		$Model->Attachment->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP:test_suite:DS::Source.basename:'));
		$Model->Attachment->Behaviors->attach('Media.Media', array('base' => $this->TestFolder->pwd()));

		$fileA = $this->TestData->getFile(array('image-jpg.jpg' => 'ta.jpg'));
		$fileB = $this->TestData->getFile(array('image-png.png' => 'tb.png'));
		$data = array(
					'Movie' => array('title' => 'Weekend', 'director' => 'Jean-Luc Godard'),
					'Attachment' => array(
										array('file' => $fileA, 'model' => 'Movie'),
										array('file' => $fileB, 'model' => 'Movie')
										)
					);
		$Model->create();
		$this->assertTrue($Model->saveAll($data, array('validate' => 'first')));
		$this->assertTrue(file_exists($this->TestFolder->pwd() . 'ta.jpg'));
		$this->assertTrue(file_exists($this->TestFolder->pwd() . 'tb.png'));
		$result = $Model->find('first', array('conditions' => array('title' => 'Weekend')));
		$expected = array(
			0 => array(
					'id' => 1,
					'model' => 'Movie',
					'foreign_key' => 4,
					'dirname' => null,
					'basename' => 'ta.jpg',
					'checksum' => '1920c29e7fbe4d1ad2f9173ef4591133',
					'group' => null,
					'alternative' => null,
					),
			1 => array(
					'id' => 2,
					'model' => 'Movie',
					'foreign_key' => 4,
					'dirname' => null,
					'basename' => 'tb.png',
					'checksum' => '7f9af648b511f2c83b1744f42254983f',
					'group' => null,
					'alternative' => null,
					)
				);
		$this->assertEqual($result['Attachment'], $expected);
	}
/*
	function testHasManyMultiple() {
		$Model = ClassRegistry::init('Movie');
		$assoc = array(
					'Poster' => array(
						'className' => 'Media.Attachment',
						'foreignKey' => 'foreign_key',
						'conditions' => array('model' => 'Movie', 'group' => 'poster'),
						'dependent' => true,
					),
					'Photo' => array(
						'className' => 'Media.Attachment',
						'foreignKey' => 'foreign_key',
						'conditions' => array('model' => 'Movie', 'group' => 'photo'),
						'dependent' => true,
					),
					'Trailer' => array(
						'className' => 'Media.Attachment',
						'foreignKey' => 'foreign_key',
						'conditions' => array('model' => 'Movie', 'group' => 'trailer'),
						'dependent' => true,
					)
				);
		$Model->bindModel(array('hasMany' => $assoc), false);
		$Model->Poster->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP:test_suite:DS:poster:DS::Source.basename:'));
		$Model->Poster->Behaviors->attach('Media.Media', array('base' => $this->TestFolder->pwd()));
		$Model->Photo->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP:test_suite:DS:photo:DS::Source.basename:'));
		$Model->Photo->Behaviors->attach('Media.Media', array('base' => $this->TestFolder->pwd()));
		$Model->Trailer->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP:test_suite:DS:trailer:DS::Source.basename:'));
		$Model->Trailer->Behaviors->attach('Media.Media', array('base' => $this->TestFolder->pwd()));
	}
*/

	function testHasOne() {
		$Model = ClassRegistry::init('Movie');
		$assoc = array(
					'Attachment' => array(
						'className' => 'Media.Attachment',
						'foreignKey' => 'foreign_key',
						'conditions' => array('model' => 'Movie'),
						'dependent' => true,
						),
					);
		$Model->bindModel(array('hasOne' => $assoc), false);
		$Model->Attachment->Behaviors->attach('Media.Transfer', array('destinationFile' => ':TMP:test_suite:DS::Source.basename:'));
		$Model->Attachment->Behaviors->attach('Media.Media', array('base' => $this->TestFolder->pwd()));

		$file = $this->TestData->getFile(array('image-jpg.jpg' => 'ta.jpg'));
		$data = array(
					'Movie' => array('title' => 'Weekend', 'director' => 'Jean-Luc Godard'),
					'Attachment' => array('file' => $file, 'model' => 'Movie')
					);
		$Model->create();
		$this->assertTrue($Model->saveAll($data, array('validate' => 'first')));
		$file = $Model->Attachment->getLastTransferredFile();
		$this->assertTrue(file_exists($file));
		$result = $Model->find('first', array('conditions' => array('title' => 'Weekend')));
		$expected = array(
				'id' => 1,
				'model' => 'Movie',
				'foreign_key' => 4,
				'dirname' => null,
				'basename' => 'ta.jpg',
				'checksum' => '1920c29e7fbe4d1ad2f9173ef4591133',
				'group' => null,
				'alternative' => null,
				);
		$this->assertEqual($result['Attachment'], $expected);
		unlink($file);
	}
}
?>