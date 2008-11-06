<?php
/**
 * Pear Ogg Video Medium Adapter File
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
 * Paer Ogg Video Medium Adapter Class
 * 
 * @category   media handling
 * @package    attm
 * @subpackage attm.plugins.media.libs.medium.adapter
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://cakeforge.org/projects/attm The attm Project
 * @link       http://pear.php.net/package/File_Ogg
 */
class PearOggVideoMediumAdapter extends MediumAdapter {
	var $require = array(
							'mimeTypes' => array('video/ogg'),
							'imports' => array(array('type' => 'Vendor','name'=> 'File_Ogg','file' => 'File/Ogg.php')),
							);			

	function initialize(&$Medium) {
		if (isset($Medium->objects['File_Ogg_Theora'])) {
			return true;
		}
		
		if (!isset($Medium->file)) {
			return false;
		}
		
		$Object = new File_Ogg($Medium->file);
			
		if (!$Object = $Object->getStream('file_ogg_theora')) {
			return false;
		}

		$Medium->objects['File_Ogg_Theora'] = $Object;
		
		return true;
	}
	
	function duration(&$Medium) {
		return $Medium->objects['File_Ogg_Theora']->getLength();
	}
	
	function width(&$Medium) {
		$header = $Medium->objects['File_Ogg_Theora']->getHeader();
		return $header['FMBW'];
	}
	
	function height(&$Medium) {
		$header = $Medium->objects['File_Ogg_Theora']->getHeader();
		return $header['FMBH'];
	}
	
	function frameRate(&$Medium) {
		$header = $Medium->objects['File_Ogg_Theora']->getHeader();
		return $header['FRD'] == 0 ? 0 : $header['FRN'] / $header['FRD'];
	}
	
	function quality(&$Medium) {
		//a quality between 1 (low) and 10 (high)
		$header = $Medium->objects['File_Ogg_Theora']->getHeader();
		$rate = $this->frameRate($Medium);
		$quality = $header['QUAL'];		
		
		if($quality > 9) {
			$quality = 5;
		} elseif($rate < 2) {
			$quality = 0;
		} else {
			/* Normalized between 1 and 5 where min = 8000 and max = 192.000 */
			$quality = round((($rate - 1) / (10 - 1)) * (5 * 1) + 1,0);
		}	
		
		return $quality;
	}
}
?>