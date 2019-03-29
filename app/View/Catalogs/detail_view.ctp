<?php echo $this->Html->script(array('addtocart.js'), array('inline' => false)); ?>

<?php
//$this->Html->addCrumb($product['Brand']['name'], array('controller' => 'brands', 'action' => 'view', 'slug' => $product['Brand']['slug']));
//$this->Html->addCrumb($product['Category']['name'], array('controller' => 'categories', 'action' => 'view', 'slug' => $product['Category']['slug']));
//$this->Html->addCrumb($product['Item']['name']);
?>

<h1><?php echo $product['Item']['name']; ?></h1>

<div class="row">

	<div class="col col-lg-7">
	<?php echo $this->Html->Image('/images/large/' . $product['Item']['image'], array('alt' => $product['Item']['name'], 'class' => 'img-thumbnail img-responsive')); ?>
	</div>

	<div class="col col-lg-5">

		<strong><?php echo $product['Item']['name']; ?></strong>

		<br />
		<br />

		$ <?php echo $product['Item']['price']; ?>

		<br />
		<br />

		<?php echo $this->Form->create(NULL, array('url' => array('controller' => 'shop', 'action' => 'add'))); ?>
		<?php echo $this->Form->input('id', array('type' => 'hidden', 'value' => $product['Item']['id'])); ?>
		<?php echo $this->Form->button('Add to Cart', array('class' => 'btn btn-primary addtocart', 'id' => $product['Item']['id']));?>
		<?php echo $this->Form->end(); ?>

		<br />

		<?php echo $this->FgHtml->markdown($product['Item']['description']); ?>

		<br />
		<br />

		Brand: <?php // echo $this->Html->link($product['Brand']['name'], array('controller' => 'brands', 'action' => 'view', 'slug' => $product['Brand']['slug'])); ?>

		<br />

		Category: <?php // echo $this->Html->link($product['Category']['name'], array('controller' => 'categories', 'action' => 'view', 'slug' => $product['Category']['slug'])); ?>

		<br />

	</div>

</div>
