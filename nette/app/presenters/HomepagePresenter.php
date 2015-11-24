<?php

namespace App\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BasePresenter
{
	private $db;

	public function __construct(Nette\Database\Context $database)
	{
		$this->db = $database;
	}


	public function renderDefault()
	{
		$plans = $this->db->query("SELECT P.Planovany_datum as datum, C.Priezvisko as priez,C.Rodne_cislo as rc, V.Nazov  as vykon, P.Poznamky as pozn FROM Plan P JOIN Pacient C ON P.id_Pacient = C.ID JOIN VykonMaPlan VM ON VM.id_Plan = P.ID JOIN Vykon V ON VM.id_Vykon = V.ID WHERE P.id_NavstevaOrdinacie is NULL");
		if (!$plans) {
			$this->error('Stranka sa nenasla');
		}
		$this->template->plans = $plans;
		$date = new Nette\Utils\DateTime();
		$date->getTimestamp();
		$this->template->date = $date;
	}

}
