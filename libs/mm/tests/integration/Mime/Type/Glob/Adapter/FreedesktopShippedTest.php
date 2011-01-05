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

class Mime_Type_Glob_Adapter_FreedesktopShippedTest extends PHPUnit_Framework_TestCase {

	public $subject;

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/data';

		$file = $this->_data . '/glob.db';
		$this->subject = new Mime_Type_Glob_Adapter_Freedesktop(compact('file'));
	}

	public function testAnalyze() {
		$files = array(
			'file.avi' => 'video/x-msvideo',
			'file.gif' => 'image/gif',
			'file.pdf' => 'application/pdf',
			'file.ps' => 'application/postscript',
			'file.tar' => 'application/x-tar',
			'file.wav' => 'audio/x-wav',
			'file.3gp' => 'video/3gpp',
			'file.bz2' => 'application/x-bzip',
			'file.mp4' => 'video/mp4',
			'file.gz' => 'application/x-gzip',
			'file.html' => 'text/html',
			'file.jpg' => 'image/jpeg',
			'file.mpeg' => 'video/mpeg',
			'file.ogv' => 'video/ogg',
			'file.ogg' => 'audio/x-vorbis+ogg',
			'file.php' => 'application/x-php',
			'file.png' => 'image/png',
			'file.rtf' => 'application/rtf',
			'file.doc' => 'application/msword',
			'file.xml' => 'application/xml',
			'file.odt' => 'application/vnd.oasis.opendocument.text',
			'file.mp3' => 'audio/mpeg',
			'file.txt' => 'text/plain',
			'file.css' => 'text/css',
			'file.js' => 'application/javascript',
			'file.xhtml' => 'application/xhtml+xml',
			'file.po' => 'text/x-gettext-translation',
			'file.pot' => 'text/x-gettext-translation-template',
			'file.mo' => 'application/x-gettext-translation',
			'file.flv' => 'video/x-flv',
			'file.snd' => 'audio/basic',
			'file.aiff' => 'audio/x-aiff',
			'file.swf' => 'application/x-shockwave-flash',
			'file.m4a' => 'audio/mp4',
			'file.mpc' => 'audio/x-musepack',
			'file.wav' => 'audio/x-wav',
			'file.mov' => 'video/quicktime',
			'file.flac' => 'audio/x-flac',
			'file.class' => 'application/x-java',
			'file.rm' => 'application/vnd.rn-realmedia'
		);
		foreach ($files as $file => $mimeTypes) {
			$result = $this->subject->analyze($file);
			$common = array_values(array_intersect($result, (array) $mimeTypes));

			$this->assertEquals((array) $mimeTypes, $common, "File `{$file}`.");
		}
	}

	public function testAnalyzeReverse() {
		$files = array(
			'application/x-bzip' => array('bz2', 'bz'),
			'text/css' => array('css', 'CSSL'),
			'image/gif' => array('gif'),
			'application/x-gzip' => array('gz'),
			'application/x-java' => array('class'),
			'application/javascript' => array('js'),
			'application/pdf' => array('pdf'),
			'text/x-gettext-translation' => array('po'),
			'application/vnd.ms-powerpoint' => array('pot', 'pps', 'ppt', 'ppz'),
			'text/x-gettext-translation-template' => array('pot'),
			'application/x-gettext-translation' => array('mo', 'gmo'),
			'text/plain' => array('asc', 'txt'),
			'application/msword' => array('doc'),
			'application/vnd.oasis.opendocument.text' => array('odt'),
			'application/x-tar' => array('tar', 'gtar'),
			'application/xhtml+xml' => array('xhtml'),
			'application/xml' => array('xslt', 'xbl', 'xml', 'xsl'),
			'audio/x-wav' => array('wav'),
			'video/ogg' => array('ogv'),
			'video/x-theora+ogg' => array('ogg')
		);
		foreach ($files as $mimeType => $exts) {
			$result = $this->subject->analyze($mimeType, true);
			$this->assertEquals($exts, $result, "File `{$mimeType}`.");
		}
	}
}

?>