<?php 
/**
 * Schema File
 * 
 * Copyright (c) $CopyrightYear$ David Persson
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE
 * 
 * PHP version $PHPVersion$
 * CakePHP version $CakePHPVersion$
 * 
 * @category   database
 * @package    attm
 * @subpackage attm.plugins.attachments.config.sql
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version    SVN: $Id$
 * @version    Release: $Version$
 * @link       http://cakeforge.org/projects/attm The attm Project
 * @since      attachments plugin 0.50
 * 
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Schema Class
 * 
 * @category   database
 * @package    attm
 * @subpackage attm.plugins.attachments.config.sql
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://cakeforge.org/projects/attm The attm Project
 */
class AttachmentsPluginSchema extends CakeSchema {
	/**
	 * Enter description here...
	 *
	 * @var   string
	 * @since 0.40
	 */
	var $name = 'AttachmentsPlugin';

	/**
	 * Enter description here...
	 *
	 * @var   array
	 * @since 0.40
	 */
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


	/**
	 * Enter description here...
	 *
	 * @param unknown_type $event
	 * @return unknown
	 */
	function before($event = array()) {
		return true;
	}


	/**
	 * Enter description here...
	 *
	 * @param unknown_type $event
	 */
	function after($event = array()) {
	}


}
?>