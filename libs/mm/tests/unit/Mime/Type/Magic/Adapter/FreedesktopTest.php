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

require_once 'Mime/Type/Magic/Adapter/Freedesktop.php';

class Mime_Type_Magic_Adapter_FreedesktopTest extends PHPUnit_Framework_TestCase {

	public $subject;

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/data';
	}

	public function testToArray() {
		$file = $this->_files . '/magic_freedesktop_snippet.db';
		$this->subject = new Mime_Type_Magic_Adapter_Freedesktop(compact('file'));

		$result = $this->subject->to('array');
		$this->assertEquals(24, count($result));
	}

	public function testAnalyzeFail() {
		$file = $this->_files . '/magic_freedesktop_snippet.db';
		$this->subject = new Mime_Type_Magic_Adapter_Freedesktop(compact('file'));

		$handle = fopen('php://memory', 'rb');
		$result = $this->subject->analyze($handle);
		fclose($handle);
		$this->assertNull($result);
	}
}

?>