<?php

namespace App\Presenters;

use Nette;
use Tracy\Debugger;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;
use Nette\Application\UI\Control;



class HomepagePresenter extends BasePresenter
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



		/*if($this->pacientId){
			$this->template->services = $this->db->query("SELECT Vykon.*, PocasNavstevy.ID as serviceID FROM PocasNavstevy, Vykon WHERE PocasNavstevy.id_NavstevaOrdinacie = ? AND PocasNavstevy.id_Vykon = Vykon.ID", $this->visitId);
		}
		else*/
			;//$this->error("TEST");


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










	protected function createComponentAddService()
	{
		$allServices = $this->db->table('Pacient');
		$serviceInputs = NULL;
		foreach($allServices as $service) {
			$rc = substr($service->Rodne_cislo,0, -4)  . "/"   . substr($service->Rodne_cislo, -4);
			$serviceInputs[$service->ID] = "RC: ( "  .  $rc . " )  ~  " .  $service->Meno . " " . $service->Priezvisko    ;
		}

		$form = new UI\Form;
		if($serviceInputs){
			$form->addSelect('service', 'Pacient', $serviceInputs)->setPrompt('Vyber pacienta');
			$form->addSubmit('send', '');
		}
		$form->onSuccess[] = array($this, 'addServiceSucceeded');
		$form->setRenderer(new Bs3FormRenderer);
		return $form;
	}

	public function addServiceSucceeded(UI\Form $form, $values)
	{
		Debugger::barDump($values);

		/*if($values["service"] && $this->visitId > 0 ){
			$this->db->query('INSERT INTO PocasNavstevy', array(
					'ID' => '',
					'id_NavstevaOrdinacie' => $this->visitId,
					'id_Vykon' => $values["service"]));
			$this->flashMessage('DEBUG: id_NavstevaOrdinacie = '.$this->visitId.' vykon - '.$values["service"]);
		}
		else
			$this->flashMessage('Zle zadany formular');*/
	}

}
