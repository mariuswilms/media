<?php
class SongFixture extends CakeTestFixture {
	var $name = 'Song';

	var $fields = array(
			'id'          => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary', 'extra' => 'auto_increment', 'length' => 10),
			'dirname'     => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 255),
			'basename'    => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
			'checksum'    => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
			'indexes'     => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
			);
			
	var $records = array(
						array(
						'id'  => 1,
						'dirname'  => 'static/img',
						'basename'  => 'image-png.png',
						'checksum'  => '7f9af648b511f2c83b1744f42254983f',
						),
						array(
						'id'  => 2,
						'dirname'  => 'static/img',
						'basename'  => 'image-jpg.jpg',
						'checksum'  => '1920c29e7fbe4d1ad2f9173ef4591133',
						),
						array(
						'id'  => 3,
						'dirname'  => 'static/txt',
						'basename'  => 'text-plain.txt',
						'checksum'  => '3f3f58abd4209b4c87be51018fe5a0c6',
						),
						array(
						'id'  => 4,
						'dirname'  => 'static/txt',
						'basename'  => 'not-existent.txt',
						'checksum'  => null,
						)							
					);
}
?>