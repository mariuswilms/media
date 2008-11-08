<?php
if (!defined('MEDIA')) {
	define('MEDIA', TMP . 'media' . DS);
}

App::import('Core', array('Helper', 'AppHelper', 'ClassRegistry', 'Controller', 'Model'));
App::import('Helper', 'Media.Medium');

require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';

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
	function start() {
		parent::start();

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

		$this->TestData = new MediumTestData();
		$this->file0 = $this->TestData->getFile(array('image-png.png' => $this->TmpFolder->pwd() . 'static/img/image-png.png'));
		$this->file1 = $this->TestData->getFile(array('image-png.png' => $this->TmpFolder->pwd() . 'filter/s/static/img/image-png.png'));
		$this->file2 = $this->TestData->getFile(array('image-png.png' => $this->TmpFolder->pwd() . 'filter/s/static/img/dot.ted.name.png'));
	}

	function end() {
		parent::end();
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