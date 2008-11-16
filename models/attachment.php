<?php
/**
 * Attachment Model File
 *
 * Copyright (c) 2007-2008 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.models
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2008 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Attachment Model Class
 *
 * @package    media
 * @subpackage media.models
 */
class Attachment extends MediaAppModel {
/**
 * Name of model
 *
 * @var   string
 */
	var $name = 'Attachment';
/**
 * Name of table to use
 *
 * @var   mixed
 */
	var $useTable = 'attachments';
/**
 * actsAs property
 *
 * @var   array
 */
	var $actsAs = array(
	    'Media.Polymorphic' => array(
									  'classField' => 'model',
									  'foreignKey' => 'foreign_key',
									 ),
	    'Media.Transfer'		  => array(
									  'destinationFile' => ':MEDIA:transfer:DS::Medium.short::DS::Source.basename:',
									  'createDirectory' => true,
									 ),
	    'Media.Media'			  => array(
									  'makeVersions'    => true,
									  'metadataLevel'   => 2,
									  'createDirectory' => true,
									 ),
				  );
/**
 * Validation
 *
 * @var   array
 */
	var $validate = array(
		 'file'		=> array(
		   /* @see TransferBehavior::checkResource */
		   'resource'   => array(
					'rule' => 'checkResource',
						   ),
		   /* @see TransferBehavior::checkAccess */
		   'access'     => array(
					'rule' => 'checkAccess',
						   ),
		   /* @see TransferBehavior::checkLocation */
		   'location'   => array(
					'rule' => array('checkLocation', array(':MEDIA:', '/tmp/')),
						   ),
		   /* @see TransferBehavior::checkPermission */
		   'permission' => array(
					'rule' => array('checkPermission', '*'),
						   ),
		   /* @see TransferBehavior::checkSize */
		   'size'       => array(
					'rule' => array('checkSize', '5M'),
					 	   ),
		   /* @see TransferBehavior::checkPixels */
		   'pixels'     => array(
					'rule' => array('checkPixels', '1600x1600'),
					 	   ),
		   /* @see TransferBehavior::checkExtension */
		   'extension'  => array(
					'rule' => array(
					           'checkExtension',
			 				   array(
								 'bin', 'class', 'dll', 'dms', 'exe', 'lha',
								 'lzh', 'so', 'as', 'asp', 'sh', 'java', 'js',
								 'lisp', 'lua', 'pl', 'pm', 'php', 'py', 'pyc',
								 'vb', 'bas', 'jar',
							    ),
							   '*'
							  ),
						  ),
		   /* @see TransferBehavior::checkMimeType */
		   'mimeType'   => array(
					'rule' => array(
							   'checkMimeType',
						  		false,
								'*'
							  ),
						),
		), /* END of validations for file field */
	 	'alternative' => array(
				   /* @see MediaBehavior::checkRepresent */
				   'rule' 	    => 'checkRepresent',
            	   'on' 	    => 'create',
            	   'required'   => false,
	   			   'allowEmpty' => true,
			 	   ),
     ); /* END of validation var */
}
?>