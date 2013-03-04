<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Serves as the main Entrada administrative request controller file.
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 * 
 * @version $Id: admin.php 1171 2010-05-01 14:39:27Z ad29 $
*/

require_once dirname(__FILE__) . "/../test-helper.php";
require_once "/Users/zuikerd/pear/share/pear/PHPUnit.php";

class PermissionsTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobalsBlacklist = array("db");
	private $acls = array();
	/**
     * @dataProvider adminProvider
     */
    public function testAdminPermissions(EntradaUser $user, $resource, $action, $expected, Entrada_Acl $acl, $querystring) {
        $this->assertEquals($expected, $acl->isAllowed($user, $resource, $action, true));
    }
	/**
     * @dataProvider staffadminProvider
     */
    public function testStaffAdminPermissions(EntradaUser $user, $resource, $action, $expected, Entrada_Acl $acl, $querystring) {
        $this->assertEquals($expected, $acl->isAllowed($user, $resource, $action, true));
    }
	/**
     * @dataProvider pcoordProvider
     */
    public function testProgramCoordinatorPermissions(EntradaUser $user, $resource, $action, $expected, Entrada_Acl $acl, $querystring) {
        $this->assertEquals($expected, $acl->isAllowed($user, $resource, $action, true));
    }
	/**
     * @dataProvider directorProvider
     */
    public function testDirectorPermissions(EntradaUser $user, $resource, $action, $expected, Entrada_Acl $acl, $querystring) {
        $this->assertEquals($expected, $acl->isAllowed($user, $resource, $action, true));
    }

	public function adminProvider() {
		$queries = array(); $to_query = array();

		$queries[] = array(1, "course", 					"create", true); 	// admins can create courses unconditionally
		$queries[] = array(1, new CourseResource(1, 1), 	"create", true); 	// admins can update the first course
		$queries[] = array(1, "event", 						"create", true);	// admins can create events unconditionally
		$queries[] = array(1, new EventResource(1, 1, 1), 	"create", true);  	// admins can update existing events
		
		return $this->massage_queries($queries);
	}

	public function staffadminProvider() {
		$queries = array();
		
		$queries[] = array(2, "course", "create", false); 	//* staff admin can't create courses unconditionally
		$queries[] = array(2, new CourseResource(1, 1), 	"create", true); 	// (course, org) staff admin can create courses belonging to their organization
		$queries[] = array(2, new CourseResource(1, 2), 	"create", false); 	// staff admin can't create courses not belonging to their organization
		$queries[] = array(2, "event", "create", false);	//* (event, course, org) staff admin can't create events unconditionally
		$queries[] = array(2, new EventResource(1, 1, 1), 	"create", true);  	// staff admin can create events belongning to courses belonging to their organization
		$queries[] = array(2, new EventResource(1, 1, 2), 	"create", false);  	// staff admin can't create events not belongning to courses belonging to their organization
		
		return $this->massage_queries($queries);
	}
	
	public function pcoordProvider() {
		$queries = array();
				
		$queries[] = array(4, "course", 						"create", false); 	// program coordinators can't create courses
		$queries[] = array(4, "course", 						"update", false); 	// program coordinators can't update any course
		$queries[] = array(4, "course", 						"delete", false); 	// program coordinators can't update any course
		$queries[] = array(4, new CourseContentResource(1, 1), 	"update", true); 	// program coordinators can update course content for courses they coordinate
		$queries[] = array(4, new CourseResource(1, 1), 		"update", false); 	// program coordinators can't update courses even if they coordinate them
		$queries[] = array(4, new CourseResource(2, 1), 		"update", false); 	// program coordinators can't update courses they don't coordinate
		$queries[] = array(4, "event", 					"update", false); 	// program coordinators can't unconditionally create events
		$queries[] = array(4, new EventResource(1,1,1), "create", true); 	// program coordinators can create events belonging to courses they coordinate
		$queries[] = array(4, new EventResource(1,1,1), "update", true); 	//*(event, course, org) program coordinators can update events belonging to courses they coordinate
		$queries[] = array(4, new EventResource(1,1,1), "delete", true); 	//* program coordinators can delete events belonging to courses they coordinate
		$queries[] = array(4, new EventResource(3,2,1), "update", false); 	// program coordinators can't create events belonging to courses they don't coordinate
		$queries[] = array(4, new EventContentResource(1,1,1), "update", true); 	//*(event, course, org) program coordinators can create events belonging to courses they coordinate
		$queries[] = array(4, new EventContentResource(3,2,1), "update", false); 	// program coordinators can't create events belonging to courses they don't coordinate
			
		return $this->massage_queries($queries);
	}
	
	public function directorProvider() {
		$queries = array();
				
		$queries[] = array(10, "course", 						"create", false); 	// directors can't create courses
		$queries[] = array(10, "course", 						"update", false); 	// directors can't update any course
		$queries[] = array(10, "course", 						"delete", false); 	// directors can't update any course
		$queries[] = array(10, new CourseContentResource(1, 1), "update", true); 	// *(courseID, orgID) directors can update course content for courses they direct
		$queries[] = array(10, new CourseResource(1, 1), 		"update", false); 	// directors can't update courses even if they coordinate them
		$queries[] = array(10, new CourseResource(2, 1), 		"update", false); 	// directors can't update courses they don't coordinate

		$queries[] = array(10, "event",					"create", false); 			// directors can't unconditionally create events
		$queries[] = array(10, "event",					"update", false); 			// directors can't unconditionally update events
		$queries[] = array(10, new EventResource(1,1,1), "create", false); 			// directors can't create events belonging to courses they coordinate
		$queries[] = array(10, new EventResource(1,1,1), "update", false); 			// directors can't update events belonging to courses they coordinate
		$queries[] = array(10, new EventResource(1,1,1), "delete", false); 			// directors can't delete events belonging to courses they coordinate
		$queries[] = array(10, new EventResource(3,2,1), "update", false); 			// directors can't create events belonging to courses they don't coordinate
		$queries[] = array(10, new EventContentResource(1,1,1), "update", true); 	// directors can create events belonging to courses they coordinate
		$queries[] = array(10, new EventContentResource(3,2,1), "update", false); 	// directors can't create events belonging to courses they don't coordinate
		
		return $this->massage_queries($queries);
	}
	
	private function get_user_acl($user) {
		$id = $user->details["id"];
		if(!isset($this->acls[$id])) {
			$this->acls[$id] = new Entrada_Acl($user->details);	
		}
		return $this->acls[$id];
	}

	//Populate queries with proper User and ACL objects to make the query syntax less verbose
	//Each row in the 2D array gets passed as arguments to the test functions at the top of this file, so
	//each row is massaged into the proper format for those arguments:
	// testPermissions(EntradaUser $user, EntradaResource|string $resource, string $action, boolean $expected, Entrada_Acl $acl, string $querystring) {
	private function massage_queries(&$queries) {
		foreach($queries as &$query) {
			$query[0] = get_entrada_user($query[0]);
			$query[4] = $this->get_user_acl($query[0]);
			$query[5] = $query[0]->details["username"] . " trying to ".$query[2]." on ".
						(is_string($query[1]) ? $query[1] : get_class($query[1])).
						", supposed to be " . ($query[3]) ? 'true' : 'false';
		}
		return $queries;
	}
}