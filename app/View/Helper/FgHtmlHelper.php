<?php

App::uses('HtmlHelper', 'app/lib/Cake/View/Helper');
App::uses('FgForm', 'Cake/View/Helper');
App::uses('Session', 'Helper');
App::uses('Number', 'Helper');
App::uses('Time', 'Helper');
App::uses('AppHelper', 'View/Helper');

/**
 * CakePHP Helper
 * @author dondrake
 */
class FgHtmlHelper extends HtmlHelper {

	// <editor-fold defaultstate="collapsed" desc="Properties">

	public $helpers = array('FgForm', 'Markdown.Markdown', 'Session', 'Number', 'Time');

	/**
	 * Array to verify that all the nodes output for a requested tree
	 * 
	 * When tree output (select tree) is composed of completely disconnected
	 * starting nodes, only one will be output. We need to watch and force
	 * the other starting nodes to output. Since multiple methods are
	 * involved, and recursion is involved, this property will help
	 * track progress without complicated parameter passing by reference.
	 *
	 * @var array A list of node roots to output
	     */
	public $nodeRoots = array();

	/**
	 * The offset to make indents start at zero
	 * 
	 * If there are serveral roots, the lowest nest value
	 * will be subtracted from all others to make the
	 * indenting start at zero. We'll scan for
	 * lowest, so start at an arbitrarily high number
	 *
	 * @var int
	 */
	public $indentBase = 10000;


	/**
	 * A reposistory for user & catalog access selections
	 * 
	 * To make grain access-checkboxes work
	 *
	 * @var array
	 */
	private $selected = array();
	
	/**
	 * Value necessary to construct 'available' information lines for output
	 *
	 * @var array
	 */
	private $available = array();

	/**
	 * Class lookup for Catalog Tree LI elements
	 *
	 * @var array 
	 */
	private $types = array(
			'1' => 'li-kit',
			'2' => 'li-folder',
			'4' => 'li-product',
			'8' => 'li-component'
		);
	
// </editor-fold>
	
	//============================================================
    // ID/HASH SECURITY ROUTINES TO MOVE TO SEPARATE CLASS
    //============================================================

    /**
     * Make a link/id/secureHash
     * 
     * To prevent deliberate or accidental id changes
     * in a URL, write the id and a secure hash into the urrl. 
     * The hash will allow user/session values to be used to 
     * validate the id when the form arrives back at the server
     * 
     * Accepts all standard Html->link params
     * 
     * @param type $title
     * @param type $id The id to carry in the link
     * @param type $url
     * @param type $options
     * @param type $confirmMessage
     * @return string A link
     */
    public function secureLink($title, $id, $url = array(), $options = array(), $confirmMessage = false) {
        $url = array_merge($url, array($id, $this->secureHash($id)));
        return $this->link($title, $url, $options, $confirmMessage);
    }

    /**
     * Provide a complete secured select list item
     *
     * Concatenate the actual id and chosen delimeter with the secureHash
     *
     * @param string $id The record id to secure
     * @param string $delimeter The delimeter to concat the string on, default to '/'
     * @return string The concatenation
     */
    public function secureSelect($id, $delimeter = '/') {
        return $id . $delimeter . $this->secureHash($id);
    }

	public function setSelected($selected) {
		$this->selected = array_flip($selected);
	}
	//============================================================
    // RECURSIVE PATTERN FOR TREE OUTPUT
    //============================================================

    /**
     * Output the a tree (or nodes) in the requested style
     * 
     * Given a treeType to control output style, 
     * render a full tree of unlimited nest-depth
     * Or
     * Output a series of nodes where entries in 
     * nodeRoots will identify the starting points
     * 
     * @param string $treeType The output style being requested
     * @param array $nodeGroups The sibling-grouped node records
     * @return string The HTML for output
     */
    public function recursiveTree($treeType, $nodeGroups, $nodeRoots, $accumulator = '') {
		$this->indentBase($nodeRoots); // only used for checkboxes right now
		
        if (empty($this->nodeRoots)) {
            $keys = array_keys($nodeGroups);
            $this->nodeRoots = array_flip($keys);
        }
        $start = array_keys($this->nodeRoots);

        $depth = 1;
			$group = (is_array($nodeGroups) && !empty($nodeGroups)) ? $nodeGroups[$start[0]] : '';
			$params = array(
				'treeType' => $treeType, // which tree variant to output
				'group' => $group, // the first sibling set to process
				'depth' => $depth, // track nest levels to indent-format the source 
				'parentNodeType' => isset($node['type']) ? $node['type'] : '',
				'nodeGroups' => $nodeGroups, // the full array to process
				'nodeRoots' => $nodeRoots // root nodes, the ones in user_user or catalog_user
			);
			$accumulator .= $this->outputGroup($params);
        if (!empty($this->nodeRoots)) {
            $this->recursiveTree($treeType, $nodeGroups, $this->nodeRoots, $accumulator);
        }
        return $accumulator;
    }
	
	/**
	 * Calculate the indent base so we can start at zero
	 * 
	 * @param type $nodeRoots
	 */
	private function indentBase($nodeRoots) {
		foreach ($nodeRoots as $root) {
			$indent = substr_count($root['ancestor_list'], ',')-1;
			$this->indentBase = ( $indent < $this->indentBase) ? $indent : $this->indentBase;
		}
	}

	/**
     * The recursive output of tree nodes
     * 
     * @param array $params Stuff to extract and use:
     * 	      string $treeType The control value to output variant
     * 	      array $group The sibling list for this group
     * 	      int $depth Count of nesting layers
     * 	      array $nodeGroups Master array of all nest chunks indexed by parent_id
     */
    private function outputGroup($params) {
        extract($params);
		if ($group === '') {
			return;
		}
        // open a UL tag
        $this->_View->append($treeType, "\r" . str_repeat("\t", $depth++)
                . $this->outputGroupUl($params) . "\r");
        // loop though lines for this UL
        foreach ($group as $count => $node) {
            unset($this->nodeRoots[$node['parent_id']]);
            // open an LI tag and text node
            $this->_View->append($treeType, str_repeat("\t", $depth)
                    . $this->outputGroupLi($node, $params)
                    . $this->outputGroupLiText($node, $params));
            // see if this node is a parent
            if (isset($nodeGroups[$node['id']])) {
                // recurse to output this child node
                $subParams = array(
                    'treeType' => $treeType,
                    'group' => $nodeGroups[$node['id']],
                    'depth' => $depth,
					'parentNodeType' => isset($node['type']) ? $node['type'] : '',
                    'nodeGroups' => $nodeGroups,
                    'nodeRoots' => $nodeRoots
                );
                $this->outputGroup($subParams);
                $sub = true;
            }
            // close the LI
            // if a sub UL was made insert it and close LI after that
            $this->_View->append($treeType, (isset($sub)) ? str_repeat("\t", $depth) . "</li>\r" : "</li>\r");
        }
        // close the UL
        $this->_View->append($treeType, str_repeat("\t", --$depth) . "</ul>\r");
    }

    //============================================================
    // TREE COMPONENT OUTPUT SELECTORS
    //============================================================

    /**
     * Selector for UL output based on treeType
     * 
     * @return type The prepared UL opening tag
     */
    private function outputGroupUl($params) {
        switch ($params['treeType']) {
            case 'usersSelect':
            case 'catalogsSelect':
                return $this->outputGroupUlSelect($params);
                break;
            case 'usersEdit':
            case 'catalogsEdit':
                return $this->outputGroupUlEdit($params);
                break;
			case 'checkbox':
				return '';
				break;
			case 'observer':
			case 'observed':
				return;
				break;
            default:
                return $this->outputGroupUlPlain($params);
                break;
        }
    }

    /**
     * Selector for LI tag output base on treeType
     * 
     * @param array $node The current node being output
     * @param type $params
     * @return string The prepared LI opening tag
     */
    private function outputGroupLi($node, $params) {
        switch ($params['treeType']) {
            case 'usersSelect':
                $params['attributes'] = array('class' => ($node['role'] != '') ? $node['role'] : 'Inherit');
                return $this->outputGroupLiSelect($node, $params);
                break;
            case 'catalogsSelect':
                $params['attributes'] = array();
                return $this->outputGroupLiSelect($node, $params);
                break;
            case 'usersEdit':
                $params['class'] = ($node['role'] != '') ? $node['role'] : 'Inherit';
                return $this->outputGroupLiEdit($node, $params);
                break;
            case 'catalogsEdit':
                $params['class'] = '';
                return $this->outputGroupLiEdit($node, $params);
                break;
			case 'checkbox':
				return;
				break;
			case 'observer':
			case 'observed':
				return;
				break;
            default:
                return $this->outputGroupLiPlain($node, $params);
                break;
        }
    }

    /**
     * Selector for LI text node output based on treeType
     * 
     * Select Trees output the same for Users and Catalogs
     * Switch is primarily to set appropriate name content for Edit Trees
     * Based upon differences in how Users and Catalogs are handled
     * 
     * @param array $node The current node being output
     * @param type $params
     * @return type The prepared innerHtml for the LI
     */
    private function outputGroupLiText($node, $params) {
        switch ($params['treeType']) {
            case 'usersSelect':
            case 'catalogsSelect':
                return $this->outputGroupLiTextSelect($node, $params);
                break;
			case 'item_importsSelect':
				return $this->outputGroupLiSimpleImport($node, $params);
				break;
            case 'usersEdit':
                $params['name'] = $this->discoverName($node) . ' (' . $node['role'] . ')';
                return $this->outputGroupLiTextEdit($node, $params);
                break;
            case 'catalogsEdit':
                $params['name'] = $node['name'];
                return $this->outputGroupLiTextEdit($node, $params);
                break;
			case 'checkbox':
				return $this->outputGroupCheckboxes($node, $params);
				break;
			case 'observer':
				$email = true;
				return $this->outputGroupOptions($node, $params, $email);
				break;
			case 'observed':
				$email = false;
				return $this->outputGroupOptions($node, $params, $email);
				break;
            default:
                return $this->outputGroupLiTextPlain($node, $params);
                break;
        }
    }

    //============================================================
    // TREE OUTPUT VARIATION: SIDEBAR SELECT TREE
    //============================================================

    /**
     * UL tag output for a sidebar selector tree
     * 
     * @param array $params
     * @return type The prepared UL opening tag
     */
    private function outputGroupUlSelect($params) {
        extract($params);
        return $this->tag('ul', NULL, array());
    }

    /**
     * LI tag output for a sidebar selector tree
     * 
     * @param array $params at least an attributes array element
     * @return string The prepared LI opening tag
     */
    private function outputGroupLiSelect($node, $params) {
        extract($params);
        return $this->tag('li', NULL, $attributes);
    }

    /**
     * LI text-node output for a sidebar selector tree
     * 
     * @param array $params
     * @return type The prepared innerHtml for the LI
     */
    private function outputGroupLiTextSelect($node, $params) {
//        debug($params);die;
        extract($params);
        $parent = isset($nodeGroups[$node['id']]);

        // if node has children it needs an expand/collapse tool
        if ($parent) {
            $checkboxClass = $parent ? '' : ' class="hide"';
            $checkboxId = ' id="side_check_' . $node['id'] . '"';
            $expandTool = '<input type="checkbox"' . $checkboxClass . $checkboxId . ' />';

            // if this is a leaf, we need a filler to take the place of the missing checkbox
			// for true item/user leaves, just a dot will do
			// for folder leaves, we need a folder. js will check them if need be.
        } else {
			if ($node['folder']) {
				$marker = 'folder.png';
				$attributes = array('class' => 'folder-filler');
			} else {
				$marker = 'transparent.png';
				$attributes = array('class' => 'filler');
			}
			$expandTool = $this->image($marker, $attributes);
        }
        if (strpos($this->action, 'Grain') === false) {
            $altAction = $this->action . 'Grain';
        } else {
            $altAction = str_replace('Grain', '', $this->action);
        }
        $altLink = Router::url(array('action' => $altAction, $node['id'], $this->secureHash($node['id'])));
        if (stristr($this->request->params['action'], 'user')) {
            $name = $this->discoverName($node);
        } else {
            $name = $node['name'];
        }
        $mainLink = $this->secureLink($name, $node['id'], array('action' => $this->action), array('altLink' => $altLink));


        return $expandTool . ' ' . $mainLink;
    }

    /**
     * LI text-node output for a sidebar selector tree
	 * 
	 * This version handles itemImport which doesn't need to show 
	 * tree leaves. Only container nodes are valid destinations for 
	 * imported items. 
	 * 
	 * <a> tags are required for formatting but the choice will be handled 
	 * ajax processes so the links are also hammered down to simplest form 
     * 
     * @param array $params
     * @return type The prepared innerHtml for the LI
     */
    private function outputGroupLiSimpleImport($node, $params) {
        extract($params);
        $parent = isset($nodeGroups[$node['id']]);

        // if node has children it needs an expand/collapse tool
        if ($parent) {
            $checkboxClass = $parent ? '' : ' class="hide"';
            $checkboxId = ' id="side_check_' . $node['id'] . '"';
            $expandTool = '<input type="checkbox"' . $checkboxClass . $checkboxId . ' />';

            // if this is a leaf, we need a filler to take the place of the missing checkbox
			// for true item/user leaves, just a dot will do
			// for folder leaves, we need a folder. js will check them if need be.
        } else {
			if ($node['folder']) {
				$marker = 'folder.png';
				$attributes = array('class' => 'folder-filler');
			} else {
				$marker = 'transparent.png';
				$attributes = array('class' => 'filler');
			}
			$expandTool = $this->image($marker, $attributes);
        }

		if(isset($marker) && $marker === 'transparent.png') {
			return '';
		}
        $mainLink = $this->link($node['name'], $node['id'], array('action' => ''));


        return $expandTool . ' ' . $mainLink;
    }

    /**
     * return display name based on exisitance of name virtual field
     * 
     * @param array $data the complete returned data, with the field names as indexes
     * @return string The proper display name
     */
    public function discoverName($data) {
        if (!empty($data['name']) && $data['name'] != ' ') {
            return $data['name'];
        }
        return $data['username'];
    }

    /**
     * Creates div and class container for user and catalog grain editing
     * 
     * @param type $folder
     * @param type $active
     * @return type
     */
    public function outputGroupAttributes($folder, $active) {
        $classVar = ($active) ? 'Active' : 'Inactive';
        $classVar .= ($folder) ? $this->image('folder.png', array(
                    'class' => 'folder'
                )) : $this->image('transparent.png', array(
                    'class' => 'filler'
        ));
        return $classVar;
    }

    //============================================================
    // TREE OUTPUT VARIATION: PLAIN TREE 
    // Plain also serves as the default for unknow tree types
    //============================================================

    /**
     * UL tag output for a plain tree
     * 
     * @param array $params The current node being output
     * @return type The prepared UL opening tag
     */
    private function outputGroupUlPlain($params) {
        extract($params);
        $keys = array_keys($group);
        return $this->tag('ul', NULL, array(
                    'id' => $this->secureSelect($group[$keys[0]]['parent_id'], 'ul'),
                    'class' => 'plain'));
    }

    /**
     * LI tag output for a plain tree
     * 
     * @param array $params
     * @return string The prepared LI opening tag
     */
    private function outputGroupLiPlain($node, $params) {
        extract($params);
		$attributes = array(
                    'id' => $this->secureSelect($node['id'], 'li'),
                    'sequence' => $node['sequence'],
                    'class' => 'plain'
        );
		if (isset($node['type'])) {
			$attributes['type'] = $node['type'];
		}
        return $this->tag('li', NULL, $attributes);
    }

    /**
     * LI text-node output for a plain tree
     * 
     * @param array $params
     * @return type The prepared innerHtml for the LI
     */
    private function outputGroupLiTextPlain($node, $params) {
        extract($params);
        return $this->discoverName($node);
    }

    //============================================================
    // TREE OUTPUT VARIATION: DRAG/DROP EDITABLE TREE 
    //============================================================

    /**
     * UL tag output for an editable tree
     * 
     * @param array $params
     * @return type The prepared UL opening tag
     */
    private function outputGroupUlEdit($params) {
        extract($params);
        $keys = array_keys($group);
		$targetType = $parentNodeType & (KIT | FOLDER | PRODUCT | COMPONENT);
		$class = ($targetType > 0 ? str_replace('li', 'ul', $this->types[$targetType]) : '') . ($targetType != KIT ? ' sort' : '');
        return $this->tag('ul', NULL, array(
				'id' => $this->secureSelect($group[$keys[0]]['parent_id'], 'ul'),
				'class' => trim($class)
			));
    }

    /**
     * LI tag output for an editable tree
     * 
     * @param array $params
     * @return string The prepared LI opening tag
     */
    private function outputGroupLiEdit($node, $params) {
        extract($params);
		$attributes = array(
			'id' => $this->secureSelect($node['id'], 'li'),
			'sequence' => $node['sequence'],
			'class' => $class,
			'customer' => (isset($node['customer'])) ? $node['customer'] : false
        );
		if (isset($node['type'])) {
			$attributes['type'] = $node['type'];
			$targetType = $node['type'] & (KIT | FOLDER | PRODUCT | COMPONENT);
			$attributes['class'] = $class . $this->types[$targetType];
		}
        return $this->tag('li', NULL, $attributes);
    }

    /**
     * LI text-node output for an editable tree
     * 
     * @param array $params
     * @return type The prepared innerHtml for the LI
     */
    private function outputGroupLiTextEdit($node, $params) {
        extract($params);
        $parent = isset($nodeGroups[$node['id']]);

        // if node has children it needs an expand/collapse tool
        if ($parent) {
            $checkboxClass = $parent ? '' : ' class="hide"';
            $checkboxId = ' id="edit_check_' . $node['id'] . '"';
            $expandTool = '<input type="checkbox"' . $checkboxClass . $checkboxId . ' />';

            // if this is a leaf, we need a filler to take the place of the missing checkbox
        } else {
			if ($node['folder']) {
				$marker = 'folder.png';
				$attributes = array('class' => 'folder-filler');
			} else {
				$marker = 'transparent.png';
				$attributes = array('class' => 'filler');
			}
			$expandTool = $this->image($marker, $attributes);
        }
        
        //Variable setup
        //base class
        $gearClass = 'gear ';
        
        //setup kit class if in kit situation
        if (isset($node['kit'])) {
            $kit = $node['kit'];
        } else {
            $kit = false;
        }
        
        //Accumulating class setter
        if (isset($nodeRoots[$node['id']])) {
            $gearClass .= 'root ';
        } 
        if(!$node['folder'] && !$kit) {
            $gearClass .= 'item ';
        }
        if($node['folder']) {
            $gearClass .= 'folder ';
        }
        if($kit) {
            $gearClass .= 'kit ';
        }
        
		if (isset($node['watchers'])) {
			$user = ($node['watchers'] > 1) ? 'users' : 'user';
			$watchers = $this->countAlert($node['watchers'], "Watched by {$node['watchers']} $user. (Descendents watched too)", 0);
		} else {
			$watchers = '';
		}
		
        //Set image class & return element
        $img = $this->image('gear.png', array(
            'class' => $gearClass
        ));
        return $expandTool . $img .
                $this->tag('span', $name . $watchers);
    }

    //============================================================
    // TREE OUTPUT VARIATION: CHECKBOXES
    //============================================================
	
	private function outputGroupCheckboxes($node, $params) {
		extract($params);
		if (isset($group[0]['username'])) {
			$alias = 'User';
			$name = $this->discoverName($node);
            if($this->request->params['action'] == 'edit_renderEditForm'){
                $targetName = 'data[UserManaged][UserManaged][]';
            } else {
                $targetName = 'data[UserManaged][]';
            }
		} elseif (isset($group[0]['kit'])) {
			if (!$node['folder']) {
				return '';
			}
			$alias = 'Catalog';
			$name = $node['name'];
            if($this->request->params['action'] == 'edit_renderEditForm'){
                $targetName = 'data[Catalog][Catalog][]';
            } else {
                $targetName = 'data[Catalog][]';
            }
		} else {
			return 'Array pattern not recorgnized';
		}
		$indent = substr_count($node['ancestor_list'], ',') - $this->indentBase;
		$secureId = $this->secureSelect($node['id']);
			echo $this->FgForm->input($alias . str_replace('/', '', $secureId),array(
				'class' => "indent-$indent",
				'type' => 'checkbox',
				'hiddenField' => false,
				'label' => $name,
				'value' => $secureId,
				'name' => $targetName,
				'checked' => isset($this->selected[$secureId]),
				'div' => array('class' => 'checkbox')
			));
		}

	public function outputGroupOptions($node, $params, $email) {
		if ($email && preg_match('/[@]+/', $node['username']) == 0) {
			return;
		}
		$name = $this->discoverName($node);
		$class = 'indent-' . (substr_count($node['ancestor_list'], ',')-1);
		echo $this->tag('option', $name, array(
			'value' => $node['id'],
			'class' => $class,
			'selected' => isset($this->selected[$node['id']])
		));
	}
    //============================================================
    // GENERAL TREE FUNCTIONS 
    //============================================================

	/**
	 * Make an alert triangle that shows the number of items
	 * 
	 * @param int $count The number to display
	 * @param string $title The elements title attribute (shows on hover)
	 * @param int $threshold Don't display unless number is larger than this
	 * @return string
	 */
    public function countAlert($count, $title = false, $threshold = 1, $options=array()) {
        if ($count > $threshold) {
			$attributes = ($title) ? array('title' => $title) : array();
            return $this->div('countAlert', $this->div('warning', '') . $this->para('warning', $count, $attributes), $options);
        } else {
            return '';
        }
    }

    //============================================================
    // USER GRAIN DISPLAY
    //============================================================

    /**
     * 
     * @param string $label The left hand text (wrapped in a span)
     * @param string $name Tag name that will wrap the whole thing
     * @param string $text The right hand text (wrapped in a span)
     * @param array $options Attributes for the left span and the wrapper tag
     * @return string The html fragment
     */
    public function decoratedTag($label, $name, $text = null, $options = array('class' => 'decoration')) {
		$defaultOptions = array('class' => 'decoration');
		$options = array_merge($defaultOptions, $options);
        $guts = $this->tag('span', $label . ': ', $options);
        $text = $this->tag('span', $text, array('class' => 'text'));
        return $this->tag($name, $guts . $text, $options);
    }

    /**
     * Process multiple address records into a tableCell compatible array
     * 
     * Decides which standard array format this is
     * and processes the data down to an array for tableCell
     * with Edit and Delete tools for each row
     * 
     * HANDLES hasMany ADDRESSES THAT LOOK LIKE THIS
     * 
     * 	array(
     * 	    'User' => array( ... ),
     * 	    'Address' => array(
     * 		0 => array( ... ),
     * 		1 => array( ... ),
     * 		2 => array( ... )
     * 		...
     * 
     * @param array $data
     * @param array $userEditing
     * @return array
     */
    public function addressGrainRowsFrom($data, $editAccess) {
        $shipping = array();
        foreach ($data as $index => $records) {
            $recordId = $records['User']['id'];
            $temp = array();
            foreach ($records['Address'] as $index2 => $address) {
				
				// couldn't filter out inactive addresses on query, so do it here
				if (!$address['active']) {
					continue;
				}

				if (isset($address['type']) && $address['type'] != 'vendor') {
                    $this->setAddressGrainRowArray($address, $temp, $editAccess);
                }
            }
            if (!empty($temp)) {
                unset($tools);
                if ($editAccess) {
                    //If you're a manager or this is your record, you can add new addresses
                    $colspan = 2;
                    $tools[] = $this->FgForm->newRequestButton(array('id' => 'address' . $this->secureSelect($recordId)));
                    $tools[] = array('class' => 'owner');
                } else {
                    $colspan = 3;
                    $tools = false;
                }
                $params = compact('temp', 'editAccess', 'records', 'tools', 'shipping', 'colspan');
                $shipping = $this->packageAddressGrainRowSet($params);
            } else if (empty($temp) && $editAccess) {
                //If you're a manager or this is your record, you can add new addresses
                unset($tools);
                $colspan = 2;
                $tools[] = $this->FgForm->newRequestButton(array('id' => 'address' . $this->secureSelect($recordId)));
                $tools[] = array('class' => 'owner');
                $params = compact('temp', 'editAccess', 'records', 'tools', 'shipping', 'colspan');
                $shipping = $this->packageAddressGrainRowSet($params);
            }
        }
        return $shipping;
    }

    /**
     * Compile a single address record into a row-array for tableCells()
     * 
     * This follows our first-attempt pattern:
     * Name, concatenated address, delete and edit button
     * 
     * @param array $leaf The field level array for this address
     * @param array $tableArray The acculator for table row arrays
     * @param boolean $editAccess The user is an editor
     */
    private function setAddressGrainRowArray($leaf, &$tableArray, $editAccess) {
        // pre assemble concatenated address
        $addressDisplay = $this->tag('span', $leaf['address'] . "<br />"
                . $leaf['city'] . " " . $leaf['state'], array('class' => 'addressDisplay')
        );
        // pre assemble tools for final cell

        if ($editAccess) {
            $tools = $this->FgForm->deleteRequestButton(array('id' => 'daddress' . $leaf['id'], 'bind' => 'click.addressDelete')) . ' '
                    . $this->FgForm->editRequestButton(array('id' => 'eaddress' . $leaf['id']));
        } else {
            $tools = '';
        }
        $tableArray[] = array(
            array($leaf['name'], array('class' => 'hide userID' . $leaf['user_id'])),
            array($addressDisplay, array('class' => 'hide userID' . $leaf['user_id'])),
            array($tools, array('class' => 'hide userID' . $leaf['user_id']))
        );
    }

    /**
     * Array packaging and merging for user Address Grain records
     * 
     * Called by setAddressGrainRowArray to package the final shipping
     * table array for a single user.
     * 
     * @param array $params A compact set of arrays including:
     *      @param array $temp
     *      @param boolean $editAccess
     *      @param array $records
     *      @param array $tools
     *      @param array $shipping
     * @return array
     */
    private function packageAddressGrainRowSet($params) {
        extract($params);
        $recordId = $records['User']['id'];
        $count = count($temp);
        $ownerName = $this->discoverName($records['User']);
        $user = $this->para('label', "Owner: $ownerName ($count)");
        $userArray[] = array(
            array(
                $user, array(
                    'colspan' => $colspan,
                    'class' => 'owner toggle',
                    'id' => 'userID' . $recordId,
                    'title' => 'Click to expand / collapse'
        )));
        if ($tools) {
            $userArray[0][1] = $tools;
        }
        return array_merge($userArray, $temp, $shipping);
    }
	
	/**
     * Fix image links before transforming the markdown
     * 
     * 
     * @param string $text The text to convert to html
     * @return string The html converted from markdown
     */
    public function markdown($text) {
        $text = str_replace(']: /img/', "]: {$this->request->webroot}img/", $text);
        return $this->Markdown->transform($text);
    }
    
    public function budgetIndicator($budget){
        $indicator = null;
        if ($budget['use_budget']) {
            $budget_amount = $this->tag('span', $budget['remaining_budget'], array('class' => 'flyout hide'));
            $over = stristr($budget['remaining_budget'], '-');
            $overClass = $over ? ' negative' : ' positive';
            $indicator .= $this->tag('span', ' [$]' . $budget_amount, array('class' => 'indicator budget-'. $budget['id'] . $overClass));
        }
        if ($budget['use_item_budget']){
            $item_budget_amount = $this->tag('span', $budget['remaining_item_budget'], array('class' => 'flyout hide'));
            $over = stristr($budget['remaining_item_budget'], '-');
            $overClass = $over ? ' negative' : ' positive';
            $indicator .=  $this->tag('span', ' [#]' . $item_budget_amount, array('class' => 'indicator itembudget-'. $budget['id'] . $overClass));
        }
        return $indicator;
    }

    /**
     * Make a map to open sidebar tree nodes to reveal a nested node
     * 
     * js has a tool to open nodes given a list stored in cookies
     * This makes the same kind of list from an ancestor list
     * for placement in a url. Then js can read this query string
     * and open the nodes to reveal some leaf.
     * Used for the 'Edit my account info' link in Account Tools
     * 
     * @return string The url query portion naming the ancestor nodes
     */
    public function editAccountUrl($ancestorList) {
	$pattern = array(
	    '/^\,/',
	    '/(\,)([\d]+)/',
	    '/\,\Z/');
	$replace = array(
	    'side_check_',
	    '.side_check_$2',
	    '');
	$a = preg_replace($pattern, $replace, $ancestorList);
	return $a;
    }
    
    /**
     * Construct a unit name for a specific item
     * 
     * The same item may underlay many OrderItems and ReplenishmentItems
     * When that items unit name changes, all occurance on the page
     * should change. This lays the necessary hooks for js
     * 
     * @param type $item
     * @param type $alias
     * @return type
     */
    public function unitName($item, $alias) {
	if ($alias == 'Order' || $alias == 'Catalog') {
		// TODO::DELETE
	    return $this->tag('span', $item['Catalog']['sell_unit'], array('class' => "inv_unit{$item['Item']['id']}"));
	} else {
	    // Replenishment
	    return $this->tag('span', $item['po_unit'], array('class' => "po_unit{$item['id']}"));
	}
    }

    /**
     * Return the proper calculated quantity to display for an Order/Replenishment line item
     * 
     * When ordered quantities are changed by the user, we display
     * a calculated value indicated the eventual effect on inventory.
     * This renders the right label and value for the context and
     * lays down DOM hooks that will allow js to update the values
     * as the user interacts with the quantity input (class is the key
     * and allows all occurances of an items value to be set on the page)
     * 
     * <pre><code>
     *  // 63 is the item id in this $alias = 'Order' case
     *  <span title="Inventory level after fulfilling this order" class="calcQty">Available:
     *    <span class="availableQty63">90.0</span>
     *    <span class="inv_unit63">ea</span>
     *	  </span>
     *  // 3 is the item id in this $alias = 'Replenishment' case
     *  <span title="Inventory level after revieving this item" class="calcQty">Pending: 
     *	  <span class="pendingQty3">75.0</span> 
     *	  <span class="inv_unit3">ea</span>
     *  </span>
     *  </code>
	 * $item
	 *		fields 
	 *		Item		// for Repenishiments only
	 *			fields
	 * </pre>
	 * 
     * @param array $item The line item data to operate on $item['Item'] => fields
     * @param string $alias The Model alias, Order or Replenishment
     * @param string $title An alternate title to show on hover
     * @return string The html to display the labeled value
     */
    public function calculatedQuantity($item, $alias, $title = null) {
		// Orders and Replenisments show different calculated quantities in their item rows
		// This is the entry point for Store Grain
		if ($alias == 'Catalog') {
			$this->available = $item['Catalog'];
			$this->available['Item'] = $item['Item'];

			$this->available['catalog_id'] = $item['Catalog']['id'];
			if($title == NULL){
				//set default title for catalogs
				$title = 'Inventory level after fulfilling current orders';
			}
			$this->available['title_attr'] = $title;
			return $this->availableHtml();
			
		// This is the entry for a Status Page Order
		} elseif($alias=='Order'){
			$this->available = $item;
			//move available quantity for kits and components
			if ($item['Catalog']['type'] & KIT || $item['Catalog']['type'] & COMPONENT) {
				$this->available['available_qty'] = $item['Catalog']['available_qty'];
			}
			$this->available['title_attr'] = $title;
			$this->available['type'] = $item['Catalog']['type'];
			return $this->availableHtml();
			
		// This is the entry for a Status Page Order
		} elseif($alias=='Cart'){
			$this->available = $item['Catalog'];
			$this->available['title_attr'] = $title;
			$this->available['type'] = $item['Catalog']['type'];
			return $this->availableHtml();
			
		// This is the entry for a Status Page Replenishment
		} elseif ($alias=='Replenishment') {
			// this needs to make an expanding div that shows
			// the Item's pending and the pending cals for all Products
			$this->pending = $item;
			$this->pending['catalog_id'] = '';
			$this->pending['title_attr'] = null;
			$this->pending['name'] = 'Pending Inventory';
			$pendingBlock = array();
			$pendingBlock[] = "<li>{$this->pendingHtml()}</li>";
			foreach ($item['Item']['Catalog'] as $product) {
				
				$this->pending = array_merge($this->pending, $product);
				$pendingBlock[] = "<li>{$this->pendingHtml()}</li>";
			}
			
			return '<ul>' . implode("\n", $pendingBlock) . '</ul>';
		} else {
			//We do not have a proper alias, log error and return empty string
			return 'unknown alias for calculatedQuantity';
		}
    }
	
	public function calculatePendingProduct($data) {
		//setup data array
		$this->pending = $data['Catalog'];
		$this->pending['po_quantity'] = $this->pending['sell_quantity'];
		$this->pending['po_unit'] = $this->pending['sell_unit'];
		$this->pending['catalog_id'] = $this->pending['id'];
		$this->pending['Item'] = $data['Item'];
		$this->pending['title_attr'] = 'Inventory level after receiving all replenishments.';
		$this->pending['name'] = 'Pending';
		
		//return pendingHtml string
		return $this->pendingHtml();
	}

	/**
	 * Build a standard 'available' information line that is updatable on the client side
	 * 
	 * Many products may be based on a single item. When the 
	 * available qty for an item changes, the various product
	 * 'available' information lines need to be updated.
	 * The writes that basic information unit in a way that
	 * the updates can later be done
	 * 
	 * Example 'available' information lines
	 * Available: 91 | 1 ea
	 * Available: 18.2 | grove of 5
	 * 
	 * @return string A single 'available' infomration line
	 */
	private function availableHtml() {
		// create a few values needed later
		$idHook = "-I{$this->available['item_id']}-C{$this->available['catalog_id']}-";
		$avail = $this->discoverAvailable();
		if (isset($this->available['available_qty'])) {
			$availableClass = ($this->available['available_qty'] < 0) ? ' overCommitted inventory' : ' inventory';
		} else {
			$availableClass = ($this->available['Item']['available_qty'] < 0) ? ' overCommitted inventory' : ' inventory';
		}
		$title = ($this->available['title_attr'] === null) 
				? 'Inventory level after fulfilling this order' 
				: $this->available['title_attr'];
		
		// assemble the 3 spans that are the update-able units
		$availSpan = $this->tag('span',$avail, array('class' => 'avail'.$idHook.$availableClass));
		$sellSpan = $this->tag('span',$this->available['sell_quantity'], array('class' => 'sell'.$idHook));
		$unitSpan = $this->tag('span',$this->available['sell_unit'], array('class' => 'unit'.$idHook));
		
		// build up the the final display string from all the bits
		$of = $this->available['sell_unit'] === 'ea' 
				? "$unitSpan"
				: "$unitSpan of $sellSpan";
		
	    $available = $this->tag('span', 
			"Available: $availSpan/$of",
					array(
						'class' => 'calcQty',
						'title' => $title));
		return $available;
	}
	
	/**
	 * Calculate the available quantity for a Product, Kit or Component
	 * 
	 * @return decimal The available quantity for a Product, Kit or Component
	 */
	private function discoverAvailable() {

		// if this is a kit or a componenet, we need special stuff
		// This is the calculation for Kits that are ordered 'on-demand' (kit-up style)
		if ($this->available['type'] & (KIT)) {
			return (isset($this->available['available_qty']))
					? $this->Number->precision($this->available['available_qty'],2)
					: 'x';

		// This is for Components of Inventory-kit-only kits (kit-break style)
		} elseif ($this->available['type'] & COMPONENT) {
			return (isset($this->available['available_qty']))
					? $this->Number->precision($this->available['available_qty'],2)
					: 'See Kit for available qty';
			
		// This is the "normal" calculation for typical products and some Kits and Components
		} else {
			return $this->Number->precision($this->available['Item']['available_qty'] / $this->available['sell_quantity'],2);
		}
	}
	
	private function discoverPending() {
		// if this is a kit or a componenet, we need special stuff
		if (false) {
			// Some kit stuff here
		} else {
			return $this->Number->precision($this->pending['Item']['pending_qty'] / $this->pending['po_quantity'],1);
		}		
	}


	/**
	 * 
	 * Example 'available' information lines
	 * Pending: 91 | 1 ea
	 * Pending: 18.2 | grove of 5

	 * 	 * @return string
	 */
	private function pendingHtml() {
		// create a few values needed later
		$idHook = "-I{$this->pending['item_id']}-C{$this->pending['catalog_id']}-";
		$pend = $this->discoverPending();
	    $title = ($this->pending['title_attr'] === null) 
				? 'Inventory level after receiving this replenishment' 
				: $this->pending['title_attr'];
		
		// assemble the 3 spans that are the update-able units
		$pendSpan = $this->tag('span',$pend, array('class' => 'pend'.$idHook));
		if ($this->pending['catalog_id']) {
			$sellSpan = $this->tag('span', $this->pending['sell_quantity'], array('class' => 'poqty' . $idHook));
			$unitSpan = $this->tag('span', $this->pending['sell_unit'], array('class' => 'pounit' . $idHook));
		} else {
			// with no catalog, we're looking at the Item, always 1 each
			$sellSpan = $this->tag('span', '1', array('class' => 'poqty' . $idHook));
			$unitSpan = $this->tag('span', 'ea', array('class' => 'pounit' . $idHook));
		}
		// build up the the final display string from all the bits
		$of = $this->pending['po_unit'] === 'ea' 
				? "$sellSpan $unitSpan"
				: "$unitSpan of $sellSpan";
		
	    $pending = $this->tag('span', 
			"{$this->pending['name']}: $pendSpan | $of", 
					array(
						'class' => 'calcQty',
						'title' => $title));
		return $pending;
	}
	
	//============================================================
    // ORDER, SHOPPING AND CART
    //============================================================

	/**
	 * Make an item limit alert triangle if necessary
	 * 
	 * @param array $item Item record with fieldnames at first index level
	 * @param boolean $itemLimitBudget The users budget setting
	 * @return string Empty or the alert HTML
	 */
	public function itemLimitAlert($entry, $itemLimitBudget) {
		$itemLimitAlert = '';
		if ($itemLimitBudget) {
			$unit = ($entry['Catalog']['max_quantity']) == 1 ? $entry['Catalog']['sell_unit'] : Inflector::pluralize($entry['Catalog']['sell_unit']);
			$itemLimitAlert = $this->countAlert($entry['Catalog']['max_quantity'], "Purchase of this item limited to {$entry['Catalog']['max_quantity']} $unit", 0);
		}
		return $itemLimitAlert;
	}    

    //============================================================
	// STATUS PAGE HELPERS
    //============================================================
    
	/**
	 * 
	 * @param array $data 0,1,2... level of Location return?
	 * @param boolean $ptag Controlls something about the decorated tag vs p tag
	 * @return type
	 */
	function stringLocations($data, $ptag = TRUE){
		//clear vars
		$opening = '';
		$output = '';
		$closing = '';
		$itemId = $data['Item']['id'];
		
		//setup data
		$data = $data['Item']['Location'];
		$locId = (isset($data['id'])) ? $data['id'] : '';
		
		//accumulate $opening
		$count = count($data);
		$edit = $this->tag('span', ' (click to edit)  ', array ('class' => 'locationEditText', 'bind' => 'click.editLocations'));
		$opening = $this->div('locations', NULL, array('itemId' => $itemId));
		if($ptag){
				$opening .= $this->decoratedTag('Locations', 'p', $count . $edit);
		} else {
			$opening .= $this->para('locTitle', $count . $edit);
		}
		$opening .= $this->tag('ul', NULL);
		
		//accumulate items
		foreach ($data as $index => $location) {
			//setup digit spans
			$rowNum = ($location['row']) ? $this->tag('span', $location['row'], array('class' => 'locNum')) : '';
			$binNum = ($location['bin']) ? $this->tag('span', $location['bin'], array('class' => 'locNum')) : '';
			//setup vars for accumulation
			$row = ($location['row']) ? ' R-'.$rowNum : '';
			$bin = ($location['bin']) ? ' B-'.$binNum : '';
			//accumulate all element
			$loc = $location['building'] . $row . $bin;
			//create an output as an li
			$output .= $this->tag('li', $loc);
		}
		
		//accumulate $closing
		$closing = '</ul></div>';
		
		return ($opening . $output . $closing);
	}
	
	/**
	 * Create table headers from standard find array
	 * 
	 * @param array $records the standard find array
	 * @return array the array of headers for the table
	 */
	public function makeHeadersFromStandardArray($records) {
		if(empty($records)){
			return array();
		}
		
		$keys = array_keys($records);
		
		if(is_integer($keys[0])){
			$keys = array_keys($records[$keys[0]]);
		}
		
		return $keys;
	}
	
	/**
	 * Create table rows from standard find array
	 * 
	 * @param array $records the standard find array
	 * @return array the standard row array
	 */
	public function makeRowsFromStandardArray($records) {
		if(empty($records)){
			return array();
		}
		
		$keys = array_keys($records);
		
		if(!is_integer($keys[0])){
			$records = array($records);
		} else {
			foreach($records as $record) {
				$row[] = $record;
			}
			$records = $row;
		}
		
		return $records;
	}
}
/**
 * fgHtml methods in Status and Warehouse:
 discoverName
 budgetIndicator
 noteIndicator
	double check that both of those belong in the common
 unitName
 calculatedQuantity
 itemLimitAlert
 stringLocations
 */
?>