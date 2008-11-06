<?php
class AttachmentFixture extends CakeTestFixture {
	var $name = 'Attachment';

	var $fields = array(
			'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
			'model' => array('type'=>'string', 'null' => false, 'length' => 20),
			'foreign_key' => array('type'=>'integer', 'null' => false, 'key' => 'index'),
			'dirname' => array('type'=>'string', 'null' => true, 'default' => NULL),
			'basename' => array('type'=>'string', 'null' => false),
			'checksum' => array('type'=>'string', 'null' => false),
			'group' => array('type'=>'string', 'null' => true, 'default' => NULL),
			'created' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
			'modified' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
			'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'foreign_key' => array('column' => array('foreign_key', 'model', 'basename'), 'unique' => 1))
			);
			
	var $records = array(
						array(
						'id'  => 1,
						'model'  => 'Ufo',
						'foreign_key'  => 1,
						'dirname'  => 'tmp',
						'basename'  => 'attachment.png',
						'checksum'  => null,
						'group'  => 'no-group',
						'created'  => '2008-03-25 01:01:46',
						'modified'  => '2008-03-25 01:01:46'
						),
						array(
						'id'  => 2,
						'model'  => 'Ufo',
						'foreign_key'  => 1,
						'dirname'  => 'tmp',
						'basename'  => 'attachment2.png',
						'checksum'  => null,
						'group'  => 'no-group',
						'created'  => '2008-03-25 01:01:46',
						'modified'  => '2008-03-25 01:01:46'
						),
						array(
						'id'  => 3,
						'model'  => 'Martian',
						'foreign_key'  => 1,
						'dirname'  => 'tmp',
						'basename'  => 'manifest.php',
						'checksum'  => null,
						'group'  => 'no-group',
						'created'  => '2008-03-25 01:01:46',
						'modified'  => '2008-03-25 01:01:46'
						),
						array(
						'id'  => 4,
						'model'  => 'Martian',
						'foreign_key'  => 2,
						'dirname'  => 'tmp',
						'basename'  => 'problems.txt',
						'checksum'  => null,
						'group'  => 'no-group',
						'created'  => '2008-03-25 01:01:46',
						'modified'  => '2008-03-25 01:01:46'
						)							
						
												
					);
}
?>