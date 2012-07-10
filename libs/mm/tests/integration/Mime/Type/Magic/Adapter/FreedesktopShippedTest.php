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

class Mime_Type_Magic_Adapter_FreedesktopShippedTest extends PHPUnit_Framework_TestCase {

	public $subject;

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/data';
	}

	public function testAnalyze() {
		$file = $this->_data . '/magic.db';
		$this->subject = new Mime_Type_Magic_Adapter_Freedesktop(compact('file'));

		$files = array(
			'ms_snippet.avi' => 'video/x-msvideo',
			'image_gif.gif' => 'image/gif',
			'application_pdf.pdf' => 'application/pdf',
			'postscript_snippet.ps' => 'application/postscript',
			'tar_snippet.tar' => 'application/x-tar',
			'wave_snippet.wav' => 'audio/x-wav',
			'3gp_snippet.3gp' => 'video/3gpp',
			'bzip2_snippet.bz2' => 'application/x-bzip', // application/x-bzip2
			'video_snippet.mp4' => 'video/mp4',
			'gzip_snippet.gz' => 'application/x-gzip',
			'text_html_snippet.html' => 'text/html',
			'image_jpeg_snippet.jpg' => 'image/jpeg', // audio/MP4A-LATM
			'video_mpeg_snippet.mpeg' => 'video/mpeg',
			'video_ogg_snippet.ogv' => 'video/x-theora+ogg', // application/ogg
			'audio_ogg_snippet.ogg' => 'audio/x-vorbis+ogg', //application/ogg
			'code_php.php' => 'application/x-php',
			'image_png.png' => 'image/png',
			'text_rtf_snippet.rtf' => 'application/rtf', // text/rtf
			'ms_word_snippet.doc' => 'application/msword', // audio/MP4A-LATM
			'xml_snippet.xml' => 'application/xml', // text/xml

			/* Fail! */
			/*
			'opendocument_writer_snippet.odt' => 'application/vnd.oasis.opendocument.text', // application/zip
			'ms_word_snippet.docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // application/zip
			'audio_mpeg_snippet.mp3' => 'audio/mpeg',
			'text_plain_snippet.txt' => 'text/plain',
			'css_snippet.css' => 'text/css',
			'javascript_snippet.js' => 'application/javascript',
			'text_xhtml_snippet.xhtml' => 'application/xhtml+xml',  // text/xml
			'po_snippet.po' => 'text/x-gettext-translation',
			'text_pot_snippet.pot' => 'text/x-gettext-translation-template',
			'mo_snippet.mo' => 'application/x-gettext-translation',
			*/

			'video_flash_snippet.flv' => 'video/x-flv',
			'audio_snippet.snd' => 'audio/basic',
			'audio_apple_snippet.aiff' => 'audio/x-aiff',
			'flash_snippet.swf' => 'application/x-shockwave-flash',
			'audio_mpeg_snippet.m4a' => 'audio/mp4',
			'audio_musepack_snippet.mpc' => 'audio/x-musepack',
			'video_quicktime_snippet.mov' => 'video/quicktime',
			'video_ms_snippet.wmv' => 'video/x-ms-asf',

			/* Fail! */
			/*
			'audio_snippet.aac' => 'audio/x-aac',
			'audio_ms_snippet.wma' => 'audio/x-ms-asf',
			'flac_snippet.flac' => 'audio/x-flac', // Fails only with freedesktop db
			*/

			/* Fail! No data :( */
			/*
			'java_snippet.class' => 'application/x-java',
			'real_video_snippet.rm' => 'application/vnd.rn-realmedia'
			*/
		);

		foreach ($files as $file => $mimeTypes) {
			$handle = fopen($this->_files . '/' . $file, 'rb');
			$this->assertContains($this->subject->analyze($handle), (array) $mimeTypes, "File `{$file}`.");
			fclose($handle);
		}
	}
}

?>