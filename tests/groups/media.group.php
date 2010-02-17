<?php
class AllMediaGroupTest extends TestSuite {
	var $label = 'All media related (incl. adapters) test cases';

	function AllMediaGroupTest() {
		TestManager::addTestCasesFromDirectory(
			$this, dirname(dirname(__FILE__)) . DS . 'cases' . DS . 'vendors' . DS . 'media'
		);
	}
}
?>