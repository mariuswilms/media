<?php
/* SVN FILE: $Id: polymorphic.php 1375 2009-08-03 09:05:08Z AD7six $ */

/**
 * Polymorphic Behavior.
 *
 * Allow the model to be associated with any other model object
 *
 * PHP versions 4 and 5
 *
 * Copyright (c) 2008, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright (c) 2008, Andy Dawson
 * @link          www.ad7six.com
 * @package       base
 * @subpackage    base.models.behaviors
 * @since         v 0.1
 * @version       $Revision: 1375 $
 * @modifiedby    $LastChangedBy: AD7six $
 * @lastmodified  $Date: 2009-08-03 09:05:08 +0000 (Mon, 03 Aug 2009) $
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * PolymorphicBehavior class
 *
 * @uses          ModelBehavior
 * @package       base
 * @subpackage    base.models.behaviors
 */
class PolymorphicBehavior extends ModelBehavior {

/**
 * defaultSettings property
 *
 * @var array
 * @access protected
 */
	var $_defaultSettings = array(
		'modelField' => 'model',
		'foreignKey' => 'foreign_key'
	);

/**
 * setup method
 *
 * @param mixed $Model
 * @param array $config
 * @return void
 * @access public
 */
	function setup(&$Model, $config = array()) {
		$this->settings[$Model->alias] = am ($this->_defaultSettings, $config);
	}

/**
 * afterFind method
 *
 * @param mixed $Model
 * @param mixed $results
 * @param bool $primary
 * @access public
 * @return void
 */
	function afterFind(&$Model, $results, $primary = false) {
		extract($this->settings[$Model->alias]);
		if (App::import('Vendor', 'MiCache')) {
			$models = MiCache::mi('models');
		} else {
			$models = Configure::listObjects('model');
		}
		if ($primary && isset($results[0][$Model->alias][$modelField]) && isset($results[0][$Model->alias][$foreignKey]) && $Model->recursive > 0) {
			foreach ($results as $key => $result) {
				$associated = array();
				$model = Inflector::classify($result[$Model->alias][$modelField]);
				$foreignId = $result[$Model->alias][$foreignKey];
				if ($model && $foreignId && in_array($model, $models)) {
					$result = $result[$Model->alias];
					if (!isset($Model->$model)) {
						$Model->bindModel(array('belongsTo' => array(
							$model => array(
								'conditions' => array($Model->alias . '.' . $modelField => $model),
								'foreignKey' => $foreignKey
							)
						)));
					}
					$conditions = array($model . '.' . $Model->$model->primaryKey => $result[$foreignKey]);
					$recursive = -1;
					$associated = $Model->$model->find('first', compact('conditions', 'recursive'));
					$name = $Model->$model->display($result[$foreignKey]);
					$associated[$model]['display_field'] = $name?$name:'*missing*';
					$results[$key][$model] = $associated[$model];
				}
			}
		} elseif(isset($results[$Model->alias][$modelField])) {
			$associated = array();
			$model = Inflector::classify($result[$Model->alias][$modelField]);
			$foreignId = $results[$Model->alias][$foreignKey];
			if ($model && $foreignId) {
				$result = $results[$Model->alias];
				if (!isset($Model->$model)) {
					$Model->bindModel(array('belongsTo' => array(
						$model => array(
							'conditions' => array($Model->alias . '.' . $modelField => $model),
							'foreignKey' => $foreignKey
						)
					)));
				}
				$conditions = array($model . '.' . $Model->$model->primaryKey => $result[$foreignKey]);
				$recursive = -1;
				$associated = $Model->$model->find('first', compact('conditions', 'recursive'));
				$name = $Model->$model->display($result[$foreignKey]);
				$associated[$model]['display_field'] = $name?$name:'*missing*';
				$results[$model] = $associated[$model];
			}
		}
		return $results;
	}

/**
 * display method
 *
 * Fall back. Assumes that find list is setup such that it returns users real names
 *
 * @param mixed $id
 * @return string
 * @access public
 */
	function display(&$Model, $id = null) {
		if (!$id) {
			if (!$Model->id) {
				return false;
			}
			$id = $Model->id;
		}
		return current($Model->find('list', array('conditions' => array($Model->alias . '.' . $Model->primaryKey => $id))));
	}
}
?>