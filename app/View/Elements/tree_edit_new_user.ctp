<?php
echo $this->Form->create(Inflector::classify($this->viewVars['controller']), array(
    'id'=>'treeEditForm'
));
echo $this->Form->input('parent_id', array('type'=>'hidden', 'value'=>'invalid', 'id' => 'TreeEditParent'));
echo $this->Form->input('sequence', array('type'=>'hidden', 'value'=>'invalid', 'id' => 'TreeEditSequence'));
echo $this->Form->input('id', array('type'=>'hidden', 'value'=>'invalid', 'id' => 'TreeEditId'));
echo $this->Form->input('currentNode', array('type'=>'hidden', 'value'=>$this->FgHtml->secureSelect($renderNode, 'li'), 'id' => 'TreeEditcurrentNode'));
echo $this->Form->end();
?>