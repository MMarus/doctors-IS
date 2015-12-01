<?php

namespace App\Presenters;

use Nette;
use Tracy\Debugger;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;
use Nette\Application\UI\Control;



class ToolsPresenter extends BasePresenter
{
	private $db;


	public function __construct(Nette\Database\Context $database)
	{
		$this->db = $database;
	}


	public function renderDefault()
	{
		//$this->allplans = $this->getPlans();
		;
	}

	public function fdate($data, $format, $shift = "0", $shiftMode = "days")
	{
		if($data === "today") {$dto = new  Nette\Utils\DateTime( date('Y-m-d H:i:s') );}
		else{$dto = new DateTime( $data );}

		$dto -> modify($shift . " ". $shiftMode);
		$tmp = "";

		switch ($format)
		{
			//01-01-2015
			case 'dmy':
			{
				$tmp = $dto->format('d. m. Y');
				break;
			}

			case 'dm':
			{
				$tmp = $dto->format('d. m.');
				break;
			}

			case 'y':
			{
				$tmp = $dto->format('Y');
				break;
			}

			//01-01-2015 hhmmss
			case 'dmy+':
			{
				$tmp = $dto->format('d. m. Y H:i:s');
				break;
			}

			//2015-01-01
			case 'ymd':
			{
				$tmp = $dto->format('Y-m-d');
				break;
			}

			case 'ym':
			{
				$tmp = $dto->format('Y-m');
				break;
			}

			//2015-01-01 HH:MM:SS
			case 'ymd+':
			{
				$tmp = $dto->format('Y-m-d H:i:s');
				break;
			}

			//week day number
			case 'wdnr':
			{
				$tmp = $dto->format('w');
				break;

			}

			//first day month
			case 'fdm':
			{
				$tmp = $dto->format('Y-m-') . "01";
				break;
			}

			default:
				# code...
				break;
		}


		return $tmp;
	}




}
