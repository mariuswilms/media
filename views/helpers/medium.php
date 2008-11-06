<?php
/**
 * Medium Helper File
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
 * @subpackage attm.plugins.media.views.helpers
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
App::import('Vendor', 'Media.MimeType');
App::import('Vendor', 'Media.Medium');
/**
 * Medium Helper Class
 * 
 * Handles various kinds of media
 *
 * @category   media handling
 * @package    attm
 * @subpackage attm.plugins.media.controllers
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  $CopyrightYear$ David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://cakeforge.org/projects/attm The attm Project
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
			'object'			=> '<object%s>%s%s</object>',
			'param' 			=> '<param%s />',
			'csslink' 			=> '<link type="text/css" rel="stylesheet" href="%s" %s/>',
			'javascriptlink' 	=> '<script type="text/javascript" src="%s"></script>',
			'rsslink'			=> '<link type="application/rss+xml" rel="alternate" href="%s" title="%s"/>', // v2
			);
/**
 * Configuration settings for this helper
 *
 * @var array
 */
	var $settings = array(
					/* Directory names to be scanned for */
					'directories' => array('static' ,'transfer', 'filter'),
	
					/* Version names to be scanned for */
					'versions' => array('xxs', 'xs', 's', 'm', 'l', 'xl', 'xxl', 'c'),
	
					'extensions' => array(
						'arc'	=> array('zip'),
						'aud' 	=> array('mp3', 'ogg', 'aif', 'wma', 'wav'),
						'css' 	=> array('css'),
						'doc' 	=> array('odt', 'rtf', 'pdf', 'doc', 'png', 'jpg', 'jpeg'),
						'gen' 	=> array(),
						'ico' 	=> array('ico', 'png', 'gif', 'jpg', 'jpeg'),
						'img' 	=> array('png', 'jpg', 'jpeg' , 'gif'),
						'js' 	=> array('js'),
						'txt' 	=> array('txt'),
						'vid' 	=> array('avi', 'mpg', 'qt', 'mov', 'ogg', 'wmv', 'png', 'jpg', 'jpeg', 'gif', 'mp3', 'ogg', 'aif', 'wma', 'wav'),
						)
					);
/**
 * Holds cached paths
 *
 * @var array
 */
	var $_found;
/**
 * Constructor
 * 
 * Sets up cache
 */
	function __construct($settings = array()) {
		$this->settings = Set::merge($this->settings, $settings);
		
		if (!$this->_found = Cache::read('media_found')) {
			$this->_found = array();
		}
	}
/**
 * Destructor
 * 
 * Updates cache
 */
	function __destruct() {
		Cache::write('media_found', $this->_found, YEAR);
	}
/**
 * Output filtering
 *
 * @param string $content
 * @param bool $inline True to return content, false to add content to scripts_for_layout
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
 * Turns a file into a (routed) url string
 * 
 * Reimplemented method from Helper
 *
 * @param string $file Absolut path or relative (to MEDIA) path to file
 * @param array $options For future usage
 * @return arrays
 */
	function url($url = null, $full = false) {
		if (is_array($url) || strpos($url, '://') !== false) {
			return parent::url($url, $full);
		}
		return $this->webroot($url);
	}
/**
 * Enter description here...
 *
 * @param unknown_type $path
 * @return unknown
 */
	function webroot($path) {
		if (!$file = $this->file($path)) {
			return null;
		}
		
		$path = str_replace(MEDIA, null, $file);
		$path = str_replace(DS, '/', $path); /* Normalize url path */
		return $this->webroot . MEDIA_URL . $path;
	}
/**
 * Display a file inline
 * 
 * @param unknown_type $file
 * @param unknown_type $options restrict: embed to display certain medium types only 
 * @return unknown
 */
	function embed($path, $options = array()) {
		$default = array(
						/* Internal */
						'restrict' => array(),
						/* Attributes */
						'alt' => null,
						'width' => null,
						'height' => null,
						'class' => null,
						/* Parameters */
						'background' => '#000000',
						'autoplay' => false, // also: autostart
						'controls' => false, // also: controller
						'branding' => false,
						);
						
		$options = array_merge($default, $options); 

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

		if (is_string($path)) {
			$file = $this->file($path);
			$mimeType = MimeType::guessType($file); 
		} else {
			$file = parse_url($url, PHP_URL_PATH);
			$mimeType = MimeType::guessType($file);
		}

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
				$attributes = array(
								'width' => $width,
								'height' => $height,
								'alt' => $alt,
								'class' => $class,
								'src' => $url,
								);
				if (strpos($path, 'ico/') !== false) {
					$attributes = $this->addClass($attributes, 'icon');
				}
				return sprintf($this->Html->tags['image'], $url, $this->_parseAttributes($attributes));
				
			/* Windows Media */
			case 'video/x-ms-asx':
			case 'video/x-ms-wmv': // official
			case 'video/x-msvideo':
				$attributes = array(
								'type' => $mimeType,
								'width' => $width,
								'height' => $height,
								'data' => $url,
								);
				$parameters = array(
								'src' => $url,
								'autostart' => $autoplay,
								'controller' => $controls,
								);
				return sprintf($this->tags['object'], $this->_parseAttributes($attributes), $this->_parseParameters($parameters), $alt);
				
			/* RealVideo */
			case 'application/vnd.rn-realmedia':	 // ?
			case 'application/vnd.pn-realmedia':     // ?
			case 'video/vnd.rn-realvideo':
			case 'video/vnd.pn-realvideo':
				$attributes = array(
								'type' => $mimeType,
								'width' => $width,
								'height' => $height,
								'data' => $url,
								'classid' => 'clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA',
								);
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
								);
				return sprintf($this->tags['object'], $this->_parseAttributes($attributes), $this->_parseParameters($parameters), $alt);
				
			/* QuickTime */
			case 'video/quicktime':
				$attributes = array(
								'type' => $mimeType,
								'width' => $width,
								'height' => $height,
								'data' => $url,
								'classid' => 'clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B',
								'codebase' => 'http://www.apple.com/qtactivex/qtplugin.cab',
								);
				$parameters = array(
								'src' => $url,
								'autoplay' => $autoplay,
								'controller' => $controls,
								'bgcolor' => substr($background, 1),
								'showlogo' => $branding,
								);
				return sprintf($this->tags['object'], $this->_parseAttributes($attributes), $this->_parseParameters($parameters), $alt);
				
			/* Mpeg */
			case 'video/mpeg':
				$attributes = array(
								'type' => $mimeType,
								'width' => $width,
								'height' => $height,
								'data' => $url,
								);
				$parameters = array(
								'src' => $url,
								'autostart' => $autoplay,
								);
				return sprintf($this->tags['object'], $this->_parseAttributes($attributes), $this->_parseParameters($parameters), $alt);
				
			/* Flashy Flash */
			case 'application/x-shockwave-flash':
				$attributes = array(
								'type' => $mimeType,
								'width' => $width,
								'height' => $height,
								'data' => $url,
								'classid' => 'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000',
								'codebase' => 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab',
								);
				$parameters = array(
								'movie' => $url,
								'wmode' => 'transparent',
								'pluginspage' => 'http://www.macromedia.com/go/getflashplayer',
								'bgcolor' => $background,
								'FlashVars' => 'playerMode=embedded',
								'quality' => 'best',
								'scale' => 'noScale',
								'salign' => 'TL',
								);
				return sprintf($this->tags['object'], $this->_parseAttributes($attributes), $this->_parseParameters($parameters), $alt);

			case 'application/pdf':
				$attributes = array(
								'type' => $mimeType,
								'width' => $width,
								'height' => $height,
								'data' => $url,
								);
				$parameters = array(
								'src' => $url,
								'toolbar' => $controls, // 1 or 0 vvditovvv
								'scrollbar' => $controls,
								'navpanes' => $controls,
								);
				return sprintf($this->tags['object'], $this->_parseAttributes($attributes), $this->_parseParameters($parameters), $alt);				
				
			case 'audio/x-wav':
			case 'audio/mpeg':
			case 'audio/ogg': // better: application/ogg?
			case 'audio/x-midi':
				$attributes = array(
								'type' => $mimeType,
								'width' => 200, // assuming this is the audio player default size
								'height' => 25, // increase size if necessary
								'data' => $url,
								);
				$parameters = array(
								'src' => $url,
								'autoplay' => $autoplay,
								);				
				return sprintf($this->tags['object'], $this->_parseAttributes($attributes), $this->_parseParameters($parameters), $alt);
					
			default:
				$attributes = array(
								'type' => $mimeType,
								'width' => $width,
								'height' => $height,
								'data' => $url,
								);
				$parameters = array(
								'src' => $url,
								);
				return sprintf($this->tags['object'], $this->_parseAttributes($attributes), $this->_parseParameters($parameters), $alt);					
		}
	}	
/**
 * Enter description here...
 *
 * @param unknown_type $url
 * @param unknown_type $options
 * @return unknown
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
		
		if (is_string($path)) {
			$file = $this->file($path);
			$mimeType = MimeType::guessType($file); 
		} else {
			$file = parse_url($url, PHP_URL_PATH);
			$mimeType = MimeType::guessType($file);
		}

		$Medium = Medium::factory($file, $mimeType);
		
		if (!empty($options['restrict']) && !in_array(strtolower($Medium->name), (array) $options['restrict'])) {
			return null;
		}
		unset($options['restrict']);		

		switch ($mimeType) {
			case 'text/css':
				$out = sprintf($this->tags['csslink'], $url, $this->_parseAttributes($options, null, '', ' '));
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
				return null;
		}
	}	
/**
 * Get mime type for a path
 *
 * @param string $path
 * @return mixed
 */
	function mimeType($path) {
		return MimeType::guessType($path);
	}
/**
 * Get size of file
 *
 * @param string $path
 * @return mixed False on error or integer
 */
	function size($path)	{
		if ($file = $this->file($path)) {
			return filesize($file);
		} 
		return false;
	}
/**
 * Enter description here...
 *
 * @param unknown_type $path
 * @return unknown
 */
	function file($path) {
		if (isset($this->_found[$path])) { /* Lookup cached */
			return $this->_found[$path];
		}
				
		$path = str_replace('/', DS, trim($path)); /* Correct slashes if we are on windows */
		$path = str_replace(MEDIA, null, $path); /* Make path relative */
		
		$parts = explode(DS, $path);
		
		if (in_array($parts[0], $this->settings['versions'])) {
			array_unshift($parts, 'filter');
		}
		if (!in_array($parts[0], $this->settings['directories'])) {
			array_unshift($parts, 'static');
		}
		/*
		if (!empty($this->themeWeb) && $parts[0] === 'static') {
			$themeParts = $parts;
			array_splice($themeParts, 0, 1, array('static', $this->themeWeb));
			$path = implode(DS, $themeParts);
			$file = MEDIA . $path;
	
			if (isset($this->_found[$path]) || file_exists($file)) {
				return $this->_found[$path] = $file;
			}
		} 
		*/
		
		$path = implode(DS, $parts);
		$file = MEDIA . $path;

		if (isset($this->_found[$path])) {
			return $this->_found[$path];
		}
		if(file_exists($file)) {
			return $this->_found[$path] = $file;
		}
	
		$short = current(array_intersect(array_keys($this->settings['extensions']), $parts));
		extract(pathinfo($file), EXTR_SKIP);

		for ($i = 0; $i < 2; $i++) {
			$file = $i ? $dirname . DS . $filename : $dirname . DS . $basename;

			foreach ($this->settings['extensions'][$short] as $extension) {
				$try = $file . '.' . $extension;
				if (file_exists($try)) {
					return $this->_found[$path] = $try;
				}
			}
		}
		
		return false;
	}
/**
 * Enter description here...
 *
 * @param unknown_type $options
 * @return unknown
 */	
	function _parseParameters($options) {
		$parameters = array();
		$options = Set::filter($options);
		
		foreach ($options as $key => $value) {
			if ($value === true) {
				$value = 'true';
			} else if ($value === false) {
				$value = 'false';
			}
			$parameters[] = sprintf($this->tags['param'], $this->_parseAttributes(array('name' => $key, 'value' => $value)));
		}
		return implode("\n", $parameters);
	}
/**
 * Render a link to a file with extra information 
 *
 * @deprecated 
 * @param string $file A path to a file relative to MEDIA
 * @param array $options Valid options are: -"size" Size in bytes of file, -"name" Name to display
 * @return string
 */
	function nice($file, $options = array()) {
		trigger_error('MediumHelper::nice - Deprecated: Is no longer supported and will be removed in a future version.', E_USER_WARNING);
	}
/**
 * Formats given option/s 
 * Primarly used for parsing parameters for embedding video
 *
 * @param unknown_type $option
 * @return unknown
 * @deprecated 
 */
	function _format($option) {
		trigger_error('MediumHelper::_format - Deprecated: Is no longer supported and will be removed in a future version.', E_USER_WARNING);
	}
}
?>