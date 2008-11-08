<?php
/**
 * Attachment Model File
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
 * @category   media handling
 * @package    attm
 * @subpackage attm.plugins.attachments.models
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
 * Attachment Model Class
 *
 * @category   media handling
 * @package    attm
 * @subpackage attm.plugins.attachments.models
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://cakeforge.org/projects/attm The attm Project
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