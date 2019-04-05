<?php
//debug($product);
?>
<div class="catalogs view">
    <?php echo $this->Html->script(array('addtocart.js'), array('inline' => false)); ?>
    
    <h1><?php echo $product['Catalog']['name']; ?></h1>

    <div class="row">

        <div class="col col-lg-7">
            <?php 
				if (isset($product['Item']['Image'][0])) {
					echo $this->Html->image('image' . DS . 'img_file' . DS . $product['Item']['Image'][0]['id'] . DS . 'x500y375_' . $product['Item']['Image'][0]['img_file']);
				}            
			?>
        </div>

        <div class="col col-lg-5">

            <strong><?php echo $product['Catalog']['name']; ?></strong>

            <br />
            <br />

            $ <?php echo $product['Catalog']['price']; ?>

            <br />
            <br />

            <?php 
				$product['sell_unit'] = $product['Catalog']['sell_unit'];
				echo $this->FgHtml->itemLimitAlert($product, $itemLimitBudget);

				// BUTTON AND END OF FORM
				echo $this->Store->addToCartBlock($product, $backorderAllow, $itemLimitBudget);
//				echo $this->FgForm->create(NULL, array('url' => array('controller' => 'shop', 'action' => 'add')));
//				echo $this->FgForm->input('id', array('type' => 'hidden', 'value' => $product['Item']['id']));
//				echo $this->FgForm->input('quantity', array(
//					'class' => 'cartQuantityInput',
//					'id' => $product['Item']['id'].'quantity',
//					'default' => 1));
//				echo $this->Form->button('Add to Cart', array(
//					'class' => 'btn btn-primary addtocart',
//					'itemId' => $product['Item']['id']));
//				echo $this->FgForm->end(); 
			?>

            <br />

            <?php echo $this->FgHtml->markdown($product['Catalog']['description']); ?>

        </div>

    </div>
</div>
