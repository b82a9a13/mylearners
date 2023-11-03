<?php
require_once(__DIR__.'/../../../../config.php');
//Check the user is logged in
require_login();
//use the lib class
use block_mylearners\lib;
//Create the lib varaible from the class
$lib = new lib();
//Return text is used for the return
$returnText = new stdClass();
//Variable used in the get_string function
$p = 'block_mylearners';

if(!isset($_POST['id'])){
    //No ID provided
    $returnText->error = get_string('no_idp', $p);
} else {
    $id = $_POST['id'];
    if(!preg_match("/^[0-9]*$/", $id) || empty($id)){
        //Invalid ID provided
        $returnText->error = get_string('invalid_idp', $p);
    } else {
        if(!$lib->has_supervisor_role()){
            //User does not have the supervisor role
            $returnText->error = get_string('you_dhrr', $p);
        } else {
            //Has the supervisor role
            //Get data for the current user and course id provided
            $data = $lib->get_users_list_data($id);
            //only set a return in the returnText class if the array is not empty
            if($data != []){
                $returnText->return = $data;
            }
        }
    }
}
//Output returnText variable
echo(json_encode($returnText));