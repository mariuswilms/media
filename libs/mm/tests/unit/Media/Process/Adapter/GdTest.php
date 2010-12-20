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

require_once 'Media/Process/Adapter/Gd.php';
require_once 'Mime/Type.php';

class Media_Process_Adapter_GdTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		if (!extension_loaded('gd')) {
			$this->markTestSkipped('The `gd` extension is not available.');
		}

		$this->_files = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data';
		$this->_data = dirname(dirname(dirname((dirname(dirname(dirname(__FILE__))))))) .'/data';

		Mime_Type::config('Magic', array(
			'adapter' => 'Freedesktop',
			'file' => "{$this->_data}/magic.db"
		));
		Mime_Type::config('Glob', array(
			'adapter' => 'Freedesktop',
			'file' => "{$this->_data}/glob.db"
		));
	}

	public function testDimensions() {
		$source = fopen("{$this->_files}/image_png.png", 'rb');
		$subject = new Media_Process_Adapter_Gd($source);

		$this->assertEquals(70, $subject->width());
		$this->assertEquals(54, $subject->height());

		fclose($source);
	}

	public function testStore() {
		$source = fopen("{$this->_files}/image_png.png", 'rb');
		$target = fopen('php://temp', 'w+b');

		$subject = new Media_Process_Adapter_Gd($source);
		$result = $subject->store($target);
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $result);

		fclose($source);
		fclose($target);
	}

	public function testConvertImageToImage() {
		$source = fopen("{$this->_files}/image_png.png", 'rb');
		$target = fopen('php://temp', 'wb');

		$subject = new Media_Process_Adapter_Gd($source);
		$subject->convert('image/jpeg');
		$result = $subject->store($target);

		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $result);
		$this->assertEquals('image/jpeg', Mime_Type::guessType($target));

		fclose($source);
		fclose($target);
	}

	public function testCrop() {
		$source = fopen("{$this->_files}/image_landscape.png", 'rb');
		$subject = new Media_Process_Adapter_Gd($source);
		// original size is 400x200

		$result = $subject->crop(10, 10, 100, 50);
		$this->assertTrue($result);

		$this->assertEquals(100, $subject->width());
		$this->assertEquals(50, $subject->height());
	}

	public function testResize() {
		$source = fopen("{$this->_files}/image_landscape.png", 'rb');
		$subject = new Media_Process_Adapter_Gd($source);
		// original size is 400x200

		$result = $subject->resize(100, 50);
		$this->assertTrue($result);

		$this->assertEquals(100, $subject->width());
		$this->assertEquals(50, $subject->height());
	}

	public function testCropAndResize() {
		$source = fopen("{$this->_files}/image_landscape.png", 'rb');
		$subject = new Media_Process_Adapter_Gd($source);
		// original size is 400x200

		$result = $subject->cropAndResize(10, 10, 100, 50, 70, 50);
		$this->assertTrue($result);

		$this->assertEquals(70, $subject->width());
		$this->assertEquals(50, $subject->height());
	}

	public function testCompressPng() {
		for ($i = 1; $i < 10; $i++) {
			$source = fopen("{$this->_files}/image_png.png", 'rb');

			$uncompressed = fopen('php://temp', 'w+b');
			$compressed = fopen('php://temp', 'w+b');

			$subject = new Media_Process_Adapter_Gd($source);
			$subject->compress(0);
			$subject->store($uncompressed);

			$subject->compress($i);
			$subject->store($compressed);

			$uncompressedMeta = fstat($uncompressed);
			$compressedMeta = fstat($compressed);

			$this->assertLessThan(
				$uncompressedMeta['size'], $compressedMeta['size'], "Compr. `{$i}`."
			);

			fclose($source);
			fclose($uncompressed);
			fclose($compressed);
		}
	}

	public function testCompressJpeg() {
		for ($i = 1; $i < 10; $i++) {
			$source = fopen("{$this->_files}/image_jpg.jpg", 'rb');

			$uncompressed = fopen('php://temp', 'w+b');
			$compressed = fopen('php://temp', 'w+b');

			$subject = new Media_Process_Adapter_Gd($source);
			$subject->compress(0);
			$subject->store($uncompressed);

			$subject->compress($i);
			$subject->store($compressed);

			$uncompressedMeta = fstat($uncompressed);
			$compressedMeta = fstat($compressed);

			$this->assertLessThan(
				$uncompressedMeta['size'], $compressedMeta['size'], "Compr. `{$i}`."
			);

			fclose($source);
			fclose($uncompressed);
			fclose($compressed);
		}
	}
}

?>