<?php

class Item {
	public $type = 0; // доход или расход...
	public $amount = 0; // сумма...
	public $date = 0; // дата...
	public $deadLine = 0; // дата дедлайна...
	
	function __construct($amount, $type, $date, $deadDate) {
		$this->type = $type;
		$this->amount = $amount;
		$this->date = $date;
		$this->deadLine = $deadDate;
   }
}




class Document {

    public $organization = ''; // название организации
    public $person = ''; // название должника
	public $period = ''; // название должника
	public $address = ''; // название должника
	public $startBalance = 0; // начальный баланс
	
	public $documentType = 'неизвестно'; // название документа...
	
	// настройки для запроса расчета по api
	public $zero_penalty = false;
	public $correct_debt_dates = false;
	public $exact_date;
	
	public $method = 1;
	public $rate = 2;
	public $stop;
   
	public $items = array(); // элементы списка...
	
	// конструктор
	function __construct($stop) {
		$this->stop = $stop;
		$this->exact_date = $stop;
	}
	
	function addItem($amount, $type, $date) {

	if (!$amount || $amount=='#NULL!') return false; // проверка на пустоту...
	
	// убрать звездочку...
	$amount = str_replace(['*'], '', $amount);
	
	// запятая - всегда на точку...
	$amount = str_replace([','], '.', $amount);
	
	// смена типа, если сумма отрицательная...
	if ($amount < 0) {
		$amount = abs($amount);
		$type = - $type;
	}
	
	$amount = floatval($amount);
	
	if (!$amount) return false; // проверка на пустоту...
	
	if (!is_float($amount)) return false;
	
	// проверить нет ли уже суммы на такую дату...
	
	foreach ($this->items as &$item)
	{
		if ($item->date == $date && $item->type == $type) {
			$item->amount+=$amount;
			unset($item);
			return;
		}
	}
	
	$this->items[] = new Item($amount, $type, $date, $this->getDeadLineDate($date));
    }
	
	function setBalance($amount) {
		
	if (!$amount || $amount=='#NULL!') {

		$this->startBalance = 0;
		return false; // проверка на пустоту...	
	}
	
	// убрать звездочку...
	$amount = str_replace(['*'], '', $amount);
	
	// запятая - всегда на точку...
	$amount = str_replace([','], '.', $amount);
	
	$this->startBalance = $amount * 1;
    }
	
	function getDeadLineDate($date) {
	
	$EX = explode(".", $date);
	
	$M = $EX[1] + 0;
	$Y = $EX[2] + 0;
	
	$M++;

	if ($M > 12) {
		$M = 1;
		$Y++;
	}
	
	return "11.".($M < 10 ? "0".$M : $M).".".$Y;
	}
	
	
	function getReverseDate($date) {
		$EX = explode(".", $date);
		return $EX[2].$EX[1].$EX[0];
	}
	
	function getNormalDate($date) {
		return substr($date,6).'.'.substr($date,4,2).'.'.substr($date,0,4);
	}
	

	
	
	function printSortedDocument() {

		print "<h1>Распознанный тип документа: <i>".$this->documentType."</i></h1>";
		
		print "<HR>";
		print "<b>Должник: </b>".$this->person;
		print "</br><b>Адрес: </b>".$this->address;
		print "</br><b>Организация: </b>".$this->organization;
		print "</br><b>Период: </b>".$this->period;
		print "</br><b>Начальный баланс: </b>".$this->startBalance;
		
		print "<HR>";
		print "<table class='output'>";
		
		// создать общий массив данные идут и так попорядку...
		
		foreach ($this->items as $item)
		{
			
		$amount = $item->amount;
		$amount = str_replace(".",",",$amount);
		//$amount = round($amount, 2);
		$date = $item->date;
		$deadLine = $item->deadLine;
		
		if ($item->type==1)
		print "<tr><td>$date</td><td>$amount</td><td></td><td>$deadLine</td></tr>";

		else
		print "<tr><td>$date</td><td></td><td>$amount</td><td></td></tr>";
		}
		
		print "</table>";	
		
    }
	
	function packToJson() {
		
		// упаковка в json строку...
		
		$D = array();
		
		$D["zero_penalty"] = $this->zero_penalty;
		$D["correct_debt_dates"] = $this->correct_debt_dates;
		$D["exact_date"] = $this->exact_date;
		$D["method"] = $this->method;
		$D["rate"] = $this->rate;
		$D["stop"] = $this->stop;
		
		$DEBTS = array();
		
		foreach ($this->items as $item)
		{
			if ($item->type!=1) continue;

			$A = array();
			
			$A['start'] = $item->date;
			$A['amount'] = round($item->amount, 2);
			$A['part'] = "1/1";
			
			$DEBTS[] = $A;
		}
		
		$D["debts"] = $DEBTS;
		
		$PAYMENTS = array();
		
		foreach ($this->items as $item)
		{
			if ($item->type!=-1) continue;
			
			$A = array();
			
			$A['payment_date'] = $item->date;
			$A['amount'] = round($item->amount, 2);
			//$A['pay_for'] = null;
			
			$PAYMENTS[] = $A;
		}
		
		$D["payments"] = $PAYMENTS;
		
		$json = json_encode($D);

		return $json;
	}
	
	function packToJson2() {
		
		// упаковка в json строку... со всеми метаданными...
		
		$D = array();
		
		$D["organization"] = $this->organization;
		$D["person"] 	   = $this->person;
		$D["period"]	   = $this->period;
		$D["address"] 	   = $this->address;
		$D["startBalance"] = $this->startBalance;
		
		$DEBTS = array();
		
		foreach ($this->items as $item)
		{
			if ($item->type!=1) continue;

			$A = array();
			
			$A['start'] = $item->date;
			$A['amount'] = round($item->amount, 2);
			$A['part'] = "1/1";
			
			$DEBTS[] = $A;
		}
		
		$D["debts"] = $DEBTS;
		
		$PAYMENTS = array();
		
		foreach ($this->items as $item)
		{
			if ($item->type!=-1) continue;
			
			$A = array();
			
			$A['payment_date'] = $item->date;
			$A['amount'] = round($item->amount, 2);
			//$A['pay_for'] = null;
			
			$PAYMENTS[] = $A;
		}
		
		$D["payments"] = $PAYMENTS;
		
		$json = json_encode($D);

		return $json;
	}
	
	
	function apiRequest($json) {
	
	$myCurl = curl_init();
	
	curl_setopt_array($myCurl, array(
    CURLOPT_URL => 'http://45.137.65.38:7006/api/v1/',
	CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true
	));
	
	curl_setopt($myCurl, CURLOPT_POSTFIELDS, $json);
	
	$response = curl_exec($myCurl);
	
	curl_close($myCurl);

	return $response;
	}
	
	

	
	
	
}


?>
