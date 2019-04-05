<!--This is being abandoned in favor of a Catalog based view because 
Items don't have the proper data context to detail out the product under consideration for purchase
2/27/14
-->


<div class="items view">
    <?php echo $this->Html->script(array('addtocart.js'), array('inline' => false)); ?>
    
    <h1><?php echo $item['Item']['name']; ?></h1>

    <div class="row">

        <div class="col col-lg-7">
            <?php 
            echo $this->Html->image('image' . DS . 'img_file' . DS . $item['Image'][0]['id'] . DS . 'x500y375_' . $item['Image'][0]['img_file']);
            ?>
        </div>

        <div class="col col-lg-5">

            <strong><?php echo $item['Item']['name']; ?></strong>

            <br />
            <br />

            $ <?php echo $item['Item']['price']; ?>

            <br />
            <br />

            <?php echo $this->FgForm->create(NULL, array('url' => array('controller' => 'shop', 'action' => 'add')));
            echo $this->FgForm->input('id', array('type' => 'hidden', 'value' => $item['Item']['id']));
    echo $this->FgForm->input('quantity', array(
        'class' => 'cartQuantityInput',
        'id' => $item['Item']['id'].'quantity',
        'default' => 1));
    echo $this->Form->button('Add to Cart', array(
        'class' => 'btn btn-primary addtocart',
        'itemId' => $item['Item']['id']));
            echo $this->FgForm->end(); ?>

            <br />

            <?php echo $this->FgHtml->markdown($item['Item']['description']); ?>

        </div>

    </div>
</div>
