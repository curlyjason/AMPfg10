<!--<link type="text/css" href="cake.generic.css" rel="stylesheet" />
<link type="text/css" href="ampfg.css" rel="stylesheet" />-->
<link type="text/css" href="report.css" rel="stylesheet" />
<link type="text/css" href="print.order_report.css" rel="stylesheet" />
<h2>
<?php
	echo 'Order Report<br />';
	echo $customerName . '<br />';
//	echo $start . ' - ' . $end;
	echo date('M d, Y', strtotime($start)) . ' - ' . date('M d, Y', strtotime($end)) ;
?>
</h2>
<?php
$c = count($data);

if($c==0){
	echo $this->Html->tag('h2', "There are no orders for $customerName.");
} else {
	$key = key($data);
	$custIds = array_keys($data);
	unset($data[$key]['User']);
	foreach ($data[$key] as $status => $junk) {
		$s_it = new AppendIterator();
		$i=0;
		while($i<$c){
			$s_it->append(new ArrayIterator($data[$custIds[$i++]][$status]));
		}
		echo $this->Report->orderReportBlock($status, $s_it);
	}
}