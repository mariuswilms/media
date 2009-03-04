<?php
/**
 * Medium Helper Test Case File
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
 * @subpackage media.tests.cases.views.helpers
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
if (!defined('MEDIA')) {
	define('MEDIA', TMP . 'media' . DS);
}
App::import('Core', array('Helper', 'AppHelper', 'ClassRegistry', 'Controller', 'Model'));
App::import('Helper', 'Media.Medium');
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Medium Helper Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.views.helpers
 */
class TheMediumTestController extends Controller {
/**
 * name property
 *
 * @var string 'TheTest'
 * @access public
 */
	var $name = 'TheTest';
/**
 * uses property
 *
 * @var mixed null
 * @access public
 */
	var $uses = null;
}
class MediumHelperTestCase extends CakeTestCase {
	function setUp() {
		Cache::clear(false, 'default');

		$this->Helper =& new MediumHelper();
		$View =& new View(new TheMediumTestController());
		ClassRegistry::addObject('view', $View);

		$this->TmpFolder = new Folder(MEDIA, true);
		$this->TmpFolder->create($this->TmpFolder->pwd().'static');
		$this->TmpFolder->create($this->TmpFolder->pwd().'static/img');
		$this->TmpFolder->create($this->TmpFolder->pwd().'filter');
		$this->TmpFolder->create($this->TmpFolder->pwd().'filter/s/static/img');
		$this->TmpFolder->create($this->TmpFolder->pwd().'transfer');

		$this->TestData = new TestData();
		$this->file0 = $this->TestData->getFile(array('image-png.png' => $this->TmpFolder->pwd() . 'static/img/image-png.png'));
		$this->file1 = $this->TestData->getFile(array('image-png.png' => $this->TmpFolder->pwd() . 'filter/s/static/img/image-png.png'));
		$this->file2 = $this->TestData->getFile(array('image-png.png' => $this->TmpFolder->pwd() . 'filter/s/static/img/dot.ted.name.png'));
	}

	function skip() {
		$this->skipIf(defined('MEDIA') && MEDIA !== TMP . 'media' . DS, 'MEDIA constant already defined');
	}

	function tearDown() {
		$this->TestData->flushFiles();
		$this->TmpFolder->delete();
		ClassRegistry::flush();
		Cache::clear(false, 'default');
	}

	function testFile() {
		$this->expectError();
		$this->Helper->file('image-png');

		$result = $this->Helper->file('static/img/not-existant.jpg');
		$this->assertFalse($result);

		$result = $this->Helper->file('img/image-png');
		$this->assertEqual($result, $this->file0);

		$result = $this->Helper->file('static/img/image-png');
		$this->assertEqual($result, $this->file0);

		$result = $this->Helper->file('static/img/image-png.png');
		$this->assertEqual($result, $this->file0);

		$result = $this->Helper->file('s/img/image-png');
		$this->assertEqual($result, $this->file1);

		$result = $this->Helper->file('filter/s/img/image-png');
		$this->assertEqual($result, $this->file1);

		$result = $this->Helper->file('filter/s/static/img/image-png');
		$this->assertEqual($result, $this->file1);

		$result = $this->Helper->file('filter/s/img/dot.ted.name');
		$this->assertEqual($result, $this->file2);
	}
}
?>