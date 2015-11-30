<?php
/**
 * Created by PhpStorm.
 * User: marek
 * Date: 11/28/15
 * Time: 10:13 PM
 */

use Nette\Application\UI\Control;

class PollControl extends Control
{

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/poll.latte');
        // vložíme do šablony nějaké parametry
        $template->param = $value;
        // a vykreslíme ji
        $template->render();
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

            $results = $this->db->table($tableFrom);
            $options = NULL;
            foreach ($results as $result) {
                $options[$result->ID] = $result->Nazov;
            }
            Debugger::barDump($options);
            $form = new UI\Form;
            if ($options) {
                $form->addSelect($tableTo, '', $options)->setPrompt('Vybrat');
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
        var_dump($values);

        if ($values[$tableTo] && $this->ID > 0) {
            $this->db->query('INSERT INTO '.$tableTo, array(
                'ID' => '',
                'id_'.$this->presenterName => $this->ID,
                'id_'.$tableFrom => $values[$tableTo]));
        } else
            $this->flashMessage('Zle zadany formular');

    }
}