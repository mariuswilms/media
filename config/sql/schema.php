<?php
/**
 * Media Plugin Schema File
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
 * @subpackage media.config.sql
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Media Plugin Schema Class
 *
 * @package    media
 * @subpackage media.config.sql
 */
class MediaPluginSchema extends CakeSchema {
	var $name = 'MediaPlugin';

	function before($event = array()) {
		return true;
	}

	function after($event = array()) {
	}

	var $attachments = array(
						'id'          => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary', 'extra' => 'auto_increment', 'length' => 10),
						'model'       => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
						'foreign_key' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10),
						'dirname'     => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 255),
						'basename'    => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
						'checksum'    => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
						'group'       => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 255),
						'alternative' => array('type' => 'string', 'null' => true, 'default' => NULL,'length' => 50),
						'created'     => array('type' => 'datetime', 'null' => true, 'default' => NULL),
						'modified'    => array('type' => 'datetime', 'null' => true, 'default' => NULL),
						'indexes'     => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
						);

	var $media = array(
			'id'          => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary', 'extra' => 'auto_increment', 'length' => 10),
			'dirname'     => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 255),
			'basename'    => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
			'checksum'    => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 255),
			'group'       => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 255),
			'alternative' => array('type' => 'string', 'null' => true, 'default' => NULL,'length' => 50),
			'created'     => array('type' => 'datetime', 'null' => true, 'default' => NULL),
			'modified'    => array('type' => 'datetime', 'null' => true, 'default' => NULL),
			'indexes'     => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
					);
}
?>