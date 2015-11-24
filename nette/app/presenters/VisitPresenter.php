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
		$this->template->id = $id;
	}

    protected function createComponentRegistrationForm()
    {
        //$drugs = $this->db->table('Liek')->order('Nazov DESC')->;
        $form = new UI\Form;
        $form->addText('name', 'Jméno*')->setRequired('Zadejte prosím jméno');
        $form->addPassword('password', 'Heslo*')->setRequired('Zadejte prosím Heslo');
        $form->addSelect('drug', 'Liek:', $drugs)->setPrompt('Zvolte liek');
        $form->addSubmit('login', 'Registrovat');
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
