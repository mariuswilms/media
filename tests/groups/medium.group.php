<?php
class AllMediaGroupTest extends GroupTest {
	var $label = 'All media related (incl. adapters) test cases';

	function AllMediaGroupTest() {
		TestManager::addTestCasesFromDirectory($this, dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'vendors' . DS . 'media');
	}
}
?>