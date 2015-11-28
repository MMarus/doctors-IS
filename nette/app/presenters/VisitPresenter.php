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

    public function actionEdit($id)
    {
        $this->visitId = $id;
    }

    public function renderEdit()
    {
        if ($this->visitId) {
            $this->template->services = $this->db->query("SELECT Vykon.*, PocasNavstevy.ID as serviceID FROM PocasNavstevy, Vykon WHERE PocasNavstevy.id_NavstevaOrdinacie = ? AND PocasNavstevy.id_Vykon = Vykon.ID", $this->visitId);
            //$this->template->services = $this->db->query("SELECT Liek.*, PredpisanyLiek.ID as drugID FROM PredpisanyLiek, Vykon WHERE PocasNavstevy.id_NavstevaOrdinacie = ? AND PocasNavstevy.id_Vykon = Vykon.ID", $this->visitId);
        } else
            $this->error("TEST");
    }

    protected function createComponentAddService()
    {
        $allServices = $this->db->table('Vykon');
        $serviceInputs = NULL;
        foreach ($allServices as $service) {
            $serviceInputs[$service->ID] = $service->Nazov;
        }

        $form = new UI\Form;
        if ($serviceInputs) {
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

        if ($values["service"] && $this->visitId > 0) {
            $this->db->query('INSERT INTO PocasNavstevy', array(
                'ID' => '',
                'id_NavstevaOrdinacie' => $this->visitId,
                'id_Vykon' => $values["service"]));
            $this->flashMessage('DEBUG: id_NavstevaOrdinacie = ' . $this->visitId . ' vykon - ' . $values["service"]);
        } else
            $this->flashMessage('Zle zadany formular');
    }

    protected function createComponentRemoveService()
    {
        $form = new UI\Form;
        $form->addSubmit('sendRemove', '');
        $form->onSuccess[] = array($this, 'removeServiceSucceeded');
        $form->setRenderer(new Bs3FormRenderer);
        return $form;
    }


    // volá se po úspěšném odeslání formuláře
    public function removeServiceSucceeded(UI\Form $form, $values)
    {
        $valuesCheck = $form->getHttpData($form::DATA_TEXT | $form::DATA_KEYS, 'sel[]');

        if ($valuesCheck) {
            foreach ($valuesCheck as $val) {
                $this->db->query("DELETE FROM PocasNavstevy WHERE ID = ?", $val);
            }
        } else {
            $this->flashMessage('Zle zadany formular');
        }

    }

}
