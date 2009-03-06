<?php
class AllModelGroupTest extends GroupTest {
	var $label = 'All model and behavior related test cases';

	function AllModelGroupTest() {
		TestManager::addTestCasesFromDirectory($this,dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'models');
	}
}
?>