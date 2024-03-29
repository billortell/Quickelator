<?php
session_start(); // added for storing historical component

error_reporting(2);
// only cuz i wanted to use prefilling of the $_POST within the form itself... to save posted input.

define("CLASS_DIR","helpers");
define("VIEWS_DIR","views");
define("SITE_TITLE","Quickelator");

/****
 * yes, yes, i know the framework would've autoloaded my classes/helper/library for
 * CalcFactory, but in lieu of using one, i just did this
 * @param object $class_name
 **/
function __autoload($class_name) {
	$class_file=".".DIRECTORY_SEPARATOR.CLASS_DIR.DIRECTORY_SEPARATOR.$class_name . '.class.php';
	require_once($class_file);
}

/**
 * would later be driven out dynamically from separation of all
 * the CalcXXXXXX classes available.
 */
$operations = array(
	"Add"=>"Plus [ + ]",
	"Subtract"=>"Subtract [ - ]",
	"Multiply"=>"Multiplied by [ * ]",
	"Divide"=>"Divided by [ / ]"
);


/***
 * would normally do this within the controller class
 */
if ( $_SERVER['REQUEST_METHOD']=='POST' )
{
	$c = new CalcFactory();
	$c->setFirst($_POST['first']);
	$c->setOperator( $_POST['operation']);
	$c->setSecond($_POST['second']);
	$answers = $c->getAnswer();

}


/***
 * let's have some fun and show a history of calculations made
 */
$h = new CalcHistory();
$history = $h->getHistory();


/**
 * NOTE: these would be passed as 'allowed' parameters to a 'view'/layout - from
 * within the Model/Controller class of the framework chosen
 **/
$first = array_key_exists('first', $_POST) ? $_POST['first'] : '';
$second = array_key_exists('second', $_POST) ? $_POST['second'] : '';

// in lieu of using a framework view/render, just keep it all on one page
include(VIEWS_DIR.DIRECTORY_SEPARATOR."calculator.html"); // this would normally be in a some kinda layout/view folder.


