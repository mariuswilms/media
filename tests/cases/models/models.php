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
	var $beforeMakeArgs = array();

	function beforeMake() {
		$this->beforeMakeArgs[] = func_get_args();
		return false;
	}
}

class Pirate extends CakeTestModel {
	var $name = 'Pirate';
	var $useTable = 'pirates';
}
?>