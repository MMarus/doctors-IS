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

	public function __construct(Nette\Database\Context $database)
	{
		$this->db = $database;
	}


	public function renderDefault()
	{
		$this->allplans = $this->getPlans();

		$this->template->plans = $this->allplans;
		$date = new Nette\Utils\DateTime();
		$date->getTimestamp();
		$this->template->date = $date;

	}


	private function getPlans()
	{
		$idp 	= $this->db->query("SELECT P.ID as idp ,count(*) as cntx FROM Plan P JOIN VykonMaPlan VM ON VM.id_Plan = P.ID WHERE P.id_NavstevaOrdinacie is NULL group by P.ID");
		$plans 	= $this->db->query("SELECT P.ID as idp , C.ID as idPac, V.ID as idVyk,  P.Planovany_datum as datum, C.Priezvisko as priez, C.Rodne_cislo as rc, V.Nazov  as vykon, P.Poznamky as pozn FROM Plan P JOIN Pacient C ON P.id_Pacient = C.ID JOIN VykonMaPlan VM ON VM.id_Plan = P.ID JOIN Vykon V ON VM.id_Vykon = V.ID WHERE P.id_NavstevaOrdinacie is NULL");

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
		$this->db->query("INSERT INTO NavstevaOrdinacie (id_Pacient, Datum, Poznamky) VALUES ( '". $tmp->idPac ."','".$date."' ,'".$defPozn."')");
		$idNav = $this->db->query("SELECT MAX(ID) as ID FROM NavstevaOrdinacie WHERE Poznamky = '".$defPozn."' AND id_Pacient = '".$tmp->idPac."' AND Datum = '".$date."' ");
		if($idNav == null){return;}//??halt chyba
		$idNav = $idNav->fetchAll();
		foreach ($idNav as $Nav)
		{
			$idn = $Nav->ID;
		}

		$i = 0;
		foreach ($tmpx as $vyk)
		{
			if($i==0){$i++;continue;}//not first
			$idv = $vyk[0];

			$this->db->query("INSERT INTO PocasNavstevy (id_NavstevaOrdinacie, id_vykon) VALUES( '".$idn."', '".$idv."' )   ");
		}


		$this->db->query("UPDATE Plan SET id_NavstevaOrdinacie = '".$idn."' WHERE ID = '".$id."'");


		$this->redirect("Visit:show", $idn);
	}


}
