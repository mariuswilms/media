<?php
class AllCompatibleGroupTest extends TestSuite {
	var $label = 'All test cases which can run in a sequence';

	function AllCompatibleGroupTest() {
		$cases = dirname(dirname(__FILE__)) . DS . 'cases' . DS;
		TestManager::addTestCasesFromDirectory($this, $cases . DS . 'models' . DS . 'behaviors');
		TestManager::addTestCasesFromDirectory($this, $cases . DS . 'libs');
		TestManager::addTestCasesFromDirectory($this, $cases . DS . 'views' . DS . 'helpers');
	}
}
?>
