<?php
/**
 * Generator Behavior Test Case File
 *
 * Copyright (c) 2007-2010 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.tests.cases.models.behaviors
 * @copyright  2007-2010 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
require_once dirname(__FILE__) . DS . 'base.test.php';

/**
 * Generator Behavior Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.models.behaviors
 */
class GeneratorBehaviorTestCase extends BaseBehaviorTestCase {

	function setUp() {
		parent::setUp();
		$this->_behaviorSettings = array(
			'baseDirectory' => $this->Folder->pwd(),
			'createDirectory' => false,
			'filterDirectory' => $this->Folder->pwd() . 'filter' . DS
		);
	}

	function testSetup() {
		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Generator');

		$Model =& ClassRegistry::init('Song');
		$Model->Behaviors->attach('Media.Generator');
	}

	function testBeforeMake() {
		Configure::write('Media.filter.image', array(
			's' => array('convert' => 'image/png', 'fit' => array(5, 5)),
			'm' => array('convert' => 'image/png', 'fit' => array(10, 10))
		));

		$Model =& ClassRegistry::init('Unicorn', 'Model');

		$Model->Behaviors->attach('Media.Generator', array(
			'baseDirectory' => $this->Folder->pwd(),
			'filterDirectory' => $this->Folder->pwd() . 'filter' . DS,
			'createDirectory' => true
		));

		$file = $this->Data->getFile(array(
			'image-jpg.jpg' => $this->Folder->pwd() . 'image-jpg.jpg'
		));

		$expected[] = array(
			$file,
			array(
				'overwrite' => false,
				'directory' => $this->Folder->pwd() . 'filter' . DS . 's' . DS,
				'name' => 'Image',
				'version' => 's',
				'instructions' => array('convert' => 'image/png', 'fit' => array(5, 5))
		));
		$expected[] = array(
			$file,
			array(
				'overwrite' => false,
				'directory' => $this->Folder->pwd() . 'filter' . DS . 'm' . DS,
				'name' => 'Image',
				'version' => 'm',
				'instructions' => array('convert' => 'image/png', 'fit' => array(10, 10))
		));

		$Model->make($file);
		$this->assertEqual($Model->beforeMakeArgs, $expected);
	}
}
?>