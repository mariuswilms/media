<?php 
class AttachmentsPluginComplexExampleSchema extends CakeSchema {
	var $name = 'AttachmentsPluginComplexExample';

	function before($event = array()) {
		return true;
	}

	function after($event = array()) {
	}

	var $movies = array(
			'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 100, 'key' => 'primary', 'extra' => 'auto_increment'),
			'created' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
			'modified' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
			'title' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 200),
			'director' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 250),
			'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
		);
}
?>