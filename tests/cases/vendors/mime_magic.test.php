<?php
/**
 * Mime Magic Test Case File
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
 * @subpackage media.tests.cases.libs
 * @author     David Persson <davidpersson@qeweurope.org>
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.MimeMagic');
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Mime Magic Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs
 */
class MimeMagicTest extends CakeTestCase {
	function setup() {
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function testFormat() {
		$this->assertNull(MimeMagic::format(true));
		$this->assertNull(MimeMagic::format(5));
//		$this->assertNull(MimeMagic::format(array('foo' => 'bar')));
		$this->assertNull(MimeMagic::format('does-not-exist.db'));

		$file = $this->TestData->getFile('text-html.snippet.html');
		$this->assertNull(MimeMagic::format($file));

		$file = $this->TestData->getFile('magic.apache.snippet.db');
		$this->assertEqual(MimeMagic::format($file), 'Apache Module mod_mime_magic');

		$file = $this->TestData->getFile('magic.freedesktop.snippet.db');
		$this->assertEqual(MimeMagic::format($file), 'Freedesktop Shared MIME-info Database');
	}

	function testRead() {
		$fileA = $this->TestData->getFile('magic.apache.snippet.db');
		$fileB = $this->TestData->getFile('magic.freedesktop.snippet.db');

		$Mime =& new MimeMagic($fileA);

		$Mime =& new MimeMagic($fileB);

		$this->expectError();
		$Mime =& new MimeMagic(5);
	}

	function testAnalyzeFail() {
		$file = $this->TestData->getFile('magic.apache.snippet.db');
		$Mime =& new MimeMagic($file);

		$this->assertEqual($Mime->analyze('i-dont-exist.sla'), array());

		$file = $this->TestData->getFile('magic.freedesktop.snippet.db');
		$Mime =& new MimeMagic($file);

		$this->assertEqual($Mime->analyze('i-dont-exist.sla'), array());
	}

	function testShippedAnalyze() {
		$file = APP . 'plugins' . DS . 'media' . DS . 'vendors' . DS . 'magic.db';
		$this->skipUnless(file_exists($file), '%s. No shipped magic db.');
		$Mime =& new MimeMagic($file);

		// $this->assertEqual($Mime->analyze($this->TestData->getFile('3gp.snippet.3gp')), 'video/3gpp');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('ms.avi')), 'video/x-msvideo');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('bzip2.snippet.bz2')), 'application/x-bzip');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('video.snippet.mp4')), 'video/mp4');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('css.snippet.css')), 'text/css');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('flac.snippet.flac')), 'audio/x-flac');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('flash.snippet.swf')), 'application/x-shockwave-flash');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('image-gif.gif')), 'image/gif');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('gzip.snippet.gz')), 'application/x-gzip');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('text-html.snippet.html')), 'text/html');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('audio-mpeg.snippet.mp3')), 'audio/mpeg');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('java.snippet.class')), 'application/x-java');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('javascript.snippet.js')), 'application/javascript');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('image-jpeg.snippet.jpg')), 'image/jpeg');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('video-mpeg.snippet.mpeg')), 'video/mpeg');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('audio-ogg.snippet.ogg')), 'application/ogg');
		$this->assertEqual($Mime->analyze(__FILE__), 'application/x-php');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('application-pdf.pdf')), 'application/pdf');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('image-png.png')), 'image/png');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('postscript.ps')), 'application/postscript');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('po.snippet.po')), 'text/x-gettext-translation');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('text-pot.snippet.pot')), 'text/x-gettext-translation-template');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('mo.snippet.mo')), 'application/x-gettext-translation');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('real-video.snippet.rm')), 'application/vnd.rn-realmedia');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('text-rtf.snippet.rtf')), 'application/rtf');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('text-plain.snippet.txt')), 'text/plain');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('ms-word.snippet.doc')), 'application/msword');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('ms-word.snippet.docx')), 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('opendocument-writer.snippet.odt')), 'application/vnd.oasis.opendocument.text');
		$this->assertEqual($Mime->analyze($this->TestData->getFile('tar.snippet.tar')), 'application/x-tar');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('wave.snippet.wav')), 'audio/x-wav');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('text-xhtml.snippet.xhtml')), 'application/xhtml+xml');
		// $this->assertEqual($Mime->analyze($this->TestData->getFile('xml.snippet.xml')), 'application/xml');
	}
}
?>