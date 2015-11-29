<?php

namespace App\Presenters;


use Nette;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;
use Nette\Application\UI\Control;
use Nette\Application\UI\Multiplier;

use Tracy\Debugger;


class VisitPresenter extends BasePresenter
{
    private $db;
    private $ID;
    //Musi byt rovnake meno ako meno Tabulky, pre ktoru je tento prezenter
    //inak by sa nedalo vkladat nove zaznamy pomocou addDropdownSubmitSucceeded()
    private $presenterName;


    public function __construct(Nette\Database\Context $database)
    {
        $this->db = $database;
        $this->presenterName = "NavstevaOrdinacie";
    }

    //Actions
    public function actionEdit($id)
    {
        $this->ID = $id;
    }

    //Renderers
    public function renderDefault()
    {
        $this->template->anyVariable = 'any value';
    }

    public function renderEdit()
    {
        if ($this->ID) {
            $this->template->services = $this->db->query("SELECT Vykon.*, PocasNavstevy.ID as IDcko FROM PocasNavstevy, Vykon WHERE PocasNavstevy.id_NavstevaOrdinacie = ? AND PocasNavstevy.id_Vykon = Vykon.ID", $this->ID);

            //Nazvy foriem
            $this->template->form1 = "PocasNavstevy";
            $this->template->theads = array("Nazov", "Popis");

            $this->template->ext = $this->db->query("SELECT ExternePracovisko.*, Odporucenie.ID as recommendID FROM Odporucenie, ExternePracovisko WHERE Odporucenie.id_NavstevaOrdinacie = ? AND Odporucenie.id_ExternePracovisko = ExternePracovisko.ID", $this->ID);

        } else
            $this->error("TEST");
    }

    //Component SERVICE
    protected function createComponentAddDropdown()
    {

        return new Multiplier(function ($tableTo) {
            Debugger::barDump($tableTo);
            switch ($tableTo) {
                case "PocasNavstevy":
                    $tableFrom =  "Vykon";
                    break;
                case "Odporucenie":
                    $tableFrom =  "ExternePracovisko";
                    break;
            }

            $results = $this->db->table($tableFrom);
            $options = NULL;
            foreach ($results as $result) {
                $options[$result->ID] = $result->Nazov;
            }
            Debugger::barDump($options);
            $form = new UI\Form;
            if ($options) {
                $form->addSelect($tableTo, '', $options)->setPrompt('Vybrat');
                $form->addSubmit('send'.$tableTo, '');
            }

            $form->onSuccess[] = function($form) use($tableTo, $tableFrom) {
                $this->addDropdownSubmitSucceeded($form, $tableTo, $tableFrom);
            };

            $form->setRenderer(new Bs3FormRenderer);

            return $form;

        });



    }

    public function addDropdownSubmitSucceeded($form, $tableTo, $tableFrom){
        $values = $form->getValues();
        var_dump($values);

        if ($values[$tableTo] && $this->ID > 0) {
            $this->db->query('INSERT INTO '.$tableTo, array(
                'ID' => '',
                'id_'.$this->presenterName => $this->ID,
                'id_'.$tableFrom => $values[$tableTo]));
        } else
            $this->flashMessage('Zle zadany formular');

    }

    protected function createComponentRemoveRow()
    {

        return new Multiplier(function ($table) {
            $form = new UI\Form;
            $form->onSuccess[] = function($form) use($table) {

                //Debugger::barDump($form);
                $valuesChecked = $form->getHttpData($form::DATA_TEXT | $form::DATA_KEYS, $table."Sel[]");
                Debugger::barDump($valuesChecked);

                if ($valuesChecked) {
                    foreach ($valuesChecked as $val) {
                        $this->db->query("DELETE FROM ".$table." WHERE ID = ?", $val);
                    }
                } else {
                    $this->flashMessage('Zle zadany formular');
                }
            };

            $form->setRenderer(new Bs3FormRenderer);

            return $form;
        });



    }



    //Component SERVICE

}
