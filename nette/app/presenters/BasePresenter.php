<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Tracy\Debugger;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    public function startup()
    {
        parent::startup();

        if( !$this->user->isLoggedIn() )
        {
            if($this->name == 'Sign'){return;}
            $this->redirect('Sign:in');
        }
        $this->template->username = $this->user->getIdentity()->meno . " " . $this->user->getIdentity()->priezvisko;
        $this->template->userinfo = $this->user->getIdentity();

        $mm="";//default menu wrapper state
        if (isset($_SESSION['menu_wrapper'])){$mm=$_SESSION['menu_wrapper'];}
        else{$_SESSION['menu_wrapper'] = $mm;}
        $this->template->menu_wrapper = $mm;


        Debugger::barDump("GOTO: ". $this->presenter->name    . ">>" . $this->action );
        //permissions
        if($this->user->isAllowed($this->presenter->name,$this->action))
        {
            Debugger::barDump("ALLOWED: YES!" );

        }
        else
        {
            Debugger::barDump("ALLOWED: NO!" );
            $this->flashMessage("Nemáte dostatočné práva na vykonanie tejto akcie!");
            //Debugger::barDump($this->presenter->getParent());
            $this->redirect("Homepage:");
        }


    }


}
