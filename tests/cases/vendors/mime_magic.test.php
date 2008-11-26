<?php
/**
 * Mime Magic Test Case File
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
		$this->TestData = new MediumTestData();
	}

	function tearDown() {
		$this->TestData->flushFiles();
	}
/**
 * testLoadmethod
 *
 * @access public
 * @return void
 */
	function testLoad() {
		$Mime =& new MimeMagic(true);
		$this->assertNull($Mime->format);

		$Mime =& new MimeMagic(5);
		$this->assertNull($Mime->format);

		$Mime =& new MimeMagic(array('foo' => 'bar'));
		$this->assertNull($Mime->format);

		$Mime =& new MimeMagic('does-not-exist.db');
		$this->assertNull($Mime->format);

		$file = $this->TestData->getFile('text-html.snippet.html');
		$Mime =& new MimeMagic($file);
		$this->assertNull($Mime->format);

		$file = $this->TestData->getFile('magic.apache.snippet.db');
		$Mime =& new MimeMagic($file);
		$this->assertEqual($Mime->format, 'Apache Module mod_mime_magic');

		$file = $this->TestData->getFile('magic.freedesktop.snippet.db');
		$Mime =& new MimeMagic($file);
		$this->assertEqual($Mime->format, 'Freedesktop Shared MIME-info Database');
	}
/**
 * MimeMagic::analyze should properly detect the mime type
 *
 * @access public
 * @return void
 */
	function testApacheFormatAnalyze() {
		$file = $this->TestData->getFile('magic.apache.snippet.db');
		$Mime =& new MimeMagic($file);

		$result = $Mime->analyze('i-dont-exist.sla');
		$this->assertFalse($result);

		$result = $Mime->analyze(WWW_ROOT.'img'.DS.'cake.icon.gif');
		$this->assertEqual($result, 'image/gif');

		$file = $this->TestData->getFile('image-jpeg.snippet.jpg');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'image/jpeg');

		$file = $this->TestData->getFile('text-html.snippet.html');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'text/html');

		/* Aspects offset 1 to be \213 but offset 1 of the test data is \139 */
		// $file = $this->TestData->getFile('gzip.snippet.gz');
		// $result = $Mime->analyze($file);
		// $this->assertEqual($result, 'application/x-gzip');

		/* Same applies here */
		// $file = $this->TestData->getFile('tar-gzip.snippet.tar.gz');
		// $result = $Mime->analyze($file);
		// $this->assertEqual($result, 'application/x-gzip');

		$file = $this->TestData->getFile('pdf.snippet.pdf');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/pdf');

		/* Magic data seems outdated because this fails */
		// $file = $this->TestData->getFile('ms-word.snippet.doc');
		// $result = $Mime->analyze($file);
		// $this->assertEqual($result, 'application/msword');

		$file = $this->TestData->getFile('text-rtf.snippet.rtf');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/rtf');

		$file = $this->TestData->getFile('mo.snippet.mo');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/octet-stream');
	}
/**
 * MimeMagic::analyze should properly detect the mime type
 *
 * @access public
 * @return void
 */
	function testFreedesktopFormatAnalyze() {
		$file = VENDORS.'magic.freedesktop.db';
		$this->skipUnless(file_exists($file));
		$Mime =& new MimeMagic($file);

		$result = $Mime->analyze('i-dont-exist.sla');
		$this->assertFalse($result);

		$result = $Mime->analyze(WWW_ROOT.'img'.DS.'cake.icon.gif');
		$this->assertEqual($result, 'image/gif');

		$file = $this->TestData->getFile('image-jpeg.snippet.jpg');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'image/jpeg');

		$file = $this->TestData->getFile('text-html.snippet.html');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'text/html');

		$file = $this->TestData->getFile('mo.snippet.mo');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/octet-stream');

		/* Reenable if text/x-po is in the standard magic file */
		// $file = $this->TestData->getFile('po.snippet.po');
		// $result = $Mime->analyze($file);
		// $this->assertEqual($result, 'text/x-po');

		/* Reenable if text/x-po is in the standard magic file */
		// $file = $this->TestData->getFile('text-pot.snippet.pot');
		// $result = $Mime->analyze($file);
		// $this->assertEqual($result, 'text/x-po');

		$file = $this->TestData->getFile('gzip.snippet.gz');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/x-gzip');

		$file = $this->TestData->getFile('tar.snippet.tar');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/x-tar');

		$file = $this->TestData->getFile('tar-gzip.snippet.tar.gz');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/x-gzip');

		$file = $this->TestData->getFile('zip.snippet.zip');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/zip');

		$file = $this->TestData->getFile('bzip2.snippet.bz2');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/x-bzip');

		$file = $this->TestData->getFile('pdf.snippet.pdf');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/pdf');

		$file = $this->TestData->getFile('ms-word.snippet.doc');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/x-ole-storage');

		/* Reenable if application/vnd.... is in the standard magic file */
		// $file = $this->TestData->getFile('ms-word.snippet.docx');
		// $result = $Mime->analyze($file);
		// $this->assertEqual($result, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

		$file = $this->TestData->getFile('opendocument-writer.snippet.odt');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/vnd.oasis.opendocument.text');

		$file = $this->TestData->getFile('text-rtf.snippet.rtf');
		$result = $Mime->analyze($file);
		$this->assertEqual($result, 'application/rtf');
	}
}
?>