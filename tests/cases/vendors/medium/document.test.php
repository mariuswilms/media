<?php
App::import('Vendor','Media.DocumentMedium', array('file' => 'medium'.DS.'document.php'));
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';

class DocumentMediumTest extends CakeTestCase {
	function start() {
		parent::start();
		$this->TestData = new MediumTestData();
	}
	
	function end() {
		parent::end();
		$this->TestData->flushFiles();
	}	
	
	function testInformation() {
		$file = $this->TestData->getFile('application-pdf.pdf');
		$Medium = new DocumentMedium($file);
		$result = $Medium->width();
		$expecting = 595;
		$this->assertEqual($result,$expecting);

		$result = $Medium->height();
		$expecting = 842;
		$this->assertEqual($result,$expecting);
		
		$result = $Medium->quality();
		$expecting = 0;
		$this->assertEqual($result,$expecting);

		$result = $Medium->ratio();
		$expecting = '1:√2';
		$this->assertEqual($result,$expecting);
	}
}
?>