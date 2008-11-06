<?php
class AllMediumGroupTest extends GroupTest {
	var $label = 'All medium plus adapters';

	function AllMediumGroupTest() {
		//TestManager::addTestFile($this, dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'vendors' . DS . 'medium' . DS );
		TestManager::addTestCasesFromDirectory($this,dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'vendors' . DS . 'medium');
	}
}
?>