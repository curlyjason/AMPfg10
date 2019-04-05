<html>
    <head>
        <?php echo $this->Html->charset(); ?>
        <title>
            <?php // echo $cakeDescription ?>:
            <?php // echo $title_for_layout; ?>
        </title>
        <?php
        echo $this->Html->meta('icon');
        echo $this->Html->css(array('accumulator'));
//        echo $this->Html->script();

        echo $this->fetch('meta');
        echo $this->fetch('css');
        echo $this->fetch('script');
        ?>
    </head>
    <body>
		<div class="page">
			<div class="type">
				<?php
					echo $this->fetch('type');
				?>
			</div>
			<div id="amp" class="left">
				<?php
				echo $this->FgHtml->para(null, 'Amp Printing + Graphics');
				echo $this->FgHtml->para(null, '6955 Sierra Court');
				echo $this->FgHtml->para(null, 'Dublin, CA 94568');
				?>
			</div>
			<!-- REFERENCE BLOCK -->
			<div class="reference_block">
				<div class="header">
					<?php $this->Accumulator->columns($data['reference']['labels']); ?>
				</div>
				<div class="content_block">
					<?php $this->Accumulator->columns($data['reference']['data']); ?>
				</div>
			</div>
			<!-- BILLING ADDRESS -->
			<div class="section address left">
				<div class="header">
					<p><?php echo $type == 'order' ? 'Billing' : 'Vendor'; ?> address</p>
				</div>
				<div class="content_block">
					<?php echo $this->fetch('address_billing'); ?>
				</div>
			</div>
			<!-- SHIPPING ADDRESS -->
			<div class="section address right <?php echo $type; ?>">
				<div class="header">
					<p>Shipping Address</p>
				</div>
				<div class="content_block">
					<?php echo $this->fetch('address_shipping'); ?>
				</div>
			</div>
			<div class="summary <?php echo $type; ?>">
				<div class="header">
					<?php echo $this->fetch('summary_header'); ?>
				</div>
				<div class="content_block">
					<?php echo $this->fetch('summary_data'); ?>
				</div>
			</div>
			<!-- PAGE 1 LINE ITEM SECTION -->
			<div class="section accumulator <?php echo $type; ?>">
				<!-- PAGE 1 LINE ITEM SECTION HEADER-->
				<div class="header">
					<?php 
						// this should loop through the first batch of line items
						// possibly the first slice of X records
						echo $this->fetch('accumulator_row'); 
					?>
				</div>
				<!-- PAGE 1 LINE ITEMS -->
				<div id="line_items">
					<?php echo $this->fetch('line_items'); ?>
				</div>
			</div>
			<div class="page_header">
					<?php printf('<p>%s %s - printed on %s - page %s of %s</p>', $type, $data['reference']['data'][1], $data['reference']['data'][0], 1, count($chunk)+1); ?>
			</div>
		</div>
		<!--http://www.javascriptkit.com/dhtmltutors/pagebreak.shtml-->
		<?php foreach ($chunk as $number => $page) { ?>
		<div class="page-x">
			<div class="page_header">
					<?php printf('<p>%s %s - printed on %s - page %s of %s</p>', $type, $data['reference']['data'][1], $data['reference']['data'][0], $number+2, count($chunk)+1); ?>
			</div>
			<div class="section accumulator <?php echo $type; ?>">
				<div class="header">
					<?php echo $this->fetch('accumulator_row'); ?>
				</div>
			<div id="line_items">
				<?php
					foreach ($page as $index => $line) {
						echo $this->Html->div('line', null);
							echo $this->Accumulator->columns($line);
						echo '</div>';
					}	
				?>
			</div>
			</div>
			
		</div>
		<?php } // end of one chunk (one page) ?>
</body>
</html>