<?php
class Movie extends AppModel {

	var $name = 'Movie';

 	var $hasMany = array(
 						'Poster' => array(
										'className' => 'Attachments.Attachment',
										'foreignKey' => 'foreign_key', // required
										'conditions' => array('model' => 'Movie','group' => 'poster'), // required
 										'dependent' => true,
										),
 						'Photo' => array(
										'className' => 'Attachments.Attachment',
										'foreignKey' => 'foreign_key', // required
										'conditions' => array('model' => 'Movie','group' => 'photo'), // required
										'dependent' => true,
										),
						'Trailer' => array(
										'className' => 'Attachments.Attachment',
										'foreignKey' => 'foreign_key', // required
										'conditions' => array('model' => 'Movie','group' => 'trailer'), // required
										'dependent' => true,
										)
										
					 	);

}
?>