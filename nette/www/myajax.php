<?php

$tmp = "";
if(!isset($_POST['action']))return;
switch($_POST['action'])
{
    case "save":
        session_start();
        $_SESSION['menu_wrapper'] = $_POST['data'];
        $tmp.= "Saved OK: " . $_POST['data'];
        echo $tmp;

        break;

    case "load":
        session_start();
        if(!isset($_SESSION['menu_wrapper']))
        {
            echo $tmp = "";
            return;
        }
        else
        {
            $tmp.= $_SESSION['menu_wrapper'];
        }

        echo $tmp;

        break;

    case "exit":
        session_start();
        session_destroy();
        echo "STOP!";
        break;

    default :

        break;
}

?>
