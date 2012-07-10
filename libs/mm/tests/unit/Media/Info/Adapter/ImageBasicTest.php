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

require_once 'Media/Info/Adapter/ImageBasic.php';

class Media_Info_Adapter_ImageBasicTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		if (!function_exists('getimagesize')) {
			$this->markTestSkipped('The `getimagesize` function is not available.');
		}

		$this->_files = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data';
		$this->_data = dirname(dirname(dirname((dirname(dirname(dirname(__FILE__))))))) .'/data';
	}

	public function testAll() {
		$source = "{$this->_files}/image_png.png";
		$subject = new Media_Info_Adapter_ImageBasic($source);

		$result = $subject->all();
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result);

		$this->assertArrayHasKey('width', $result);
		$this->assertArrayHasKey('height', $result);
		$this->assertArrayHasKey('bits', $result);

		$this->assertEquals(70, $result['width']);
		$this->assertEquals(54, $result['height']);
		$this->assertEquals(16, $result['bits']);

		$source = "{$this->_files}/image_jpg.jpg";
		$subject = new Media_Info_Adapter_ImageBasic($source);

		$result = $subject->all();
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result);

		$this->assertArrayHasKey('width', $result);
		$this->assertArrayHasKey('height', $result);
		$this->assertArrayHasKey('channels', $result);
		$this->assertArrayHasKey('bits', $result);
	}

	public function testAllAndGetSymmetry() {
		$source = "{$this->_files}/image_png.png";
		$subject = new Media_Info_Adapter_ImageBasic($source);

		$results = $subject->all();

		foreach ($results as $name => $value)  {
			$result = $subject->get($name);
			$this->assertEquals($value, $result, "Result for name `{$name}`.");
		}
	}
}

?>