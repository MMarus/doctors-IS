<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Tracy\Debugger;


class HomepagePresenter extends BasePresenter
{
	private $db;

	public function __construct(Nette\Database\Context $database)
	{
		$this->db = $database;
	}


	public function renderDefault()
	{
		$idp 	= $this->db->query("SELECT P.ID as idp ,count(*) as cntx FROM Plan P JOIN VykonMaPlan VM ON VM.id_Plan = P.ID WHERE P.id_NavstevaOrdinacie is NULL group by P.ID");
		$plans 	= $this->db->query("SELECT P.ID as idp ,P.Planovany_datum as datum, C.Priezvisko as priez,C.Rodne_cislo as rc, V.Nazov  as vykon, P.Poznamky as pozn FROM Plan P JOIN Pacient C ON P.id_Pacient = C.ID JOIN VykonMaPlan VM ON VM.id_Plan = P.ID JOIN Vykon V ON VM.id_Vykon = V.ID WHERE P.id_NavstevaOrdinacie is NULL");

		$plans 	= $plans->fetchAll();
		$idp	= $idp->fetchAll();

		$xx = array();
		$i = $x = 1;



		foreach ($idp as $idpx)
		{

			foreach ($plans as $plansx)
			{
				if( $idpx->idp == $plansx->idp )
				{
					$xx[$idpx->idp][$x] = $plansx->vykon;
					$x++;
					$xx[$idpx->idp][0] = $plansx;
				}
			}$x=1;
		}

		Debugger::barDump($xx);



		if (!$plans) {
			$this->error('Stranka sa nenasla');
		}
		$this->template->plans = $plans;
		$date = new Nette\Utils\DateTime();
		$date->getTimestamp();
		$this->template->date = $date;


	}

}
