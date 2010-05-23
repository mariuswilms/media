<?php
/**
 * Gd Media Adapter File
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */

/**
 * Gd Media Adapter Class
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 */
class GdMediaAdapter extends MediaAdapter {
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

	function compatible($Media) {
		if (!function_exists('imageTypes')) {
			return false;
		}
		$types = imageTypes();

		if ($types & IMG_GIF) {
			$this->require['mimeTypes'][] = 'image/gif';
		}
		if ($types & IMG_JPG) {
			$this->require['mimeTypes'][] = 'image/jpeg';
		}
		if ($types & IMG_PNG) {
			$this->require['mimeTypes'][] = 'image/png';
		}
		if ($types & IMG_WBMP) {
			$this->require['mimeTypes'][] = 'image/wbmp';
		}
		if ($types & IMG_XPM) {
			$this->require['mimeTypes'][] = 'image/xpm';
		}
		return parent::compatible($Media);
	}

	function initialize($Media) {
		$this->_format = $this->_formatMap[$Media->mimeType];

		if (isset($Media->resources['gd'])) {
			return true;
		}
		if (!isset($Media->file)) {
			return false;
		}

		$Media->resources['gd'] = call_user_func_array(
			'imageCreateFrom' . $this->_format,
			array($Media->file)
		);

		if (!$this->_isResource($Media->resources['gd'])) {
			return false;
		}

		if (imageIsTrueColor($Media->resources['gd'])) {
			imageAlphaBlending($Media->resources['gd'], false);
			imageSaveAlpha($Media->resources['gd'], true);
		}
		return true;
	}

	function close($Media) {
		if (is_resource($Media->resources['gd'])) {
			return imagedestroy($Media->resources['gd']);
		}
		return false;
	}

	function toString($Media) {
		ob_start();
		$this->store($Media, null);
		return ob_get_clean();
	}

	function store($Media, $file) {
		$args = array($Media->resources['gd'], $file);

		switch ($Media->mimeType) {
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

	function convert($Media, $mimeType) {
		if (in_array($mimeType, $this->require['mimeTypes'])) {
			return $this->_format = $this->_formatMap[$mimeType];
		}
		return false;
	}

	function compress($Media, $value) {
		switch ($Media->mimeType) {
			case 'image/jpeg':
				$this->_compression = (integer)(100 - ($value * 10));
				break;

			case 'image/png':
				if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
					$this->_compression = (integer)$value;
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
						if (intval($value) <= 5 && imageIsTrueColor($Media->resources['gd'])) {
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

	function crop($Media, $left, $top, $width, $height) {
		$left   = (integer)$left;
		$top    = (integer)$top;
		$width  = (integer)$width;
		$height = (integer)$height;

		$Image = imageCreateTrueColor($width, $height);
		$this->_adjustTransparency($Media->resources['gd'], $Image);

		if ($this->_isTransparent($Media->resources['gd'])) {
			imageCopyResized(
				$Image,
				$Media->resources['gd'],
				0, 0,
				$left, $top,
				$width, $height,
				$width, $height
			);
		} else {
			imageCopyResampled(
				$Image,
				$Media->resources['gd'],
				0, 0,
				$left, $top,
				$width, $height,
				$width, $height
			);
		}
		if ($this->_isResource($Image)) {
			$Media->resources['gd'] = $Image;
			return true;
		}
		return false;
	}

	function resize($Media, $width, $height) {
		$width  = (integer)$width;
		$height = (integer)$height;

		$Image = imageCreateTrueColor($width, $height);
		$this->_adjustTransparency($Media->resources['gd'], $Image);

		if ($this->_isTransparent($Media->resources['gd'])) {
			imageCopyResized(
				$Image,
				$Media->resources['gd'],
				0, 0,
				0, 0,
				$width, $height,
				$this->width($Media), $this->height($Media)
			);
		} else {
			imageCopyResampled(
				$Image,
				$Media->resources['gd'],
				0, 0,
				0, 0,
				$width, $height,
				$this->width($Media), $this->height($Media)
			);
		}
		if ($this->_isResource($Image)) {
			$Media->resources['gd'] = $Image;
			return true;
		}
		return false;
	}

	function cropAndResize($Media, $cropLeft, $cropTop, $cropWidth, $cropHeight, $resizeWidth, $resizeHeight) {
		$cropLeft     = (integer)$cropLeft;
		$cropTop      = (integer)$cropTop;
		$cropWidth    = (integer)$cropWidth;
		$cropHeight   = (integer)$cropHeight;
		$resizeWidth  = (integer)$resizeWidth;
		$resizeHeight = (integer)$resizeHeight;

		$Image = imageCreateTrueColor($resizeWidth, $resizeHeight);
		$this->_adjustTransparency($Media->resources['gd'], $Image);

		if ($this->_isTransparent($Media->resources['gd'])) {
			imageCopyResized(
				$Image,
				$Media->resources['gd'],
				0, 0,
				$cropLeft, $cropTop,
				$resizeWidth, $resizeHeight,
				$cropWidth, $cropHeight
			);
		} else {
			imageCopyResampled(
				$Image,
				$Media->resources['gd'],
				0, 0,
				$cropLeft, $cropTop,
				$resizeWidth, $resizeHeight,
				$cropWidth, $cropHeight
			);
		}
		if ($this->_isResource($Image)) {
			$Media->resources['gd'] = $Image;
			return true;
		}
		return false;
	}

	function width($Media) {
		return imageSX($Media->resources['gd']);
	}

	function height($Media) {
		return imageSY($Media->resources['gd']);
	}

	function _isResource($Image) {
		return is_resource($Image) && get_resource_type($Image) == 'gd';
	}

	function _isTransparent($Image)	{
		return imageColorTransparent($Image) >= 0;
	}

	function _adjustTransparency(&$Source, &$Destination) {
		if ($this->_isTransparent($Source)) {
			$rgba  = imageColorsForIndex($Source, imageColorTransparent($Source));
			$color = imageColorAllocate($Destination, $rgba['red'], $rgba['green'], $rgba['blue']);
			imageColorTransparent($Destination, $color);
			imageFill($Destination, 0, 0, $color);
		} else {
			if ($this->_format == 'png') {
				imageAlphaBlending($Destination, false);
				imageSaveAlpha($Destination, true);
			} elseif ($this->_format != 'gif') {
				$white = imageColorAllocate($Destination, 255, 255, 255);
				imageFill($Destination, 0, 0 , $white);
			}
		}
	}
}
?>