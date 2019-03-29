<?php
$this->start('css');
echo $this->FgHtml->css('ampfg_grain');
echo $this->FgHtml->css('status');
$this->end();

$this->start('script');
echo $this->FgHtml->script('grain');
$this->end();

$manager = $this->Session->read('Auth.User.access') === 'Manager';

if (isset($editGrain)) {
    //================================
    //==Set Variables               ==
    //================================
    $grainName = $this->FgHtml->discoverName($editGrain['User']);
    $grainId = $editGrain['User']['id'];
    $customer = '';
    $customerFlag = 0;
	$folderFlag = $editGrain['User']['folder'];
    if($editGrain['Customer']['user_id'] != NULL){
        $customerFlag = '\''.$this->FgHtml->secureSelect($editGrain['Customer']['id']).'\'';
        $customer = ' - Customer';
    }
    $this->start('jsGlobalVars');
    echo "var customer = $customerFlag;";
	if(isset($showInvoicePDF) && $showInvoicePDF){
		echo "var showInvoicePDF = '$showInvoicePDF';";
//		echo "
//		window.open(webroot + '/invoices/viewOldInvoice/$showInvoicePDF.pdf',
//		  '_blank' // <- This is what makes it open in a new window
//		);
//		";
//		echo "location.assign(webroot + '/invoices/viewOldInvoice/' + $invoiceId + '.pdf')";
	}
    $this->end();
    //================================    

    echo '<h2>' . $this->FgHtml->outputGroupAttributes($editGrain['User']['folder'], $editGrain['User']['active'])
    . $grainName . $customer . '</h2>';
    
    echo $this->element('user_display', array(
        'grain' => $editGrain,
		'customerFlag' => $customerFlag
    ));
	if (!$folderFlag || $customerFlag) {
			echo $this->element('observer_display', array(
				'grain' => $editGrain,
				'access' => $access,
				'group' => $group,
				'owner' => $owner,
				'class' => 'observerDisplay',
				'heading' => "These are $grainName's observers"
			));
		if (!$customerFlag) {
			echo $this->element('observer_display', array(
				'grain' => $editGrain,
				'access' => $access,
				'group' => $group,
				'owner' => $owner,
				'class' => 'userObserverDisplay',
				'heading' => "$grainName observes these user's processes"
			));
			echo $this->element('catalog_permission_display', array(
				'grain' => $editGrain,
				'access' => $access,
				'group' => $group,
				'owner' => $owner,
				'heading' => "$grainName has access to these catalogs"
			));
			echo $this->element('user_permission_display', array(
				'grain' => $editGrain,
				'access' => $access,
				'group' => $group,
				'owner' => $owner,
				'heading' => "$grainName has access to these users & customers"
			));
		}
		echo $this->element('GrainPage/invoice_display', array(
			'grain' => $editGrain,
			'editAccess' => ($access === 'Manager' || $owner),
			'heading' => "$grainName's Invoices"
		));
		echo $this->element('address_display', array(
			'grain' => array($editGrain),
			'editAccess' => ($access === 'Manager' || $owner),
			'heading' => "$grainName's Addresses"
		));
		if (!empty($addresses)) {
			echo $this->element('address_display', array(
				'grain' => $addresses,
				'editAccess' => ($access === 'Manager'),
				'heading' => "Other addresses $grainName has access to"
			));
		}
	}
}
?>
</div>