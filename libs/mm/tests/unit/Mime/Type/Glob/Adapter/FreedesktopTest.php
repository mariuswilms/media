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

require_once 'Mime/Type/Glob/Adapter/Freedesktop.php';

class Mime_Type_Glob_Adapter_FreedesktopTest extends PHPUnit_Framework_TestCase {

	public $subject;

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/data';
}

	public function testToArray() {
		$file = $this->_files . '/glob_freedesktop_snippet.db';
		$this->subject = new Mime_Type_Glob_Adapter_Freedesktop(compact('file'));

		$result = $this->subject->to('array');
		$this->assertEquals(55, count($result));
	}

	public function testAnalyzeFail() {
		$file = $this->_files . '/glob_freedesktop_snippet.db';
		$this->subject = new Mime_Type_Glob_Adapter_Freedesktop(compact('file'));

		$result = $this->subject->analyze('');
		$this->assertEquals(array(), $result);
	}

	public function testAnalyze() {
		$file = $this->_files . '/glob_freedesktop_snippet.db';
		$this->subject = new Mime_Type_Glob_Adapter_Freedesktop(compact('file'));

		$files = array(
			'file.bz2' => array('application/x-bzip'),
			'file.css' => array('text/css'),
			'file.gif' => array('image/gif'),
			'file.gz' => array('application/x-gzip'),
			'file.class' => array('application/x-java'),
			'file.js' => array('application/javascript'),
			'file.pdf' => array('application/pdf'),
			'file.po' => array('text/x-gettext-translation'),
			'file.pot' => array(
				'application/vnd.ms-powerpoint', 'text/x-gettext-translation-template'
			),
			'file.mo' => array('application/x-gettext-translation'),
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

	public function testAnalyzeReverse() {
		$file = $this->_files . '/glob_freedesktop_snippet.db';
		$this->subject = new Mime_Type_Glob_Adapter_Freedesktop(compact('file'));

		$files = array(
			'application/x-bzip' => array('bz2', 'bz'),
			'text/css' => array('css'),
			'image/gif' => array('gif'),
			'application/x-gzip' => array('gz'),
			'application/x-java' => array('class'),
			'application/javascript' => array('js'),
			'application/pdf' => array('pdf'),
			'text/x-gettext-translation' => array('po'),
			'application/vnd.ms-powerpoint' => array('pot'),
			'text/x-gettext-translation-template' => array('pot'),
			'application/x-gettext-translation' => array('gmo', 'mo'),
			'text/plain' => array('txt'),
			'application/msword' => array('doc'),
			'application/vnd.oasis.opendocument.text' => array('odt'),
			'application/x-tar' => array('tar'),
			'application/xhtml+xml' => array('xhtml'),
			'application/xml' => array('xbl', 'xml')
		);
		foreach ($files as $mimeType => $exts) {
			$result = $this->subject->analyze($mimeType, true);
			$this->assertEquals($exts, $result, "File `{$mimeType}`.");
		}
	}
}

?>