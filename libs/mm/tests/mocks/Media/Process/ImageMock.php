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

require_once 'Media/Process/Image.php';

class Media_Process_ImageMock extends Media_Process_Image {

	public function testBoxify($width, $height, $gravity = 'center') {
		return parent::_boxify($width, $height, $gravity);
	}

	public function testNormalizeDimensions($width, $height, $recalculateBy = 'ratio') {
		return parent::_normalizeDimensions($width, $height, $recalculateBy);
	}
}

?>