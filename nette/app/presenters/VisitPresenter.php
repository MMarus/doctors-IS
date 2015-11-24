<?php

namespace App\Presenters;


use Nette;
use App\Model;
use Test\Bs3FormRenderer;
use Nette\Application\UI;


class VisitPresenter extends BasePresenter
{
    private $db;

    public function __construct(Nette\Database\Context $database)
    {
        $this->db = $database;
    }

    public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

	public function renderEdit($id)
	{
        $this->template->services = $this->db->query("SELECT Vykon.* FROM PocasNavstevy, Vykon WHERE PocasNavstevy.id_NavstevaOrdinacie = ? AND PocasNavstevy.id_Vykon = Vykon.ID", $id);
	}

    protected function createComponentRegistrationForm()
    {

        $allServices = $this->db->table('Vykon');
        $serviceInputs = [];
        foreach($allServices as $service) {
            $serviceInputs[$service->ID] = $service->Nazov;
        }

        $form = new UI\Form;
        $form->addText('search', 'Vykon*')->setRequired('Zadejte prosím jméno');
        $form->addSelect('hladaj', 'Vykon', $serviceInputs);


        //$form['country']->setDefaultValue('sk');


        $form->addSubmit('send', '');
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
