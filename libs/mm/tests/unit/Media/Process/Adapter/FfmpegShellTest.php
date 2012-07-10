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

require_once 'Media/Process/Adapter/FfmpegShell.php';
require_once 'Mime/Type.php';

class Media_Process_Adapter_FfmpegShellTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
			exec("ffmpeg.exe -version>> nul 2>&1", $out, $return);
		} else {
			exec("ffmpeg -version &> /dev/null", $out, $return);
		}

		if ($return != 0) {
			$this->markTestSkipped('The `ffmpeg` command is not available.');
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

	public function testStore() {
		$source = fopen('php://temp', 'r+b');
		$target = fopen('php://temp', 'w+b');

		fwrite($source, 'test');
		$subject = new Media_Process_Adapter_FfmpegShell($source);
		$subject->store($target);
		$this->assertEquals('test', stream_get_contents($target, -1, 0));

		fclose($source);
		fclose($target);
	}

	public function testConvertToImage() {
		$source = fopen("{$this->_files}/video_theora_comments.ogv", 'rb');
		$target = fopen('php://temp', 'wb');

		$subject = new Media_Process_Adapter_FfmpegShell($source);
		$subject->convert('image/png');
		$result = $subject->store($target);

		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $result);
		$this->assertEquals('image/png', Mime_Type::guessType($target));

		fclose($source);
		fclose($target);
	}

	public function testConvertToVideo() {
		$source = fopen("{$this->_files}/video_theora_comments.ogv", 'rb');
		$target = fopen('php://temp', 'wb');

		$subject = new Media_Process_Adapter_FfmpegShell($source);
		$subject->convert('video/mpeg');
		$result = $subject->store($target);

		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $result);
		$this->assertEquals('video/mpeg', Mime_Type::guessType($target));

		fclose($source);
		fclose($target);
	}

	public function testPassthru() {
		$source = fopen("{$this->_files}/video_theora_comments.ogv", 'rb');
		$target = fopen('php://temp', 'wb');

		$subject = new Media_Process_Adapter_FfmpegShell($source);
		$subject->passthru('s', '50x100');
		$subject->store($target);

		fclose($source);

		$subject = new Media_Process_Adapter_FfmpegShell($target);
		$this->assertEquals(50, $subject->width());
		$this->assertEquals(100, $subject->height());

		fclose($target);
	}

	public function testDimensions() {
		$source = fopen("{$this->_files}/video_theora_comments.ogv", 'rb');
		$subject = new Media_Process_Adapter_FfmpegShell($source);

		$this->assertEquals(320, $subject->width());
		$this->assertEquals(176, $subject->height());

		fclose($source);
	}
}

?>