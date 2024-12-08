<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function recognize($sheet, &$doc)
{
	$doc->documentType = 'Справка о задолженности по выставленному счету с учетом';
	$doc->setBalance(0);

	$H = $sheet->getHighestRow();
	$S = 0; // начало - признак салодо начальное
	
	$pl = array();
	$mn = array();

	for ($i=10; $i<$H+1; $i++)
	{
		$plDateStr = trim($sheet->getCell("A".$i)->getValue());
		if ($plDateStr && preg_match('/^="(0[1-9]|1[0-2])\.([0-9]{4})"$/', $plDateStr, $match)) {
			$plDate = mktime(0,0,0, (int)$match[1], 1, (int)$match[2]);
			$plus = trim($sheet->getCell("B".$i)->getCalculatedValue());
			//echo 'plDate = ' . date("d.m.Y", $plDate) . ' - ' . $plus .'<br>';
			
			$doc->addItem($plus, 1, date("d.m.Y", $plDate));
		}
		
		$mnDateStr = trim($sheet->getCell("C".$i)->getValue());
		if ($mnDateStr && preg_match('/^(0[1-9]|[1-2][0-9]|3[0-1])\.(0[1-9]|1[0-2])\.([0-9]{4})$/', $mnDateStr, $match)) {
			$mnDate = mktime(0,0,0, (int)$match[2], (int)$match[1], (int)$match[3]);
			$minus = trim($sheet->getCell("D".$i)->getCalculatedValue());
			//echo 'mnDate = ' . date("d.m.Y", $mnDate) . ' - ' . $minus . '<br>';
			
			$doc->addItem($minus, -1, date("d.m.Y", $mnDate));
		}
	}
}

?>
