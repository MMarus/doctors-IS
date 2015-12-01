<?php

namespace App\Presenters;

use Nette;
use Tracy\Debugger;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;
use Nette\Application\UI\Control;



class AdminPresenter extends BasePresenter
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
		//$this->allplans = $this->getPlans();
		;

	}




}
