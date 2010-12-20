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

require_once 'Mime/Type/Glob/Adapter/Apache.php';

class Mime_Type_Glob_Adapter_ApacheTest extends PHPUnit_Framework_TestCase {

	public $subject;

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/data';
	}

	public function testToArray() {
		$file = $this->_files . '/glob_apache_snippet.db';
		$this->subject = new Mime_Type_Glob_Adapter_Apache(compact('file'));

		$result = $this->subject->to('array');
		$this->assertEquals(390, count($result));
	}

	public function testAnalyzeFail() {
		$file = $this->_files . '/glob_apache_snippet.db';
		$this->subject = new Mime_Type_Glob_Adapter_Apache(compact('file'));

		$result = $this->subject->analyze('');
		$this->assertEquals(array(), $result);
	}

	public function testAnalyze() {
		$file = $this->_files . '/glob_apache_snippet.db';
		$this->subject = new Mime_Type_Glob_Adapter_Apache(compact('file'));

		$files = array(
			'file.css' => array('text/css'),
			'file.gif' => array('image/gif'),
			'file.class' => array('application/java-vm'),
			'file.js' => array('application/x-javascript'),
			'file.pdf' => array('application/pdf'),
			'file.txt' => array('text/plain'),
			'file.doc' => array('application/msword'),
			'file.odt' => array('application/vnd.oasis.opendocument.text'),
			'file.tar' => array('application/x-tar'),
			'file.xhtml' => array('application/xhtml+xml'),
			'file.xml' => array('application/xml')
		);
		foreach ($files as $file => $mimeTypes) {
			$this->assertEquals($mimeTypes, $this->subject->analyze($file), "File `{$file}`.");
		}
	}
}

?>