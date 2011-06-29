<?php
class Movie extends CakeTestModel {
	public $name = 'Movie';
	public $useTable = 'movies';
	public $hasMany = array('Actor');
}

class Actor extends CakeTestModel {
	public $name = 'Actor';
	public $useTable = 'actors';
	public $belongsTo = array('Movie');
}

class Unicorn extends CakeTestModel {
	public $name = 'Unicorn';
	public $useTable = false;
	public $makeVersionArgs = array();
	public $returnMakeVersion = true;

	public function makeVersion() {
		$this->makeVersionArgs[] = func_get_args();
		return $this->returnMakeVersion;
	}
}

class Pirate extends CakeTestModel {
	public $name = 'Pirate';
	public $useTable = 'pirates';
}
?>