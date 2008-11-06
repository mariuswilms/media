<?php
/**
 * Transfer Validation File
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
 * @category   validation
 * @package    attm
 * @subpackage attm.plugins.media.libs
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version    SVN: $Id$
 * @version    Release: $Version$
 * @link       http://cakeforge.org/projects/attm The attm Project
 * @since      media plugin 0.50
 * 
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 */
if (!class_exists('Validation')) {
	App::import('Core', 'Validation');
}
App::import('Vendor', 'Media.MediaValidation');
/**
 * Transfer Validation Class
 * 
 * @category   validation
 * @package    attm
 * @subpackage attm.plugins.media.libs
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://cakeforge.org/projects/attm The attm Project
 */
class TransferValidation extends MediaValidation {
/**
 * Checks if subject is transferable 
 *
 * @param mixed $check Path to file in local FS, URL or file-upload array
 * @return bool
 */
	function resource($check) {
		if (TransferValidation::fileUpload($check)
		 || TransferValidation::uploadedFile($check) /* This must appear above file */
		 || MediaValidation::file($check)
		 || TransferValidation::url($check)) {
		  	return !TransferValidation::blank($check);
		}
		
		return false;		
	}
/**
 * Checks if resource is not blank or empty
 *
 * @param mixed $check Array or string
 * @return bool
 */
	function blank($check) {
		if (empty($check)) {
			return true;
		}
		
		if (TransferValidation::fileUpload($check) && $check['error'] == UPLOAD_ERR_NO_FILE) {
			return true;
		}

		if (is_string($check) && Validation::blank($check)) {
			return true;
		}
			
		return false;
	}		
/**
 * Identifies a file upload array
 *
 * @param mixed $check
 * @return bool
 */
	function fileUpload($check) {
		if (!is_array($check)) {
			return false;
		}

		if (!array_key_exists('name',$check)
		 || !array_key_exists('type',$check)
		 || !array_key_exists('tmp_name',$check)
		 || !array_key_exists('error',$check)
		 || !array_key_exists('size',$check)) {
			return false;
		}

		return true;
	}	
/**
 * Checks if subject is an uploaded file
 *
 * @param mixed $check
 */
	function uploadedFile($check) {
		return MediaValidation::file($check) && is_uploaded_file($check); 
	}	
/**
 * Validates url
 *
 * @param string string to check
 * @param array options for allowing different url parts currently only scheme is supported
 */
	function url($check, $options = array()) {
		if (!is_string($check)) {
			return false;
		}
		if (isset($options['scheme'])) {
			if (!preg_match('/^(' . implode('|', (array) $options['scheme']) . ':)+/', $check)) {
				return false;
			}
		}
		return Validation::url($check);
	}
}
?>