<div class="toolPallet">
    <p class='close'>X</p>
    
    <p><?php echo $product['Catalog']['name']; ?></p>


        <div class="col col-lg-7">
            <?php 
        if (isset($product['Item']['Image'][0]['img_file'])) {
            $image = $this->Html->image('image' . DS . 'img_file' . DS . $product['Item']['Image'][0]['id'] . DS . 'x160y120_' . $product['Item']['Image'][0]['img_file']);
        } else {
            $image = '';
        }
            echo $image;
            ?>
        </div>

        <div class="col col-lg-5">

            <strong><?php echo $product['Catalog']['name']; ?></strong>

            <br />
            <br />

            $ <?php echo $product['Catalog']['price']; ?>

            <br />

            <?php echo $product['Catalog']['description']; ?>

        </div>

</div>
