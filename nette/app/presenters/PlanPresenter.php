<?php

namespace App\Presenters;

use Nette;
use Tracy\Debugger;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;
use Nette\Application\UI\Control;



class PlanPresenter extends BasePresenter
{
	private $db;
	private $id;
	public $pacientId;
	public $allplans;
	public $date;

	public function __construct(Nette\Database\Context $database)
	{
		$this->db = $database;
	}


	public function renderDefault()
	{
		$this->date = new Nette\Utils\DateTime();
		$this->date->getTimestamp();
		$this->template->date = $this->date;

		$this->allplans = $this->getPlans("all");
		$this->template->plans = $this->allplans;


	}


	public function getPlans($mode = "all")
	{
		$tools = new ToolsPresenter($this->db);
		$date = $tools->fdate("today","ymd");





		if($mode == "today")
		{
			Debugger::barDump("today");
			$idp 	= $this->db->query("SELECT P.ID as idp ,count(*) as cntx FROM Plan P JOIN VykonMaPlan VM ON VM.id_Plan = P.ID WHERE P.Planovany_datum LIKE '%".$date."%' group by P.ID");
			$plans 	= $this->db->query("SELECT P.ID as idp , C.ID as idPac, V.ID as idVyk, P.id_NavstevaOrdinacie as done ,  P.Planovany_datum as datum, C.Priezvisko as priez, C.Rodne_cislo as rc, V.Nazov  as vykon, P.Poznamky as pozn FROM Plan P JOIN Pacient C ON P.id_Pacient = C.ID JOIN VykonMaPlan VM ON VM.id_Plan = P.ID JOIN Vykon V ON VM.id_Vykon = V.ID WHERE P.Planovany_datum LIKE '%".$date."%' ");
		}
		else
		{
			Debugger::barDump("all");
			$idp 	= $this->db->query("SELECT P.ID as idp ,count(*) as cntx FROM Plan P JOIN VykonMaPlan VM ON VM.id_Plan = P.ID group by P.ID");
			$plans 	= $this->db->query("SELECT P.ID as idp , C.ID as idPac, V.ID as idVyk, P.id_NavstevaOrdinacie as done ,  P.Planovany_datum as datum, C.Priezvisko as priez, C.Rodne_cislo as rc, V.Nazov  as vykon, P.Poznamky as pozn FROM Plan P JOIN Pacient C ON P.id_Pacient = C.ID JOIN VykonMaPlan VM ON VM.id_Plan = P.ID JOIN Vykon V ON VM.id_Vykon = V.ID ");

		}

		$plans 	= $plans->fetchAll();
		$idp	= $idp->fetchAll();

		$xx = array();
		$x = 1;
		$i = 0;



		foreach ($idp as $idpx)
		{

			foreach ($plans as $plansx)
			{

				if( $idpx->idp == $plansx->idp )
				{
					$xx[$i][$x][0] = $plansx->idVyk;
					$xx[$i][$x][1] = $plansx->vykon;
					$x++;
					$xx[$i][0] = $plansx;
				}
			}$x=1;$i++;
		}
		//Debugger::barDump($xx);
		return $xx;
	}






	public function actionGoplan($id)
	{
		//load plan
		$tmp;
		$tmpx;
		$idNav;
		$date = new Nette\Utils\DateTime();
		$date->getTimestamp();

		$plans = $this->getPlans();

		$i = 0;
		foreach ($plans as $plan)
		{
			if($plan[0]->idp == $id )
			{
				$tmpx = $plans[$i];
			}
			$i++;
		}

		$tmp = $tmpx[0];

		$defPozn = "Planovana navsteva.";
		$idn = $this->db->table("NavstevaOrdinacie")->insert(array(
				"id_Pacient" 	=> $tmp->idPac,
				"Datum"			=> $date,
				"Poznamky"		=> "Planovana navsteva..."));

		$idn = $idn->getPrimary();



		foreach ($tmpx as $vyk)
		{
			if(isset( $vyk->idp )){continue;}//not first
			$idv = $vyk[0];
			//Debugger::barDump($idv);

			$this->db->query("INSERT INTO PocasNavstevy (id_NavstevaOrdinacie, id_vykon) VALUES( '".$idn."', '".$idv."' )   ");
		}


		$this->db->query("UPDATE Plan SET id_NavstevaOrdinacie = '".$idn."' WHERE ID = '".$id."'");


		$this->redirect("Visit:show", $idn);
	}


}
