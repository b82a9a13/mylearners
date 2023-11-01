<?php
/**
 * @package   block_mylearners
 * @author      Robert Tyrone Cullen
 */
use block_mylearners\lib;
class block_mylearners extends block_base{
    //Initialization function, defines title
    public function init(){
        $this->title = 'My Learners';
    }
    //Content for the block
    public function get_content(){
        $this->content = new stdClass();
        $lib = new lib();
        $this->content->text = '';
    }
}