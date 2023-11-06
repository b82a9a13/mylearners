<?php 
/**
 * @package   block_mylearners
 * @author    Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace block_mylearners;
use stdClass;

class lib{
    //Function is called to get the current userid
    private function get_userid(): int{
        global $USER;
        return $USER->id;
    }

    //Function is called to check if the learning plan supervisor (user) role exists
    private function check_role_exists(): bool{
        global $DB;
        return $DB->record_exists('role', [$DB->sql_compare_text('shortname') => "learningplansupervisoruser"]);
    }

    //Function is called to get all learning plan supervisor (user) role assignments and return userids
    private function supervisor_roles(): array{
        global $DB;
        //Check if the LPS role exists
        if(!$this->check_role_exists()){
            return [];
        } else {
            //Get all records which the current user is assigned the LPS role
            $userid = $this->get_userid();
            $records = $DB->get_records_sql('SELECT ra.id as id, ra.contextid as contextid, u.id as userid FROM {role_assignments} ra
                LEFT JOIN {role} r ON r.id = ra.roleid
                LEFT JOIN {context} c ON c.id = ra.contextid
                LEFT JOIN {user} u ON u.id = c.instanceid
                WHERE r.shortname = "learningplansupervisoruser" AND ra.userid = ?',
            [$userid]);
            $array = [];
            foreach($records as $record){
                array_push($array, $record->userid);
            }
            return $array;
        }
    }

    //Function is called to check if the current user has the supervisor role (user)
    public function has_supervisor_role(): bool{
        return ($this->supervisor_roles() != []) ? True : False;
    }

    //Function is called to get all learning plans that the current user is a LPS for
    public function get_plans_list(): array{
        global $DB;
        //Get a list of the users which the current user is a LPS for
        $array = $this->supervisor_roles();
        if($array != []){
            //Create a new list of all the unique competency templates used.
            $list = [];
            foreach($array as $arr){
                //Get the competency plans and the template used for the user
                $records = $DB->get_records_sql('SELECT cp.id as id, cp.name as name, ct.id as templateid FROM {competency_plan} cp
                    LEFT JOIN {competency_template} ct ON ct.id = cp.templateid
                    WHERE cp.userid = ?',
                [$arr]);
                foreach($records as $record){
                    if(!in_array([$record->name, $record->templateid], $list)){
                        array_push($list, [$record->name, $record->templateid]);
                    }
                }
            }
            return $list;
        }
        return [];
    }

    //Function is called to check if a template with a sepcific id exists
    private function check_template_exists($id): bool{
        global $DB;
        return $DB->record_exists('competency_template', [$DB->sql_compare_text('id') => $id]);
    }

    //Function is called to get a users full name from a sepcific id
    private function get_user_fullname($id): string{
        global $DB;
        $record = $DB->get_record_sql('SELECT id, firstname, lastname FROM {user} WHERE id = ?',[$id]);
        return $record->firstname.' '.$record->lastname;
    }

    //Function is called to get all learners which have the current user as their LPS for a specific template id
    public function get_user_list_data($id): array{
        global $DB;
        //Check if the competency template exists
        if($this->check_template_exists($id)){
            //set $array to an array of all the users the current user is LPS for
            $array = $this->supervisor_roles();
            if($array != []){
                //Get all competencies related to a template
                $records = $DB->get_records_sql('SELECT id, competencyid FROM {competency_templatecomp} WHERE templateid = ?',[$id]);
                if(count($records) > 0){
                    //Put the competencies into an array
                    $comptencies = [];
                    foreach($records as $record){
                        array_push($comptencies, $record->competencyid);
                    }
                    //Get the name of the template and assign it to the variable $name
                    $name = $DB->get_record_sql('SELECT id, shortname FROM {competency_template} WHERE id = ?',[$id])->shortname;
                    //Create $data array which has [0] as the name and total number of competencies for the template
                    $data = [[$name, count($comptencies)], []];
                    //Loop through all users the current user is LPS for
                    foreach($array as $arr){
                        //Check if the user has a plan with the template id
                        $planid = $DB->get_record_sql('SELECT id FROM {competency_plan} WHERE userid = ? and templateid = ?',[$arr, $id])->id;
                        if($planid != null){
                            //Get all competencies for the the current user
                            $comps = $DB->get_records_sql('SELECT id, userid, competencyid, status, proficiency FROM {competency_usercomp} WHERE userid = ?',[$arr]);
                            //Put the total number of competencies awaiting review and the number of complete competencies into variables
                            $awaitReview = 0;
                            $complete = 0;
                            foreach($comps as $comp){
                                if(in_array($comp->competencyid, $comptencies)){
                                    if($comp->status == 1){
                                        $awaitReview++;
                                    } elseif($comp->status == 0 && $comp->proficiency == 1){
                                        $complete++;
                                    }
                                }
                            }
                            //Add the user data to the $data[1] array
                            array_push($data[1], [$this->get_user_fullname($arr), $arr, $awaitReview, $complete, $planid]);
                        }
                    }
                    //Sort the user data by name
                    asort($data[1]);
                    return $data;
                }
            }
        }
        return [];
    }
}