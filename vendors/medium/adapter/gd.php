<?php
/**
 * Gd Medium Adapter File
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
 * Gd Medium Adapter Class
 * 
 * @category   media handling
 * @package    attm
 * @subpackage attm.plugins.media.libs.medium.adapter
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://cakeforge.org/projects/attm The attm Project
 */
class GdMediumAdapter extends MediumAdapter {
	var $require = array(
							'mimeTypes' => array('image/gd'), /* Gets dynamically set in constructor */
							'extensions' => array('gd'),
							);
	var $_Image;
	var $_formatMap = array(
						'image/jpeg' => 'jpeg', 
						'image/gif' => 'gif', 
						'image/png' => 'png',
						'image/gd' => 'gd',
						'image/vnd.wap.wbmp' => 'wbmp',
						'image/xbm' => 'xbm',
						);	
	var $_format;
/**
 * Enter description here...
 *
 * @var integer A value between 1-100 where 1 is highest compression
 */
	var $_jpegQuality = null;
/**
 * The compression level used for PNGs
 *
 * Defaults to 0 because a higher value causes 
 * errors in some environments
 * 
 * @var integer A value between 0-9 where 9 is highest compression
 */
	var $_pngCompression = null;
/**
 * Enter description here...
 * 
 * PNG_NO_FILTER or PNG_ALL_FILTERS
 * PNG_FILTER_NONE, PNG_FILTER_SUB, PNG_FILTER_UP, PNG_FILTER_AVG, PNG_FILTER_PAETH
 *
 * @var unknown_type
 */
	var $_pngFilters = null;
	
	function compatible(&$Medium) {
		$types = imageTypes();
		if($types & IMG_GIF) {
			$this->require['mimeTypes'][] = 'image/gif'; 
		}
		if($types & IMG_JPG) {
			$this->require['mimeTypes'][] = 'image/jpeg';
		}		
		if($types & IMG_PNG) {
			$this->require['mimeTypes'][] = 'image/png';
		}
		if($types & IMG_WBMP) {
			$this->require['mimeTypes'][] = 'image/wbmp';
		}	
		if($types & IMG_XPM) {
			$this->require['mimeTypes'][] = 'image/xpm'; 
		}		
		return parent::compatible($Medium);
	}
	
	function initialize(&$Medium) {
		$this->_pngFilters = PNG_ALL_FILTERS;
		$this->_format = $this->_formatMap[$Medium->mimeType]; // could be a problem here...

		if (isset($Medium->resources['gd'])) {
			return true;
		}
		if (!isset($Medium->file)) {
			return false;
		}
		
		$Medium->resources['gd'] = call_user_func_array('imageCreateFrom' . $this->_format, $Medium->file); 
		 
		if (!$this->_isResource($Medium->resources['gd'])) {
			return false;
		}
		
		if (imageIsTrueColor($Medium->resources['gd'])) {
			imageSaveAlpha($Medium->resources['gd'], true);
		}
		
		return true;
	}
	
	function toString(&$Medium) {
		ob_start();
		$this->store($Medium, null);
		return ob_get_clean();
	}
	
	function store(&$Medium, $file) {
		if ($this->_format == 'jpeg') {
			$args = array($Medium->resources['gd'], $file);
			
			if (isset($this->_jpegQuality)) {
				$args[] = $this->_jpegQuality;
			}			
		} else if ($this->_format == 'png') {
			$args = array($Medium->resources['gd'], $file);
			
			if (isset($this->_pngCompression)) {
				$args[] = $this->_pngCompression;
				
				if (isset($this->_pngFilters)) {
					$args[] = $this->_pngFilters;
				}
			}
		} else {
			$args = array($Medium->resources['gd'], $file);
		}	
		
		return call_user_func_array('image' . $this->_format, $args);
	}
	
	function convert(&$Medium, $mimeType) {
		if (in_array($mimeType, $this->require['mimeTypes'])) {
			return $this->_format = $this->_formatMap[$mimeType];
		}
		return false;
	}	
	
	function crop(&$Medium, $left, $top, $width, $height) {
		$left   = intval($left);
		$top    = intval($top);
		$width  = intval($width);
		$height = intval($height);

		$Image = imageCreateTrueColor($width, $height);

		if ($this->_isTransparent($Medium->resources['gd'])) {
			$Image = $this->_copyTransparency($Medium->resources['gd'], $Image);
			imageCopyResized($Image, $Medium->resources['gd'], 0, 0, $left, $top, $width, $height, $width, $height);
		} else {
			imageSaveAlpha($Image, true);
			imageCopyResampled($Image, $Medium->resources['gd'], 0, 0, $left, $top, $width, $height, $width, $height);
		}
		if ($this->_isResource($Image)) {
			$this->_Image = $Image;
			return true;
		} 
		return false;
	}	
	
	function resize(&$Medium, $width, $height) {
		$width  = intval($width);
		$height = intval($height);
		
		$Image = imageCreateTrueColor($width, $height);
		
		if ($this->_isTransparent($Medium->resources['gd'])) {
			$Image = $this->_copyTransparency($Medium->resources['gd'], $Image);
			imageCopyResized($Image, $Medium->resources['gd'], 0, 0, 0, 0, $width, $height, $this->width($Medium), $this->height($Medium));
		} else {
			imageSaveAlpha($Image, true);
			imageCopyResampled($Image, $Medium->resources['gd'], 0, 0, 0, 0, $width, $height, $this->width($Medium), $this->height($Medium));		
		}
		if ($this->_isResource($Image)) {
			$Medium->resources['gd'] = $Image;
			return true;
		}
		return false;
	}
	
	function cropAndResize(&$Medium, $cropLeft, $cropTop, $cropWidth, $cropHeight, $resizeWidth, $resizeHeight) {
		$cropLeft     = intval($cropLeft);
		$cropTop      = intval($cropTop);
		$cropWidth    = intval($cropWidth);
		$cropHeight   = intval($cropHeight);
		$resizeWidth  = intval($resizeWidth);
		$resizeHeight = intval($resizeHeight);
		
		$Image = imageCreateTrueColor($resizeWidth, $resizeHeight);
		
		if ($this->_isTransparent($Medium->resources['gd'])) {
			$Image = $this->_copyTransparency($Medium->resources['gd'], $Image);
			imageCopyResized($Image, $Medium->resources['gd'], 0, 0, $cropLeft, $cropTop, $resizeWidth, $resizeHeight, $cropWidth, $cropHeight);
		} else {
			imageSaveAlpha($Image, true);
			imageCopyResampled($Image, $Medium->resources['gd'], 0, 0, $cropLeft, $cropTop, $resizeWidth, $resizeHeight, $cropWidth, $cropHeight);
		}
		if ($this->_isResource($Image)) {
			$Medium->resources['gd'] = $Image;
			return true;
		} 
		return false;		
	}
	
	function width(&$Medium) {
		return imageSX($Medium->resources['gd']);
	}
	
	function height(&$Medium) {
		return imageSY($Medium->resources['gd']);
	}
	
	function _isResource($Image) {
		return is_resource($Image) && get_resource_type($Image) == 'gd';
	}
	
	function _isTransparent($Image)	{
		return imageColorTransparent($Image) >= 0;
	}
	
	function _copyTransparency($sourceImage, $destinationImage)	{
		$rgba  = imageColorsForIndex($sourceImage, imageColorTransparent($sourceImage));
		$color = imageColorAllocate($destinationImage, $rgba['red'], $rgba['green'], $rgba['blue']);
		imageColorTransparent($destinationImage, $color);
		imageFill($destinationImage, 0, 0, $color);
		return $destinationImage;
	}
}
?>