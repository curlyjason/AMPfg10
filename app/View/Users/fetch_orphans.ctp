<?php
echo $this->FgHtml->tag('h2', 'Orphan Items');
print_r($orphanItems);
echo $this->FgHtml->tag('h2', 'Orphan Customers');
print_r($orphanCustomers);
echo $this->FgHtml->tag('h2', 'Orphan User Ancestors');
print_r($orphanUserAncestors);
echo $this->FgHtml->tag('h2', 'Orphan Catalog Ancestors');
print_r($orphanCatalogAncestors);
echo $this->FgHtml->tag('h2', 'Orphan User Addresses');
print_r($orphanUserAddresses);
echo $this->FgHtml->tag('h2', 'Orphan Budgets');
print_r($orphanBudgets);
echo $this->FgHtml->tag('h2', 'Out of Date Carts');
print_r($outOfDateCarts);



?>