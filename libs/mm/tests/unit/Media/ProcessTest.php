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

require_once 'Media/Process.php';
require_once 'Mime/Type.php';
require_once dirname(dirname(dirname(__FILE__))) . '/mocks/Media/Process/Adapter/GenericMock.php';

class Media_ProcessTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(__FILE__))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(__FILE__)))) .'/data';

		Media_Process::config(array(
			'image' => new Media_Process_Adapter_GenericMock(null),
			'audio' => new Media_Process_Adapter_GenericMock(null),
			'document' => new Media_Process_Adapter_GenericMock(null),
			'video' => new Media_Process_Adapter_GenericMock(null)
		));
		Mime_Type::config('Magic', array(
			'adapter' => 'Freedesktop',
			'file' => "{$this->_data}/magic.db"
		));
		Mime_Type::config('Glob', array(
			'adapter' => 'Freedesktop',
			'file' => "{$this->_data}/glob.db"
		));
	}

	public function testMediaFactorySourceFile() {
		$result = Media_Process::factory(array('source' => "{$this->_files}/image_jpg.jpg"));
		$this->assertType('Media_Process_Image', $result);

		$result = Media_Process::factory(array('source' => "{$this->_files}/image_png.png"));
		$this->assertType('Media_Process_Image', $result);

		$result = Media_Process::factory(array('source' => "{$this->_files}/application_pdf.pdf"));
		$this->assertType('Media_Process_Document', $result);

		$result = Media_Process::factory(array('source' => "{$this->_files}/audio_ogg_snippet.ogg"));
		$this->assertType('Media_Process_Audio', $result);
	}

	public function testMediaFactorySourceStream() {
		$result = Media_Process::factory(array(
			'source' => fopen("{$this->_files}/image_jpg.jpg", 'rb')
		));
		$this->assertType('Media_Process_Image', $result);
	}

	public function testMediaFactoryTransplantAdapter() {
		$result = Media_Process::factory(array(
			'adapter' => new Media_Process_Adapter_GenericMock(null),
			'source' => 'image/jpeg'
		));
		$this->assertType('Media_Process_Image', $result);
	}
}

?>