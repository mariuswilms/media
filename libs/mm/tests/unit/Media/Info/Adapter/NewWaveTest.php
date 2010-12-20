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

require_once 'Media/Info/Adapter/NewWave.php';

class Media_Info_Adapter_NewWaveTest extends PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data';
		$this->_data = dirname(dirname(dirname((dirname(dirname(dirname(__FILE__))))))) .'/data';
	}

	public function testAll() {
		$source = "{$this->_files}/audio_wave.wav";
		$subject = new Media_Info_Adapter_NewWave($source);

		$result = $subject->all();
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result);

		$this->assertArrayHasKey('samples', $result);
	}

	public function testAllAndGetSymmetry() {
		$source = "{$this->_files}/audio_wave.wav";
		$subject = new Media_Info_Adapter_NewWave($source);

		$results = $subject->all();

		foreach ($results as $name => $value)  {
			$result = $subject->get($name);
			$this->assertEquals($value, $result, "Result for name `{$name}`.");
		}
	}
}

?>