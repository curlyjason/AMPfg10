<?php 
require_once(APP . 'Vendor' . DS . 'dompdf' . DS . 'dompdf_config.inc.php');
spl_autoload_register('DOMPDF_autoload');
$dompdf = new DOMPDF();
$dompdf->set_paper = 'letter';
$dompdf->DOMPDF_ENABLE_CSS_FLOAT = TRUE;
$dompdf->set_base_path(CSS);
debug($content);
$dompdf->load_html(utf8_decode($content_for_layout), Configure::read('App.encoding'));
$dompdf->render();
echo $dompdf->stream();
?>