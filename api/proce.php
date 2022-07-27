<?php
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
require_once __DIR__.'/../class/Db.class.php';
$DB=new Db();
require_once __DIR__.'/../class/Todo.class.php';
$todo=new Todo($DB);

if(isset($_POST['doWhat'])){
	$back='';
	switch ($_POST['doWhat']) {
		case 'ini': $back=json_encode($todo->initiate());
		break;
        case 'add': $back=json_encode($todo->add($_POST['newInList']));
		break;
        case 'update': $back=json_encode($todo->update($_POST['newInList']));
		break;
        case 'delete': $back=json_encode($todo->delete($_POST['id']));
		break;
		default:
	}
	if($back!=''){
		echo $back;
	}
}




