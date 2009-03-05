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
?>