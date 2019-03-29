<?php
echo $this->Form->create(Inflector::classify($this->viewVars['controller']), array(
    'id'=>'treeEditForm',
    'type'=>'file'
));
echo $this->Form->input('parent_id', array('type'=>'hidden', 'value'=>'invalid', 'id' => 'TreeEditParent'));
echo $this->Form->input('sequence', array('type'=>'hidden', 'value'=>'invalid', 'id' => 'TreeEditSequence'));
echo $this->Form->input('id', array('type'=>'hidden', 'value'=>'invalid', 'id' => 'TreeEditId'));
echo $this->Form->input('type_context', array('type'=>'hidden', 'value'=>'', 'id' => 'TreeEditTypeContext'));
if (isset($renderNode)) {
    echo $this->Form->input('currentNode', array('type'=>'hidden', 'value'=>$this->FgHtml->secureSelect($renderNode, 'li'), 'id' => 'TreeEditcurrentNode'));
}
echo $this->Html->tag('fieldSet','');
echo $this->Form->button('Save', array('type' => 'submit', 'id' => 'saveNew'));
echo $this->Form->button('Cancel', array('type' => 'button', 'id' => 'cancel'));
echo $this->Form->end();
?>