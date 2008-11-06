<?php
/**
 * ImagickShell Medium Adapter File
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
 * @version    SVN: $Id: imagick_shell.php 181 2008-10-06 18:24:04Z davidpersson $
 * @version    Release: $Version$
 * @link       http://cakeforge.org/projects/attm The attm Project
 * @since      media plugin 0.50
 * 
 * @modifiedby   $LastChangedBy: davidpersson $
 * @lastmodified $Date: 2008-10-06 20:24:04 +0200 (Mo, 06 Okt 2008) $
 */
/**
 * ImagickShell Medium Adapter Class
 * 
 * @category   media handling
 * @package    attm
 * @subpackage attm.plugins.media.libs.medium.adapter
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://cakeforge.org/projects/attm The attm Project
 * @link 		http://www.imagemagick.org/
 */
class ImagickShellMediumAdapter extends MediumAdapter {
	var $require = array(
							'mimeTypes' => array( /* readable */
								'image/jpeg',
								'image/gif',
								'image/png',
								'image/tiff',
								'image/wpg',
								'image/xbm',
								'image/xcf',
								'image/wbmp',
								'image/ms-bmp',
								'image/pcx',
								'application/pdf',
								'image/quicktime',
								'image/svg',
								'image/xpm',
								'image/ico',
								'image/psd',
								),
							 'commands' => array('convert', 'identify'),
							);
	
	var $_formatMap = array( /* writable */
								'image/jpeg' => 'jpeg',
								'image/gif' => 'gif',
								'image/png' => 'png',
								'image/tiff' => 'tiff',
								'image/wbmp' => 'wbmp',
								'image/ms-bmp' => 'bmp',
								'image/pcx' => 'pcx',
								'image/ico' => 'ico',
								'image/xbm' => 'xbm',
								'image/psd' => 'psd',
						);
	
	var $_temporaryFormat = 'png';
	
	function initialize(&$Medium) {
		if (!isset($Medium->files['temporary'])) {
			if (!isset($Medium->file)) {
				return false;
			}
			$Medium->files['temporary'] = TMP . uniqid('medium_');
		}
		
		return $this->_execute(':command: :source: :format:::destination:',
								array(
									  'command'     => 'convert',
									  'source'      => $Medium->file,
									  'destination' => $Medium->files['temporary'],
									  'format'      => $this->_temporaryFormat, 
									 )
								 );
	}
	
	function store(&$Medium, $file) {
		return $this->_execute(':command: :sourceFormat:::source: :format:::destination:',
								array(
									  'command'     => 'convert',
									  'source'      => $Medium->files['temporary'],
									  'sourceFormat' => $this->_temporaryFormat,
									  'destination' => $file,
									  'format'      => $this->_formatMap[$Medium->mimeType], 
									 )
								 );
		
	}
	
	function convert(&$Medium, $mimeType) {
		if (!isset($this->_formatMap[$mimeType])) {
			return false;
		}
		
		$Medium->mimeType = $mimeType;

		if ($Medium->name === 'Document') { // application/pdf -> image
			$this->store($Medium, $Medium->files['temporary']);

			/* Unset files to prevent too early deletion by $Medium */
			$temporary = $Medium->files['temporary'];
			unset($Medium->files);
			
			return Medium::factory(array('temporary' => $temporary), $mimeType);
		} 
		
		return true;
	}
	
	function crop(&$Medium, $left, $top, $width, $height) {
		return $this->_execute(':command: -crop :width:x:height:+:left:+:top: :source: :destination:',
								array(
									  'command'     => 'convert',
									  'width'       => intval($width),
									  'height'      => intval($height),
									  'left'		=> intval($left),
									  'top'			=> intval($top),
									  'source'      => $Medium->files['temporary'],
									  'destination' => $Medium->files['temporary'],
									 )
								 );
	}
	
	function resize(&$Medium, $width, $height) { 
		return $this->_execute(':command: -geometry :width:x:height:! :source: :destination:',
								array(
									  'command'     => 'convert',
									  'width'       => intval($width),
									  'height'      => intval($height),
									  'source'      => $Medium->files['temporary'],
									  'destination' => $Medium->files['temporary'],
									 )
								 );
	}
	
	function cropAndResize(&$Medium, $cropLeft, $cropTop, $cropWidth, $cropHeight, $resizeWidth, $resizeHeight) {
		return 	$this->crop($Medium, $cropLeft, $cropTop, $cropWidth, $cropHeight) 
				&& $this->resize($Medium, $resizeWidth, $resizeHeight);
				
		/* This is faster but broken: convert: geometry does not contain image `xxxx.jpg'.
		return $this->_execute(':command: -crop :cropWidth:x:cropHeight:+:cropLeft:+:cropTop: -geometry :resizeWidth:x:resizeHeight:! :source: :destination:',
								array(
									  'command'      => 'convert',
									  'cropLeft'     => intval($cropLeft),
									  'cropTop'      => intval($cropTop),
									  'cropWidth'    => intval($cropWidth),
									  'cropHeight'   => intval($cropHeight),
									  'resizeWidth'  => intval($resizeWidth),
									  'resizeHeight' => intval($resizeHeight),
									  'source'       => $Medium->files['temporary'],
									  'destination'  => $Medium->files['temporary'],
									 )
								 );
		*/		
	}
	
	function width(&$Medium) {
		return $this->_execute(':command: -format %w :file:',
								array(
									  'command'     => 'identify',
									  'file' => $Medium->files['temporary'],
									 )
								 );
	}
	
	function height(&$Medium) {
		return $this->_execute(':command: -format %h :file:',
								array(
									  'command'     => 'identify',
									  'file' => $Medium->files['temporary'],
									 )
								 );
	}
}
?>