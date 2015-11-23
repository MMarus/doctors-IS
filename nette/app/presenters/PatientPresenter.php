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
            $patient = $this->db->table('Pacient')->get($id);
            
            if (!$patient) {
                $this->error('Stránka nebyla nalezena');
            }
            $this->template->patient = $patient;
            
            $plans = $this->db->query("SELECT *, DATE_FORMAT(Planovany_datum,'%H:%i %d.%m.%Y') AS niceDate FROM Plan WHERE id_Pacient = ? ORDER BY Planovany_datum DESC ", $id);
            
            $this->template->plans = $plans;
            
            $this->template->visits = $this->db->query("SELECT *, DATE_FORMAT(Datum,'%H:%i %d.%m.%Y') AS niceDate FROM NavstevaOrdinacie WHERE id_Pacient = ? ORDER BY Datum DESC ", $id);
            
    }

}
