<?php
/**
 * Mime Type Test Case File
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
 * @subpackage media.tests.cases.libs
 * @author     David Persson <davidpersson@gmx.de>
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.MimeType');
require_once dirname(dirname(dirname(__FILE__))) . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Mime Type Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs
 */
class MimeTypeTest extends CakeTestCase {
	function setUp() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function testSimplify() {
		MimeType::config('magic', array('engine' => false));
		MimeType::config('glob', array('engine' => false));

		$this->assertEqual(MimeType::simplify('application/x-pdf'), 'application/pdf');
		$this->assertEqual(MimeType::simplify('x-inode/x-directory'), 'inode/directory');
		$this->assertEqual(MimeType::simplify('application/octet-stream; encoding=compress'), 'application/octet-stream');
		$this->assertEqual(MimeType::simplify('application/x-test; encoding=compress'), 'application/test');
		$this->assertEqual(MimeType::simplify('text/plain; charset=iso-8859-1'), 'text/plain');
		$this->assertEqual(MimeType::simplify('text/plain charset=us-ascii'), 'text/plain');
	}

	function testGuessTypeFileinfoShippedGlob() {
		$this->skipUnless(extension_loaded('fileinfo'), '%s. Fileinfo extension not loaded.');

		MimeType::config('magic', array(
			'engine' => 'fileinfo'
		));
		MimeType::config('glob', array(
			'engine' => 'core',
			'file' => dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'vendors' . DS . 'mime_glob.db'
		));

		/* Some tests have been commented (if not otherwise stated) because of missing support the extension */

		$this->assertEqual(MimeType::guessType($this->TestData->getFile('3gp.snippet.3gp')), 'video/3gpp');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('ms.avi')), 'video/x-msvideo');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('bzip2.snippet.bz2')), 'application/x-bzip');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('video.snippet.mp4')), 'video/mp4');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('css.snippet.css')), 'text/css');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('flac.snippet.flac')), 'audio/x-flac');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('flash.snippet.swf')), 'application/x-shockwave-flash');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('image-gif.gif')), 'image/gif');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('gzip.snippet.gz')), 'application/x-gzip');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('text-html.snippet.html')), 'text/html');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('audio-mpeg.snippet.mp3')), 'audio/mpeg');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('java.snippet.class')), 'application/x-java');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('javascript.snippet.js')), 'application/javascript');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('image-jpeg.snippet.jpg')), 'image/jpeg');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('video-mpeg.snippet.mpeg')), 'video/mpeg');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('audio-ogg.snippet.ogg')), 'audio/ogg');
		/* Fails application<->text */
		//$this->assertEqual(MimeType::guessType(__FILE__), 'application/x-php');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('application-pdf.pdf')), 'application/pdf');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('image-png.png')), 'image/png');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('postscript.ps')), 'application/postscript');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('po.snippet.po')), 'text/x-gettext-translation');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('text-pot.snippet.pot')), 'text/x-gettext-translation-template');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('mo.snippet.mo')), 'application/x-gettext-translation');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('real-video.snippet.rm')), 'application/vnd.rn-realmedia');
		/* Fails application<->text */
		//$this->assertEqual(MimeType::guessType($this->TestData->getFile('text-rtf.snippet.rtf')), 'application/rtf');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('text-plain.snippet.txt')), 'text/plain');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('ms-word.snippet.doc')), 'application/msword');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('ms-word.snippet.docx')), 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('opendocument-writer.snippet.odt')), 'application/vnd.oasis.opendocument.text');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('tar.snippet.tar')), 'application/x-tar');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('wave.snippet.wav')), 'audio/x-wav');
		/* Fails application<->text */
		//$this->assertEqual(MimeType::guessType($this->TestData->getFile('text-xhtml.snippet.html')), 'application/xhtml+xml');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('xml.snippet.xml')), 'application/xml');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('generic-binary')), 'application/octet-stream');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('generic-text')), 'text/plain');
/* Start added tests */
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('video-flash.snippet.flv')), 'video/x-flv');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('audio.snippet.snd')), 'audio/basic');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('audio-apple.snippet.aiff')), 'audio/x-aiff');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('flash.snippet.swf')), 'application/x-shockwave-flash');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('audio-mpeg.snippet.m4a')), 'audio/mpeg');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('audio-musepack.snippet.mpc')), 'audio/x-musepack');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('video-quicktime.snippet.mov')), 'video/quicktime');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('video-ms.snippet.wmv')), 'video/x-ms-wmv');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('audio.snippet.aac')), 'audio/x-aac');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('audio-ms.snippet.wma')), 'audio/x-ms-wma');
		$this->assertEqual(MimeType::guessType($this->TestData->getFile('flac.snippet.flac')), 'audio/x-flac');//Fail only with freedesktop db!
/* End added tests */
		$this->assertEqual(MimeType::guessType('file.3gp'), 'video/3gpp');
		$this->assertEqual(MimeType::guessType('file.avi'), 'video/x-msvideo');
		$this->assertEqual(MimeType::guessType('file.bz2'), 'application/x-bzip');
		$this->assertEqual(MimeType::guessType('file.mp4'), 'video/mp4');
		$this->assertEqual(MimeType::guessType('file.css'), 'text/css');
		$this->assertEqual(MimeType::guessType('file.flac'), 'audio/x-flac');
		$this->assertEqual(MimeType::guessType('file.swf'), 'application/x-shockwave-flash');
		$this->assertEqual(MimeType::guessType('file.gif'), 'image/gif');
		$this->assertEqual(MimeType::guessType('file.gz'), 'application/x-gzip');
		$this->assertEqual(MimeType::guessType('file.html'), 'text/html');
		$this->assertEqual(MimeType::guessType('file.mp3'), 'audio/mpeg');
		$this->assertEqual(MimeType::guessType('file.class'), 'application/x-java');
		$this->assertEqual(MimeType::guessType('file.js'), 'application/javascript');
		$this->assertEqual(MimeType::guessType('file.jpg'), 'image/jpeg');
		$this->assertEqual(MimeType::guessType('file.mpeg'), 'video/mpeg');
		$this->assertEqual(MimeType::guessType('file.ogg'), 'audio/ogg');
		/* Fails application<->text */
		//$this->assertEqual(MimeType::guessType('file.php'), 'application/x-php');
		$this->assertEqual(MimeType::guessType('file.pdf'), 'application/pdf');
		$this->assertEqual(MimeType::guessType('file.png'), 'image/png');
		$this->assertEqual(MimeType::guessType('file.ps'), 'application/postscript');
		$this->assertEqual(MimeType::guessType('file.po'), 'text/x-gettext-translation');
		$this->assertEqual(MimeType::guessType('file.pot'), 'text/x-gettext-translation-template');
		$this->assertEqual(MimeType::guessType('file.mo'), 'application/x-gettext-translation');
		$this->assertEqual(MimeType::guessType('file.rm'), 'application/vnd.rn-realmedia');
		/* Fails application<->text */
		//$this->assertEqual(MimeType::guessType('file.rtf'), 'application/rtf');
		$this->assertEqual(MimeType::guessType('file.txt'), 'text/plain');
		$this->assertEqual(MimeType::guessType('file.doc'), 'application/msword');
		$this->assertEqual(MimeType::guessType('file.docx'), 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
		$this->assertEqual(MimeType::guessType('file.odt'), 'application/vnd.oasis.opendocument.text');
		$this->assertEqual(MimeType::guessType('file.tar'), 'application/x-tar');
		$this->assertEqual(MimeType::guessType('file.wav'), 'audio/x-wav');
		$this->assertEqual(MimeType::guessType('file.xhtml'), 'application/xhtml+xml');
		$this->assertEqual(MimeType::guessType('file.xml'), 'application/xml');
	}

	function testGuessExtension() {
		MimeType::config('magic');
		MimeType::config('glob');

		$this->assertFalse(MimeType::guessExtension('i-m-not-a-mime-type'));
		$this->assertFalse(MimeType::guessExtension('/tmp/i-m-do-not-exist.txt'));

		$this->assertEqual(MimeType::guessExtension('video/3gpp'), '3gp');
		$this->assertEqual(MimeType::guessExtension('video/x-msvideo'), 'avi');
		$this->assertEqual(MimeType::guessExtension('application/x-bzip'), 'bz2');
		$this->assertEqual(MimeType::guessExtension('video/mp4'), 'mp4');
		$this->assertEqual(MimeType::guessExtension('text/css'), 'css');
		$this->assertEqual(MimeType::guessExtension('audio/x-flac'), 'flac');
		$this->assertEqual(MimeType::guessExtension('application/x-shockwave-flash'), 'swf');
		$this->assertEqual(MimeType::guessExtension('image/gif'), 'gif');
		$this->assertEqual(MimeType::guessExtension('application/x-gzip'), 'gz');
		$this->assertEqual(MimeType::guessExtension('text/html'), 'html');
		$this->assertEqual(MimeType::guessExtension('audio/mpeg'), 'mp3');
		$this->assertEqual(MimeType::guessExtension('application/x-java'), 'class');
		$this->assertEqual(MimeType::guessExtension('application/javascript'), 'js');
		$this->assertEqual(MimeType::guessExtension('image/jpeg'), 'jpg');
		$this->assertEqual(MimeType::guessExtension('video/mpeg'), 'mpeg');
		$this->assertEqual(MimeType::guessExtension('application/ogg'), 'ogx');
		/* Fails application<->text */
		// $this->assertEqual(MimeType::guessExtension('application/x-php'), 'php');
		$this->assertEqual(MimeType::guessExtension('application/pdf'), 'pdf');
		$this->assertEqual(MimeType::guessExtension('image/png'), 'png');
		$this->assertEqual(MimeType::guessExtension('application/postscript'), 'ps');
		$this->assertEqual(MimeType::guessExtension('text/x-gettext-translation'), 'po');
		$this->assertEqual(MimeType::guessExtension('text/x-gettext-translation-template'), 'pot');
		$this->assertEqual(MimeType::guessExtension('application/x-gettext-translation'), 'mo');
		$this->assertEqual(MimeType::guessExtension('application/vnd.rn-realmedia'), 'rm');
		/* Fails application<->text */
		// $this->assertEqual(MimeType::guessExtension('application/rtf'), 'rtf');
		$this->assertEqual(MimeType::guessExtension('text/plain'), 'txt');
		$this->assertEqual(MimeType::guessExtension('application/msword'), 'doc');
		$this->assertEqual(MimeType::guessExtension('application/vnd.openxmlformats-officedocument.wordprocessingml.document'), 'docx');
		$this->assertEqual(MimeType::guessExtension('application/vnd.oasis.opendocument.text'), 'odt');
		$this->assertEqual(MimeType::guessExtension('application/x-tar'), 'tar');
		$this->assertEqual(MimeType::guessExtension('audio/x-wav'), 'wav');
		$this->assertEqual(MimeType::guessExtension('application/xhtml+xml'), 'xhtml');
		$this->assertEqual(MimeType::guessExtension('application/xml'), 'xml');
	}

	function testGuessTypeParanoid() {
		$this->skipUnless(extension_loaded('fileinfo'), '%s. Fileinfo extension not loaded.');

		MimeType::config('magic', array('engine' => 'fileinfo'));
		MimeType::config('glob', array('engine' => 'core', 'file' => dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'vendors' . DS . 'mime_glob.db'));

		$file = $this->TestData->getFile(array('image-png.png' => TMP . 'image-png.jpg'));
		$this->assertEqual(MimeType::guessType($file, array('paranoid' => true)), 'image/png');
		$this->assertEqual(MimeType::guessType($file, array('paranoid' => false)), 'image/jpeg');
	}
}
?>