<?php
/**
 * Media Helper File
 *
 * Copyright (c) 2007-2011 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.views.helpers
 * @copyright  2007-2011 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
require_once 'Mime/Type.php';

/**
 * Media Helper Class
 *
 * To load the helper just include it in the helpers property
 * of a controller:
 * {{{
 *     var $helpers = array('Form', 'Html', 'Media.Media');
 * }}}
 *
 * If needed you can also pass additional path to URL mappings when
 * loading the helper:
 * {{{
 *     var $helpers = array('Media.Media' => array(MEDIA_FOO => 'foo/'));
 * }}}
 *
 * Nearly all helper methods take so called partial paths. Partial paths are
 * dynamically expanded path fragments for let you specify paths to files in a
 * very short way.
 *
 * @see file()
 * @see __construct()
 * @link http://book.cakephp.org/view/99/Using-Helpers
 * @package    media
 * @subpackage media.views.helpers
 */
class MediaHelper extends AppHelper {

/**
 * Helpers
 *
 * @var array
 */
	var $helpers = array('Html');

/**
 * Tags
 *
 * @var array
 */
	var $tags = array(
		'audio'          => '<audio%s>%s%s</audio>',
		'video'          => '<video%s>%s%s</video>',
		'source'         => '<source%s/>',
		'object'         => '<object%s>%s%s</object>',
		'param'          => '<param%s/>'
	);

/**
 * Directory paths mapped to URLs. Can be modified by passing custom paths as
 * settings to the constructor.
 *
 * @var array
 */
	var $_paths = array(
		MEDIA_STATIC => MEDIA_STATIC_URL,
		MEDIA_TRANSFER => MEDIA_TRANSFER_URL,
		MEDIA_FILTER => MEDIA_FILTER_URL
	);

/**
 * Constructor
 *
 * Merges user supplied map settings with default map
 *
 * @param array $settings An array of base directory paths mapped to URLs. Used for determining
 *                        the absolute path to a file in `file()` and for determining the URL
 *                        corresponding to an absolute path. Paths are expected to end with a
 *                        trailing slash.
 * @return void
 */
	function __construct($settings = array()) {
		$this->_paths = array_merge($this->_paths, (array) $settings);
	}

/**
 * Turns a file path into an URL (without passing it through `Router::url()`)
 *
 * Reimplemented method from Helper
 *
 * @param string $path Absolute or partial path to a file
 * @param boolean $full Forces the URL to be fully qualified
 * @return string|void An URL to the file
 */
	function url($path = null, $full = false) {
		if (!$path = $this->webroot($path)) {
			return null;
		}
		if ($full && strpos($path, '://') === false) {
			$path = FULL_BASE_URL . $path;
		}
		return $path;
	}

/**
 * Webroot
 *
 * Reimplemented method from Helper
 *
 * @param string $path Absolute or partial path to a file
 * @return string|void An URL to the file
 */
	function webroot($path) {
		if (!$file = $this->file($path)) {
			return null;
		}

		foreach ($this->_paths as $directory => $url) {
			if (strpos($file, $directory) !== false) {
				if ($url === false) {
					return null;
				}
				$path = str_replace($directory, $url, $file);
				break;
			}
		}
		$path = str_replace('\\', '/', $path);

		if (strpos($path, '://') !== false) {
			return $path;
		}
		return $this->webroot . $path;
	}

/**
 * Generates HTML5 markup for one ore more media files
 *
 * Determines correct dimensions for all images automatically. Dimensions for all
 * other media should be passed explictly within the options array in order to prevent
 * the browser refloating the layout.
 *
 * @param string|array $paths Absolute or partial path to a file (or an array thereof)
 * @param array $options The following options control the output of this method:
 *                       - autoplay: Start playback automatically on page load, defaults to `false`.
 *                       - preload: Start buffering when page is loaded, defaults to `false`.
 *                       - controls: Show controls, defaults to `true`.
 *                       - loop: Loop playback, defaults to `false`.
 *                       - fallback: A string containing HTML to use when element is not supported.
 *                       - poster: The path to a placeholder image for a video.
 *                       - url: If given wraps the result with a link.
 *                       - full: Will generate absolute URLs when `true`, defaults to `false`.
 *
 *                       The following HTML attributes may also be passed:
 *                       - id
 *                       - class
 *                       - alt: This attribute is required for images.
 *                       - title
 *                       - width, height: For images the method will try to automatically determine
 *                                        the correct dimensions if no value is given for either
 *                                        one of these.
 * @return string|void
 */
	function embed($paths, $options = array()) {
		$default = array(
			'autoplay' => false,
			'preload' => false,
			'controls' => true,
			'loop' => false,
			'fallback' => null,
			'poster' => null,
			'full' => false
		);
		$optionalAttributes = array(
			'alt' => null,
			'id' => null,
			'title' => null,
			'class' => null,
			'width' => null,
			'height' => null
		);

		if (isset($options['url'])) {
			$link = $options['url'];
			unset($options['url']);

			return $this->Html->link($this->embed($paths, $options), $link, array(
				'escape' => false
			));
		}
		$options = array_merge($default, $options);
		extract($options, EXTR_SKIP);

		if (!$sources = $this->_sources((array) $paths, $full)) {
			return;
		}
		$attributes = array_intersect_key($options, $optionalAttributes);

		switch($sources[0]['name']) {
			case 'audio':
				$body = null;

				foreach ($sources as $source) {
					$body .= sprintf(
						$this->tags['source'],
						$this->_parseAttributes(array(
							'src' => $source['url'],
							'type' => $source['mimeType']
					)));
				}
				$attributes += compact('autoplay', 'controls', 'preload', 'loop');
				return sprintf(
					$this->tags['audio'],
					$this->_parseAttributes($attributes),
					$body,
					$fallback
				);
			case 'document':
				break;
			case 'image':
				$attributes = $this->_addDimensions($sources[0]['file'], $attributes);

				return sprintf(
					$this->Html->tags['image'],
					$sources[0]['url'],
					$this->_parseAttributes($attributes)
				);
			case 'video':
				$body = null;

				foreach ($sources as $source) {
					$body .= sprintf(
						$this->tags['source'],
						$this->_parseAttributes(array(
							'src' => $source['url'],
							'type' => $source['mimeType']
					)));
				}
				if ($poster) {
					$attributes = $this->_addDimensions($this->file($poster), $attributes);
					$poster = $this->url($poster, $full);
				}

				$attributes += compact('autoplay', 'controls', 'preload', 'loop', 'poster');
				return sprintf(
					$this->tags['video'],
					$this->_parseAttributes($attributes),
					$body,
					$fallback
				);
			default:
				break;
		}
	}

/**
 * Generates markup for a single media file using the `object` tag similar to `embed()`.
 *
 * @param string|array $paths Absolute or partial path to a file. An array can be passed to be make
 *                            this method compatible with `embed()`, in which case just the first file
 *                            in that array is actually used.
 * @param array $options The following options control the output of this method. Please note that
 *                       support for these options differs from type to type.
 *                       - autoplay: Start playback automatically on page load, defaults to `false`.
 *                       - controls: Show controls, defaults to `true`.
 *                       - loop: Loop playback, defaults to `false`.
 *                       - fallback: A string containing HTML to use when element is not supported.
 *                       - url: If given wraps the result with a link.
 *                       - full: Will generate absolute URLs when `true`, defaults to `false`.
 *
 *                       The following HTML attributes may also be passed:
 *                       - id
 *                       - class
 *                       - alt
 *                       - title
 *                       - width, height
 * @return string
 */
	function embedAsObject($paths, $options = array()) {
		$default = array(
			'autoplay' => false,
			'controls' => true,
			'loop' => false,
			'fallback' => null,
			'full' => false
		);
		$optionalAttributes = array(
			'alt' => null,
			'id' => null,
			'title' => null,
			'class' => null,
			'width' => null,
			'height' => null
		);

		if (isset($options['url'])) {
			$link = $options['url'];
			unset($options['url']);

			return $this->Html->link($this->embed($paths, $options), $link, array(
				'escape' => false
			));
		}
		$options = array_merge($default, $options);
		extract($options + $default);

		if (!$sources = $this->_sources((array) $paths, $full)) {
			return;
		}
		$attributes  = array('type' => $sources[0]['mimeType'], 'data' => $sources[0]['url']);
		$attributes += array_intersect_key($options, $optionalAttributes);

		switch ($sources[0]['mimeType']) {
			/* Windows Media */
			case 'video/x-ms-wmv': /* official */
			case 'video/x-ms-asx':
			case 'video/x-msvideo':
				$attributes += array(
					'classid' => 'clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6'
				);
				$parameters = array(
					'src' => $url,
					'autostart' => $autoplay,
					'controller' => $controls,
					'pluginspage' => 'http://www.microsoft.com/Windows/MediaPlayer/'
				);
				break;
			/* RealVideo */
			case 'application/vnd.rn-realmedia':
			case 'video/vnd.rn-realvideo':
			case 'audio/vnd.rn-realaudio':
				$attributes += array(
					'classid' => 'clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA',
				);
				$parameters = array(
					'src' => $sources[0]['url'],
					'autostart' => $autoplay,
					'controls' => isset($controls) ? 'ControlPanel' : null,
					'console' => 'video' . uniqid(),
					'loop' => $loop,
					'nologo' => true,
					'nojava' => true,
					'center' => true,
					'pluginspage' => 'http://www.real.com/player/'
				);
				break;
			/* QuickTime */
			case 'video/quicktime':
				$attributes += array(
					'classid' => 'clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B',
					'codebase' => 'http://www.apple.com/qtactivex/qtplugin.cab'
				);
				$parameters = array(
					'src' => $sources[0]['url'],
					'autoplay' => $autoplay,
					'controller' => $controls,
					'showlogo' => false,
					'pluginspage' => 'http://www.apple.com/quicktime/download/'
				);
				break;
			/* Mpeg */
			case 'video/mpeg':
				$parameters = array(
					'src' => $sources[0]['url'],
					'autostart' => $autoplay,
				);
				break;
			/* Flashy Flash */
			case 'application/x-shockwave-flash':
				$attributes += array(
					'classid' => 'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000',
					'codebase' => 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab'
				);
				$parameters = array(
					'movie' => $sources[0]['url'],
					'wmode' => 'transparent',
					'FlashVars' => 'playerMode=embedded',
					'quality' => 'best',
					'scale' => 'noScale',
					'salign' => 'TL',
					'pluginspage' => 'http://www.adobe.com/go/getflashplayer'
				);
				break;
			case 'application/pdf':
				$parameters = array(
					'src' => $sources[0]['url'],
					'toolbar' => $controls, /* 1 or 0 */
					'scrollbar' => $controls, /* 1 or 0 */
					'navpanes' => $controls
				);
				break;
			case 'audio/x-wav':
			case 'audio/mpeg':
			case 'audio/ogg':
			case 'audio/x-midi':
				$parameters = array(
					'src' => $sources[0]['url'],
					'autoplay' => $autoplay
				);
				break;
			default:
				$parameters = array(
					'src' => $sources[0]['url']
				);
				break;
		}
		return sprintf(
			$this->tags['object'],
			$this->_parseAttributes($attributes),
			$this->_parseParameters($parameters),
			$fallback
		);
	}

/**
 * Get the name of a media for a path
 *
 * @param string $path Absolute or partial path to a file
 * @return string|void i.e. `image` or `video`
 */
	function name($path) {
		if ($file = $this->file($path)) {
			return Mime_Type::guessName($file);
		}
	}

/**
 * Get MIME type for a path
 *
 * @param string $path Absolute or partial path to a file
 * @return string|void
 */
	function mimeType($path) {
		if ($file = $this->file($path)) {
			return Mime_Type::guessType($file);
		}
	}

/**
 * Get size of file
 *
 * @param string $path Absolute or partial path to a file
 * @return integer|void
 */
	function size($path)	{
		if ($file = $this->file($path)) {
			return filesize($file);
		}
	}

/**
 * Resolves partial path to an absolute path by trying to find an existing file matching the
 * pattern `{<base path 1>, <base path 2>, [...]}/<provided partial path without ext>.*`.
 * The base paths are coming from the `_paths` property.
 *
 * Examples:
 * img/cern                 >>> MEDIA_STATIC/img/cern.png
 * img/mit.jpg              >>> MEDIA_TRANSFER/img/mit.jpg
 * s/<...>/img/hbk.jpg      >>> MEDIA_FILTER/s/<...>/img/hbk.png
 *
 * @param string $path A relative or absolute path to a file.
 * @return string|boolean False on error or if path couldn't be resolved otherwise
 *                        an absolute path to the file.
 */
	function file($path) {
		// Most recent paths are probably searched more often
		$bases = array_reverse(array_keys($this->_paths));

		if (Folder::isAbsolute($path)) {
			return file_exists($path) ? $path : null;
		}

		$extension = null;
		extract(pathinfo($path), EXTR_OVERWRITE);

		if (!isset($filename)) { /* PHP < 5.2.0 */
			$filename = substr($basename, 0, isset($extension) ? - (strlen($extension) + 1) : 0);
		}

		foreach ($bases as $base) {
			if (file_exists($base . $path)) {
				return $base . $path;
			}
			$files = glob($base . $dirname . DS . $filename . '.*', GLOB_NOSORT | GLOB_NOESCAPE);

			if (count($files) > 1) {
				$message  = "MediaHelper::file - ";
				$message .= "A relative path (`{$path}`) was given which triggered search for ";
				$message .= "files with the same name but not the same extension.";
				$message .= "This resulted in multiple files being found. ";
				$message .= "However the first file being found has been picked.";
				trigger_error($message, E_USER_NOTICE);
			}
			if ($files) {
				return array_shift($files);
			}
		}
	}

/**
 * Takes an array of paths and generates and array of source items.
 *
 * @param array $paths An array of  relative or absolute paths to files.
 * @param boolean $full When `true` will generate absolute URLs.
 * @return array An array of sources each one with the keys `name`, `mimeType`, `url` and `file`.
 */
	function _sources($paths, $full = false) {
		$sources = array();

		foreach ($paths as $path) {
			if (!$url = $this->url($path, $full)) {
				return;
			}
			if (strpos('://', $path) !== false) {
				$file = parse_url($url, PHP_URL_PATH);
			} else {
				$file = $this->file($path);
			}
			$mimeType = Mime_Type::guessType($file);
			$name = Mime_Type::guessName($mimeType);

			$sources[] = compact('name', 'mimeType', 'url', 'file');
		}
		return $sources;
	}

/**
 * Adds dimensions to an attributes array if possible.
 *
 * @param string $file An absolute path to a file.
 * @param array $attributes
 * @return array The modified attributes array.
 */
	protected function _addDimensions($file, $attributes) {
		if (isset($attributes['width']) || isset($attribues['height'])) {
			return $attributes;
		}
		if (function_exists('getimagesize')) {
			list($attributes['width'], $attributes['height']) = getimagesize($file);
		}
		return $attributes;
	}

/**
 * Generates attributes from options. Overwritten from Helper::_parseAttributes
 * to take new minimized HTML5 attributes used here into account.
 *
 * @param array $options
 * @return string
 */
	function _parseAttributes($options) {
		$attributes = array();
		$minimizedAttributes = array('autoplay', 'controls', 'autobuffer', 'loop');

		foreach ($options as $key => $value) {
			if (in_array($key, $minimizedAttributes)) {
				if ($value === 1 || $value === true || $value === 'true' || $value == $key) {
					$attributes[] = sprintf('%s="%s"', $key, $key);
					unset($options[$key]);
				}
			}
		}
		return parent::_parseAttributes($options) . ' ' . implode(' ', $attributes);
	}

/**
 * Generates `param` tags
 *
 * @param array $options
 * @return string
 */
	function _parseParameters($options) {
		$parameters = array();
		$options = Set::filter($options);

		foreach ($options as $key => $value) {
			if ($value === true) {
				$value = 'true';
			} elseif ($value === false) {
				$value = 'false';
			}
			$parameters[] = sprintf(
				$this->tags['param'],
				$this->_parseAttributes(array('name' => $key, 'value' => $value))
			);
		}
		return implode("\n", $parameters);
	}
}

?>
