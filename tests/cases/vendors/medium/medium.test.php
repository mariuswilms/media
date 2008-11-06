<?php
App::import('Vendor', 'Media.Medium');
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';

class BananaMediumAdapter extends MediumAdapter {
}

class CherryMediumAdapter extends MediumAdapter {
}

class SweetMedium extends Medium {
	var $adapters = array('Banana', 'Cherry');
}

class MediumTest extends CakeTestCase {
	function start() {
		parent::start();
		$this->TestData = new MediumTestData();
	}
	
	function end() {
		parent::end();
		$this->TestData->flushFiles();
	}	
	
	function testMediumFactory() {
		$file = $this->TestData->getFile('image-jpg.jpg');
		$result = Medium::factory($file);
		$this->assertIsA($result,'ImageMedium');
		
		$file = $this->TestData->getFile('image-png.png');
		$result = Medium::factory($file);
		$this->assertIsA($result,'ImageMedium');
				
		$file = $this->TestData->getFile('image-gif.gif');
		$result = Medium::factory($file);
		$this->assertIsA($result,'ImageMedium');

		$file = $this->TestData->getFile('text-plain.txt');
		$result = Medium::factory($file);
		$this->assertIsA($result,'TextMedium');

		$file = $this->TestData->getFile('application-pdf.pdf');
		$result = Medium::factory($file);
		$this->assertIsA($result,'DocumentMedium');
	}
	
	function testMediumNameAndShort() {
		$file = $this->TestData->getFile('image-jpg.jpg');
		$result = Medium::factory($file);
		$this->assertEqual($result->name,'Image');
		$this->assertEqual($result->short,'img');
		
		$file = $this->TestData->getFile('image-png.png');
		$result = Medium::factory($file);
		$this->assertEqual($result->name,'Image');
		$this->assertEqual($result->short,'img');
				
		$file = $this->TestData->getFile('image-gif.gif');
		$result = Medium::factory($file);
		$this->assertEqual($result->name,'Image');
		$this->assertEqual($result->short,'img');
		
		$file = $this->TestData->getFile('text-plain.txt');
		$result = Medium::factory($file);
		$this->assertEqual($result->name,'Text');
		$this->assertEqual($result->short,'txt');
		
		$file = $this->TestData->getFile('application-pdf.pdf');
		$result = Medium::factory($file);
		$this->assertEqual($result->name,'Document');
		$this->assertEqual($result->short,'doc');
	}
	
	function testMediumAdapterCollection() {
		
//		$Collection = new MediumAdapterCollection();
//		$Collection 
		
	}
}
?>