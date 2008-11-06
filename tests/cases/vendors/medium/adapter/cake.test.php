<?php
App::import('Vendor', 'Media.MimeType');
App::import('Vendor', 'Media.Medium');
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';

class TestMediumAdapter extends MediumAdapter 
{
	var $require = array(
							'mimeTypes' => array(
										'image/jpeg',
							),
							'extensions' => array('standard'),
							);	
}

class MediumAdapterTest extends CakeTestCase
{
	function start() {
		parent::start();
		$this->TestData = new MediumTestData();
	}
	
	function end() {
		parent::end();
		$this->TestData->flushFiles();
	}
}
?>