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
 * Can parse files containing one huge PHP array.
 *
 * Files must look like this:
 * {{{
 * <?php return array(
 *     item0,
 *     item1,
 *     item2,
 *     item3,
 * ); ?>
 * }}}
 */
class Mime_Type_Magic_Adapter_Php extends Mime_Type_Magic_Adapter {

	public function __construct($config) {
		foreach (require $config['file'] as $item) {
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