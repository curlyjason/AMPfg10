<?php
App::uses('AppHelper', 'Helper');
App::uses('ItemImportsErrorsHelper', 'Helper');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * CakePHP Helper
 * @author dondrake
 */
class ItemImportHelper extends AppHelper {

    public $helpers = array('FgHtml', 'ItemImportsErrors');

    public function __construct(View $View, $settings = array()) {
	    parent::__construct($View, $settings);
    }

	/**
	 * Merge for display, required headers and user headers 
	 * 
	 * This makes data to render the header row on the preview table 
	 * to show the (required) name of the data, what the user previous called 
	 * it and leaves the unused user data names trailing off to identify 
	 * their unused data points in the rest of the table.
	 * 
	 * @param type $registry
	 * @return array
	 */
    public function previewTableHeader($registry)
    {
        $required_column_headers = $registry->getRequiredLabels(true);
        $result = [];
        foreach ($required_column_headers as $index => $header){
            $result[] = "<span class='required'>$header</span><span class='user'>{$registry->getMappedHeader()[$index]}</span>";
        }
        return $this->Html->tableHeaders($result);
    }
    
	/**
	 * Mark preview data cells that aren't mapped as class=unused
	 * 
	 * @param ItemRegistry $registry
	 * @return HTML string
	 */
	public function previewRow($registry) {
		$delimeter = '</td> ';
		$map = $registry->ItemImportMap;
		$data = $registry->getMappedRecord($registry->current());
		$trNode = $this->Html->tableCells([$data]);
		
		$cells = explode($delimeter, $trNode);
		$numberOfCells = $this->countColumns($registry);
		for ($i = $map->requiredColumnCount(); $i < $numberOfCells; $i++) {
			$cells[$i] = str_replace('<td>', '<td class="unused">', $cells[$i]);
		}
		
		$trCatalog = $this->collectCatalogPreviews($registry->item($registry->current()), $numberOfCells);

        $trNode = implode($delimeter, $cells);
		return $trNode . $trCatalog;
	}
	
	protected function collectCatalogPreviews($item, $numberOfCells) {
		$result = '';
		if ($item->hasCatalogs()) {
		    $catalogs = [];
			foreach ($item->rawCatalogs() as $catalog) {
				$catalogs[] = $this->catalogPreview($catalog);
			}
            $result = "<tr><td class=\"catalog\" colspan=\"$numberOfCells\">" . implode(' <br/> ', $catalogs) . "</td><tr>";
        }
		return $result;
	}
	
	public function failedRow($registry) {
	    $numberOfCells = $this->countColumns($registry);
	    $errorRows = '';
	    $errors = $this->ItemImportsErrors->simplify($registry->item()->errors(), 'String');
	    foreach ($errors as $error){
            $errorRows .= "<tr><td class='error' colspan='$numberOfCells'>$error</td></tr>";
        }
        return $errorRows;
	}

	protected function catalogPreview($catalog) {
		$format = 'Also sold as (item_id) %s (name) %s (desc) %s by (unit) %s (qty) %s';
		return sprintf(
				$format, 
				$catalog->customerItemCode(),
				$catalog->name(), 
				$catalog->description(), 
				$catalog->unit(), 
				$catalog->quantity()
				);
	}

	protected function countColumns($registry){
        $count =  count($registry->getMappedRecord($registry->current()));
        return $count;
    }

//<tr>
//	<td>nothic</td> 
//	<td>Nothic</td> 
//	<td>Aberration</td> 
//	<td>Neutral Evil</td> 
//	<td>Medium</td> 
//	<td>Basic Rules</td> 
//	<td>2    </td> <td></td>
//</tr>

}
