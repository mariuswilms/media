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

require_once 'PHPUnit/Framework.php';
require_once 'Mime/Type.php';

class Mime_TypeTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(__FILE__))) . '/resources';
		$this->_data = dirname(dirname(dirname(dirname(__FILE__)))) .'/data';

		Mime_Type::config('Magic', array(
			'adapter' => 'Freedesktop',
			'file' => $this->_data . '/magic.db'
		));
		Mime_Type::config('Glob', array(
			'adapter' => 'Freedesktop',
			'file' => $this->_data . '/glob.db'
		));
	}

	protected function tearDown() {
		Mime_Type::reset();
	}

	public function testSimplify() {
		$this->assertEquals(
			'application/pdf',
			Mime_Type::simplify('application/x-pdf')
		);
		$this->assertEquals(
			'inode/directory',
			Mime_Type::simplify('x-inode/x-directory')
		);
		$this->assertEquals(
			'application/octet-stream',
			Mime_Type::simplify('application/octet-stream; encoding=compress')
		);
		$this->assertEquals(
			'application/test',
			Mime_Type::simplify('application/x-test; encoding=compress')
		);
		$this->assertEquals(
			'text/plain',
			Mime_Type::simplify('text/plain; charset=iso-8859-1')
		);
		$this->assertEquals(
			'text/plain',
			Mime_Type::simplify('text/plain charset=us-ascii')
		);
	}

	public function testGuessTypeFile() {
		$files = array(
			'image_gif.gif' => 'image/gif',
			'application_pdf.pdf' => 'application/pdf',
			'postscript_snippet.ps' => 'application/postscript',
			'tar_snippet.tar' => 'application/x-tar',
			'wave_snippet.wav' => 'audio/x-wav',
			'video_snippet.mp4' => 'video/mp4',
			'text_html_snippet.html' => 'text/html',
			'code_php.php' => 'application/x-php',
			'image_png.png' => 'image/png',
			'video_flash_snippet.flv' => 'video/x-flv',
			'audio_apple_snippet.aiff' => 'audio/x-aiff',
			'flash_snippet.swf' => 'application/x-shockwave-flash',
			'audio_mpeg_snippet.m4a' => 'audio/mp4',
		);
		foreach ($files as $file => $mimeType) {
			$this->assertEquals(
				$mimeType,
				Mime_Type::guessType("{$this->_files}/{$file}"),
				"File `{$file}`."
			);
		}
	}

	public function testGuessTypeParanoid() {
		$this->assertEquals(
			'image/png',
			Mime_Type::guessType("{$this->_files}/image_png.jpg", array('paranoid' => true))
		);
		$this->assertEquals(
			'image/jpeg',
			Mime_Type::guessType("{$this->_files}/image_png.jpg", array('paranoid' => false))
		);
	}

	public function testGuessTypeFallback() {
		$files = array(
			'generic_binary' => 'application/octet-stream',
			'generic_text' => 'text/plain'
		);
		foreach ($files as $file => $mimeType) {
			$this->assertEquals(
				$mimeType,
				Mime_Type::guessType("{$this->_files}/{$file}"),
				"File `{$file}`."
			);
		}
	}

	public function testGuessExtensionFail() {
		$this->assertNull(Mime_Type::guessExtension('i-m-not-a-mime-type'));
		$this->assertNull(Mime_Type::guessExtension('/tmp/i-do-not-exist'));
	}

	public function testGuessExtensionFilename() {
		$this->assertEquals('txt', Mime_Type::guessExtension('/tmp/i-do-not-exist.txt'));
	}

	public function testGuessExtensionMimeType() {
		$this->assertEquals('jpg', Mime_Type::guessExtension('image/jpeg'));
		$this->assertEquals('xhtml', Mime_Type::guessExtension('application/xhtml+xml'));
		$this->assertEquals('bin', Mime_Type::guessExtension('application/octet-stream'));
		$this->assertEquals('wav', Mime_Type::guessExtension('audio/x-wav'));
	}

	public function testGuessNameMimeType() {
		$map = array(
			'video/webm' => 'video',
			'video/x-msvideo' => 'video',
			'application/octet-stream' => 'generic',
			'text/unknown' => 'generic',
			'image/jpeg' => 'image',
			'image/tiff' => 'image'
		);
		foreach ($map as $mimeType => $name) {
			$this->assertEquals($name, Mime_Type::guessName($mimeType), "MIME type `{$mimeType}`.");
		}
	}

	public function testGuessNameFilename() {
		$map = array(
			'test' => 'generic',
			'test.jpg' => 'image',
			'test.tif' => 'image'
		);
		foreach ($map as $mimeType => $name) {
			$this->assertEquals($name, Mime_Type::guessName($mimeType), "MIME type `{$mimeType}`.");
		}
	}

	public function testGuessNameFile() {
		$map = array(
			'video_flash_snippet.flv' => 'video',
			'audio_ogg_snippet.ogg' => 'audio',
			'xml_snippet.xml' => 'generic',
			'image_png.png' => 'image',
		);
		foreach ($map as $file => $name) {
			$this->assertEquals(
				$name,
				Mime_Type::guessName($this->_files . '/' . $file),
				"File `{$file}`."
			);
		}
	}
}

?>