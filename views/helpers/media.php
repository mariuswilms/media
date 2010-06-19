<?php
/**
 * Media Helper File
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
 * @subpackage media.views.helpers
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Lib', 'Mime_Type', array('file' => 'mm/src/Mime/Type.php'));
App::import('Lib', 'Media.Media'); // @deprecated

/**
 * Media Helper Class
 *
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
		'object'         => '<object%s>%s%s</object>',
		'param'          => '<param%s/>',
		'csslink'        => '<link type="text/css" rel="stylesheet" href="%s" %s/>', // @deprecated
		'javascriptlink' => '<script type="text/javascript" src="%s"></script>', // @deprecated
		'rsslink'        => '<link type="application/rss+xml" rel="alternate" href="%s" title="%s"/>', /* v2 */ // @deprecated
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
		if (!is_array(current($settings))) { // for BC
			$this->_paths = array_merge($this->_paths, (array) $settings);
		}
		$this->__compatConstruct($settings);
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
 * Generates markup to render a file inline
 *
 * Determines correct dimensions for all images automatically. Dimensions for all
 * other media should be passed explictly within the options array in order to prevent
 * the browser refloating the layout.
 *
 * @param string $path Absolute or partial path to a file
 * @param array $options Following are valid options:
 *                       - restrict: an array of lowercase media names (i.e. image) to restrict to
 *                                   Causes markup for other media not to be generated.
 *                       - width
 *                       - height
 *                       - several HTML attributes: alt, title, class
 * @return string
 */
	function embed($path, $options = array()) {
		$default = array(
			'restrict' => array(),
			'background' => '#000000',
			'autoplay' => false, /* aka `autostart` */
			'controls' => false, /* aka `controller` */
			'branding' => false,
			'alt' => null,
			'width' => null,
			'height' => null,
		);
		$additionalAttributes = array(
			'id' => null,
			'title' => null,
			'class' => null,
			'usemap' => null,
		);

		$options = array_merge($default, $options);
		$attributes = array_intersect_key($options, $additionalAttributes);

		if (is_array($path)) {
			$out = null;
			foreach ($path as $pathItem) {
				$out .= "\t" . $this->embed($pathItem, $options) . "\n";
			}
			return $out;
		}

		if (isset($options['url'])) {
			$link = $options['url'];
			unset($options['url']);

			$out = $this->embed($path, $options);
			return $this->Html->link($out, $link, array('escape' => false));
		}

		if (!$url = $this->url($path)) {
			return;
		}

		if (strpos('://', $path) !== false) {
			$file = parse_url($url, PHP_URL_PATH);
		} else {
			$file = $this->file($path);
		}

		$mimeType = Mime_Type::guessType($file);
		$name = Mime_Type::guessName($mimeType);

		extract($options, EXTR_SKIP);

		if ($restrict && !in_array($name, (array) $restrict)) {
			return;
		}

		switch ($mimeType) {
			/* Images */
			case 'image/gif':
			case 'image/jpeg':
			case 'image/png':
				if (!$width && !$height && function_exists('getimagesize')) {
					list($width, $height) = getimagesize($file);
				}
				$attributes = array_merge($attributes, array(
					'alt' => $alt,
					'width' => $width,
					'height' => $height
				));
				if (strpos($path, 'ico/') !== false) {
					$message  = "MediaHelper::embed - ";
					$message .= "All functionality related to assets has been deprecated.";
					trigger_error($message, E_USER_NOTICE);
					$attributes = $this->addClass($attributes, 'icon');
				}
				return sprintf(
					$this->Html->tags['image'],
					$url,
					$this->_parseAttributes($attributes)
				);
			/* Windows Media */
			case 'video/x-ms-wmv': /* official */
			case 'video/x-ms-asx':
			case 'video/x-msvideo':
				$attributes = array_merge($attributes, array(
					'type' => $mimeType,
					'width' => $width,
					'height' => $height,
					'data' => $url,
					'classid' => 'clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6',
				));
				$parameters = array(
					'src' => $url,
					'autostart' => $autoplay,
					'controller' => $controls,
					'pluginspage' => 'http://www.microsoft.com/Windows/MediaPlayer/',
				);
				break;
			/* RealVideo */
			case 'application/vnd.rn-realmedia':
			case 'video/vnd.rn-realvideo':
			case 'audio/vnd.rn-realaudio':
				$attributes = array_merge($attributes, array(
					'type' => $mimeType,
					'width' => $width,
					'height' => $height,
					'data' => $url,
					'classid' => 'clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA',
				));
				$parameters = array(
					'src' => $url,
					'autostart' => $autoplay,
					'controls' => isset($controls) ? 'ControlPanel' : null,
					'console' => 'video' . uniqid(),
					'loop' => $loop,
					'bgcolor' => $background,
					'nologo' => $branding ? false : true,
					'nojava' => true,
					'center' => true,
					'backgroundcolor' => $background,
					'pluginspage' => 'http://www.real.com/player/',
				);
				break;
			/* QuickTime */
			case 'video/quicktime':
				$attributes = array_merge($attributes, array(
					'type' => $mimeType,
					'width' => $width,
					'height' => $height,
					'data' => $url,
					'classid' => 'clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B',
					'codebase' => 'http://www.apple.com/qtactivex/qtplugin.cab',
				));
				$parameters = array(
					'src' => $url,
					'autoplay' => $autoplay,
					'controller' => $controls,
					'bgcolor' => substr($background, 1),
					'showlogo' => $branding,
					'pluginspage' => 'http://www.apple.com/quicktime/download/',
				);
				break;
			/* Mpeg */
			case 'video/mpeg':
				$attributes = array_merge($attributes, array(
					'type' => $mimeType,
					'width' => $width,
					'height' => $height,
					'data' => $url,
				));
				$parameters = array(
					'src' => $url,
					'autostart' => $autoplay,
				);
				break;
			/* Flashy Flash */
			case 'application/x-shockwave-flash':
				$attributes = array_merge($attributes, array(
					'type' => $mimeType,
					'width' => $width,
					'height' => $height,
					'data' => $url,
					'classid' => 'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000',
					'codebase' => 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab',
				));
				$parameters = array(
					'movie' => $url,
					'wmode' => 'transparent',
					'bgcolor' => $background,
					'FlashVars' => 'playerMode=embedded',
					'quality' => 'best',
					'scale' => 'noScale',
					'salign' => 'TL',
					'pluginspage' => 'http://www.adobe.com/go/getflashplayer',
				);
				break;
			case 'application/pdf':
				$attributes = array_merge($attributes, array(
					'type' => $mimeType,
					'width' => $width,
					'height' => $height,
					'data' => $url,
				));
				$parameters = array(
					'src' => $url,
					'toolbar' => $controls, /* 1 or 0 */
					'scrollbar' => $controls, /* 1 or 0 */
					'navpanes' => $controls,
				);
				break;
			case 'audio/x-wav':
			case 'audio/mpeg':
			case 'audio/ogg': /* must use application/ogg instead? */
			case 'audio/x-midi':
				$attributes = array_merge($attributes, array(
					'type' => $mimeType,
					'width' => $width,
					'height' => $height,
					'data' => $url,
				));
				$parameters = array(
					'src' => $url,
					'autoplay' => $autoplay,
				);
				break;
			default:
				$attributes = array_merge($attributes, array(
					'type' => $mimeType,
					'width' => $width,
					'height' => $height,
					'data' => $url,
				));
				$parameters = array(
					'src' => $url,
				);
				break;
		}
		return sprintf(
			$this->tags['object'],
			$this->_parseAttributes($attributes),
			$this->_parseParameters($parameters),
			$alt
		);
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
		if (is_array($path) || func_num_args() > 1) {
			$args = func_get_args();
			return call_user_func_array(array($this, '__compatFile'), $args);
		}

		// Most recent paths are probably searched more often
		$bases = array_reverse(array_keys($this->_paths));

		if (Folder::isAbsolute($path)) {
			return file_exists($path) ? $path : $this->__compatFile($path);
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
		return $this->__compatFile($path);
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

	/* Deprecated */

/**
 * Maps basenames of directories to absoulte paths
 *
 * @var array
 * @deprecated
 */
	var $_directories = array();

/**
 * Holds an indexed array of version names
 *
 * @var array
 * @deprecated
 */
	var $_versions = array();

/**
 * Maps short media types to extensions
 *
 * @var array
 * @deprecated
 */
	var $_extensions = array(
		'aud' => array('mp3', 'ogg', 'aif', 'wma', 'wav'),
		'css' => array('css'), // @deprecated
		'doc' => array('odt', 'rtf', 'pdf', 'doc', 'png', 'jpg', 'jpeg'),
		'gen' => array(),
		'ico' => array('ico', 'png', 'gif', 'jpg', 'jpeg'), // @deprecated
		'img' => array('png', 'jpg', 'jpeg' , 'gif', 'ico'),
		'js'  => array('js'), // @deprecated
		'txt' => array('txt'),
		'vid' => array(
			'avi', 'mpg', 'qt', 'mov', 'ogg', 'wmv',
			'png', 'jpg', 'jpeg', 'gif', 'mp3', 'ogg',
			'aif', 'wma', 'wav', 'flv'
	));

/**
 * Holds cached resolved paths
 *
 * @var array
 * @deprecated
 */
	var $__cached;

/**
 * Maps absolute paths to url paths
 *
 * @var array
 * @deprecated
 */
	var $_map = array(
		'static'   => array(MEDIA_STATIC => MEDIA_STATIC_URL),
		'transfer' => array(MEDIA_TRANSFER => MEDIA_TRANSFER_URL),
		'filter'   => array(MEDIA_FILTER => MEDIA_FILTER_URL)
	);

/**
 * Compat Constructor
 *
 * Sets up cache and merges user supplied map settings with default map
 *
 * @param array $settings The map settings to add
 * @return void
 * @deprecated
 */
	function __compatConstruct($settings) {
		if (is_array(current($settings))) {
			$this->_map = array_merge($this->_map, (array)$settings);
		}

		foreach ($this->_map as $key => $value) {
			$this->_directories[basename(key($value))] = key($value);
		}
		foreach (Configure::read('Media.filter') as $type) {
			$this->_versions += $type;
		}
		$this->_versions = array_keys($this->_versions);

		if (!$this->__cached = Cache::read('media_found', '_cake_core_')) {
			$this->__cached = array();
		}
	}

/**
 * Destructor
 *
 * Updates cache
 *
 * @return void
 * @deprecated
 */
	function __destruct() {
		Cache::write('media_found', $this->__cached, '_cake_core_');
	}

/**
 * Resolves partial path (compat)
 *
 * Examples:
 * img/cern                 >>> MEDIA_STATIC/img/cern.png
 * transfer/img/image.jpg   >>> MEDIA_TRANSFER/img/image.jpg
 * s/img/image.jpg          >>> MEDIA_FILTER/s/static/img/image.jpg
 *
 * @param string|array $path Either a string or an array with dirname and basename keys
 * @return string|boolean False on error or if path couldn't be resolbed otherwise
 *                        an absolute path to the file
 * @deprecated
 */
	function __compatFile($path) {
		$path = array();

		foreach (func_get_args() as $arg) {
			if (is_array($arg)) {
				if (isset($arg['dirname'])) {
					$path[] = rtrim($arg['dirname'], '/\\');
				}
				if (isset($arg['basename'])) {
					$path[] = $arg['basename'];
				}
			} else {
				$path[] = rtrim($arg, '/\\');
			}
		}
		$path = implode(DS, $path);
		$path = str_replace(array('/', '\\'), DS, $path);

		if (isset($this->__cached[$path])) {
			return $this->__cached[$path];
		}
		if (Folder::isAbsolute($path)) {
			return file_exists($path) ? $path : false;
		}

		$parts = explode(DS, $path);

		if (in_array($parts[0], $this->_versions)) {
			array_unshift($parts, basename(key($this->_map['filter'])));
		}
		if (!in_array($parts[0], array_keys($this->_directories))) {
			array_unshift($parts, basename(key($this->_map['static'])));
		}
		if (in_array($parts[1], $this->_versions)
		&& !in_array($parts[2], array_keys($this->_directories))) {
			array_splice($parts, 2, 0, basename(key($this->_map['static'])));
		}

		$path = implode(DS, $parts);

		if (isset($this->__cached[$path])) {
			return $this->__cached[$path];
		}

		$file = $this->_directories[array_shift($parts)] . implode(DS, $parts);

		if (file_exists($file)) {
			return $this->__cached[$path] = $file;
		}

		$short = current(array_intersect(Media::short(), $parts));

		if (!$short) {
			$message  = "MediaHelper::file - ";
			$message .= "You've provided a partial path without a media directory (e.g. img) ";
			$message .= "which is required to resolve the path.";
			trigger_error($message, E_USER_NOTICE);
			return false;
		}

		$extension = null;
		extract(pathinfo($file), EXTR_OVERWRITE);

		if (!isset($filename)) { /* PHP < 5.2.0 */
			$filename = substr($basename, 0, isset($extension) ? - (strlen($extension) + 1) : 0);
		}

		for ($i = 0; $i < 2; $i++) {
			$file = $i ? $dirname . DS . $filename : $dirname . DS . $basename;

			foreach ($this->_extensions[$short] as $extension) {
				$try = $file . '.' . $extension;
				if (file_exists($try)) {
					return $this->__cached[$path] = $try;
				}
			}
		}
		return false;
	}

/**
 * Generates markup to link to file
 *
 * @param string $path Absolute or partial path to a file
 * @param array $options
 * @return mixed
 * @deprecated
 */
	function link($path, $options = array()) {
		$message  = "MediaHelper::link - ";
		$message .= "All functionality related to assets has been deprecated.";
		trigger_error($message, E_USER_NOTICE);

		$default = array(
			'inline' => true,
			'restrict' => array(),
		);
		$defaultRss = array(
			'title' => 'RSS Feed',
		);

		if (is_bool($options)) {
			$options = array('inline' => $options);
		}
		$options = array_merge($default, $options);

		if (is_array($path) && !array_key_exists('controller', $path)) {
			$out = null;
			foreach ($path as $i) {
				$out .= $this->link($i, $options);
			}
			if (empty($out)) {
				return;
			}
			return $out;
		}

		$inline = $options['inline'];
		unset($options['inline']);

		if (!$url = $this->url($path)) {
			return;
		}

		if (strpos('://', $path) !== false) {
			$file = parse_url($url, PHP_URL_PATH);
		} else {
			$file = $this->file($path);
		}

		$mimeType = Mime_Type::guessType($file);
		$name = Mime_Type::guessName($mimeType);

		if ($options['restrict'] && !in_array($name, (array) $options['restrict'])) {
			return;
		}
		unset($options['restrict']);

		switch ($mimeType) {
			case 'text/css':
				$out = sprintf(
					$this->tags['csslink'],
					$url,
					$this->_parseAttributes($options, null, '', ' ')
				);
				return $this->output($out, $inline);
			case 'application/javascript':
			case 'application/x-javascript':
				$out = sprintf($this->tags['javascriptlink'], $url);
				return $this->output($out, $inline);
			case 'application/rss+xml':
				$options = array_merge($defaultRss,$options);
				$out = sprintf($this->tags['rsslink'], $url, $options['title']);
				return $this->output($out, $inline);
			default:
				return $this->Html->link(basename($file), $url);
		}
	}

/**
 * Output filtering
 *
 * @param string $content
 * @param boolean $inline True to return content, false to add content to `scripts_for_layout`
 * @return mixed String if inline is true or null
 * @deprecated
 */
	function output($content, $inline = true) {
		$message  = "MediaHelper::output - ";
		$message .= "All functionality related to assets has been deprecated.";
		trigger_error($message, E_USER_NOTICE);

		if ($inline) {
			return $content;
		}

		$View =& ClassRegistry::getObject('view');
		$View->addScript($content);
	}
}

?>