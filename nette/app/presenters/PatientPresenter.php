<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;


class PatientPresenter extends BasePresenter
{
    private $db;
    
    public function __construct(Nette\Database\Context $database)
    {
        $this->db = $database;
    }
    
    public function renderDefault()
    {
            $patients = $this->db->table('Pacient');
            if (!$patients) {
                $this->error('Stránka nebyla nalezena');
            }
            $this->template->patients = $patients;
            
            //$this->template->patient = $this->db->table('Pacient')->get($patientId);
    }

    public function renderEdit($id)
    {
        $patient = $this->db->table('Pacient')->get($id);
        if (!$patient) {
            $patient = [];
        }
        $this->template->patient = $patient;

    }
    
    public function renderShow($id)
    {
            $this->template->id = $id;
            $patient = $this->db->table('Pacient')->get($id);
            
            if (!$patient) {
                $this->error('Stránka nebyla nalezena');
            }
            $this->template->patient = $patient;
            
            $plans = $this->db->query("SELECT *, DATE_FORMAT(Planovany_datum,'%H:%i %d.%m.%Y') AS niceDate FROM Plan WHERE id_Pacient = ? ORDER BY Planovany_datum DESC ", $id);
            
            $this->template->plans = $plans;
            
            $this->template->visits = $this->db->query("SELECT *, DATE_FORMAT(Datum,'%H:%i %d.%m.%Y') AS niceDate FROM NavstevaOrdinacie WHERE id_Pacient = ? ORDER BY Datum DESC ", $id);
            
    }

    protected function createComponentRegistrationForm()
    {

        $form = new UI\Form;
        $form->addText('search', 'Vykon')->setRequired('Zadejte prosím jméno');
        $form->addText('Priezvisko', 'Priezvisko*')->setRequired('Zadejte prosím jméno');
        $form->addText('Rodne', 'Rodne cislo*')->setRequired('Zadejte prosím jméno');
        $form->addText('Adresa', 'Adresa*')->setRequired('Zadejte prosím jméno');
        $form->addText('Krvna', 'Krvna skupina*')->setRequired('Zadejte prosím jméno');
        $form->addTextArea('Poznamky', 'Poznamky*')->setRequired('Zadejte prosím jméno');
        $form->addText('Poistovna', 'Poistovna*')->setRequired('Zadejte prosím jméno');
        $form->addPassword('password', 'Heslo*')->setRequired('Zadejte prosím Heslo');
        //$form['country']->setDefaultValue('sk');


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
        $this->redirect('Homepage:');
    }

}
