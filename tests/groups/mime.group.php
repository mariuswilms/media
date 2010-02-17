<?php
class AllMimeGroupTest extends TestSuite {
	var $label = 'All MIME related test cases';

	function AllMimeGroupTest() {
		TestManager::addTestFile(
			$this, dirname(dirname(__FILE__)) . DS . 'cases' . DS . 'vendors' . DS . 'mime_glob'
		);
		TestManager::addTestFile(
			$this, dirname(dirname(__FILE__)) . DS . 'cases' . DS . 'vendors' . DS . 'mime_magic'
		);
		TestManager::addTestFile(
			$this, dirname(dirname(__FILE__)) . DS . 'cases' . DS . 'vendors' . DS . 'mime_type'
		);
	}
}
?>