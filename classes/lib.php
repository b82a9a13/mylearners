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

    //Function is called to retrieve all the courses which the current user has users assigned to them in the course
    public function get_course_list(): array{
        global $DB;
        //Get a list of all the users with the current user assigned as supervisor for.
        $array = $this->supervisor_roles();
        if($array != []){
            $courses = [];
            //Retrieve the courses which the array of learners are enrolled in and add it to the array if it isn't already in it.
            foreach($array as $arr){
                $records = $DB->get_records_sql('SELECT ue.id as id, e.id as enrolid, e.courseid as courseid, c.fullname as fullname FROM {user_enrolments} ue 
                    LEFT JOIN {enrol} e ON e.id = ue.enrolid
                    LEFT JOIN {course} c ON c.id = e.courseid
                    WHERE ue.userid = ? AND e.roleid = 5',
                [$arr]);
                foreach($records as $record){
                    if(!in_array([$record->fullname, $record->courseid], $courses)){
                        array_push($courses, [$record->fullname, $record->courseid]);
                    }
                }
            }
            asort($courses);
            return $courses;
        }
        return [];
    }

    //Function is called to check if the a course with a specific id exists
    private function check_course_exists($courseid): bool{
        global $DB;
        return $DB->record_exists('course', [$DB->sql_compare_text('id') => $courseid]);
    }

    //Function is called to get all leanrers which have the current user as their learning plan superviosor and for a specific course
    public function get_users_list_data($courseid): array{
        global $DB;
        //Check if the course exists
        if($this->check_course_exists($courseid)){
            //Get all users which are the current user is a LPS for
            $array = $this->supervisor_roles();
            if($array != []){
                $users = [];
                //Get all users which are enrolled in the course provided
                foreach($array as $arr){
                    $record = $DB->get_record_sql('SELECT ue.id as id, u.firstname as firstname, u.lastname as lastname FROM {user_enrolments} ue
                        LEFT JOIN {enrol} e ON e.id = ue.enrolid
                        LEFT JOIN {user} u ON u.id = ue.userid
                        WHERE ue.userid = ? AND e.courseid = ?',
                    [$arr, $courseid]);
                    if(count($record) > 0){
                        array_push($users, [$record->firstname.' '.$record->lastname, $arr]);
                    }
                }
                asort($users);
                if($users != []){
                    $array = [];
                    //Get the total completion percentage and number of competencies awaiting review for each user and push an array to $array
                    foreach($users as $user){
                        //Get the total complete and the total number of modules in a specific course
                        $complete = $DB->get_record_sql('SELECT count(*) as total FROM {course_modules} c
                            INNER JOIN {course_modules_completion} cm ON cm.coursemoduleid = c.id
                            WHERE c.course = ? AND c.completion != 0 AND cm.userid = ? AND cm.completionstate = 1',
                        [$courseid, $user[1]])->total;
                        $total = $DB->get_record_sql('SELECT count(*) as total FROM {course_modules} WHERE course = ? and completion != 0',[$courseid])->total;
                        $awaitReview = $DB->get_record_sql('SELECT count(*) as total FROM {competency_usercomp} WHERE userid = ? AND status = 1',[$user[1]])->total;
                        if($total - $complete == 0){
                            array_push($array, [$user[0], $user[1], 100, $awaitReview]);
                        } else {
                            $percentage = ($complete / $total) * 100;
                            array_push($array, [$user[0], $user[1], $percentage, $awaitReview]);
                        }
                    }
                    return $array;
                }
            }
        }
        return [];
    }
}