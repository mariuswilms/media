<?php
class AllCompatibleGroupTest extends GroupTest {
	var $label = 'All test cases which can run in a sequence';

	function AllCompatibleGroupTest() {
		$cases = dirname(__FILE__) . DS . '..' . DS . 'cases' . DS;
		TestManager::addTestCasesFromDirectory($this, $cases . DS . 'models' . DS . 'behaviors');
		TestManager::addTestCasesFromDirectory($this, $cases . DS . 'vendors');
	}
}
?>