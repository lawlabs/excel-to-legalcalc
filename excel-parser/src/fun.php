<?php

function only_digits($c)
{
$a=$c;
//$a = preg_replace(/[^0-9.]/, '', $a); // только цифры... и точка для дробных...
$a = preg_replace('/[^0-9]/', '', $a); // только цифры...и запятая для дробных... 
return $a;
}

function only_digits_dots($c)
{
$a=$c;
$a = preg_replace('/[^0-9.]/', '', $a); // только цифры... и точка для дробных...
//$a = preg_replace('/[^0-9]/', '', $a); // только цифры... 
return $a;
}




function trim_it($c)
{
// написать функцию полного отсечения пробелов, и табуляций, переносов строк...
$a=$c;

$a=strip_tags($a); // теги

$a = preg_replace("/ {2,}/"," ",$a); // лишние пробелы...

$a = str_replace(chr(13),"", $a);
$a = str_replace(chr(10),"", $a);
$a = str_replace(chr(9),"", $a);

$a=trim($a); // пробелы на краях...

return $a;
}

function kill_spaces($c)
{
// функция удаления всех пробелов...
$a=$c;

$a=str_replace(" ","",$a);

return $a;
}

function fullYear($c)
{
// функция добавления чисел до полного года...
$a=$c;
if (strlen($a)<4) $a = "20".$a;
return $a;
}

function GetFullDate($netDate, $delimetr=" "){
// вычислить полную дату...
$EX = explode($delimetr, $netDate);

$month = mb_strtolower($EX[0]);
$year = fullYear(mb_strtolower($EX[1]));

$monthByName = array(
'январь' =>'01',
'февраль' =>'02',
'март' =>'03',
'апрель' =>'04',
'май' =>'05',
'июнь' =>'06',
'июль' =>'07',
'август' =>'08',
'сентябрь' =>'09',
'октябрь' =>'10',
'ноябрь' =>'11',
'декабрь' =>'12'
);

$MM = $monthByName[$month];

$daysByMonth = array(
'январь' =>'31',
'февраль' =>'28',
'март' =>'31',
'апрель' =>'30',
'май' =>'31',
'июнь' =>'30',
'июль' =>'31',
'август' =>'31',
'сентябрь' =>'30',
'октябрь' =>'31',
'ноябрь' =>'30',
'декабрь' =>'31'
);

$DD = $daysByMonth[$month];

// а если высокосный год? )))... более правильная проверка только для 2000 и 2400 года - думаю не целесообразно...
if ($year%4==0 && $MM=="02") $DD="29";

return $DD.".".$MM.".".$year;	
}

function GetFullDateDigits($netDate, $delimetr="."){
// вычислить полную дату... месяц указан просто числом... дописываем число...
$EX = explode($delimetr, $netDate);

$month = mb_strtolower($EX[0]);
$year = fullYear(mb_strtolower($EX[1]));

$daysByMonth = array(
'01' =>'31',
'02' =>'28',
'03' =>'31',
'04' =>'30',
'05' =>'31',
'06' =>'30',
'07' =>'31',
'08' =>'31',
'09' =>'30',
'10' =>'31',
'11' =>'30',
'12' =>'31'
);

$DD = $daysByMonth[$month];

// а если высокосный год? )))... более правильная проверка только для 2000 и 2400 года - думаю не целесообразно...
if ($year%4==0 && $MM=="02") $DD="29";

return $DD.".".$month.".".$year;	
}



function GetFullDateFullVersion($netDate){
// вычислить полную дату...

$EX = explode(".", $netDate);

if (count($EX)<3) {
// подразумевается что дата в числовом формате экселя.

$date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($netDate);		
$date = $date->format('d.m.Y');	
}
else {
	
$date = $EX[0].".".$EX[1].".".fullYear($EX[2]);
}

return $date;
}









function zeroes($c,$n)
{
// простановка нужного кол-ва нулей
$a=$c;

while(strlen($a)<$n)
{
$a="0".$a;
}

return $a;
}


?>