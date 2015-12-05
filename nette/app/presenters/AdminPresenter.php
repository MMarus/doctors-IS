<?php

namespace App\Presenters;

use Nette;
use Tracy\Debugger;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Application\UI\Control;
use Nette\Security\Passwords;
use App\Model\UserManager;



class AdminPresenter extends BasePresenter
{
	private $db;
	private $ID;
	public $pacientId;
	public $allplans;
	public $mode;
	public $zam;

	private $theads = array(
			"uid" => "Login",
			"upx"  => "Heslo",
			"role" => "Prava",
			"meno" => "Meno",
			"priezvisko"  => "Priezvisko",
			"adresa" => "Adresa"
	);
	private $theadsMaxLength = array(
			"meno" => 25,
			"priezvisko"  => 25,
			"adresa" => 100,
			"uid" => 10,
			"upx"  => 30,
			"role" => 10,
	);


	public function __construct(Nette\Database\Context $database)
	{
		$this->db = $database;



	}


	public function renderDefault()
	{
		//$this->allplans = $this->getPlans();
		$this->template->title = "Zamestnanci";
		$this->template->rows  = $this->db->table("Zamestnanec")->order("deleted ASC, disabled ASC");
		$this->template->zam = $this->zam;
		$this->template->mode = $this->mode;
	}

	public function actionDelete($id)
	{
		if(isset($id))
		{
			$this->db->query("UPDATE Zamestnanec SET deleted = 1 WHERE ID = " . $id . ";");
		}
		$this->redirect("Admin:");
	}

	public function actionUndelete($id)
	{
		if(isset($id))
		{
			$this->db->query("UPDATE Zamestnanec SET deleted = 0 WHERE ID = " . $id . ";");
		}
		$this->redirect("Admin:");
	}

	public function actionDisable($id)
	{
		if(isset($id))
		{
			$this->db->query("UPDATE Zamestnanec SET disabled = 1 WHERE ID = " . $id . ";");
		}
		$this->redirect("Admin:");
	}
	public function actionUndisable($id)
	{
		if(isset($id))
		{
			$this->db->query("UPDATE Zamestnanec SET disabled = 0 WHERE ID = " . $id . ";");
		}
		$this->redirect("Admin:");
	}


	public function actionEdit($id)
	{
		$this->ID = $id;
		$this->mode = "new";
		if($id)
			$this->mode = "edit";

		$zam = $this->db->table('Zamestnanec')->get($this->ID);

		if(!isset($this->ID))
		{
			$this->mode = "new";
		}
		else
		{
			if($zam){$this->mode = "edit";}
			else{$this->mode = "err";}
		}



		if($this->mode == "edit")
		{
			$zam = $this->db->query("SELECT * FROM Zamestnanec WHERE ID = '" . $this->ID . "';")->fetchAll()[0];
		}
		$this->zam = $zam;

		$this->template->title = "Zamestnanci";
		$this->template->zam = $this->zam;
		$this->template->mode = $this->mode;
	}

	public function renderEdit()
	{
		$this->template->title = "Zamestnanci";
		$this->template->zam = $this->zam;
		$this->template->mode = $this->mode;
		//$this->ID = 1;
	}






	//Components
	protected function createComponentZamestnanecForm()
	{
		//make form
		$form = new UI\Form;
		if($this->mode == "err"){return $form ;}

		$zam = $this->zam;






		$roles = array("admin" => "admin", "doktor" => "doktor", "sestra" => "sestra");



		foreach($this->theads as $key => $thead)
		{
			if ($key == "adresa" )
			{
				$form->addTextArea($key, $thead.":");
			}
			else if($key == "upx")
			{
				$form->addPassword($key, $thead.":");
			}
			else if($key == "role")
			{
				$form->addSelect($key, $thead.":", $roles)
						->setPrompt("Vybrať");
			}
			else
				$form->addText($key, $thead.":");


			//defs
			if($this->mode == "edit")
			{
				$form[$key]->setDefaultValue($zam->$key);
			}


			if($key == "meno" || $key == "priezvisko" || $key == "uid" || $key == "role" || ($this->mode == "new" && $key == "upx"))
				$form[$key]->setRequired('Vyplnte policko '.$thead."!");



			$form[$key]->addRule(Form::MAX_LENGTH, 'Prilis vela znakov v '.$thead.'!', $this->theadsMaxLength[$key]);
		}





		$form->addSubmit('send', 'Ulozit');
		$form->onSuccess[] = array($this, 'ZamestnanecFormSucceeded');
		$form->setRenderer(new Bs3FormRenderer);
		return $form;
	}

	// volá se po úspěšném odeslání formuláře
	public function ZamestnanecFormSucceeded(UI\Form $form, $values)
	{
		$ERR = true;
		$regcode = $this->register($values);
		switch($regcode)
		{
			case "EXIST":
				$msg = "ERR. Zamestnanec s Login:" . $values["Login"] . "  už existuje!";
				break;

			case 0:
				$msg = "ERR.Zamestanec neulozeny!:";
				break;

			case NULL:
				$msg = "ERR.Zamestanec neulozeny!::";
				break;

			default:
				$ERR = false;
				$msg = "OKK.Zamestanec ulozeny!";
				$id = $regcode;
				break;
		}

		if(!isset($this->ID)){
			$this->flashMessage($msg);
			if(!$ERR)
				$this->redirect("edit", $id     );
		}else
			$this->flashMessage($msg);
	}


	public function register($data)
	{
		$tools = new ToolsPresenter($this->db);
		$date = $tools->fdate("today","ymd+");
		$login = $this->db->table("Zamestnanec")->select("uid")->where(array("uid"=>$data["uid"]))->fetchAll();

		if(   $this->mode != "edit" && count($login) != 0 ){return "EXIST";}//create new check exists

		//repack
		$vals["role"] 		= $data["role"];
		$vals["uid"] 		= $data["uid"];
		$vals["meno"] 		= $data["meno"];
		$vals["priezvisko"] = $data["priezvisko"];
		$vals["adresa"] 	= $data["adresa"];
		$vals["chng"]		= $date;

		if($data["upx"] != ""){$vals["upx"] = Passwords::hash($data["upx"]);}

		if($this->mode == "edit")
		{
			$tt = "";
			$i = 0;
			foreach($vals as $key => $val)
			{
				if($i++!=0){$tt .=", ";}
				$tt .= $key . " = '" . $val . "' ";
			}
			$this->db->query("UPDATE Zamestnanec SET " . $tt . " WHERE uid = '" . $vals["uid"] . "';" );
		}


		return $this->db->table(UserManager::TABLE_NAME)->insert( $vals )->getPrimary();
	}






}
