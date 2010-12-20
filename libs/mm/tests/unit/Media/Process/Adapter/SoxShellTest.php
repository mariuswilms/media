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

require_once 'Media/Process/Adapter/SoxShell.php';
require_once 'Mime/Type.php';

class Media_Process_Adapter_SoxShellTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$command = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'sox.exe' : 'sox';
		exec("{$command} --version", $out, $return);

		if ($return != 0) {
			$this->markTestSkipped('The `sox` command is not available.');
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
		$subject = new Media_Process_Adapter_SoxShell($source);
		$subject->store($target);
		$this->assertEquals('test', stream_get_contents($target, -1, 0));

		fclose($source);
		fclose($target);
	}

	public function testConvert() {
		$source = fopen("{$this->_files}/audio_vorbis_comments.ogg", 'rb');
		$target = fopen('php://temp', 'wb');

		$subject = new Media_Process_Adapter_SoxShell($source);
		$subject->convert('audio/x-wav');
		$result = $subject->store($target);

		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $result);
		$this->assertEquals('audio/x-wav', Mime_Type::guessType($target));

		fclose($source);
		fclose($target);
	}
}

?>