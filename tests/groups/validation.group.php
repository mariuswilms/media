<?php
class AllValidationGroupTest extends GroupTest {
	var $label = 'All validation related test cases';

	function AllValidationGroupTest() {
		TestManager::addTestFile($this, dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'vendors' . DS . 'media_validation');
		TestManager::addTestFile($this, dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'vendors' . DS . 'transfer_validation');
	}
}
?>