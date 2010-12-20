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

require_once 'Mime/Type/Magic/Adapter/Apache.php';

class Mime_Type_Magic_Adapter_ApacheTest extends PHPUnit_Framework_TestCase {

	public $subject;

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/data';
	}

	public function testToArray() {
		$file = $this->_files . '/magic_apache_snippet.db';
		$this->subject = new Mime_Type_Magic_Adapter_Apache(compact('file'));

		$result = $this->subject->to('array');
		$this->assertEquals(38, count($result));
	}

	public function testAnalyzeFail() {
		$file = $this->_files . '/magic_apache_snippet.db';
		$this->subject = new Mime_Type_Magic_Adapter_Apache(compact('file'));

		$handle = fopen('php://memory', 'rb');
		$result = $this->subject->analyze($handle);
		fclose($handle);
		$this->assertNull($result);
	}

	public function testIntegrationShipped() {
		$file = $this->_files . '/magic_apache_snippet.db';
		$this->subject = new Mime_Type_Magic_Adapter_Apache(compact('file'));

		/* @todo Commented fail but should are present in data */
		$files = array(
			'image_gif.gif' => 'image/gif',
			'application_pdf.pdf' => 'application/pdf',
			'postscript_snippet.ps' => 'application/postscript',
			'wave_snippet.wav' => 'audio/x-wav',
			// 'gzip_snippet.gz' => 'application/x-gzip',
			'text_html_snippet.html' => 'text/html',
			'image_jpeg_snippet.jpg' => 'image/jpeg',
			'text_rtf_snippet.rtf' => 'application/rtf',
			// 'ms_word_snippet.doc' => 'application/msword',
			// 'audio_mpeg_snippet.mp3' => 'audio/mpeg',
			// 'text_plain_snippet.txt' => 'text/plain'
		);

		foreach ($files as $file => $mimeTypes) {
			$handle = fopen($this->_files . '/' . $file, 'rb');
			$this->assertContains($this->subject->analyze($handle), (array) $mimeTypes, "File `{$file}`.");
			fclose($handle);
		}
	}
}

?>