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

require_once 'Media/Info/Generic.php';

/**
 * `Media_Info_Document` handles document files like PDFs.
 * Most methods are simply inherited from the generic media type wile some overlap with
 * those defined in `Media_Info_Image`.
 *
 * @see Media_Info_Image
 */
class Media_Info_Document extends Media_Info_Generic {

	/**
	 * Determines the ratio.
	 *
	 * @return float
	 */
	public function ratio() {
		return $this->get('width') / $this->get('height');
	}

	/**
	 * Determines the known ratio.
	 *
	 * @return string
	 */
	public function knownRatio() {
		return $this->_knownRatio();
	}
}

?>