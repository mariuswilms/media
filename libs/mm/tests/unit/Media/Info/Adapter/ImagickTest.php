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

require_once 'PHPUnit/Framework.php';
require_once 'Media/Info/Adapter/Imagick.php';

class Media_Infos_Adapter_ImagickTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		if (!extension_loaded('imagick')) {
			$this->markTestSkipped('The `imagick` extension is not available.');
		}

		$this->_files = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/resources';
		$this->_data = dirname(dirname(dirname((dirname(dirname(dirname(__FILE__))))))) .'/data';
	}

	public function testAll() {
		$source = "{$this->_files}/image_png.png";
		$subject = new Media_Info_Adapter_Imagick($source);

		$result = $subject->all();
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result);

		$this->assertArrayHasKey('width', $result);
		$this->assertArrayHasKey('height', $result);

		$this->assertEquals(70, $result['width']);
		$this->assertEquals(54, $result['height']);

		$source = "{$this->_files}/image_jpg.jpg";
		$subject = new Media_Info_Adapter_Imagick($source);

		$result = $subject->all();
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result);

		$this->assertArrayHasKey('width', $result);
		$this->assertArrayHasKey('height', $result);

		$source = "{$this->_files}/application_pdf.pdf";
		$subject = new Media_Info_Adapter_Imagick($source);

		$result = $subject->all();
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result);

		$this->assertArrayHasKey('width', $result);
		$this->assertArrayHasKey('height', $result);
	}

	public function testAllAndGetSymmetry() {
		$source = "{$this->_files}/image_png.png";
		$subject = new Media_Info_Adapter_Imagick($source);

		$results = $subject->all();

		foreach ($results as $name => $value)  {
			$result = $subject->get($name);
			$this->assertEquals($value, $result, "Result for name `{$name}`.");
		}
	}
}

?>