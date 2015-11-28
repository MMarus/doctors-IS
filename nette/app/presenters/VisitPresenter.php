<?php

namespace App\Presenters;


use Nette;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;
use Nette\Application\UI\Control;


class VisitPresenter extends BasePresenter
{
    private $db;
    private $visitId;


    public function __construct(Nette\Database\Context $database)
    {
        $this->db = $database;
    }

    public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

    public function actionEdit($id){
        $this->visitId = $id;
    }

	public function renderEdit()
	{
        if($this->visitId){
            $this->template->services = $this->db->query("SELECT Vykon.* FROM PocasNavstevy, Vykon WHERE PocasNavstevy.id_NavstevaOrdinacie = ? AND PocasNavstevy.id_Vykon = Vykon.ID", $this->visitId);
        }
        else
            $this->error("TEST");
	}

    protected function createComponentAddService()
    {
        $allServices = $this->db->query('SELECT * from Vykon WHERE ID NOT IN(SELECT id_Vykon From PocasNavstevy WHERE id_NavstevaOrdinacie = ?)', $this->visitId);
        $serviceInputs = NULL;
        foreach($allServices as $service) {
            $serviceInputs[$service->ID] = $service->Nazov;
        }

        $form = new UI\Form;
        if($serviceInputs){
            $form->addSelect('service', 'Vykon', $serviceInputs)->setPrompt('Vybrat vykon');
            $form->addSubmit('send', '');
        }
        $form->onSuccess[] = array($this, 'addServiceSucceeded');
        $form->setRenderer(new Bs3FormRenderer);
        return $form;
    }

    // volá se po úspěšném odeslání formuláře
    public function addServiceSucceeded(UI\Form $form, $values)
    {
        var_dump($values);
        var_dump($this->visitId);

        if($values["service"] && $this->visitId > 0 ){
            $this->db->query('INSERT INTO PocasNavstevy', array(
                'ID' => '',
                'id_NavstevaOrdinacie' => $this->visitId,
                'id_Vykon' => $values["service"]));
            $this->flashMessage('DEBUG: id_NavstevaOrdinacie = '.$this->visitId.' vykon - '.$values["service"]);
            $this->redirect("Visit:edit", $this->visitId);
        }
        else
            $this->flashMessage('Zle zadany formular');
    }

}
