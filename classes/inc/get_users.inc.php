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
                //Create HTML to send as a response
                $returnText->return = "<h2 class='text-center'>".$lib->get_course_fullname($id)."</h2>
                    <table class='table table-bordered table-striped table-hover'>
                        <thead>
                            <tr>
                                <th>".get_string('learner', $p)."</th>
                                <th>".get_string('course_p', $p)."</th>
                                <th>".get_string('competency_ar', $p)."</th>
                            </tr>
                        </thead>
                        <tbody>
                ";
                foreach($data as $dat){
                    $returnText->return .= "
                            <tr>
                                <td><a href='./../user/view.php?id=$dat[1]&course=$id'>$dat[0]</a></td>
                                <td>$dat[2]%</td>
                                <td>$dat[3]</td>
                            </tr>
                    ";
                }
                $returnText->return .= "</tbody></table>";
                //Remove blank spaces from the return to reduce the size of the response
                $returnText->return = str_replace("  ","",$returnText->return);
            } else {
                $returnText->return = "<h2 class='text-danger text-center'>".get_string('no_la', $p)."</h2>";
            }
        }
    }
}
//Output returnText variable
echo(json_encode($returnText));