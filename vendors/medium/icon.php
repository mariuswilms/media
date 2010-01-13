<?php
/**
 * Icon Medium File
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
 * @subpackage media.libs.medium
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
if (!class_exists('ImageMedium')) {
	App::import('Vendor', 'Media.ImageMedium', array('file' => 'medium' . DS . 'image.php'));
}
/**
 * Icon Medium Class
 *
 * @package    media
 * @subpackage media.libs.medium
 */
class IconMedium extends ImageMedium {
}
?>