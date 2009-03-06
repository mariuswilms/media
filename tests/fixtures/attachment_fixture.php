<?php
class AttachmentFixture extends CakeTestFixture {
	var $name = 'Attachment';

	var $fields = array(
			'id'          => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary', 'extra' => 'auto_increment', 'length' => 10),
			'model'       => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
			'foreign_key' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10),
			'dirname'     => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 255),
			'basename'    => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
			'checksum'    => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
			'group'       => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 255),
			'alternative' => array('type' => 'string', 'null' => true, 'default' => NULL,'length' => 50),
			'indexes'     => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
			);

	var $records = array();
}
