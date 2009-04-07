<?php
/**
 * Gd Medium Adapter File
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
 * @subpackage media.libs.medium.adapter
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Gd Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
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
	var $_compression;
	var $_pngFilter;

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
		$this->_format = $this->_formatMap[$Medium->mimeType]; // could be a problem here...

		if (isset($Medium->resources['gd'])) {
			return true;
		}
		if (!isset($Medium->file)) {
			return false;
		}

		$Medium->resources['gd'] = call_user_func_array('imageCreateFrom' . $this->_format, array($Medium->file));

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
		$args = array($Medium->resources['gd'], $file);

		switch ($Medium->mimeType) {
			case 'image/jpeg':
				if (isset($this->_compression)) {
					$args[] = $this->_compression;
				}
				break;

			case 'image/png':
				if (isset($this->_compression)) {
					$args[] = $this->_compression;

					if (isset($this->_pngFilter)) {
						$args[] = $this->_pngFilter;
					}
				}
				break;
		}

		return call_user_func_array('image' . $this->_format, $args);
	}

	function convert(&$Medium, $mimeType) {
		if (in_array($mimeType, $this->require['mimeTypes'])) {
			return $this->_format = $this->_formatMap[$mimeType];
		}
		return false;
	}

	function compress(&$Medium, $value) {
		switch ($Medium->mimeType) {
			case 'image/jpeg':
				$this->_compression = intval(100 - ($value * 10));
				break;

			case 'image/png':
				if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
					$this->_compression = intval($value);
				}
				if (version_compare(PHP_VERSION, '5.1.3', '>=')) {
					$filter = ($value * 10) % 10;
					$map = array(
						0 => PNG_FILTER_NONE,
						1 => PNG_FILTER_SUB,
						2 => PNG_FILTER_UP,
						3 => PNG_FILTER_AVG,
						4 => PNG_FILTER_PAETH,
						);

					if (array_key_exists($filter, $map)) {
						$this->_pngFilter = $map[$filter];
					} elseif ($filter == 5) {
						if (intval($value) <= 5 && imageIsTrueColor($Medium->resources['gd'])) {
							$this->_pngFilter = PNG_ALL_FILTERS;
						} else {
							$this->_pngFilter = PNG_NO_FILTER;
						}
					} else {
						$this->_pngFilter = PNG_ALL_FILTERS;
					}
				}
				break;
		}
		return true;
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
			$Medium->resources['gd'] = $Image;
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
