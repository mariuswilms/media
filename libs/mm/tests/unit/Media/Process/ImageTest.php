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

require_once 'Media/Process/Image.php';
require_once 'Media/Process/Adapter/Imagick.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/mocks/Media/Process/ImageMock.php';

class Media_Process_ImageTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(__FILE__))) . '/_files';
		$this->_data = dirname(dirname(dirname(dirname(__FILE__)))) .'/data';
	}

	public function testFitInside() {
		$adapterMock = $this->getMock(
			'Media_Process_Adapter_Imagick',
			array(), array(), '', false
		);
		$adapterMock->expects($this->any())->method('width')->will($this->returnValue(70));
		$adapterMock->expects($this->any())->method('height')->will($this->returnValue(47));

		$adapterMock->expects($this->once())->method('resize')->with($this->equalTo(30, 20));

		$media = new Media_Process_ImageMock(array(
			'adapter' => $adapterMock
		));

		$media->fitInside(30, 30);
	}

	public function testFitOutside() {
		$adapterMock = $this->getMock(
			'Media_Process_Adapter_Imagick',
			array(), array(), '', false
		);
		$adapterMock->expects($this->any())->method('width')->will($this->returnValue(70));
		$adapterMock->expects($this->any())->method('height')->will($this->returnValue(47));

		$adapterMock->expects($this->once())->method('resize')->with($this->equalTo(30, 20));

		$media = new Media_Process_ImageMock(array(
			'adapter' => $adapterMock
		));

		$media->fitOutside(30, 30);
	}

	public function testNormalizeDimensionsRatio() {
		$adapterMock = $this->getMock(
			'Media_Process_Adapter_Imagick',
			array(), array(), '', false
		);
		$adapterMock->expects($this->any())->method('width')->will($this->returnValue(70));
		$adapterMock->expects($this->any())->method('height')->will($this->returnValue(47));

		$media = new Media_Process_ImageMock(array(
			'adapter' => $adapterMock
		));

		$result = $media->testNormalizeDimensions(0, 0);
		$expected = array(0, 0);
		$this->assertEquals($expected, $result);
	}

	public function testNormalizeDimensionsMaximum() {
		$adapterMock = $this->getMock(
			'Media_Process_Adapter_Imagick',
			array(), array(), '', false
		);
		$adapterMock->expects($this->any())->method('width')->will($this->returnValue(70));
		$adapterMock->expects($this->any())->method('height')->will($this->returnValue(47));

		$media = new Media_Process_ImageMock(array(
			'adapter' => $adapterMock
		));

		$result = $media->testNormalizeDimensions(0, 0, 'maximum');
		$expected = array(70, 47);
		$this->assertEquals($expected, $result);
	}

	public function testBoxify() {
		$adapterMock = $this->getMock(
			'Media_Process_Adapter_Imagick',
			array(), array(), '', false
		);
		$adapterMock->expects($this->any())->method('width')->will($this->returnValue(70));
		$adapterMock->expects($this->any())->method('height')->will($this->returnValue(47));

		$media = new Media_Process_ImageMock(array(
			'adapter' => $adapterMock
		));

		$result = $media->testBoxify(20, 20, 'center');
		$expected = array(25, 13.5);
		$this->assertEquals($expected, $result);

		$result = $media->testBoxify(20, 20, 'topleft');
		$expected = array(0, 0);
		$this->assertEquals($expected, $result);

		$result = $media->testBoxify(20, 20, 'topright');
		$expected = array(50, 0);
		$this->assertEquals($expected, $result);

		$result = $media->testBoxify(20, 20, 'bottomleft');
		$expected = array(0, 27);
		$this->assertEquals($expected, $result);

		$result = $media->testBoxify(20, 20, 'bottomright');
		$expected = array(50, 27);
		$this->assertEquals($expected, $result);

		$this->setExpectedException('InvalidArgumentException');
		$result = $media->testBoxify(20, 20, 'XXXXXXX');
		$expected = array(25, 13.5);
		$this->assertEquals($expected, $result);
	}
}

?>