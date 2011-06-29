<?php
/**
 * Coupler Behavior Test Case File
 *
 * Copyright (c) 2007-2011 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.tests.cases.models.behaviors
 * @copyright  2007-2011 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
require_once dirname(__FILE__) . DS . 'base.test.php';

/**
 * Coupler Behavior Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.models.behaviors
 */
class CouplerBehaviorTestCase extends BaseBehaviorTestCase {

	function setUp() {
		parent::setUp();
		$this->_behaviorSettings = array(
			'baseDirectory' => $this->Folder->pwd()
		);
	}

	function testSetup() {
		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Coupler');

		$Model =& ClassRegistry::init('Song');
		$Model->Behaviors->attach('Media.Coupler');
	}

	function testFind() {
		$Model =& ClassRegistry::init('Song');
		$Model->Behaviors->attach('Media.Coupler', $this->_behaviorSettings);
		$result = $Model->find('all');
		$this->assertEqual(count($result), 4);

		/* Virtual */
		$result = $Model->findById(1);
		$this->assertTrue(Set::matches('/Song/file', $result));
		$this->assertEqual($result['Song']['file'], $this->file0);
	}

	function testSave() {
		$Model =& ClassRegistry::init('Song');
		$Model->Behaviors->attach('Media.Coupler', $this->_behaviorSettings);

		$file = $this->Data->getFile(array(
			'application-pdf.pdf' => $this->Folder->pwd() . 'static/doc/application-pdf.pdf'
		));
		$item = array('file' => $file);
		$Model->create();
		$result = $Model->save($item);
		$this->assertTrue($result);

		$result = $Model->findById(5);
		$expected = array(
			'Song' => array (
				'id' => '5',
					'dirname' => 'static/doc',
					'basename' => 'application-pdf.pdf',
					'checksum' => null,
					'file' => $file
		));
		$this->assertEqual($expected, $result);
	}
}
?>
