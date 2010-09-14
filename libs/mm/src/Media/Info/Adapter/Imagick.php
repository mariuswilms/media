<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright  2007-2010 David Persson <nperson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/mm
 */

require_once 'Media/Info/Adapter.php';

/**
 * This media info adapter allows for interfacing with ImageMagick through
 * the `imagick` pecl extension (which must be loaded in order to use this adapter).
 *
 * @link       http://php.net/imagick
 * @link       http://www.imagemagick.org
 */
class Media_Info_Adapter_Imagick extends Media_Info_Adapter {

	protected $_object;

	protected $_map = array(
		'width' => 'getImageWidth',
		'height' => 'getImageHeight'
	);

	public function __construct($file) {
		$this->_object = new Imagick($file);
	}

	public function __destruct() {
		if ($this->_object) {
			$this->_object->clear();
		}
	}

	public function all() {
		$results = array();

		foreach (array_keys($this->_map) as $name) {
			$results[$name] = $this->get($name);
		}
		return $results;
	}

	public function get($name) {
		if (isset($this->_map[$name])) {
			return $this->_object->{$this->_map[$name]}();
		}
	}
}

?>