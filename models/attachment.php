<?php
/**
 * Attachment Model File
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.models
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Attachment Model Class
 *
 * A ready-to-use model combining multiple behaviors.
 *
 * @package    media
 * @subpackage media.models
 */
class Attachment extends MediaAppModel {
/**
 * Name of model
 *
 * @var string
 * @access public
 */
	var $name = 'Attachment';
/**
 * Name of table to use
 *
 * @var mixed
 * @access public
 */
	var $useTable = 'attachments';
/**
 * actsAs property
 *
 * @var array
 * @access public
 */
	var $actsAs = array(
		'Media.Polymorphic' => array(
			'classField' => 'model',
			'foreignKey' => 'foreign_key',
		),
		'Media.Transfer' => array(
			'trustClient'     => false,
			'destinationFile' => ':Medium.short::DS::Source.basename:',
			'baseDirectory'   => MEDIA_TRANSFER,
			'createDirectory' => true,
			'alternativeFile' => 100
		),
		'Media.Media' => array(
			'metadataLevel'   => 2,
			'makeVersions'    => true,
			'filterDirectory' => MEDIA_FILTER,
	));
/**
 * Validation rules for file and alternative fields
 *
 * For more information on the rules used here
 * see the source of TransferBehavior and MediaBehavior or
 * the test case for MediaValidation.
 *
 * If you experience problems with your model not validating,
 * try commenting the mimeType rule or providing less strict
 * settings for single rules.
 *
 * `checkExtension()` and `checkMimeType()` take both a blacklist and
 * a whitelist. If you are on windows make sure that you addtionally
 * specify the `'tmp'` extension in case you are using a whitelist.
 *
 * @var array
 * @access public
 */
	var $validate = array(
		'file' => array(
			'resource'   => array('rule' => 'checkResource'),
			'access'     => array('rule' => 'checkAccess'),
			'location'   => array('rule' => array('checkLocation', array(
				MEDIA_TRANSFER, '/tmp/'
			))),
			'permission' => array('rule' => array('checkPermission', '*')),
			'size'       => array('rule' => array('checkSize', '5M')),
			'pixels'     => array('rule' => array('checkPixels', '1600x1600')),
			'extension'  => array('rule' => array('checkExtension', false, array(
				'jpg', 'jpeg', 'png', 'tif', 'tiff', 'gif', 'pdf', 'tmp'
			))),
			'mimeType'   => array('rule' => array('checkMimeType', false, array(
				'image/jpeg', 'image/png', 'image/tiff', 'image/gif', 'application/pdf'
		)))),
		'alternative' => array(
			'rule'       => 'checkRepresent',
			'on'         => 'create',
			'required'   => false,
			'allowEmpty' => true,
		));
/**
 * beforeMake Callback
 *
 * Called from within `MediaBehavior::make()`
 *
 * $process an array with the following contents:
 *	overwrite - If the destination file should be overwritten if it exists
 *	directory - The destination directory (guranteed to exist)
 *  name - Medium name of $file (e.g. `'Image'`)
 *	version - The version requested to be processed (e.g. `'xl'`)
 *	instructions - An array containing which names of methods to be called
 *
 * @param string $file Absolute path to the source file
 * @param array $process directory, version, name, instructions, overwrite
 * @access public
 * @return boolean True signals that the file has been processed,
 * 	false or null signals that the behavior should process the file
 */
	function beforeMake($file, $process) {
	}
}
?>