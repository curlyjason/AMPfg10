<div class="addressDisplay">
    <?php

    echo $this->FgHtml->tag('h3', $heading, array('class' => 'grainDisplay'));
    
    // parse location records into a cake tableCells compatible array
    $tableArray = $this->FgHtml->addressGrainRowsFrom($grain, $editAccess);
    echo $this->FgHtml->tag('Table', null, array('class' => 'order'));
    echo $this->FgHtml->tableCells($tableArray)
    ?>
</table>
</div>