<?php
/**
 * Mime Glob Test Case File
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
App::import('Vendor', 'Media.MimeGlob');
require_once dirname(dirname(dirname(__FILE__))) . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Mime Glob Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs
 */
class MimeGlobTest extends CakeTestCase {
	function setUp() {
		Configure::write('Cache.disable', true);
		$this->TestData = new TestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}

	function testFormat() {
		$this->assertNull(MimeGlob::format(true));
		$this->assertNull(MimeGlob::format(5));
//		$this->assertNull(MimeGlob::format(array('foo' => 'bar')));
		$this->assertNull(MimeGlob::format('does-not-exist.db'));

		$file = $this->TestData->getFile('glob.apache.snippet.db');
		$this->assertEqual(MimeGlob::format($file), 'Apache Module mod_mime');

		$file = $this->TestData->getFile('glob.freedesktop.snippet.db');
		$this->assertEqual(MimeGlob::format($file), 'Freedesktop Shared MIME-info Database');
	}

	function testRead() {
		$fileA = $this->TestData->getFile('glob.apache.snippet.db');
		$fileB = $this->TestData->getFile('glob.freedesktop.snippet.db');

		$Mime = new MimeGlob($fileA);

		$Mime = new MimeGlob($fileB);

		$this->expectError();
		$Mime = new MimeGlob(5);
	}

	function testToArrayAndRead() {
		$file = $this->TestData->getFile('glob.apache.snippet.db');

		$Mime = new MimeGlob($file);
		$expected = $Mime->toArray();
		$Mime = new MimeGlob($expected);
		$result = $Mime->toArray();

		$this->assertEqual($result, $expected);
	}

	function testAnalyzeFail() {
		$file = $this->TestData->getFile('glob.apache.snippet.db');
		$Mime = new MimeGlob($file);

		$this->assertEqual($Mime->analyze('i-dont-exist.sla'), array());

		$file = $this->TestData->getFile('glob.freedesktop.snippet.db');
		$Mime = new MimeGlob($file);
	}

	function testApacheAnalyze() {
		$file = $this->TestData->getFile('glob.apache.snippet.db');
		$Mime = new MimeGlob($file);

		$this->assertEqual($Mime->analyze('file.3gp'), array());
		$this->assertEqual($Mime->analyze('file.avi'), array());
		$this->assertEqual($Mime->analyze('file.bz2'), array());
		$this->assertEqual($Mime->analyze('file.mp4'), array());
		$this->assertEqual($Mime->analyze('file.css'), array('text/css'));
		$this->assertEqual($Mime->analyze('file.flac'), array('application/x-flac'));
		$this->assertEqual($Mime->analyze('file.swf'), array('application/x-shockwave-flash'));
		$this->assertEqual($Mime->analyze('file.gif'), array('image/gif'));
		$this->assertEqual($Mime->analyze('file.gz'), array());
		$this->assertEqual($Mime->analyze('file.html'), array('text/html'));
		$this->assertEqual($Mime->analyze('file.mp3'), array('audio/mpeg'));
		$this->assertEqual($Mime->analyze('file.class'), array('application/java-vm'));
		$this->assertEqual($Mime->analyze('file.js'), array('application/x-javascript'));
		$this->assertEqual($Mime->analyze('file.jpg'), array('image/jpeg'));
		$this->assertEqual($Mime->analyze('file.mpeg'), array());
		$this->assertEqual($Mime->analyze('file.ogg'), array('application/ogg'));
		$this->assertEqual($Mime->analyze('file.php'), array());
		$this->assertEqual($Mime->analyze('file.pdf'), array('application/pdf'));
		$this->assertEqual($Mime->analyze('file.png'), array('image/png'));
		$this->assertEqual($Mime->analyze('file.ps'), array('application/postscript'));
		$this->assertEqual($Mime->analyze('file.po'), array());
		$this->assertEqual($Mime->analyze('file.pot'), array('text/plain'));
		$this->assertEqual($Mime->analyze('file.mo'), array());
		$this->assertEqual($Mime->analyze('file.rm'), array('audio/x-pn-realaudio'));
		$this->assertEqual($Mime->analyze('file.rtf'), array('text/rtf'));
		$this->assertEqual($Mime->analyze('file.txt'), array('text/plain'));
		$this->assertEqual($Mime->analyze('file.doc'), array('application/msword'));
		$this->assertEqual($Mime->analyze('file.docx'), array());
		$this->assertEqual($Mime->analyze('file.odt'), array('application/vnd.oasis.opendocument.text'));
		$this->assertEqual($Mime->analyze('file.tar'), array('application/x-tar'));
		$this->assertEqual($Mime->analyze('file.wav'), array('audio/x-wav'));
		$this->assertEqual($Mime->analyze('file.xhtml'), array('application/xhtml+xml'));
		$this->assertEqual($Mime->analyze('file.xml'), array('application/xml'));
	}

	function testApacheAnalyzeReverse() {
		$file = $this->TestData->getFile('glob.apache.snippet.db');
		$Mime = new MimeGlob($file);

		$this->assertEqual($Mime->analyze('text/plain', true), array('asc', 'txt', 'text', 'diff', 'pot'));
		$this->assertEqual($Mime->analyze('application/pdf', true), array('pdf'));
	}

	function testFreedesktopAnalyze() {
		$file = $this->TestData->getFile('glob.freedesktop.snippet.db');
		$Mime = new MimeGlob($file);

		$this->assertEqual($Mime->analyze('file.3gp'), array());
		$this->assertEqual($Mime->analyze('file.avi'), array());
		$this->assertEqual($Mime->analyze('file.bz2'), array('application/x-bzip'));
		$this->assertEqual($Mime->analyze('file.mp4'), array());
		$this->assertEqual($Mime->analyze('file.css'), array('text/css'));
		$this->assertEqual($Mime->analyze('file.flac'), array());
		$this->assertEqual($Mime->analyze('file.swf'), array());
		$this->assertEqual($Mime->analyze('file.gif'), array('image/gif'));
		$this->assertEqual($Mime->analyze('file.gz'), array('application/x-gzip'));
		$this->assertEqual($Mime->analyze('file.html'), array());
		$this->assertEqual($Mime->analyze('file.mp3'), array());
		$this->assertEqual($Mime->analyze('file.class'), array('application/x-java'));
		$this->assertEqual($Mime->analyze('file.js'), array('application/javascript'));
		$this->assertEqual($Mime->analyze('file.jpg'), array());
		$this->assertEqual($Mime->analyze('file.mpeg'), array());
		$this->assertEqual($Mime->analyze('file.ogg'), array());
		$this->assertEqual($Mime->analyze('file.php'), array());
		$this->assertEqual($Mime->analyze('file.pdf'), array('application/pdf'));
		$this->assertEqual($Mime->analyze('file.png'), array());
		$this->assertEqual($Mime->analyze('file.ps'), array());
		$this->assertEqual($Mime->analyze('file.po'), array('text/x-gettext-translation'));
		$this->assertEqual($Mime->analyze('file.pot'), array('application/vnd.ms-powerpoint','text/x-gettext-translation-template'));
		$this->assertEqual($Mime->analyze('file.mo'), array('application/x-gettext-translation'));
		$this->assertEqual($Mime->analyze('file.rm'), array());
		$this->assertEqual($Mime->analyze('file.rtf'), array('application/rtf'));
		$this->assertEqual($Mime->analyze('file.txt'), array('text/plain'));
		$this->assertEqual($Mime->analyze('file.doc'), array('application/msword'));
		$this->assertEqual($Mime->analyze('file.docx'), array());
		$this->assertEqual($Mime->analyze('file.odt'), array('application/vnd.oasis.opendocument.text'));
		$this->assertEqual($Mime->analyze('file.tar'), array('application/x-tar'));
		$this->assertEqual($Mime->analyze('file.wav'), array());
		$this->assertEqual($Mime->analyze('file.xhtml'), array('application/xhtml+xml'));
		$this->assertEqual($Mime->analyze('file.xml'), array('application/xml'));
	}

	function testShippedAnalyze() {
		$file = dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'vendors' . DS . 'mime_glob.db';
		$skip = $this->skipIf(!file_exists($file), '%s. No shipped glob db.');

		if ($skip) { /* Skipping does not silence the error */
			$this->expectError();
		}
		$Mime = new MimeGlob($file);

		$this->assertEqual($Mime->analyze('file.3gp'), array('video/3gpp'));
		$this->assertEqual($Mime->analyze('file.avi'), array('video/x-msvideo'));
		$this->assertEqual($Mime->analyze('file.bz2'), array('application/x-bzip'));
		$this->assertEqual($Mime->analyze('file.mp4'), array('video/mp4'));
		$this->assertEqual($Mime->analyze('file.css'), array('text/css'));
		$this->assertEqual($Mime->analyze('file.flac'), array('audio/x-flac'));
		$this->assertEqual($Mime->analyze('file.swf'), array('application/x-shockwave-flash'));
		$this->assertEqual($Mime->analyze('file.gif'), array('image/gif'));
		$this->assertEqual($Mime->analyze('file.gz'), array('application/x-gzip'));
		$this->assertEqual($Mime->analyze('file.html'), array('text/html'));
		$this->assertEqual($Mime->analyze('file.mp3'), array('audio/mpeg'));
		$this->assertEqual($Mime->analyze('file.class'), array('application/x-java'));
		$this->assertEqual($Mime->analyze('file.js'), array('application/javascript'));
		$this->assertEqual($Mime->analyze('file.jpg'), array('image/jpeg'));
		$this->assertEqual($Mime->analyze('file.mpeg'), array('video/mpeg'));
		$this->assertEqual($Mime->analyze('file.ogg'), array('application/ogg', 'audio/x-vorbis+ogg', 'audio/x-flac+ogg', 'audio/x-speex+ogg', 'video/x-theora+ogg'));
		$this->assertEqual($Mime->analyze('file.php'), array('application/x-php'));
		$this->assertEqual($Mime->analyze('file.pdf'), array('application/pdf'));
		$this->assertEqual($Mime->analyze('file.png'), array('image/png'));
		$this->assertEqual($Mime->analyze('file.ps'), array('application/postscript'));
		$this->assertEqual($Mime->analyze('file.po'), array('text/x-gettext-translation'));
		$this->assertEqual($Mime->analyze('file.pot'), array('application/vnd.ms-powerpoint','text/x-gettext-translation-template'));
		$this->assertEqual($Mime->analyze('file.mo'), array('application/x-gettext-translation'));
		$this->assertEqual($Mime->analyze('file.rm'), array('application/vnd.rn-realmedia'));
		$this->assertEqual($Mime->analyze('file.rtf'), array('application/rtf'));
		$this->assertEqual($Mime->analyze('file.txt'), array('text/plain'));
		/* Fails with text/plain */
		// $this->assertEqual($Mime->analyze('file.doc'), array('application/msword', 'application/msword'));
		/* This really shouldn't fail */
		// $this->assertEqual($Mime->analyze('file.docx'), array('application/vnd.openxmlformats-officedocument.wordprocessingml.document'));
		$this->assertEqual($Mime->analyze('file.odt'), array('application/vnd.oasis.opendocument.text'));
		$this->assertEqual($Mime->analyze('file.tar'), array('application/x-tar'));
		$this->assertEqual($Mime->analyze('file.wav'), array('audio/x-wav'));
		$this->assertEqual($Mime->analyze('file.xhtml'), array('application/xhtml+xml'));
		$this->assertEqual($Mime->analyze('file.xml'), array('application/xml'));
	}
}
?>
