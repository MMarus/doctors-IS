<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;


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
        $blood_types = array("A+" => "A+", "A-" => "A-", "B+" => "B+", "B-" => "B-", "AB+" => "AB+", "AB-" => "AB-", "0+" => "0+", "0-" => "0-");

        $form = new UI\Form;
        foreach($this->theads as $key => $thead){
            if($key == "Poistovna"){
                $form->addSelect($key, $thead."*:", $poistovne)->setDefaultValue($patient->$key);
            }
            elseif($key == "Poznamky"){
                $form->addTextArea($key, $thead."*:");
            }
            elseif($key == "Krvna_skupina"){
                $form->addSelect($key, $thead."*:", $blood_types)->setDefaultValue($patient->$key);
            }
            else
            $form->addText($key, $thead."*:");
        }
/*
        $form->addText('Priezvisko', 'Priezvisko*');
        $form->addText('Rodne', 'Rodne cislo*')->setRequired('Zadejte prosím jméno');
        $form->addText('Adresa', 'Adresa*')->setRequired('Zadejte prosím jméno');
        $form->addText('Krvna', 'Krvna skupina*')->setRequired('Zadejte prosím jméno');
        $form->addTextArea('Poznamky', 'Poznamky*')->setRequired('Zadejte prosím jméno');
        $form->addText('Poistovna', 'Poistovna*')->setRequired('Zadejte prosím jméno');
*/
        $form->addSubmit('send', 'Ulozit');
        $form->onSuccess[] = array($this, 'registrationFormSucceeded');
        $form->setRenderer(new Bs3FormRenderer);
        return $form;
    }

    // volá se po úspěšném odeslání formuláře
    public function registrationFormSucceeded(UI\Form $form, $values)
    {
        // ...
        $this->flashMessage('Byl jste úspěšně registrován.');
        //$this->redirect($this);
    }

}
