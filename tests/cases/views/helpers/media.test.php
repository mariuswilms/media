<?php
/**
 * Media Helper Test Case File
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
 * @subpackage media.tests.cases.views.helpers
 * @copyright  2007-2011 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */

if (!defined('MEDIA')) {
	define('MEDIA', TMP . 'tests' . DS);
} elseif (MEDIA != TMP . 'tests' . DS) {
	trigger_error('MEDIA constant already defined and not pointing to tests directory.', E_USER_ERROR);
}

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DS . 'config' . DS . 'core.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'fixtures' . DS . 'test_data.php';

App::import('Core', array('Helper', 'AppHelper', 'ClassRegistry'));
App::import('Helper', 'Media.Media');

/**
 * Mock Media Helper
 *
 * @package    media
 * @subpackage media.tests.cases.views.helpers
 */
class MockMediaHelper extends MediaHelper {

	function versions() {
		return $this->_versions;
	}

	function directories() {
		return $this->_directories;
	}
}

/**
 * Media Helper Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.views.helpers
 */
class MediaHelperTestCase extends CakeTestCase {

	function setUp() {
		$this->_config = Configure::read('Media');

		$this->TmpFolder = new Folder(TMP . 'tests' . DS, true);
		$this->TmpFolder->create($this->TmpFolder->pwd() . 'static');
		$this->TmpFolder->create($this->TmpFolder->pwd() . 'static' . DS . 'img');
		$this->TmpFolder->create($this->TmpFolder->pwd() . 'filter');
		$this->TmpFolder->create($this->TmpFolder->pwd() . 'filter' . DS . 's' . DS . 'static' . DS . 'img');
		$this->TmpFolder->create($this->TmpFolder->pwd() . 'transfer');
		$this->TmpFolder->create($this->TmpFolder->pwd() . 'transfer' . DS . 'img');
		$this->TmpFolder->create($this->TmpFolder->pwd() . 'filter' . DS . 's' . DS . 'transfer' . DS . 'img');
		$this->TmpFolder->create($this->TmpFolder->pwd() . 'theme');
		$this->TmpFolder->create($this->TmpFolder->pwd() . 'theme' . DS . 'blanko');
		$this->TmpFolder->create($this->TmpFolder->pwd() . 'theme' . DS . 'blanko' . DS . 'img' . DS);

		$this->TestData = new TestData();

		$this->file0 = $this->TestData->getFile(array(
			'image-png.png' => $this->TmpFolder->pwd() . 'static/img/image-png.png'));
		$this->file1 = $this->TestData->getFile(array(
			'image-png.png' => $this->TmpFolder->pwd() . 'filter/s/static/img/image-png.png'));
		$this->file2 = $this->TestData->getFile(array(
			'image-png.png' => $this->TmpFolder->pwd() . 'filter/s/static/img/dot.ted.name.png'));
		$this->file3 = $this->TestData->getFile(array(
			'image-png.png' => $this->TmpFolder->pwd() . 'transfer/img/image-png-x.png'));
		$this->file4 = $this->TestData->getFile(array(
			'image-png.png' => $this->TmpFolder->pwd() . 'filter/s/transfer/img/image-png-x.png'));
		$this->file5 = $this->TestData->getFile(array(
			'image-png.png' => $this->TmpFolder->pwd() . 'theme/blanko/img/image-blanko.png'));

		$settings = array(
			$this->TmpFolder->pwd() . 'static' . DS => 'media/static/',
			$this->TmpFolder->pwd() . 'filter' . DS => 'media/filter/',
			$this->TmpFolder->pwd() . 'transfer' . DS => false,
			$this->TmpFolder->pwd() . 'theme' . DS  => 'media/theme/'
		);
		$this->Helper = new MediaHelper($settings);
	}

	function tearDown() {
		Configure::write('Media', $this->_config);
		$this->TestData->flushFiles();
		$this->TmpFolder->delete();
		ClassRegistry::flush();
	}

	function testConstruct() {
		$settings = array(
			$this->TmpFolder->pwd() . 'static' . DS => 'media/static/',
			$this->TmpFolder->pwd() . 'theme' . DS  => 'media/theme/'
		);
		Configure::write('Media.filter', array(
			'image'	 => array('s' => array(), 'm' => array()),
			'video' => array('s' => array(), 'xl' => array())
		));
		$Helper = new MockMediaHelper($settings);
	}

	function testUrl() {
		$result = $this->Helper->url('img/image-png');
		$this->assertEqual($result, 'media/static/img/image-png.png');

		$result = $this->Helper->url('s/static/img/image-png');
		$this->assertEqual($result, 'media/filter/s/static/img/image-png.png');

		$result = $this->Helper->url('img/image-png-x');
		$this->assertNull($result);

		$result = $this->Helper->url('img/image-png-xyz');
		$this->assertNull($result);

		$result = $this->Helper->url('s/transfer/img/image-png-x');
		$this->assertEqual($result, 'media/filter/s/transfer/img/image-png-x.png');

		$result = $this->Helper->url($this->TmpFolder->pwd() . 'filter/s/transfer/img/image-png-x.png');
		$this->assertEqual($result, 'media/filter/s/transfer/img/image-png-x.png');
	}

	function testWebroot() {
		$result = $this->Helper->webroot('img/image-png');
		$this->assertEqual($result, 'media/static/img/image-png.png');

		$result = $this->Helper->webroot('s/static/img/image-png');
		$this->assertEqual($result, 'media/filter/s/static/img/image-png.png');

		$result = $this->Helper->webroot('img/image-png-x');
		$this->assertNull($result);

		$result = $this->Helper->webroot('img/image-png-xyz');
		$this->assertNull($result);

		$result = $this->Helper->webroot('s/transfer/img/image-png-x');
		$this->assertEqual($result, 'media/filter/s/transfer/img/image-png-x.png');

		$result = $this->Helper->webroot($this->TmpFolder->pwd() . 'filter/s/transfer/img/image-png-x.png');
		$this->assertEqual($result, 'media/filter/s/transfer/img/image-png-x.png');
	}

	function testFile() {
		$result = $this->Helper->file('static/img/not-existant.jpg');
		$this->assertFalse($result);

		$result = $this->Helper->file('img/image-png');
		$this->assertEqual($result, $this->file0);

		$result = $this->Helper->file('s/static/img/image-png');
		$this->assertEqual($result, $this->file1);

		$result = $this->Helper->file('s/static/img/dot.ted.name');
		$this->assertEqual($result, $this->file2);

		$result = $this->Helper->file('img/image-png-x');
		$this->assertEqual($result, $this->file3);

		$result = $this->Helper->file('s/transfer/img/image-png-x');
		$this->assertEqual($result, $this->file4);

		$result = $this->Helper->file($this->TmpFolder->pwd() . 'filter/s/transfer/img/image-png-x.png');
		$this->assertEqual($result, $this->file4);

		$result = $this->Helper->file('blanko/img/image-blanko');
		$this->assertEqual($result, $this->file5);
	}

	function testName() {
		$this->assertEqual($this->Helper->name('img/image-png.png'), 'image');
		$this->assertNull($this->Helper->name('static/img/not-existant.jpg'));
	}

	function testMimeType() {
		$this->assertEqual($this->Helper->mimeType('img/image-png.png'), 'image/png');
		$this->assertNull($this->Helper->mimeType('static/img/not-existant.jpg'));
	}

	function testSize() {
		$this->assertEqual($this->Helper->size('img/image-png.png'), 10142);
		$this->assertNull($this->Helper->size('static/img/not-existant.jpg'));
	}
}

?>
