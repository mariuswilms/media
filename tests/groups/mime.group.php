<?php
class AllMimeGroupTest extends GroupTest {
	var $label = 'All MIME related test cases';

	function AllMimeGroupTest() {
		TestManager::addTestFile($this, dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'vendors' . DS . 'mime_glob');
		TestManager::addTestFile($this, dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'vendors' . DS . 'mime_magic');
		TestManager::addTestFile($this, dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'vendors' . DS . 'mime_type');
	}
}
?>