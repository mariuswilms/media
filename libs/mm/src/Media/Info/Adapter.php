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

/**
 * This class must be subclass by all media info adapters.
 */
abstract class Media_Info_Adapter {

	/**
	 * Prepare the adapter and load the source.
	 *
	 * @param string $file Absolute path to the source file.
	 * @return void
	 */
	abstract public function __construct($file);

	/**
	 * Retrieves all possible information keyed by name.
	 *
	 * @return array
	 */
	abstract public function all();

	/**
	 * Retrieves information for a given (field) name.
	 *
	 * @param string $name Retrieve data just for the given name.
	 * @return mixed A scalar value.
	 */
	abstract public function get($name);
}

?>