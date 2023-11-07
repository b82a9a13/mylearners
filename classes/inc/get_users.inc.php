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
            $data = $lib->get_user_list_data($id);
            if($data != []){
                //Create HTML to send as a response
                $returnText->return = "<h2 class='text-center'>".$data[0][0]."</h2>
                    <table class='table table-bordered table-striped table-hover'>
                        <thead>
                            <tr>
                                <th>".get_string('learner', $p)."</th>
                                <th>".get_string('progress', $p)."</th>
                                <th>".get_string('total_car', $p)."</th>
                            </tr>
                        </thead>
                        <tbody>
                ";
                //Script variable is used to store HTML that is used to render the chart
                $script = '';
                //Add a record in the table for each user
                foreach($data[1] as $dat){
                    $returnText->return .= "
                            <tr>
                                <td><a href='./../user/view.php?id=$dat[1]'>$dat[0]</a></td>
                                <td class='bml-progress-td'><canvas class='bml-chart' id='bml_$dat[1]_$dat[4]'></canvas>$dat[3]/".$data[0][1]."</td>
                                <td>$dat[2] <a href='./../admin/tool/lp/plan.php?id=$dat[4]'>&rarr;</a></td>
                            </tr>
                    ";
                    $script .= "bml_render_chart('bml_$dat[1]_$dat[4]', ".$data[0][1].", $dat[3], $dat[2]);";
                }
                $returnText->script = $script;
                $returnText->return .= "</tbody></table>";
                $returnText->return = str_replace("  ","",$returnText->return);
            }
        }
    }
}
//Output returnText variable
echo(json_encode($returnText));