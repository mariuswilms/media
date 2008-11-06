<?php
/**
 * Pear Mp3 Medium Adapter File
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
 * @subpackage attm.plugins.media.libs.medium.adapter
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
/**
 * Pear Mp3 Medium Adapter Class
 * 
 * @category   media handling
 * @package    attm
 * @subpackage attm.plugins.media.libs.medium.adapter
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://cakeforge.org/projects/attm The attm Project
 * @link       http://pear.php.net/package/MP3_Id
 */
class PearMp3MediumAdapter extends MediumAdapter {
	var $require = array(
							'mimeTypes' => array('audio/mpeg'),
							'imports' => array(array('type' => 'Vendor','name'=> 'MP3_Id','file' => 'MP3/Id.php')),
							);	

	function initialize(&$Medium) {
		if (isset($Medium->objects['MP3_Id'])) {
			return true;
		}
		
		if (!isset($Medium->file)) {
			return false;
		}
		
		$Object = new MP3_Id();
		
		if(!$Object->read($Medium->file) || !$Object->study()) {
			return false;
		}
		
		$Medium->objects['MP3_Id'] =& $Object;
		
		return true;
	}
	
	function artist(&$Medium) {
		return $Medium->objects['MP3_Id']->getTag('artists');
	}

	function title(&$Medium) {
		return $Medium->objects['MP3_Id']->getTag('name');
	}
	
	function album(&$Medium) {
		return $Medium->objects['MP3_Id']->getTag('album');
	}
	
	function year(&$Medium) {
		return $Medium->objects['MP3_Id']->getTag('year');
	}
	
	function duration(&$Medium) {
		return $Medium->objects['MP3_Id']->getTag('lengths');
	}
	
	function track(&$Medium) {
		return $Medium->objects['MP3_Id']->getTag('track');
	}	

	function samplingRate(&$Medium) {
		return $Medium->objects['MP3_Id']->getTag('frequency');
	}	
}
?>