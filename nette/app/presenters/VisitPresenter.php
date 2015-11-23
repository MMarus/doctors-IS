<?php

namespace App\Presenters;

use Nette;
use App\Model;


class VisitPresenter extends BasePresenter
{
	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

}
