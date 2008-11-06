<?php
class AllMimeGroupTest extends GroupTest {
	var $label = 'All mime type related';

	function AllMimeGroupTest() {
		TestManager::addTestFile($this, dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'vendors' . DS . 'mime_glob');
		TestManager::addTestFile($this, dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'vendors' . DS . 'mime_magic');
		TestManager::addTestFile($this, dirname(__FILE__) . DS . '..' . DS . 'cases' . DS . 'vendors' . DS . 'mime_type');
	}
}
?>