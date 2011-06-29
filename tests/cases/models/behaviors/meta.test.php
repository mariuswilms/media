<?php
/**
 * Meta Behavior Test Case File
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
 * Meta Behavior Test Case Class
 *
 * @package    media
 * @subpackage media.tests.cases.models.behaviors
 */
class MetaBehaviorTestCase extends BaseBehaviorTestCase {

	function setUp() {
		parent::setUp();
		$this->_behaviorSettings['Coupler'] = array(
			'baseDirectory' => $this->Folder->pwd()
		);
		$this->_behaviorSettings['Meta'] = array(
			'level' => 1
		);
	}

	function testSetup() {
		$Model =& ClassRegistry::init('TheVoid');
		$Model->Behaviors->attach('Media.Meta');

		$Model =& ClassRegistry::init('Song');
		$Model->Behaviors->attach('Media.Meta');
	}

	function testSave() {
		$Model =& ClassRegistry::init('Song');
		$Model->Behaviors->attach('Media.Meta', $this->_behaviorSettings['Meta']);

		$data = array('Song' => array('file' => $this->file0));
		$result = $Model->save($data);
		$Model->Behaviors->detach('Media.Meta');

		$id = $Model->getLastInsertID();
		$result = $Model->findById($id);
		$Model->delete($id);
		$this->assertEqual($result['Song']['checksum'], md5_file($this->file0));
	}

	function testFind() {
		$Model =& ClassRegistry::init('Song');
		$Model->Behaviors->attach('Media.Coupler', $this->_behaviorSettings['Coupler']);
		$Model->Behaviors->attach('Media.Meta', $this->_behaviorSettings['Meta']);
		$result = $Model->find('all');
		$this->assertEqual(count($result), 3);

		/* Virtual */
		$result = $Model->findById(1);
		$this->assertTrue(Set::matches('/Song/size', $result));
		$this->assertTrue(Set::matches('/Song/mime_type',$result));
	}
}
?>
