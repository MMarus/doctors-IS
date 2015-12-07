<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Application\UI\Control;
use Nette\Application\UI\Multiplier;
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
    private $theadsMaxLength = array("Rodne_cislo" => 15,
        "Meno" => 25,
        "Priezvisko"  => 25,
        "Poistovna" => 10,
        "Adresa" => 100,
        "Krvna_skupina" => 3,
        "Poznamky"  => 255
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
        $this->template->UpravovanaTabulka = "Pacient";

        $patients = $this->db->query("
            SELECT Pacient.*, Poistovna.Nazov as Poistovna FROM Pacient
            LEFT JOIN Poistovna ON Pacient.id_Poistovna = Poistovna.ID
        ");
        if (!$patients) {
            $this->error('Stránka nebyla nalezena');
        }

        $this->template->rows = $patients;
        $this->template->theads = $this->theads;

        if( isset($_POST['type']) )
        {
            $this->template->type = $_POST['type'];//rc  /  name
            $this->template->data1 = $_POST['data1'];
            $this->template->data2 = $_POST['data2'];
        }
        else
        {
            //def
            $this->template->type = "";
            $this->template->data1 =  "";
            $this->template->data2 = "";
        }


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
                $this->redirect("default");
            }
            if($patient->deleted == 1 ){
                if (!$this->user->isInRole('admin')) { // je uživatel v roli admina?
                    $this->redirect("default");
                }
            }
            $this->template->patient = $patient;
            
            //$plans = $this->db->query("SELECT *, DATE_FORMAT(Planovany_datum,'%H:%i %d.%m.%Y') AS niceDate FROM Plan WHERE id_Pacient = ? AND id_navstevaordinacie is NULL ORDER BY Planovany_datum DESC ", $this->ID);
            $pl = new PlanPresenter($this->db);
            $tools = new ToolsPresenter($this->db);
            $date = $tools->fdate("today","ymd");
            $this->template->date = $date;

            $plans = $pl->getPlans("all",$this->ID);

            $this->template->plans = $plans;

            if($this->user->isInRole("admin"))
            {
                $this->template->visits = $this->db->query("SELECT *, DATE_FORMAT(Datum,'%H:%i %d.%m.%Y') AS niceDate FROM NavstevaOrdinacie WHERE id_Pacient = ? ORDER BY Datum DESC ", $this->ID);
            }
            else
            {
                $this->template->visits = $this->db->query("SELECT *, DATE_FORMAT(Datum,'%H:%i %d.%m.%Y') AS niceDate FROM NavstevaOrdinacie WHERE id_Pacient = ? AND deleted = 0 ORDER BY Datum DESC ", $this->ID);

            }
        $drugs = [];
        $drugs = $this->db->query("SELECT Liek.*, PredpisanyLiek.Davkovanie, PredpisanyLiek.PocetBaleni
FROM PredpisanyLiek, Liek
WHERE id_NavstevaOrdinacie IN
(SELECT ID FROM NavstevaOrdinacie WHERE id_Pacient = ?)
AND PredpisanyLiek.id_Liek = Liek.ID", $this->ID);
        $this->template->drugs = $drugs;


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

            $form[$key]->addRule(Form::MAX_LENGTH, 'Prilis vela znakov v '.$thead.'!', $this->theadsMaxLength[$key]);
        }





        if(isset($_GET['type']))
        {
            if($_GET['type'] == "rc")
            {
                $form["Rodne_cislo"]->setDefaultValue($_GET['data1']);
            }
            else if($_GET['type'] == "name")
            {
                $form["Meno"]->setDefaultValue($_GET['data1']);
                $form["Priezvisko"]->setDefaultValue($_GET['data2']);
            }

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


    public function createComponentAddPlan()
    {

        $services = [];
        $rows = $this->db->table("Vykon");
        foreach($rows as $row){
            $services[$row->ID] = $row->Nazov;
        }

        $rows = $this->db->table('Pacient');
        $patients = NULL;
        foreach($rows as $row) {
            $rc = substr($row->Rodne_cislo,0, -4)  . "/"   . substr($row->Rodne_cislo, -4);
            $patients[$row->ID] = "RC: ( "  .  $rc . " )  ~  " .  $row->Meno . " " . $row->Priezvisko;
        }


        $form = new UI\Form;

        //$form->addSelect("pacient", "Pacient:", $patients)
            //->setPrompt("Vyber pacienta")
            //->setRequired('Zvolte pacienta');
        $form->addMultiSelect("vykony", "Vykony:", $services)
            ->setRequired('Zvolte vykon');
        $form->addTbDateTimePicker('Datum', 'Datum:')
            ->setRequired();
        $form->addTextArea("Poznamky", "Poznamky:")
            ->addRule(Form::MAX_LENGTH, 'Prilis vela znakov', 255);


        $form->addSubmit('send', 'Ulozit');
        $form->onSuccess[] = array($this, 'AddPlanSucceeded');
        $form->setRenderer(new Bs3FormRenderer);
        return $form;
    }

    public function AddPlanSucceeded(UI\Form $form, $values){
        if ($values && $this->ID > 0) {
            //Vytvorit plan
            $idcko = $this->db->table("Plan")->insert(array(
                "ID" => "",
                "Planovany_datum" => $values->Datum,
                "Poznamky" => $values->Poznamky,
                "id_Pacient" => $this->ID
            ));


            //Povkladat naplanovane vykony
            foreach( $values->vykony as $val ){
                Debugger::barDump($val);
                $this->db->table("VykonMaPlan")->insert(array(
                    "ID" => "",
                    "id_Vykon" => $val,
                    "id_Plan" => $idcko
                ));
            }

            $this->flashMessage('Plan pridany.');
            $this->redirect('this');
        }
        $this->flashMessage('Chybicka se vloudila.');
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
                        $query1 = "UPDATE ".$table." SET deleted = 1 WHERE ID = ?";
                        $query2 = "UPDATE NavstevaOrdinacie SET deleted = 1 WHERE id_Pacient = ?";
                        $query3 = "DELETE FROM Plan WHERE id_Pacient = ?";
                    foreach ($valuesChecked as $val) {
                        $this->db->query($query1, $val);
                        $this->db->query($query2, $val);
                        $this->db->query($query3, $val);
                    }
                } else {
                    $this->flashMessage('Zle zadany formular');
                }
            };

            $form->setRenderer(new Bs3FormRenderer);

            return $form;
        });
    }

}
