<?php
class Movie extends CakeTestModel {
	public $hasMany = array('Actor');
}

class Actor extends CakeTestModel {
	public $belongsTo = array('Movie');
}

class Unicorn extends CakeTestModel {
	public $useTable = false;
	public $makeVersionArgs = array();
	public $returnMakeVersion = true;

	public function makeVersion() {
		$this->makeVersionArgs[] = func_get_args();
		return $this->returnMakeVersion;
	}
}

class Pirate extends CakeTestModel {
}