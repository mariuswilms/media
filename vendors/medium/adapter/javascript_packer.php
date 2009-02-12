<?php
/**
 * Javascript Packer Medium Adapter File
 *
 * Copyright (c) 2007-2009 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * Javascript Packer Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @link       http://dean.edwards.name/download/#packer
 */
class JavascriptPackerMediumAdapter extends MediumAdapter {

	var $require = array(
							'mimeTypes' => array('application/javascript'),
							'imports' => array(array('type' => 'Vendor', 'name'=> 'JavaScriptPacker', 'file' => 'packer/class.JavaScriptPacker.php')),
							);

	function initialize(&$Medium) {
		if (isset($Medium->contents['raw'])) {
			return true;
		}

		if (!isset($Medium->file)) {
			return false;
		}

		return $Medium->contents['raw'] = file_get_contents($Medium->file);
	}

	function store(&$Medium, $file) {
		return file_put_contents($Medium->contents['raw'], $file);
	}

/**
 * Enter description here...
 *
 * Does not work: prototype* in any compression level
 *
 * @return unknown
 */
	function compress(&$Medium) {
		$Packer = new JavaScriptPacker($Medium->contents['raw']);
		return $Medium->contents['raw'] = $Packer->pack();
	}
}
?>