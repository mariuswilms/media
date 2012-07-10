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

require_once 'Mime/Type/Magic/Adapter.php';

/**
 * This is a very simplistic adapter and can be used for building up your own
 * magic database in memory.
 */
class Mime_Type_Magic_Adapter_Memory extends Mime_Type_Magic_Adapter {

	public function __construct($config) {
		foreach ($config['items'] as $item) {
			$this->_register($item);
		}
	}

	public function analyze($file) {
		return $this->_test($handle, $this->_items);
	}

	public function to($type) {
		return $this->_to($type);
	}

	public function register($item) {
		return $this->_register($item);
	}
}

?>