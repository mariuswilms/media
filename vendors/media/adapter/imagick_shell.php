<?php
/**
 * ImagickShell Media Adapter File
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
 * ImagickShell Media Adapter Class
 *
 * @package    media
 * @subpackage media.libs.media.adapter
 * @link       http://www.imagemagick.org/
 */
class ImagickShellMediaAdapter extends MediaAdapter {
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
	var $_compressionType;
	var $_compression;
	var $_pngFilter;

	function compatible($Media) {
		if ($this->_which(array('gs', 'gswin32c'))) { /* Check for ghostscript */
			$this->require['mimeTypes'][] = 'application/pdf';
		}
		return parent::compatible($Media);
	}

	function initialize($Media) {
		if (!isset($Media->files['temporary'])) {
			if (!isset($Media->file)) {
				return false;
			}
			$Media->files['temporary'] = TMP . uniqid('media_');
		}

		return $this->_execute(':command: :source: :format:::destination:', array(
			'command'     => 'convert',
			'source'      => $Media->file,
			'destination' => $Media->files['temporary'],
			'format'      => $this->_temporaryFormat
		 ));
	}

	function store($Media, $file) {
		$args =	array(
			'command'      => 'convert',
			'source'       => $Media->files['temporary'],
			'sourceFormat' => $this->_temporaryFormat,
			'destination'  => $file,
			'format'       => $this->_formatMap[$Media->mimeType],
		);

		if (isset($this->_compressionType)) {
			$args['compress'] = $this->_compressionType;
		}
		if (isset($this->_compression)) {
			if ($this->_compressionType === 'ZIP') {
				$args['quality'] = $this->_compression * 10 + $this->_pngFilter;
			} else {
				$args['quality'] = $this->_compression;
			}
		}

		return $this->_execute(':command:'
								. (isset($args['compress']) ? ' -compress :compress:' : '')
								. (isset($args['quality']) ? ' -quality :quality:' : '')
								. ' :sourceFormat:::source: :format:::destination:', $args);
	}

	function convert($Media, $mimeType) {
		if (!isset($this->_formatMap[$mimeType])) {
			return false;
		}

		$Media->mimeType = $mimeType;

		if ($Media->name === 'Document') { // application/pdf -> image
			$this->store($Media, $Media->files['temporary']);

			/* Unset files to prevent too early deletion by $Media */
			$temporary = $Media->files['temporary'];
			unset($Media->files);

			return Media::factory(array('temporary' => $temporary), $mimeType);
		}
		return true;
	}

	function compress($Media, $value) {
		switch ($Media->mimeType) {
			case 'image/tiff':
				$this->_compressionType = 'LZW';
				break;
			case 'image/png':
				$this->_compressionType = 'ZIP';
				$this->_compression = (integer)$value;
				$this->_pngFilter = ($value * 10) % 10;
				break;
			case 'image/jpeg':
				$this->_compressionType = 'JPEG';
				$this->_compression = (integer)(100 - ($value * 10));
				break;
		}
		return true;
	}

	function crop($Media, $left, $top, $width, $height) {
		return $this->_execute(':command: -crop :width:x:height:+:left:+:top: :source: :destination:', array(
			'command'     => 'convert',
			'width'       => (integer)$width,
			'height'      => (integer)$height,
			'left'        => (integer)$left,
			'top'         => (integer)$top,
			'source'      => $Media->files['temporary'],
			'destination' => $Media->files['temporary'],
		));
	}

	function resize($Media, $width, $height) {
		return $this->_execute(':command: -geometry :width:x:height:! :source: :destination:', array(
			'command'     => 'convert',
			'width'       => (integer)$width,
			'height'      => (integer)$height,
			'source'      => $Media->files['temporary'],
			'destination' => $Media->files['temporary'],
		));
	}

	function cropAndResize($Media, $cropLeft, $cropTop, $cropWidth, $cropHeight, $resizeWidth, $resizeHeight) {
		return 	$this->crop($Media, $cropLeft, $cropTop, $cropWidth, $cropHeight)
				&& $this->resize($Media, $resizeWidth, $resizeHeight);

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
									  'source'       => $Media->files['temporary'],
									  'destination'  => $Media->files['temporary'],
									 )
								 );
		*/
	}

	function width($Media) {
		return $this->_execute(':command: -format %w :file:', array(
			'command'     => 'identify',
			'file' => $Media->files['temporary'],
		));
	}

	function height($Media) {
		return $this->_execute(':command: -format %h :file:', array(
			'command'     => 'identify',
			 'file' => $Media->files['temporary'],
		));
	}
}
?>