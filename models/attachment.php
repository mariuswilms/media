<?php
/**
 * Attachment Model File
 *
 * Copyright (c) 2007-2009 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.models
 * @copyright  2007-2009 David Persson <davidpersson@gmx.de>
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
 */
	var $name = 'Attachment';
/**
 * Name of table to use
 *
 * @var mixed
 */
	var $useTable = 'attachments';
/**
 * actsAs property
 *
 * @var array
 */
	var $actsAs = array(
			'Media.Polymorphic' => array(
				'classField' => 'model',
				'foreignKey' => 'foreign_key',
				),
			'Media.Transfer' => array(
				'destinationFile' => ':MEDIA:transfer:DS::Medium.short::DS::Source.basename:',
				'createDirectory' => true,
				),
			'Media.Media' => array(
				'makeVersions'    => true,
				'metadataLevel'   => 2,
				'createDirectory' => true,
				),
			);
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
 * checkExtension and checkMimeType take both a blacklist and
 * a whitelist. If you are on windows make sure that you addtionally
 * specify the 'tmp' extension in case you are using a whitelist.
 *
 * @var array
 */
	var $validate = array(
			'file' => array(
				'resource'   => array('rule' => 'checkResource'),
				'access'     => array('rule' => 'checkAccess'),
				'location'   => array('rule' => array('checkLocation', array(':MEDIA:', '/tmp/'))),
				'permission' => array('rule' => array('checkPermission', '*')),
				'size'       => array('rule' => array('checkSize', '5M')),
				'pixels'     => array('rule' => array('checkPixels', '1600x1600')),
				'extension'  => array('rule' => array('checkExtension',
													array(
														'bin', 'class', 'dll', 'dms', 'exe', 'lha',
														'lzh', 'so', 'as', 'asp', 'sh', 'java', 'js',
														'lisp', 'lua', 'pl', 'pm', 'php', 'py', 'pyc',
														'vb', 'bas', 'jar',
														),
													'*'
													),
												),
				'mimeType'   => array('rule' => array('checkMimeType', false, '*')),
				),
			'alternative' => array(
				'rule'       => 'checkRepresent',
				'on'         => 'create',
				'required'   => false,
				'allowEmpty' => true,
				),
			);

/**
 * beforeMake Callback
 *
 * Called from within MediaBehavior::make
 *
 * $process an array with the following contents:
 *	overwrite - If the destination file should be overwritten if it exists
 *	directory - The destination directory (guranteed to exist)
 *  name - Medium name of $file (e.g. 'image')
 *	version - The version requested to be processed (e.g. 'xl')
 *	instructions - An array containing which names of methods to be called
 *
 * @param string $file Absolute path to the source file
 * @param array $options directory, version, name, instructions, overwrite
 * @access public
 * @return boolean True signals that the file has been processed, false signals that the behavior should process the file
 */
	function beforeMake($file, $process = array()) {
		return false;
	}
}
?>
