<?php
class AllModelGroupTest extends TestSuite {
	var $label = 'All model and behavior related test cases';

	function AllModelGroupTest() {
		TestManager::addTestCasesFromDirectory(
			$this, dirname(dirname(__FILE__)) . DS . 'cases' . DS . 'models'
		);
	}
}
?>