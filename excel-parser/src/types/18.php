<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function recognize($sheet, &$doc){

	$doc->documentType = 'все равно пока как';
	
	// метаданные...
	$A3 = $sheet->getCell("A3")->getValue();
	$A2 = $sheet->getCell("A2")->getValue();

	$ex = explode(",", $A2);

	$doc->period = str_replace(["Период: "], "", $A3);
	$doc->person = trim($ex[1]);

	$B6 = $sheet->getCell("B6")->getValue();

	$balance = 0;
	
	if ($B6)
		$balance = $B6;
	
	$hasPayed = false;

	$H = Coordinate::columnIndexFromString($sheet->getHighestColumn());

	if ($H > 6)
		$hasPayed = true;

	$A6 = $sheet->getCell("A6")->getValue();

	$year = trim_it($A6); // год - пока 2022 только один - ждем контр примеров...
	
	$doc->setBalance($balance); // пока для ячейки B6 - если будут контр примеры документов - то уточнить этот адрес...
	
	$monthNames =['Январь' => '01', 'Февраль' => '02', 'Март' => '03', 'Апрель' => '04', 'Май' => '05', 'Июнь' => '06', 'Июль' => '07', 'Август' => '08', 'Сентябрь' => '09', 'Октябрь' => '10', 'Ноябрь' => '11', 'Декабрь' => '12'];

	$row = 7;
	
	while (true) {

		$month_ = trim_it($sheet->getCell("A".$row)->getValue());

		if (!strlen($month_))
			break;

		preg_match('/^\d{4}$/', $month_, $m);

		if (count($m))
			$year = $m[0];

		if (array_key_exists($month_, $monthNames)){

			$date = "01." . $monthNames[$month_] . "." . $year;
			
			$plus = floatval(trim_it($sheet->getCell("C".$row)->getValue()));
			$correction = floatval(trim_it($sheet->getCell("E".$row)->getValue())); // перерасчет...

			// суммируем результат...
			$doc->addItem($plus, 1, $date);
			$doc->addItem($correction, 1, $date);

			if ($hasPayed) {
				$minus = floatval(trim_it($sheet->getCell("F".$row)->getValue())); // оплачено...
				$doc->addItem($minus, -1, $date);
			}

		}

		$row++;

	}
	
}

?>
