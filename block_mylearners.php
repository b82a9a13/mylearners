<?php
/**
 * @package   block_mylearners
 * @author      Robert Tyrone Cullen
 */
//use the lib class
use block_mylearners\lib;
class block_mylearners extends block_base{
    //Initialization function, defines title
    public function init(){
        $this->title = 'My Learners';
    }
    //Content for the block
    public function get_content(){
        //Add a empty string to the content text
        $this->content = new stdClass();
        $this->content->text = '';
        //Create lib variable from the lib class
        $lib = new lib();
        //Check if the user has the supervisor role
        if($lib->has_supervisor_role()){
            $data = $lib->get_plans_list();
            //Create HTML for the block
            $this->content->text .= "
                <link rel='stylesheet' href='./../blocks/mylearners/classes/css/block_mylearners.css'>
                <div class='text-center'>
            ";
            //Add a button for each learning plan
            foreach($data as $dat){
                $this->content->text .= "<button class='btn btn-primary mr-1 mb-1' onclick='bml_get_users($dat[1])'>$dat[0]</button>";
            }
            $this->content->text .= "
                </div>
                <h2 class='text-danger text-center' id='bml_mylearners_error'></h2>
                <div id='bml_mylearners_div' style='display:none;' class='table-section'></div>
                <script src='./../blocks/mylearners/amd/min/block_mylearners.min.js' defer></script>
            ";
            //Reduce the size of the HTML
            $this->content->text = str_replace("  ","",$this->content->text);
        }
    }
}