<?php

function recognize($sheet, &$doc)
{
	$doc->documentType = 'Расчет задолженности';
	$doc->setBalance(0);
	
	// если ли смещение?
	$B1 = $sheet->getCell("B1")->getValue();
	$B1 = mb_strtolower($B1);
	
	$shift = 0;
	if (strpos($B1, "расчет задолженности")!==false) $shift = 1;
	
	$letter = array(1 => array("B","E","G"), 0 => array("A","D","F"));
	
	//print_r($letter);
	
	//print $shift." я есть смещение";
	
	//die();
	
	
	$B2 = $sheet->getCell($letter[$shift][0]."2")->getValue();
	
	
	$EX = explode("\n", $B2);
	
	$pos = strpos($EX[0], "за период");
	
	if ($pos) {
		
		$period = substr($EX[0], $pos);
		$doc->period = trim_it(str_replace(['за период', ':'], '', $period));
	}
	
	if (isset($EX[1])) {

		$organization = mb_strtolower($EX[1]);
		if (strpos($organization, "между")!==false) {
			$doc->organization = trim_it(str_replace(['между', ''], '', $organization));
		}
	}
	
	if (isset($EX[2])) {
		
		$owner = mb_strtolower($EX[2]);
		$pos = strpos($owner, "и ");
		if ($pos!==false) {
			if ($pos==0) $doc->person = trim_it(str_replace(['и '], '', $owner));
		}
	}
	
	
	$H = $sheet->getHighestRow();
	
	$F = $H;
	
	// найти окончание данных - признак - ячейка со значением "Обороты за период" в столбце B...
	
	for ($i=5; $i<$H+1; $i++) // данные идут с 5 строки...
	{
		$B = trim_it($sheet->getCell($letter[$shift][0].$i)->getValue());
		if ($B=="Обороты за период") {
			$F=$i-1;
			break;
		}
	}
	
	
	for ($i=5; $i<$F+1; $i++) // данные идут с 5 строки... 
	{
		$DateStr = trim_it($sheet->getCell($letter[$shift][0].$i)->getValue());
		
		// вытащить дату...
		
		if ($DateStr && preg_match('/[0-9]{2}\.[0-9]{2}\.[0-9]{2,4}/', $DateStr, $match))
		{
			$EX = explode(".", $match[0]);
			$fullDate = $EX[0].".".$EX[1].".".fullYear($EX[2]);
		}
		
		else if($DateStr) {
				
			$fullDate = GetFullDateFullVersion($DateStr);
		}
		
			$plus = trim($sheet->getCell($letter[$shift][1].$i)->getCalculatedValue());

			if ($plus)
			$doc->addItem($plus, 1, $fullDate);
			
			$minus = trim($sheet->getCell($letter[$shift][2].$i)->getCalculatedValue());

			if ($minus)
			$doc->addItem($minus, -1, $fullDate);
		
	}
}






?>
