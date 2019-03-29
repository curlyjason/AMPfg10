<div class="EditGrain">
    <?php
    echo $this->FgForm->create('Catalog');
    echo $this->FgForm->input('Catalog.name');
    echo $this->FgForm->input('Item.name');
    echo $this->FgForm->input('Item.description');
    echo $this->FgForm->submit('Submit');
    ?>
</div>