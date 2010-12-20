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

require_once 'Media/Info/Image.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/mocks/Media/Info/Adapter/GenericMock.php';

class Media_Info_ImageTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(__FILE__)))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/data';
	}

	public function testQuality() {
		$adapter = $this->getMock('Media_Info_Adapter_GenericMock', array('get'), array(null));
		$media = new Media_Info_Image(array(
			'source' => "{$this->_files}/image_png.png", // not used by adapter
			'adapters' => array($adapter)
		));

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('width'))
			->will($this->returnValue(1));
		$adapter->expects($this->at(1))
			->method('get')->with($this->equalTo('height'))
			->will($this->returnValue(1));
		$result = $media->get('quality');
		$this->assertEquals(1, $result);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('width'))
			->will($this->returnValue(500));
		$adapter->expects($this->at(1))
			->method('get')->with($this->equalTo('height'))
			->will($this->returnValue(700));
		$result = $media->get('quality');
		$this->assertEquals(1, $result);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('width'))
			->will($this->returnValue(1500));
		$adapter->expects($this->at(1))
			->method('get')->with($this->equalTo('height'))
			->will($this->returnValue(1700));
		$result = $media->get('quality');
		$this->assertEquals(2, $result);
	}

	public function testRatio() {
		$adapter = $this->getMock('Media_Info_Adapter_GenericMock', array('get'), array(null));
		$media = new Media_Info_Image(array(
			'source' => "{$this->_files}/image_png.png", // not used by adapter
			'adapters' => array($adapter)
		));

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('width'))
			->will($this->returnValue(500));
		$adapter->expects($this->at(1))
			->method('get')->with($this->equalTo('height'))
			->will($this->returnValue(700));
		$result = $media->get('ratio');
		$this->assertEquals(500 / 700, $result);
	}

	public function testKnownRatio() {
		$adapter = $this->getMock('Media_Info_Adapter_GenericMock', array('get'), array(null));
		$media = new Media_Info_Image(array(
			'source' => "{$this->_files}/image_png.png", // not used by adapter
			'adapters' => array($adapter)
		));

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('width'))
			->will($this->returnValue(500));
		$adapter->expects($this->at(1))
			->method('get')->with($this->equalTo('height'))
			->will($this->returnValue(700));
		$result = $media->get('knownRatio');
		$this->assertEquals('1:√2', $result);
	}
}

?>