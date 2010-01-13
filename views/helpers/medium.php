<?php
/**
 * Medium Helper File
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
 * @subpackage media.views.helpers
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.MimeType');
App::import('Vendor', 'Media.Medium');
/**
 * Medium Helper Class
 *
 * @package    media
 * @subpackage media.views.helpers
 */
class MediumHelper extends AppHelper {
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
		'csslink'        => '<link type="text/css" rel="stylesheet" href="%s" %s/>',
		'javascriptlink' => '<script type="text/javascript" src="%s"></script>',
		'rsslink'        => '<link type="application/rss+xml" rel="alternate" href="%s" title="%s"/>', /* v2 */
	);
/**
 * Maps absolute paths to url paths
 *
 * @var array
 */
	var $_map = array(
		'static'   => array(MEDIA_STATIC => MEDIA_STATIC_URL),
		'transfer' => array(MEDIA_TRANSFER => MEDIA_TRANSFER_URL),
		'filter'   => array(MEDIA_FILTER => MEDIA_FILTER_URL)
	);
/**
 * Maps basenames of directories to absoulte paths
 *
 * @var array
 */
	var $_directories = array();
/**
 * Holds an indexed array of version names
 *
 * @var array
 */
	var $_versions = array();
/**
 * Maps short medium types to extensions
 *
 * @var array
 */
	var $_extensions = array(
		'aud' => array('mp3', 'ogg', 'aif', 'wma', 'wav'),
		'css' => array('css'),
		'doc' => array('odt', 'rtf', 'pdf', 'doc', 'png', 'jpg', 'jpeg'),
		'gen' => array(),
		'ico' => array('ico', 'png', 'gif', 'jpg', 'jpeg'),
		'img' => array('png', 'jpg', 'jpeg' , 'gif'),
		'js'  => array('js'),
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
 */
	var $__cached;
/**
 * Constructor
 *
 * Sets up cache and merges user supplied map settings with default map
 *
 * @param array $settings The map settings to add
 * @return void
 */
	function __construct($settings = array()) {
		$this->_map = array_merge($this->_map, $settings);

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
 */
	function __destruct() {
		Cache::write('media_found', $this->__cached, '_cake_core_');
	}
/**
 * Output filtering
 *
 * @param string $content
 * @param boolean $inline True to return content, false to add content to `scripts_for_layout`
 * @return mixed String if inline is true or null
 */
	function output($content, $inline = true) {
		if ($inline) {
			return $content;
		}

		$View =& ClassRegistry::getObject('view');
		$View->addScript($content);
	}
/**
 * Turns a file path into a (routed) URL
 *
 * Reimplemented method from Helper
 *
 * @param string $path Absolute or partial path to a file
 * @param boolean $full
 * @return string
 */
	function url($path = null, $full = false) {
		if (is_array($path) || strpos($path, '://') !== false) {
			return parent::url($path, $full);
		}
		if (!$path = $this->webroot($path)) {
			return null;
		}
		return $full ? FULL_BASE_URL . $path : $path;
	}
/**
 * Webroot
 *
 * Reimplemented method from Helper
 *
 * @param string $path Absolute or partial path to a file
 * @return mixed
 */
	function webroot($path) {
		if (!$file = $this->file($path)) {
			return null;
		}

		foreach ($this->_map as $value) {
			$directory = key($value);
			$url = current($value);

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
 * @param string $path Absolute or partial path to a file
 * @param array $options restrict: embed to display certain medium types only
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
			return $this->Html->link($out, $link, array(), false, false);
		}

		if (!$url = $this->url($path)) {
			return null;
		}

		if (strpos('://', $path) !== false) {
			$file = parse_url($url, PHP_URL_PATH);
		} else {
			$file = $this->file($path);
		}

		$mimeType = MimeType::guessType($file);
		$Medium = Medium::factory($file, $mimeType);

		if (!isset($options['width'])) {
			$options['width'] = $Medium->width();
		}
		if (!isset($options['height'])) {
			$options['height'] = $Medium->height();
		}

		extract($options, EXTR_SKIP);

		if (!empty($restrict) && !in_array(strtolower($Medium->name), (array) $restrict)) {
			return null;
		}

		switch ($mimeType) {
			/* Images */
			case 'image/gif':
			case 'image/jpeg':
			case 'image/png':
				$attributes = array_merge($attributes, array(
					'alt' => $alt,
					'width' => $width,
					'height' => $height,
				));
				if (strpos($path, 'ico/') !== false) {
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
 * Generates markup to link to file
 *
 * @param string $path Absolute or partial path to a file
 * @param array $options
 * @return mixed
 */
	function link($path, $options = array()) {
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
				return null;
			}
			return $out;
		}

		$inline = $options['inline'];
		unset($options['inline']);

		if (!$url = $this->url($path)) {
			return null;
		}

		if (strpos('://', $path) !== false) {
			$file = parse_url($url, PHP_URL_PATH);
		} else {
			$file = $this->file($path);
		}

		$mimeType = MimeType::guessType($file);
		$Medium = Medium::factory($file, $mimeType);

		if (!empty($options['restrict'])
		&& !in_array(strtolower($Medium->name), (array) $options['restrict'])) {
			return null;
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
 * Get MIME type for a path
 *
 * @param string|array $path Absolute or partial path to a file
 * @return string|boolean
 */
	function mimeType($path) {
		if ($file = $this->file($path)) {
			return MimeType::guessType($file);
		}
		return false;
	}
/**
 * Get size of file
 *
 * @param string|array $path Absolute or partial path to a file
 * @return integer|boolean False on error or integer
 */
	function size($path)	{
		if ($file = $this->file($path)) {
			return filesize($file);
		}
		return false;
	}
/**
 * Resolves partial path
 *
 * Examples:
 * 	css/cake.generic         >>> MEDIA_STATIC/css/cake.generic.css
 *  transfer/img/image.jpg   >>> MEDIA_TRANSFER/img/image.jpg
 * 	s/img/image.jpg          >>> MEDIA_FILTER/s/static/img/image.jpg
 *
 * @param string|array $path Either a string or an array with dirname and basename keys
 * @return string|boolean False on error or if path couldn't be resolbed otherwise
 * 							an absolute path to the file
 */
	function file($path) {
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

		$short = current(array_intersect(Medium::short(), $parts));

		if (!$short) {
			$message  = "MediumHelper::file - ";
			$message .= "You've provided a partial path without a medium directory (e.g. img) ";
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