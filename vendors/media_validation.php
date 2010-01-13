<?php
/**
 * Media Validation File
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.libs
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Core', 'Validation');
/**
 * Media Validation Class
 *
 * @package    media
 * @subpackage media.libs
 */
class MediaValidation extends Validation {
/**
 * Checks if MIME type is (not) one of given MIME types
 *
 * @param string $check Mime type to check e.g. image/jpeg
 * @param mixed $deny True or * blocks any MIME type,
 * 	an array containing MIME types selectively blocks,
 * 	false blocks no MIME type
 * @param mixed $allow True or * allows any extension,
 * 	an array containing extensions selectively allows,
 * 	false allows no MIME type
 * @return boolean
 */
	function mimeType($check, $deny = false, $allow = true) {
		if (!is_string($check) || !preg_match('/^[-\w.\+]+\/[-\w.\+]+$/', $check)) {
			return false;
		}
		list($deny, $allow) = self::_normalize($deny, $allow);

		if ($deny === true || (is_array($deny) && in_array($check, $deny))) {
			return false;
		}
		if($allow !== true && (is_array($allow) && !in_array($check, $allow))) {
			return false;
		}
		return true;
	}
/**
 * Checks if extension is (not) one of given extensions
 *
 * @param string $check Extension to check (without leading dot)
 * @param mixed $deny True or * blocks any extension,
 * 	an array containing extensions (without a leading dot) selectively blocks,
 * 	false blocks no extension
 * @param mixed $allow True or * allows any extension,
 * 	an array containing extensions (without leading dot) selectively allows,
 * 	false allows no extension
 * @return boolean
 */
	function extension($check, $deny = false, $allow = true) {
		if (!is_string($check) || !preg_match('/^[\w0-9]+(\.[\w0-9]+)?$/', $check)) {
			return false;
		}
		list($deny, $allow) = self::_normalize($deny, $allow);

		if ($deny === true || (is_array($deny) && Validation::extension($check, $deny))) {
			return false;
		}
		if ($allow !== true && (is_array($allow) && !Validation::extension($check, $allow))) {
			return false;
		}
		return true;
	}
/**
 * Checks if size is within limits
 *
 * Please note that the size will always be checked against
 * limitations set in `php.ini` for `post_max_size` and `upload_max_filesize`
 * even if $max is set to false.
 *
 * @param integer $check Size to check in bytes
 * @param mixed $max String (e.g. 8M) containing maximum allowed size, false allows any size
 * @return boolean
 */
	function size($check, $max = false) {
		if (!$check = self::_toComputableSize($check)) {
			return false;
		}

		$max = self::_normalize($max);
		$maxSizes = array();

		if ($max !== false && $max = self::_toComputableSize($max)) {
			 $maxSizes[] = $max;
		}
		if ($max = self::_toComputableSize(ini_get('post_max_size'))) {
			$maxSizes[] = $max;
		}
		if ($max = self::_toComputableSize(ini_get('upload_max_filesize'))) {
			$maxSizes[] = $max;
		}
		if (empty($maxSizes)) {
			return false;
		}

		sort($maxSizes);
		$max = $maxSizes[0];

		return $check <= $max;
	}
/**
 * Checks if pixels are within limits
 *
 * @param mixed $check Pixels to check e.g 200x200 or 40000
 * @param mixed $max String (e.g. 40000 or 200x100) containing maximum allowed amount of pixels
 * @return boolean
 */
	function pixels($check, $max = false) {
		if (strpos($check, 'x') !== false) {
			list($width, $height) = explode('x', $check);
			$check = $width * $height;
		}
		if (strpos($max, 'x') !== false) {
			list($width, $height) = explode('x' , $max);
			$max = $width * $height;
		}
		return $check <= $max;
	}
/**
 * Checks if path is within given locations
 *
 * @param string $check Absolute path
 * @param mixed $allow True or * allows any location,
 * 	an array containing absolute paths to locations
 * @return boolean
 */
	function location($check, $allow = false) {
		$allow = self::_normalize($allow);

		if ($allow === true) {
			return true;
		} elseif ($allow === false) {
			return false;
		}

		if (!is_array($allow)) {
			$allow = array($allow);
		} else {
			$allow = array_unique($allow);
		}

		if (Validation::url($check)) {
			foreach ($allow as $path) {
				if (preg_match('/^' . preg_quote($path, '/') . '/', $check)) {
					return true;
				}
			}
		} elseif(MediaValidation::file($check, false)) {
			$check = dirname($check);
			if (!Folder::isAbsolute($check)) {
				return false;
			}
			$Check = new Folder($check);

			foreach ($allow as $path) {
				if (!Folder::isAbsolute($path) || Validation::url($path)) {
					continue;
				}
				if ($Check->inPath($path)) {
					return true;
				}
			}
		}
		return false;
	}
/**
 * Checks if read/write permissions are set
 *
 * @param string $check 4-digit octal representation of file permissions,
 * 	or absolute path to a file or directory
 * @param string $type Permission r, w or rw
 * @return boolean
 */
	function access($check, $type = 'r') {
		if (self::file($check, true) || self::folder($check, true)) {
			if (strpos($type, 'r') !== false && !is_readable($check)) {
				return false;
			}
			if (strpos($type, 'w') !== false && !is_writable($check)) {
				return false;
			}
		} else {
			$ar = $check & '0444'; /* is readable? == are we able to connect? */
			$aw = $check & '0222'; /* is writable? */

			if (strpos($type, 'r') !== false && $ar === '0000') {
				return false;
			}
			if (strpos($type, 'w') !== false && $aw === '0000') {
				return false;
			}
		}
		return true;
	}
/**
 * Checks if provided or potentially dangerous permissions are set
 *
 * @param string $check
 * @param mixed $match True to check for potentially dangerous permissions,
 * 	a string containing the 4-digit octal value of the permissions to check for an exact match,
 * 	false to allow any permissions
 * @return boolean
 */
	function permission($check, $match = true) {
		$match = self::_normalize($match);

		if ($match === false) {
			return true;
		}

		/* r = 4, w = 2, x = 1 */
		$ax = $check & '0111'; /* is executable bit set for a? */
		if (($match === true || $match == '-x') && $ax !== '0000') {
			return false;
		}
		if (is_numeric($match) && $check != $match) {
			return false;
		}
		return true;
	}
/**
 * Checks if subject is an (existent) file
 * Please note, that directoires are not treated as files in strict mode
 *
 * @param string $file Absolute path to file
 * @param boolean $strict Enable checking for actual existence of file
 * @return boolean
 */
	function file($check, $strict = true) {
		if (!is_string($check)) {
			return false;
		}
		if ((is_dir($check) || !is_file($check)) && $strict === true) {
			return false;
		}
		if (strpos($check, DS) === false) {
			return false;
		}
		return true;
	}
/**
 * Checks if subject is an (existent) folder
 * Used mainly for $allow/$deny parameter contents
 *
 * @param string $check Absolute path to directory
 * @param boolean $strict Enable checking for actual existence of directory
 * @return boolean
 */
	function folder($check, $strict = true) {
		if (!is_string($check)) {
			return false;
		}
		if (!is_dir($check) && $strict === true) {
			return false;
		}
		return true;
	}
/**
 * Normalizes Parameters
 *
 * @param mixed Array containing multiple strings, or a single string
 * @return mixed
 */
	function _normalize() {
		$args = func_get_args();

		if (count($args) > 1) {
			foreach($args as $param) {
				$result[] = self::_normalize($param);
			}
			return $result;
		}

		$param = $args[0];
		switch ($param) {
			case '*':
			case array('*'):
				return true;
			case null:
			case array(null):
			case array():
			case '':
			case array(''):
				return false;
			default:
				return $param;
		}
	}
/**
 * Parse `php.ini` style size strings
 *
 * Slightly modified version of `ini_get_size()`
 * @link posted at http://www.php.net/features.file-upload
 * @author djcassis gmail com
 *
 * @param string $sizeString `php.ini` style size string e.g. `'16M'`
 * @return int Size in bytes
 */
	function _toComputableSize($sizeString) {
		if (empty($sizeString)) {
			return false;
		}
		if (is_numeric($sizeString)) {
			return $sizeString;
		}

		$sizeUnit = strtoupper(substr($sizeString, -1));
	    $size = (integer)substr($sizeString, 0, -1);

	    switch ($sizeUnit) {
			case 'Y': $size *= 1024; /* Yotta */
			case 'Z': $size *= 1024; /* Zetta */
			case 'E': $size *= 1024; /* Exa */
	        case 'P': $size *= 1024; /* Peta */
			case 'T': $size *= 1024; /* Tera */
			case 'G': $size *= 1024; /* Giga */
			case 'M': $size *= 1024; /* Mega */
			case 'K': $size *= 1024; /* Kilo */
	    }
	    return $size;
	}
}
?>