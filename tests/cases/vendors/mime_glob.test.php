<?php
/**
 * Mime Glob Test Case File
 *
 * Copyright (c) 2007-2008 David Persson
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
 * @copyright  2007-2008 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
App::import('Vendor', 'Media.MimeGlob');
require_once dirname(__FILE__) . DS . '..' . DS . '..' . DS . 'fixtures' . DS . 'test_data.php';
/**
 * Mime Glob Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.libs
 */
class MimeGlobTest extends CakeTestCase {
	function setup() {
		Configure::write('Cache.disable', true);
		$this->TestData = new MimeTestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}
/**
 * testFormat method
 *
 */
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
/**
 * testLoad method
 *
 * @return void
 * @access public
 */
	function testRead() {
		$fileA = $this->TestData->getFile('glob.apache.snippet.db');
		$fileB = $this->TestData->getFile('glob.freedesktop.snippet.db');

		$Mime =& new MimeGlob($fileA);
		$Mime =& new MimeGlob($fileB);

		$this->expectError();
		$Mime =& new MimeGlob(5);
	}
/**
 * analyze should properly detect the mime type
 *
 * @access public
 * @return void
 */
	function testApacheFormatAnalyze() {
		$file = $this->TestData->getFile('glob.apache.snippet.db');
		$Mime =& new MimeGlob($file);

		$result = $Mime->analyze('i-dont-exist.sla');
		$this->assertEqual($result, array());

		$result = $Mime->analyze(WWW_ROOT.'img'.DS.'cake.icon.gif');
		$this->assertEqual($result, array('image/gif'));

		$result = $Mime->analyze('file.jpg');
		$this->assertEqual($result, array('image/jpeg'));

		$result = $Mime->analyze('file.html');
		$this->assertEqual($result, array('text/html'));

		$result = $Mime->analyze('file.tar');
		$this->assertEqual($result, array('application/x-tar'));

		$result = $Mime->analyze('file.pdf');
		$this->assertEqual($result, array('application/pdf'));

		$result = $Mime->analyze('file.doc');
		$this->assertEqual($result, array('application/msword'));

		$result = $Mime->analyze('file.rtf');
		$this->assertEqual($result, array('text/rtf'));
	}
/**
 * analyze should properly detect the mime type
 *
 * @access public
 * @return void
 */
	function testFreedesktopFormatAnalyze() {
		$file = $this->TestData->getFile('glob.freedesktop.snippet.db');
		$Mime =& new MimeGlob($file);

		$result = $Mime->analyze('i-dont-exist.sla');
		$this->assertEqual($result, array());

		$result = $Mime->analyze(WWW_ROOT.'img'.DS.'cake.icon.gif');
		$this->assertEqual($result, array('image/gif'));

		$result = $Mime->analyze('file.xhtml');
		$this->assertEqual($result, array('application/xhtml+xml'));

		$result = $Mime->analyze('file.mo');
		$this->assertEqual($result, array('application/x-gettext-translation'));

		$result = $Mime->analyze('file.po');
		$this->assertEqual($result, array('text/x-gettext-translation'));

		$result = $Mime->analyze('file.pot');
		$this->assertEqual($result, array('application/vnd.ms-powerpoint','text/x-gettext-translation-template'));

		$result = $Mime->analyze('file.gz');
		$this->assertEqual($result, array('application/x-gzip'));

		$result = $Mime->analyze('file.tar.gz');
		$this->assertEqual($result, array('application/x-gzip'));

		$result = $Mime->analyze('file.pdf');
		$this->assertEqual($result, array('application/pdf'));

		$result = $Mime->analyze('file.doc');
		$this->assertEqual($result, array('application/msword'));

		$result = $Mime->analyze('file.rtf');
		$this->assertEqual($result, array('application/rtf'));

		$result = $Mime->analyze('file.bz2');
		$this->assertEqual($result, array('application/x-bzip'));

		/* Reenable if application/vnd.... is in the standard glob file */
		// $result = $Mime->analyze('file.docx');
		// $this->assertEqual($result, array('application/vnd.openxmlformats-officedocument.wordprocessingml.document');

		$result = $Mime->analyze('file.odt');
		$this->assertEqual($result, array('application/vnd.oasis.opendocument.text'));
	}
/**
 * testAnalyzeReverse method
 *
 * @return void
 * @access public
 */
	function testAnalyzeReverse() {
		$file = $this->TestData->getFile('glob.apache.snippet.db');
		$Mime =& new MimeGlob($file);

		$result = $Mime->analyze('text/plain', true);
		$this->assertEqual($result, array('asc', 'txt', 'text', 'diff', 'pot'));

		$result = $Mime->analyze('application/pdf', true);
		$this->assertEqual($result, array('pdf'));
	}
}
?>