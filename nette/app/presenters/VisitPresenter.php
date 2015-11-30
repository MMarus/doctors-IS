<?php

namespace App\Presenters;


use Nette;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;
use Nette\Application\UI\Control;
use Nette\Application\UI\Multiplier;

use Tracy\Debugger;



//TODO: treba pridat editacia datumu + pacienta + poznamky
//TODO: pridavanie novej navstevy


class VisitPresenter extends BasePresenter
{
    private $db;
    private $ID;
    //Musi byt rovnake meno ako meno Tabulky, pre ktoru je tento prezenter
    //inak by sa nedalo vkladat nove zaznamy pomocou addDropdownSubmitSucceeded()
    private $presenterName;
    private $theads = array("ID" => "ID",
        "Datum" => "Dátum",
        "Poznamky" => "Poznámky",
        "id_Pacient" => "Pacient");


    public function __construct(Nette\Database\Context $database)
    {
        $this->db = $database;
        $this->presenterName = "NavstevaOrdinacie";
    }

    //Actions
    public function actionShow($id)
    {
        $this->ID = $id;
    }

    public function actionEdit($id)
    {
        $this->ID = $id;
    }

    //Renderers
    public function renderDefault()
    {
        $this->template->title = "Navsteva Ordinacie";

        $visits = $this->db->query("
            SELECT NavstevaOrdinacie.ID, NavstevaOrdinacie.Datum, NavstevaOrdinacie.Poznamky,
            CONCAT_WS(' ', Pacient.Meno, Pacient.Priezvisko)  as id_Pacient FROM NavstevaOrdinacie
            LEFT JOIN Pacient ON NavstevaOrdinacie.id_Pacient = Pacient.ID
        ");
        if (!$visits) {
            $this->error('Stránka nebyla nalezena');
        }

        $this->template->rows = $visits;
        $this->template->theads = $this->theads;
    }

    public function renderEdit(){
        $this->template->title = "Navsteva editacia";
    }

    public function renderShow()
    {
        if ($this->ID) {
            $this->template->services = $this->db->query("SELECT Vykon.*, PocasNavstevy.ID as IDcko FROM PocasNavstevy, Vykon WHERE PocasNavstevy.id_NavstevaOrdinacie = ? AND PocasNavstevy.id_Vykon = Vykon.ID", $this->ID);
            //Nazvy foriem
            $this->template->form1 = "PocasNavstevy";
            $this->template->theads1 = array("Nazov", "Popis");

            $this->template->ext = $this->db->query("SELECT ExternePracovisko.*, Odporucenie.ID as IDcko FROM Odporucenie, ExternePracovisko WHERE Odporucenie.id_NavstevaOrdinacie = ? AND Odporucenie.id_ExternePracovisko = ExternePracovisko.ID", $this->ID);
            $this->template->formExt = "Odporucenie";
            $this->template->theadsExt = array("Nazov", "Specializacia", "Lekar");
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
            Debugger::barDump($tableTo);
            $results = $this->db->query("SELECT ID, Nazov FROM ".$tableFrom." WHERE ID NOT IN
                                        (SELECT id_".$tableFrom." FROM ".$tableTo."
                                        WHERE id_".$this->presenterName." = ".$this->ID.")");
            //table($tableFrom);


            $options = NULL;
            foreach ($results as $result) {
                $options[$result->ID] = $result->Nazov;
            }
            Debugger::barDump($options);
            $form = new UI\Form;
            if ($options) {
                $form->addMultiSelect($tableTo, 'Vyber', $options);
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

        if ($values[$tableTo] && $this->ID > 0) {

            foreach( $values[$tableTo] as $val ){
                Debugger::barDump($val);
                $this->db->query('INSERT INTO '.$tableTo, array(
                    'ID' => '',
                    'id_'.$this->presenterName => $this->ID,
                    'id_'.$tableFrom => $val));
            }
            $this->redirect('this');
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

    //Component editacia navstevy (datum pacient atd)

    //Components
    protected function createComponentVisitForm()
    {
        $visit = $this->db->table('NavstevaOrdinacie')->get($this->ID);

        $pacientiRows = $this->db->table('Pacient');
        foreach($pacientiRows as $row){
            $pacienti[$row->ID] = $row->Meno." ".$row->Priezvisko." ".$row->Rodne_cislo;
        }


        $form = new UI\Form;
        foreach($this->theads as $key => $thead){
            if($key == "id_Pacient"){
                $form->addSelect($key, $thead.":", $pacienti)
                    ->setPrompt("Vybrat");
            }
            elseif($key == "Poznamky"){
                $form->addTextArea($key, $thead.":");
            }
            elseif($key == "Datum"){

                $form->addTbDateTimePicker('Datum', 'Datum:')
                    ->setRequired();
                //$form->addSelect($key, $thead.":", $blood_types)
                 //   ->setPrompt("Vybrat");
            }
            else
                $form->addText($key, $thead.":");

            //Nastavenie Default values
            if($visit){
                $form[$key]->setDefaultValue($visit->$key);
            }
            //Nastavenie Required Policok
            if($key == "id_Pacient" || $key == "Datum")
                $form[$key]->setRequired('Vyplnte policko '.$thead."!");
        }

        $form->addSubmit('send', 'Ulozit');
        $form->onSuccess[] = array($this, 'VisitFormSucceeded');
        $form->setRenderer(new Bs3FormRenderer);
        return $form;
    }

    // volá se po úspěšném odeslání formuláře
    public function VisitFormSucceeded(UI\Form $form, $values)
    {
        Debugger::barDump($values);
        Debugger::barDump($this->ID);
        /*
        //Ak ID neexistuje pridaj ho
        $this->db->query("INSERT INTO Pacient
            (ID, Rodne_cislo, Meno, Priezvisko, Adresa, Krvna_skupina, Poznamky, id_Poistovna)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            Rodne_cislo = ?, Meno = ?, Priezvisko = ?, Adresa = ?, Krvna_skupina = ?, Poznamky = ?, id_Poistovna = ?",
            $this->ID, $values->Rodne_cislo, $values->Meno, $values->Priezvisko, $values->Adresa, $values->Krvna_skupina, $values->Poznamky, $values->Poistovna,
            $values->Rodne_cislo, $values->Meno, $values->Priezvisko, $values->Adresa, $values->Krvna_skupina, $values->Poznamky, $values->Poistovna
        );
        if(!isset($this->ID)){
            $id = $this->db->getInsertId('Pacient');
            $this->flashMessage('Pacient uspesne pridany.');
            $this->redirect("edit", array($id));
        }else
            $this->flashMessage('Pacient uspesne upraveny.');
        */


    }

}
