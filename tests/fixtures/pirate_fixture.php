<?php
class PirateFixture extends CakeTestFixture {
	public $name = 'Pirate';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary', 'extra' => 'auto_increment', 'length' => 10),
		'name'	=> array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 255),
		'group'	=> array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
		'model'	=> array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
	);

	public $records = array(
		array(
			'id'  => 1,
			'name'  => 'George Lowther',
			'group'  => 'atlantic',
			'model' => 'unknown'
	));
}
?>