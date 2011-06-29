<?php
class AllValidationGroupTest extends TestSuite {
	public $label = 'All validation related test cases';

	public function AllValidationGroupTest() {
		TestManager::addTestFile(
			$this,
			dirname(dirname(__FILE__)) . DS . 'cases' . DS . 'libs' . DS . 'media_validation'
		);
		TestManager::addTestFile(
			$this,
			dirname(dirname(__FILE__)) . DS . 'cases' . DS . 'libs' . DS . 'transfer_validation'
		);
	}
}
?>
