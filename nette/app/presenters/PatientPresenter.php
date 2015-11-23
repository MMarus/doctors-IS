<?php

namespace App\Presenters;

use Nette;
use App\Model;


class PatientPresenter extends BasePresenter
{
    private $db;
    
    public function __construct(Nette\Database\Context $database)
    {
        $this->db = $database;
    }
    
    public function renderDefault()
    {
            $this->template->anyVariable = 'any value';
            //$this->template->patient = $this->db->table('Pacient')->get($patientId);
    }
    
    public function renderShow($id)
    {
            $this->template->id = $id;
            $this->template->patient = $this->db->table('Pacient')->get($id);
    }

}
