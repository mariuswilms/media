<?php
/**
 * Base Behavior Test Case File
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
App::Import('Model', 'App');
require_once CORE_TEST_CASES . DS . 'libs' . DS . 'model' .DS . 'models.php';
require_once dirname(dirname(__FILE__)) . DS . 'models.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'fixtures' . DS . 'test_data.php';

if (!defined('MEDIA')) {
	define('MEDIA', TMP . 'tests' . DS);
} elseif (MEDIA != TMP . 'tests' . DS) {
	trigger_error('MEDIA constant already defined and not pointing to tests directory.', E_USER_ERROR);
}
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'config' . DS . 'core.php';

SimpleTest::ignore('BaseBehaviorTestCase');

/**
 * Base Behavior Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.models.behaviors
 */
class BaseBehaviorTestCase extends CakeTestCase {

	var $fixtures = array('plugin.media.song', 'core.image');

	var $_behaviorSettings = array();

	function start() {
		parent::start();

		if (in_array('plugin.media.song', $this->fixtures)) {
			$this->loadFixtures('Song');
		}
	}

	function setUp() {
		$this->Folder = new Folder(TMP . 'tests' . DS, true);
		$this->Folder->create($this->Folder->pwd() . 'static/img');
		$this->Folder->create($this->Folder->pwd() . 'static/doc');
		$this->Folder->create($this->Folder->pwd() . 'static/txt');
		$this->Folder->create($this->Folder->pwd() . 'filter');
		$this->Folder->create($this->Folder->pwd() . 'transfer');

		$this->Data = new TestData();
		$this->file0 = $this->Data->getFile(array(
			'image-png.png' => $this->Folder->pwd() . 'static/img/image-png.png'
		));
		$this->file1 = $this->Data->getFile(array(
			'image-jpg.jpg' => $this->Folder->pwd() . 'static/img/image-jpg.jpg'
		));
		$this->file2 = $this->Data->getFile(array(
			'text-plain.txt' => $this->Folder->pwd() . 'static/txt/text-plain.txt'
		));

		$this->_mediaConfig = Configure::read('Media');
	}

	function tearDown() {
		$this->Data->flushFiles();
		$this->Folder->delete();
		ClassRegistry::flush();
		Configure::write('Media', $this->_mediaConfig);
	}
}

?>
