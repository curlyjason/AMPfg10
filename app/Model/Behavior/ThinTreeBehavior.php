<?php

/**
 * ThinTree Behavior
 *
 * A Tree behavior that doesn't require massive record re-writing for every insertion
 * 
 * Data base requires
 * 	id
 * 	parent_id
 * 	ancestor_list
 * 	sequence
 * You must seed the table with one root record.
 * The root record can have a non-existant parent.
 * The root records ancestor_list must be a single comma (,).
 *
 * @copyright     Copyright (c) Dreaming Mind (http://dreamingmind.com)
 * @package       app.Model.Behavior
 */

/**
 * ThinTree Behavior
 *
 * A Tree behavior that doesn't require massive record re-writing for every insertion
 *
 * @copyright     Copyright (c) Dreaming Mind (http://dreamingmind.com)
 * @package       app.Model.Behavior
 */
class ThinTreeBehavior extends ModelBehavior {

    /**
     * Default configuration values
     *
     * @var array
     */
    protected $_defaults = array(
        'parent' => 'parent_id',
        'ancestors' => 'ancestor_list',
        'sequence' => 'sequence',
        'scope' => '1 = 1',
        'recursive' => -1
    );
	
    /**
     * A place to collect record with data changes for later saving
     *
     * @var array Accumulated record
     */
    private $accumulator = array();
    private $sequenceChange = false;
    private $parentChange = false;
    private $afterSaveFlag = false;

    public function setup(Model $Model, $config = array()) {
        if (isset($config[0])) {
            $config['type'] = $config[0];
            unset($config[0]);
        }
        $settings = array_merge($this->_defaults, $config);

        // ?? [] was added to support Mocks in testing
        if (in_array($settings['scope'], $Model->getAssociated('belongsTo') ?? [])) {
            $data = $Model->getAssociated($settings['scope']);
            $Parent = $Model->{$settings['scope']};
            $settings['scope'] = $Model->escapeField($data['foreignKey']) . ' = ' . $Parent->escapeField();
            $settings['recursive'] = 0;
        }
        $this->settings[$Model->alias] = $settings;
    }

/**
 * Clean up any initialization this behavior has done on a model. Called when a behavior is dynamically
 * detached from a model using Model::detach().
 *
 * @param Model $model Model using this behavior
 * @return void
 * @see BehaviorCollection::detach()
 */
    public function cleanup(Model $model) {
        parent::cleanup($model);
    }

/**
 * beforeFind can be used to cancel find operations, or modify the query that will be executed.
 * By returning null/false you can abort a find. By returning an array you can modify/replace the query
 * that is going to be run.
 *
 * @param Model $model Model using this behavior
 * @param array $query Data used to execute this query, i.e. conditions, order, etc.
 * @return boolean|array False or null will abort the operation. You can return an array to replace the
 *   $query that will be eventually run.
 */
    public function beforeFind(Model $model, $query) {
        parent::beforeFind($model, $query);
    }

/**
 * After find callback. Can be used to modify any results returned by find.
 *
 * @param Model $model Model using this behavior
 * @param mixed $results The results of the find operation
 * @param boolean $primary Whether this model is being queried directly (vs. being queried as an association)
 * @return mixed An array value will replace the value of $results - any other value will be ignored.
 */
    public function afterFind(Model $model, $results, $primary = false) {
        parent::afterFind($model, $results, $primary);
    }

/**
 * beforeValidate is called before a model is validated, you can use this callback to
 * add behavior validation rules into a models validate array. Returning false
 * will allow you to make the validation fail.
 *
 * @param Model $model Model using this behavior
 * @return mixed False or null will abort the operation. Any other result will continue.
 */
	public function beforeValidate(Model $model, $options = []) {
	    parent::beforeValidate($model, $options);
	}

/**
 * beforeSave is called before a model is saved. Returning false from a beforeSave callback
 * will abort the save operation.
 *
 * Extract command nets these variables:
 * array(
 *   'parent' => 'parent_id',
 *   'ancestors' => 'ancestor_list',
 *   'sequence' => 'sequence',
 *   'scope' => '1 = 1',
 *   'recursive' => (int) -1
 *   )
 *
 * @param Model $model Model using this behavior
 * @return mixed False if the operation should abort. Any other result will continue.
 */
    public function beforeSave(Model $model, $options = []) {
        extract($this->settings[$model->alias]); //see doc block for list of variables

        if (!$model->id || !$model->exists()) {
            //THIS IS FOR A NEW RECORD

            if (array_key_exists($parent, $model->data[$model->alias]) && $model->data[$model->alias][$parent] != '') {
                // a parent is specified. Set it's ancestor list based upon it's parent's ancestor list
                $model->data[$model->alias][$ancestors] = $this->establishAncestorList($model, $parent);
                //check if there's a sequence entry in array, if none, extablish one
                if ($sequence) {
                    //establish sequence from parent's children's max sequence
                    if(!array_key_exists($sequence, $model->data[$model->alias]) || $model->data[$model->alias][$sequence] == '' || $model->data[$model->alias][$sequence] == '0'){
                        $maxSequence = $this->getMaxSequence($model, $model->data[$model->alias][$parent]);
                        $model->data[$model->alias][$sequence] = ($maxSequence[0]['max_sequence_number']) + 1;
                    } else {
                        $this->sequenceChange = true;
                    }
                }
            } else {
                // On a parentless array, include the required default ancestor list
                $model->data[$model->alias][$ancestors] = ',';
                $model->data[$model->alias][$sequence] = 1;
            }
            //ALL BELOW FOR RECORDS ALREADY IN DATABASE
            //check to be sure there's an id on the record
        } elseif (array_key_exists($parent, $model->data[$model->alias])) {
            //Do this to an existing record where the parent or sequence exists as a part of the save array
            $currParent = $model->find('first', array(
                'conditions' => array($model->escapeField() => $model->data[$model->alias][$model->primaryKey])));
            if ($currParent[$model->alias][$parent] != $model->data[$model->alias][$parent]) {
                $model->data[$model->alias][$ancestors] = $this->establishAncestorList($model, $parent);
                $this->parentChange = true;
                if ($sequence) {
                    $this->sequenceChange = true;
                }
            } elseif (!$this->afterSaveFlag && $sequence && array_key_exists($sequence, $model->data[$model->alias])) {
                if ($currParent[$model->alias][$sequence] != $model->data[$model->alias][$sequence]) {
                    $this->sequenceChange = true;
                }
            }
            //regardless how you arrived, you now have a proper tree array, carry on
            return $model->data;
        }
    }

/**
 * afterSave is called after a model is saved.
 *
 * @param Model $model Model using this behavior
 * @param boolean $created True if this save created a new record
 * @return boolean
 */
    public function afterSave(Model $model, $created, $options = []) {
        extract($this->settings[$model->alias]);
	if ($this->parentChange) {
	    $id = $model->data[$model->alias][$model->primaryKey];
	    $parent_id = $model->data[$model->alias][$parent];
	    $this->moveToNewParent($model, $id, $parent_id);
	}
	if ($this->sequenceChange) {
	    debug('SEQUENCE CHANGE');
	    $this->resequenceSiblings($model, $model->data[$model->alias][$parent]);
	}
	if ($this->parentChange || $this->sequenceChange) {
	    $this->sequenceChange = FALSE;
	    $this->parentChange = FALSE;
        $this->afterSaveFlag = TRUE;
	    $model->saveMany($this->accumulator, array('validate' => false));
	}
    }

/**
 * Before delete is called before any delete occurs on the attached model, but after the model's
 * beforeDelete is called. Returning false from a beforeDelete will abort the delete.
 *
 * @param Model $model Model using this behavior
 * @param boolean $cascade If true records that depend on this record will also be deleted
 * @return mixed False if the operation should abort. Any other result will continue.
 */
    public function beforeDelete(Model $model, $cascade = true) {
	parent::beforeDelete($model, $cascade);
    }

/**
 * DataSource error callback
 *
 * @param Model $model Model using this behavior
 * @param string $error Error generated in DataSource
 * @return void
 */
    public function onError(Model $model, $error) {
	parent::onError($model, $error);
    }

    public function newNode(Model &$Model, $data) {
        
    }

    public function removeFromTree(Model &$Model, $id, $children = False) {
        
    }

    /**
     * Get decendents of record[$id]
     *
     * @param Model $Model
     * @param type $id
     * @param boolean $group True to group the siblings, False for flat data
     * @param array $options possible 'fields' and 'contain' settings
     * @return type
     * @throws NotFoundException
     */
    public function getDecendents(Model &$Model, $id, $group = true, $conditions = array(), $options = array()) {
        extract($this->settings[$Model->alias]);
        if (!$Model->exists($id)) {
            throw new NotFoundException(__('Invalid catalog'));
        }
        //add any user supplied conditions to the array, while protecting ID
        $localConditions = array_merge($conditions, array($Model->escapeField($ancestors).' LIKE ' => "%,$id,%"));
        $options['conditions'] = $localConditions;
        $options['order'] = array("{$Model->escapeField($ancestors)} ASC", "{$Model->escapeField($sequence)} ASC");
        $result = $Model->find('all', $options);
        // return flat records or sibling groups
        if ($group && $result) {
            return $this->nodeGroups($Model, $result);
        }
        return $result;
    }

    /**
     * Get siblings of record[$id]
     *
     * @param Model $Model
     * @param type $id
     * @param boolean $group True to group the siblings, False for flat data
     * @return type
     * @throws NotFoundException
     */
    public function getSiblings(Model &$Model, $id, $group = true) {
        extract($this->settings[$Model->alias]);
        if (!$Model->exists($id)) {
            throw new NotFoundException(__('Invalid catalog'));
        }
        // Look up the parent id of this record
        $parentId = $Model->field($parent, array(
            $Model->escapeField('id') => $id
        ));
        // Get records with the same parent
        $result = $Model->find('all', array(
            'conditions' => array(
                $Model->escapeField($parent) => $parentId
            ),
            'order' => "{$Model->escapeField($sequence)} ASC"
        ));
        // return flat records or sibling groups
        if ($group && $result) {
            return $this->nodeGroups($Model, $result);
        }
        return $result;
    }

    /**
     * Get the children of record[$id]
     *
     * @param Model $Model
     * @param type $id
     * @param boolean $group True to group the siblings, False for flat data
     * @return type
     * @throws NotFoundException
     */
    public function getChildren(Model &$Model, $id, $group = true, $options = array()) {
        extract($this->settings[$Model->alias]);
        if (!$Model->exists($id)) {
            throw new NotFoundException(__('Invalid catalog'));
        }
        
        //setup default options
        $order = ($sequence) ? "{$Model->escapeField($sequence)} ASC" : array();
        $defaultOptions = array(
            'conditions' => array($Model->escapeField($parent) => $id),
            'order' => array($order),
        );
        
        //use mergeOptions to produce final options
        $finalOptions = $this->mergeOptions($options, $defaultOptions);
        
        //getChildren defaulted to contain => false, so add this on, if it hasn't been overwritten
        if(!isset($finalOptions['contain'])){
            $finalOptions['contain'] = false;
        }
        
        // Get records with the id as a parent
        $result = $Model->find('all', $finalOptions);
        
        // return flat records or sibling groups
        if ($group && $result) {
            return $this->nodeGroups($Model, $result);
        }

        return $result;
    }
    
    /**
     * Manage supplied and default option merging for all functions
     * 
     * @param array $options The call-provided options for the function
     * @param array $defaultOptions The default options for the function
     * @return array The final options
     */
    private function mergeOptions($options, $defaultOptions){
        foreach ($options as $index => $array) {
            if(isset($defaultOptions[$index])){
                $options[$index] = array_merge($defaultOptions[$index], $options[$index]);
                unset($defaultOptions[$index]);
            } else {
                $options[$index] = $options[$index];
            }
        }
        return array_merge($options, $defaultOptions);
    }

    /**
     * Get the node 'id' and its decendents, flat or grouped
     * 
     * @param Model $Model
     * @param int $id The root node for the group
     * @param boolean $group group the returned nodes under their parent id's or not
     * @param string $conditions optional additional conditions for the query
     * @return mixed The results array, or false if no $headRecord found
     */
    public function getFullNode(Model &$Model, $id, $group = true, $conditions = array()) {
        extract($this->settings[$Model->alias]);
		$defaultConditions = array(
			$Model->escapeField('id') => $id,
			$Model->escapeField('active') => 1
		);
        //add any user supplied conditions to the array, while protecting ID
        $localConditions = array_merge($conditions, $defaultConditions);
        //find the base record set
        $headRecord = $Model->find('first', array(
            'conditions' => $localConditions
        ));
        //if no found base record set, return
        if(empty($headRecord)){
            return array();
        }
        if (!isset($nodeHead)) {
            $nodeHead = array();
        }
            //group the records if requested
        //*****************IF YOU ARE GETTING AN ERROR WITH A NULL LIST MEMBER
        //*****************YOU MAY HAVE CONNECTED TO A NON-FOLDER PARENT
        if ($group) {
            $nodeHead[$headRecord[$Model->alias][$parent]][-1] = $headRecord[$Model->alias];
        } else {
            $nodeHead[-1] = $headRecord;//[$Model->alias];
        }
		unset($localConditions[$Model->escapeField('id')]);
        $decendents = $this->getDecendents($Model, $id, $group, $localConditions);
        $fullNode = $nodeHead + $decendents;
        return $fullNode;
    }

    /**
     * Assemble the ancestor list for the children of this record
     * 
     * @param type $id The id of a parent
     */
    public function establishAncestorList(Model &$Model, $parent_id) {
        extract($this->settings[$Model->alias]);
        // a parent is specified. pull it for its ancestor list
        $parentNode = $Model->find('first', array(
            'conditions' => array($scope, $Model->escapeField() => $Model->data[$Model->alias][$parent]),
            'fields' => array($Model->primaryKey, $ancestors), 'recursive' => $recursive
        ));
//debug(array($scope, $Model->escapeField() => $Model->data[$Model->alias][$parent]));
        if (!$parentNode || !$parentNode[$Model->alias][$ancestors]) {
            // bad parent node!
            throw new NotFoundException('Parent node not found or found with invalid ancestor list');
        }
        return $parentNode[$Model->alias][$ancestors] . $parentNode[$Model->alias][$Model->primaryKey] . ',';
    }

    /**
     * Get ancestors of record[$id]
     *
     * @param Model $Model
     * @param string $id
     * @param boolean $group True to group the siblings, False for flat data
     * @return mixed array of records or false
     * @throws NotFoundException
     */
    public function getAncestors(Model &$Model, $id, $group = true, $conditions = array()) {
        extract($this->settings[$Model->alias]);
        if (!$Model->exists($id)) {
            throw new NotFoundException(__('Invalid catalog'));
        }
        // Get this records ancestor list and explode it
        $ancestorList = explode(',', $Model->field($ancestors, array(
                    $Model->escapeField('id') => $id)));
        // Get records who's id is in the list
        $localConditions = array_merge($conditions, array($Model->escapeField('id') => $ancestorList));
        $result = $Model->find('all', array(
            'conditions' => $localConditions,
            'order' => "{$Model->escapeField($sequence)} ASC"
        ));
        // return flat records or sibling groups
        if ($group && $result) {
            return $this->nodeGroups($Model, $result);
        }
        return $result;
    }

    private function buildAncestors(Model &$Model, $parent_id) {
        
    }

    private function lockNode(Model &$Model, $id) {
        
    }

    /**
     * Take a tree array in its flat state and organize it by parent groups
     *
     * @param array $flatTree The tree, flat, sorted
     * @param string $parent_id The index that hold the parent id
     * @return array Each node group in a parent_id indexed element
     */
    public function nodeGroups(Model &$Model, $flatTree) {
        extract($this->settings[$Model->alias]);
        $groups = array();
        foreach ($flatTree as $node) {
            $groups[$node[$Model->alias][$parent]][] = $node[$Model->alias];
        }
        return $groups;
    }

//    /**
//     * Change the order of Siblings
//     *
//     * This does not handle cases where a record gets a new parent.
//     * Only cases where an existing sibling set gets a new order.
//     *
//     * @param Model $Model
//     * @param int $id The record being moved
//     * @param int $previousSibling The record that will preceed the moved record (or '')
//     * @throws InvalidArgumentException
//     */
//    public function rearrangeSequence(Model &$Model, $id, $previousSibling) {
//        extract($this->settings[$Model->alias]);
//        $parentId = $Model->field($parent, array($Model->escapeField('id') => $id));
//        $SiblingparentId = $Model->field($parent, array($Model->escapeField('id') => $previousSibling));
//        if ($parentId != $SiblingparentId) {
//            throw new InvalidArgumentException('The two records don\'t have the same parent so they can\'t be sequenced.');
//        }
//        $afterMe = $this->fetchSequenceValue($Model, $previousSibling);
//        $this->setSequenceValue($Model, $id, $afterMe);
//        $this->resequenceSiblings($Model, $parentId);
//    }

    /**
     * Fetch the sequence value for a specified record
     *
     * @param Model $Model
     * @param int $id The record which will provide the sequence value
     * @return float The sequence value
     */
    private function fetchSequenceValue(Model &$Model, $id) {
        extract($this->settings[$Model->alias]);
        return $Model->field($sequence, array(
                    $Model->escapeField('id') => $id
        ));
    }

    /**
     * Slip a record in between the squence steps of a sibling group
     *
     * Using a half-sequence step, place a record after another specific
     * record in a sibling group, and before any other record in the set
     *
     * @param Model $Model
     * @param type $id The record to insert
     * @param type $sequenceValue The sequence position to be after
     */
    private function setSequenceValue(Model &$Model, $id, $sequenceValue) {
        extract($this->settings[$Model->alias]);
        $saveArray = array(
            $Model->alias => array(
                'id' => $id,
                $sequence => $sequenceValue + .5
        ));
        $Model->save($saveArray);
    }

    /**
     * Reserialize the sequence values of siblings starting from 1
     *
     * @param Model $Model
     * @param int $parent_id The parent id of the records to sequences
     * @return boolean success or failure of the save
     */
    private function resequenceSiblings(Model &$Model, $parent_id) {
        extract($this->settings[$Model->alias]);
        $siblings = $this->getChildren($Model, $parent_id, false);
        foreach ($siblings as $index => $value) {
            $this->accumulator[$siblings[$index][$Model->alias][$Model->primaryKey]][$Model->alias][$sequence] = $index + 1;
            $this->accumulator[$siblings[$index][$Model->alias][$Model->primaryKey]][$Model->alias][$Model->primaryKey] = $siblings[$index][$Model->alias][$Model->primaryKey];
        }
    }

    public function getMaxSequence(Model &$Model, $id, $fromParent = true) {
        extract($this->settings[$Model->alias]);
        if (!$fromParent) {
            //find the parent of the record id we came in with, set it to $id
            $nearestParent = $Model->find('first', array(
                'fields' => array($parent),
                'conditions' => array($Model->escapeField('id') => $id)
            ));
            $id = $nearestParent[$Model->alias][$parent];
        }

        $max = $Model->find('first', array(
            'fields' => array(
                "MAX({$Model->escapeField($sequence)}) AS max_sequence_number"
            ),
            'conditions' => array($Model->escapeField($parent) => $id)
        ));
        return $max;
    }

    /**
     * Move a node and its decendents to a new parent and place it in sequence after {$sibling_id}
     *
     * Moving the node is a simple parent_id change
     * but there is the recursive adjustment of the
     * ancestor_list to consider for node and decendents
     *
     * After the move, sequence tasks are handled
     *
     * @param Model $Model
     * @param type $id
     * @param type $parent
     * @param type $sibling
     */
    private function moveToNewParent(Model &$Model, $id, $parent_id) {
        extract($this->settings[$Model->alias]);
        // get the new parent's ancestor list
        $newAncestors = $Model->field($ancestors, array($Model->escapeField('id') => $parent_id));
        //check for prefix comma on ancestor list and add parent id
        $newAncestors.= (($newAncestors == '') ? ',' : '') . $parent_id . ',';

        //pull ourself and all our descendents
        $nodeGroups = $this->getFullNode($Model, $id);
        //set arrarys of data for updateAncestorLists
        $keys = array_keys($nodeGroups);
        $group = $nodeGroups[$keys[0]];
        $params = array(
            'parent_id' => $parent_id,
            'newAncestors' => $newAncestors, // The ancestor list for this level
            'group' => $group, // the first sibling set to process
            'nodeGroups' => $nodeGroups // the full array to process
        );
        $this->updateAncestorLists($Model, $params);
    }

    /**
     *
     * @param Model $Model
     * @param type $id
     * @param type $parent_id
     * @return boolean
     */
    public function checkParentChange(Model &$Model, $id, $parent_id) {
        extract($this->settings[$Model->alias]);
        $oldParentId = $Model->field($parent, array($Model->escapeField('id') => $id));
        if ($parent_id == $oldParentId) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 
     * 
     * The changed data is stored in $this->accumulator
     * 
     * @param Model $Model
     * @param type $params
     */
    public function updateAncestorLists(Model &$Model, $params) {
        extract($this->settings[$Model->alias]);
        extract($params);
        foreach ($group as $node) {
            // Set the new ancestor list
//            $node[$ancestors] = $newAncestors;
            $this->accumulator[$node[$Model->primaryKey]][$Model->alias] = array(
                $Model->primaryKey => $node[$Model->primaryKey],
                $ancestors => $newAncestors,
                $parent => $parent_id
            );
            // see if this node is a parent
            if (isset($nodeGroups[$node[$Model->primaryKey]])) {
                // add this id to the ancestor list
                // recurse for this child node
                $subParams = array(
                    'parent_id' => $node[$Model->primaryKey],
                    'newAncestors' => $newAncestors . $node[$Model->primaryKey] . ",", // The ancestor list for this level
                    'group' => $nodeGroups[$node[$Model->primaryKey]], // the first sibling set to process
                    'nodeGroups' => $nodeGroups // the full array to process
                );
                $this->updateAncestorLists($Model, $subParams);
            }
        }
    }

}