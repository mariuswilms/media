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

require_once 'Media/Process/Generic.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/mocks/Media/Process/Adapter/GenericMock.php';

class Media_Process_GenericTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(__FILE__)))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/data';
	}

	public function testConstruct() {
		$result = new Media_Process_Generic(array(
			'source' => "{$this->_files}/image_jpg.jpg",
			'adapter' => new Media_Process_Adapter_GenericMock(null)
		));
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $result);

		$result = new Media_Process_Generic(array(
			'source' => fopen("{$this->_files}/image_jpg.jpg", 'rb'),
			'adapter' => new Media_Process_Adapter_GenericMock(null)
		));
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $result);

		$result = new Media_Process_Generic(array(
			'source' => "{$this->_files}/image_jpg.jpg",
			'adapter' => new Media_Process_Adapter_GenericMock('test')
		));
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $result);

		$result = new Media_Process_Generic(array(
			'adapter' => new Media_Process_Adapter_GenericMock('test')
		));
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $result);
	}

	public function testConstructFailWithNoArgs() {
		$this->setExpectedException('InvalidArgumentException');
		new Media_Process_Generic(array());
	}

	public function testConstructFailWithSourceButNoAdapter() {
		$this->setExpectedException('InvalidArgumentException');
		new Media_Process_Generic(array('source' => "{$this->_files}/image_jpg.jpg"));
	}

	public function testConstructFailWithStringAdapterButNoSource() {
		$this->setExpectedException('InvalidArgumentException');
		new Media_Process_Generic(array('adapter' => 'Dummy'));
	}

	public function testName() {
		$result = new Media_Process_Generic(array(
			'source' => "{$this->_files}/image_jpg.jpg",
			'adapter' => new Media_Process_Adapter_GenericMock(null)
		));
		$this->assertEquals($result->name(), 'generic');
	}

	public function testStoreHonorsOverwrite() {
		$target = tempnam(sys_get_temp_dir(), 'mm_');
		touch($target);

		$media = new Media_Process_Generic(array(
			'source' => fopen('php://temp', 'rb'),
			'adapter' => new Media_Process_Adapter_GenericMock(null)
		));
		$result = $media->store($target);
		$this->assertFalse($result);

		$result = $media->store($target, array('overwrite' => true));
		$this->assertFileExists($result);

		unlink($target);
		$result = $media->store($target);
		$this->assertFileExists($result);

		unlink($target);
	}

	public function testPassthru() {
		$result = new Media_Process_Generic(array(
			'source' => "{$this->_files}/image_jpg.jpg",
			'adapter' => new Media_Process_Adapter_GenericMock(null)
		));
		$this->assertEquals($result->passthru('depth', 8), true);
	}
}

?>