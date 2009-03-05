<?php
class AllMediumGroupTest extends GroupTest {
	var $label = 'All medium related (incl. adapters) test cases';

	function AllMediumGroupTest() {
		TestManager::addTestCasesFromDirectory($this,dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'vendors' . DS . 'medium');
	}
}
?>