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


class InvoicePresenter extends BasePresenter
{
    private $db;
    private $ID;
    private $theads = array("Datum_vystavenia" => "Dátum vystavenia",
        "Splatnost" => "Splatnost",
        //"id_Poistovna"  => "Poistovna",
        "Poistovna"  => "Poistovna",
        "NavstevaDatum"  => "Datum navstevy",
        "Pacient"  => "Pacient",
        "id_NavstevaOrdinacie" => "Navsteva",
    );
    /*private $theadsMaxLength = array("Rodne_cislo" => 15,
        "Meno" => 25,
        "Priezvisko"  => 25,
        "Poistovna" => 10,
        "Adresa" => 100,
        "Krvna_skupina" => 3,
        "Poznamky"  => 255
    );*/


    
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

        $this->template->title = "Faktúry";

        $invoices = $this->db->query("
            SELECT Faktura.*, Poistovna.Nazov as Poistovna,
            NavstevaOrdinacie.id_Pacient, NavstevaOrdinacie.Datum as NavstevaDatum,
            CONCAT_WS(' ', Pacient.Meno, Pacient.Priezvisko)  as Pacient
            FROM Faktura
            LEFT JOIN Poistovna ON Poistovna.ID = Faktura.id_Poistovna
            LEFT JOIN NavstevaOrdinacie ON NavstevaOrdinacie.ID = Faktura.id_NavstevaOrdinacie
            LEFT JOIN Pacient ON Pacient.ID = NavstevaOrdinacie.id_Pacient
        ");
        $this->template->UpravovanaTabulka = "Faktura";
        $this->template->rows = $invoices;
        $this->template->theads = $this->theads;

    }

    public function renderEdit()
    {
        $this->template->title = "Faktura";
    }
    
    public function renderShow()
    {
        $this->template->title = "Faktúra";

        $invoice = $this->db->query("SELECT Faktura.* FROM Faktura WHERE ID = ?", $this->ID);
        if($invoice){
            $this->template->invoice = $invoice;
        }
        else{
            $this->error('Dana Faktura nexistuje');
        }
    }

    //Components
    protected function createComponentInvoiceForm()
    {
        $invoice = $this->db->table('Faktura')->get($this->ID);

        $form = new UI\Form;
        $form->addTbDateTimePicker('Datum_vystavenia', 'Datum vystavenia:')
            ->setDefaultValue($invoice->Datum_vystavenia)
            ->setRequired();
        $form->addTbDateTimePicker('Datum_splatnosti', 'Datum_splatnosti:')
            ->setDefaultValue($invoice->Splatnost)
            ->setRequired();
        $form->addSubmit('send', 'Ulozit');
        $form->onSuccess[] = array($this, 'InvoiceFormSucceeded');
        $form->setRenderer(new Bs3FormRenderer);
        return $form;
    }


    // volá se po úspěšném odeslání formuláře
    public function InvoiceFormSucceeded(UI\Form $form, $values)
    {
        Debugger::barDump($values);
        Debugger::barDump($this->ID);

        if(isset($this->ID)) {
            $this->db->query("UPDATE Faktura SET Datum_vystavenia = ? Splatnost = ? WHERE ID = ?", $values->Datum_vystavenia, $values->Splatnost, $this->ID);
            $this->flashMessage('Pacient uspesne pridany.');
        }
        else
            $this->error('Nastala chyba');
    }

}
