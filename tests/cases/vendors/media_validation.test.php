<?php
App::import('Vendor','Media.MediaValidation');
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';

class MediaValidationTest extends CakeTestCase {
	function start() {
		parent::start();
		$this->TestData = new MediumTestData();
	}
	
	function end() {
		parent::end();
		$this->TestData->flushFiles();
	}	
	
	function testMimeType() {
		$check = 'image/png';
		$result = MediaValidation::mimeType($check);
		$this->assertTrue($result);
		
		$check = 'text/x-php';
		$result = MediaValidation::mimeType($check);
		$this->assertFalse($result);
		
		$check = 'image/png';
		$result = MediaValidation::mimeType($check,array('image/png'));
		$this->assertFalse($result);

		$check = 'image/png';
		$result = MediaValidation::mimeType($check,array('image/png'),array('image/png'));
		$this->assertFalse($result);
		
		$check = 'in/val/id';
		$result = MediaValidation::mimeType($check);
		$this->assertFalse($result);
		
		$check = '';
		$result = MediaValidation::mimeType($check);
		$this->assertFalse($result);		
	}
	
	function testExtension() {
		$check = 'png';
		$result = MediaValidation::extension($check);
		$this->assertTrue($result);
		
		$check = 'php';
		$result = MediaValidation::extension($check);
		$this->assertFalse($result);
		
		$check = 'png';
		$result = MediaValidation::extension($check,array('png'));
		$this->assertFalse($result);

		$check = 'png';
		$result = MediaValidation::extension($check,array('png'),array('png'));
		$this->assertFalse($result);
		
		/* must fail cannot test for invalid extensions */
		$check = 'in.valid';
		$result = MediaValidation::extension($check);
		$this->assertFalse($result);
		
		$check = '';
		$result = MediaValidation::extension($check);
		$this->assertFalse($result);		
	}	
	
	function testSize() {
		$result = MediaValidation::size('1M','2M');
		$this->assertTrue($result);
		
		$result = MediaValidation::size('1K','2M');
		$this->assertTrue($result);
		
		$result = MediaValidation::size('1M','1K');
		$this->assertFalse($result);
		
		$result = MediaValidation::size('1048576','2M');
		$this->assertTrue($result);

		$result = MediaValidation::size(1048576,'2M');
		$this->assertTrue($result);		

		$result = MediaValidation::size('1M','1M');
		$this->assertTrue($result);
		
		$result = MediaValidation::size('1048576','1M');
		$this->assertTrue($result);
		
		$result = MediaValidation::size(1048576,10);
		$this->assertFalse($result);
		
		$result = MediaValidation::size('','2M');
		$this->assertFalse($result);
		
	}
	
	function testLocation() {
		$result = MediaValidation::location(TMP);
		$this->assertFalse($result);
		
		$result = MediaValidation::location(TMP,true);
		$this->assertTrue($result);
		
		$result = MediaValidation::location(TMP,array(DS));
		$this->assertTrue($result);

		$result = MediaValidation::location(TMP.DS.DS.DS,array(DS));
		$this->assertTrue($result);

		$result = MediaValidation::location(TMP.DS.'file.png',array(DS));
		$this->assertTrue($result);
		
		$result = MediaValidation::location(TMP,array(TMP.'subdir'));
		$this->assertFalse($result);
		
		$result = MediaValidation::location('http://cakeforge.org',true);
		$this->assertTrue($result);

		$result = MediaValidation::location('http://cakeforge.org');
		$this->assertFalse($result);		
		
		$result = MediaValidation::location('http://cakeforge.org',array(TMP));
		$this->assertFalse($result);	

		$result = MediaValidation::location('http://cakeforge.org',array(TMP,'http://'));
		$this->assertTrue($result);	
		
		$result = MediaValidation::location('http://cakeforge.org','http://rosa');
		$this->assertFalse($result);			

		$result = MediaValidation::location('http://cakeforge.org','http://cakeforge.org');
		$this->assertTrue($result);			

		$result = MediaValidation::location('http://cakeforge.org/bla/?x=?$§c $%.org','http://cakeforge.org');
		$this->assertFalse($result);			

		$result = MediaValidation::location('http://cakeforge.org/bla','http://cakeforge.org');
		$this->assertTrue($result);	

		$result = MediaValidation::location('http://cakeforge.org/bla?x=do','http://cakeforge.org');
		$this->assertTrue($result);				
	}
	
	function testAccess() {
		$result = MediaValidation::access('0444','r');
		$this->assertTrue($result);
		
		$result = MediaValidation::access(0444,'r');
		$this->assertTrue($result);		

		$result = MediaValidation::access('0004','r');
		$this->assertTrue($result);

		$result = MediaValidation::access('0111','r');
		$this->assertFalse($result);		
		
		$result = MediaValidation::access('0222','w');
		$this->assertTrue($result);

		$result = MediaValidation::access('0002','w');
		$this->assertTrue($result);
		
		$result = MediaValidation::access('0111','w');
		$this->assertFalse($result);		
	}
	
	function testPermission() {
		$result = MediaValidation::permission('0111');
		$this->assertFalse($result);

		$result = MediaValidation::permission(0111);
		$this->assertFalse($result);		
		
		$result = MediaValidation::permission('0111','-x');
		$this->assertFalse($result);		

		$result = MediaValidation::permission('0111','-x');
		$this->assertFalse($result);		
		
		$result = MediaValidation::permission('0000','-x');
		$this->assertTrue($result);			
		
		$result = MediaValidation::permission('0666','-x');
		$this->assertTrue($result);			
	}	
	
	function testFile()
	{
		$file = __FILE__;
		$result = MediaValidation::file($file);
		$this->assertTrue($result);
		
		$file = $this->TestData->getFile('image-jpg.jpg');
		$result = MediaValidation::file($file,false);
		$this->assertTrue($result);

		$file = DS.'i-am-not-a-file.png';
		$result = MediaValidation::file($file);
		$this->assertFalse($result);

		$file = DS;
		$result = MediaValidation::file($file);
		$this->assertFalse($result);	

		$file = DS;
		$result = MediaValidation::file($file,false);
		$this->assertTrue($result);		
	}	
	
	function testFolder() {
		$file = dirname(__FILE__);
		$result = MediaValidation::folder($file);
		$this->assertTrue($result);
		
		$file = $this->TestData->getFile('image-jpg.jpg');
		$result = MediaValidation::folder($file,false);
		$this->assertTrue($result);

		$file = DS.'i-am-not-a-file.png';
		$result = MediaValidation::folder($file);
		$this->assertFalse($result);

		$file = DS;
		$result = MediaValidation::folder($file);
		$this->assertTrue($result);	

		$file = DS;
		$result = MediaValidation::folder($file,false);
		$this->assertTrue($result);		

		$file = DS.DS.DS.DS;
		$result = MediaValidation::folder($file,false);
		$this->assertTrue($result);			
	}	
}
?>