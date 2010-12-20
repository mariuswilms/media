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
 * This class must be subclass by all media process adapters.
 */
abstract class Media_Process_Adapter {

	/**
	 * Prepare the adapter and load the source.
	 *
	 * @param resource $handle An open handle to use a the source.
	 * @return void
	 */
	abstract public function __construct($handle);

	/**
	 * Writes the internal object to the provided handle.
	 *
	 * @see Media_Process_Generic::store()
	 * @param resource $handle An open handle to use a the source.
	 * @return boolean|integer
	 */
	abstract public function store($handle);

	/**
	 * Converts the internal object to provided MIME-type.
	 *
	 * @param string $mimeType
	 * @return boolean
	 */
	abstract public function convert($mimeType);

	/**
	 * Allows for direct manipulation.
	 *
	 * @param string|integer $key
	 * @param mixed $value
	 * @return boolean `true` on success, `false` if something went wrong.
	 */
	abstract public function passthru($key, $value);
}

?>