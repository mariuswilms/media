<?php
class ActorFixture extends CakeTestFixture {
	public $name = 'Actor';

	public $fields = array(
			'id'		=> array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary', 'extra' => 'auto_increment', 'length' => 10),
			'movie_id'	=> array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10),
			'name'		=> array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
			'indexes'	=> array('PRIMARY' => array('column' => 'id', 'unique' => 1))
			);

	public $records = array(
						array(
							'id'  => 1,
							'movie_id' => 1,
							'name' => 'Michael Sheen',
						),
						array(
							'id'  => 2,
							'movie_id' => 1,
							'name' => 'Frank Langella',
						),
						array(
							'id'  => 3,
							'movie_id' => 2,
							'name' => 'Nassim Amrabt',
						),
					);
}
?>