<?php
/**
 * Polymorphic Behavior File
 *
 * Allow the model to be associated with any other model object
 *
 * Copyright (c), Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @author			Andy Dawson (AD7six)
 * @copyright       Andy Dawson
 * @version			$Revision: 18 $
 * @modifiedby		$LastChangedBy: andy $
 * @lastmodified	$Date: 2008-03-07 13:56:09 +0100 (Fri, 07 Mar 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Polymorphic Behavior Class
 *
 * @author			Andy Dawson (AD7six)
 * @copyright       Andy Dawson
 * @version			$Revision: 18 $
 * @modifiedby		$LastChangedBy: andy $
 * @lastmodified	$Date: 2008-03-07 13:56:09 +0100 (Fri, 07 Mar 2008) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class PolymorphicBehavior extends ModelBehavior {

	function setup(&$model, $config = array()) {
		$this->settings[$model->name] = am (array('classField' => 'class', 'foreignKey' => 'foreign_id'),$config);
	}

	function afterFind (&$model, $results, $primary = false) {
		extract($this->settings[$model->name]);
		if ($primary && isset($results[0][$model->alias][$classField]) && $model->recursive > 0) {
			foreach ($results as $key => $result) {
				$associated = array();
				$class = $result[$model->alias][$classField];
				$foreignId = $result[$model->alias][$foreignKey];
				if ($class && $foreignId) {
					$result = $result[$model->alias];
					if (!isset($model->$class)) {
						$model->bindModel(array('belongsTo' => array(
							$class => array(
								'conditions' => array($model->alias . '.' . $classField => $class),
								'foreignKey' => $foreignKey
							)
						)));
					}
					// Applied fix suggested in http://groups.google.com/group/cake-php/msg/3ecbdc13ca1e3a64
					$associated = $model->$class->find(array($class . '.id' => $foreignId),null,null, -1);
					$associated[$class]['display_field'] = $associated[$class][$model->$class->displayField];
					$results[$key][$class] = $associated[$class];
				}
			}
		} elseif(isset($results[$model->alias][$classField])) {
			$associated = array();
			$class = $results[$model->alias][$classField];
			$foreignId = $results[$model->alias][$foreignKey];
			if ($class && $foreignId) {
				$result = $results[$model->alias];
				if (!isset($model->$class)) {
					$model->bindModel(array('belongsTo' => array(
						$class => array(
							'conditions' => array($model->alias . '.' . $classField => $class),
							'foreignKey' => $foreignKey
						)
					)));
				}
				$associated = $model->$class->find(array($class.'.id' => $foreignId), array('id', $model->$class->displayField), null, -1);
				$associated[$class]['display_field'] = $associated[$class][$model->$class->displayField];
				$results[$class] = $associated[$class];
			}
		}
		return $results;
	}
}
?>