<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class PatientPresenter extends BasePresenter
{
    private $db;
    private $ID;
    private $theads = array("Rodne_cislo" => "Rodné číslo",
        "Meno" => "Meno",
        "Priezvisko"  => "Priezvisko",
        "Poistovna" => "Poistovňa",
        "Adresa" => "Adresa",
        "Krvna_skupina" => "Krvná skupina",
        "Poznamky"  => "Poznámky"
    );
    
    public function __construct(Nette\Database\Context $database)
    {
        $this->db = $database;
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
        $this->template->title = "Pacienti";

        $patients = $this->db->query("
            SELECT Pacient.*, Poistovna.Nazov as Poistovna FROM Pacient
            LEFT JOIN Poistovna ON Pacient.id_Poistovna = Poistovna.ID
        ");
        if (!$patients) {
            $this->error('Stránka nebyla nalezena');
        }

        $this->template->rows = $patients;
        $this->template->theads = $this->theads;
    }

    public function renderEdit()
    {
        $this->template->title = "Pacient editacia";
    }
    
    public function renderShow()
    {
            $this->template->id = $this->ID;
            $patient = $this->db->table('Pacient')->get($this->ID);
            
            if (!$patient) {
                $this->error('Stránka nebyla nalezena');
            }
            $this->template->patient = $patient;
            
            $plans = $this->db->query("SELECT *, DATE_FORMAT(Planovany_datum,'%H:%i %d.%m.%Y') AS niceDate FROM Plan WHERE id_Pacient = ? ORDER BY Planovany_datum DESC ", $this->ID);
            
            $this->template->plans = $plans;
            
            $this->template->visits = $this->db->query("SELECT *, DATE_FORMAT(Datum,'%H:%i %d.%m.%Y') AS niceDate FROM NavstevaOrdinacie WHERE id_Pacient = ? ORDER BY Datum DESC ", $this->ID);
            
    }


    //Components
    protected function createComponentPacientForm()
    {
        $patient = $this->db->table('Pacient')->get($this->ID);

        $poistovne = [];
        $poistovneRows = $this->db->table('Poistovna');
        foreach($poistovneRows as $row){
            $poistovne[$row->ID] = $row->Nazov;
        }
        $poistovneKeys = array_keys($poistovne);
        Debugger::barDump($poistovneKeys);
        $blood_types = array("A+" => "A+", "A-" => "A-", "B+" => "B+", "B-" => "B-", "AB+" => "AB+", "AB-" => "AB-", "0+" => "0+", "0-" => "0-");

        $form = new UI\Form;
        foreach($this->theads as $key => $thead){
            if($key == "Poistovna"){
                $form->addSelect($key, $thead.":", $poistovne)
                    ->setPrompt("Vybrat");
            }
            elseif($key == "Poznamky"){
                $form->addTextArea($key, $thead.":")

                ;
            }
            elseif($key == "Krvna_skupina"){
                $form->addSelect($key, $thead.":", $blood_types)
                    ->setPrompt("Vybrat");
            }
            else
            $form->addText($key, $thead.":");

            //Nastavenie Default values
            if($patient){
                $form[$key]->setDefaultValue($patient->$key);
            }
            //Nastavenie Required Policok
            if($key == "Meno" || $key == "Priezvisko" || $key == "Rodne_cislo" || $key == "Krvna_skupina" || $key == "Poistovna")
                $form[$key]->setRequired('Vyplnte policko '.$thead."!");
        }

        $form->addSubmit('send', 'Ulozit');
        $form->onSuccess[] = array($this, 'PacientFormSucceeded');
        $form->setRenderer(new Bs3FormRenderer);
        return $form;
    }

    // volá se po úspěšném odeslání formuláře
    public function PacientFormSucceeded(UI\Form $form, $values)
    {
        Debugger::barDump($values);
        Debugger::barDump($this->ID);

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



    }

}
