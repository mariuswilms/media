<?php
class Movie extends CakeTestModel {
	var $name = 'Movie';
	var $useTable = 'movies';
	var $hasMany = array('Actor');
}

class Actor extends CakeTestModel {
	var $name = 'Actor';
	var $useTable = 'actors';
	var $belongsTo = array('Movie');
}

class Unicorn extends CakeTestModel {
	var $name = 'Unicorn';
	var $useTable = false;
	var $makeVersionArgs = array();
	var $returnMakeVersion = true;

	function makeVersion() {
		$this->makeVersionArgs[] = func_get_args();
		return $this->returnMakeVersion;
	}
}

class Pirate extends CakeTestModel {
	var $name = 'Pirate';
	var $useTable = 'pirates';
}
?>