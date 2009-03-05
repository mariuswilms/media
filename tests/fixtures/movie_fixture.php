<?php
class MovieFixture extends CakeTestFixture {
	var $name = 'Movie';

	var $fields = array(
			'id'		=> array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary', 'extra' => 'auto_increment', 'length' => 10),
			'title'		=> array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 255),
			'director'	=> array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
			'indexes'	=> array('PRIMARY' => array('column' => 'id', 'unique' => 1))
			);

	var $records = array(
						array(
							'id'  => 1,
							'title'  => 'Frost/Nixon',
							'director'  => 'Ron Howard',
						),
						array(
							'id'  => 2,
							'title'  => 'Entre les murs',
							'director'  => 'Laurent Cantet',
						),
						array(
							'id'  => 3,
							'title'  => 'Revanche',
							'director'  => 'Goetz Spielmann',
						),
					);
}
?>