<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 * 
 *
 * 
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Jonathan Fingland <jonathan.fingland@queensu.ca>
 * @copyright Copyright 2011 Queen's University. All Rights Reserved.
*/

/**
 * Simple Collection for managing a user's photos (not the photos of multiple users)
 * 
 * @author Jonathan Fingland
 *
 */
class UserPhotos extends Collection {
	
	/**
	 * Returns a Collection of User photos belonging to the provided user_id
	 * @param $user_id
	 * @return UserPhotos
	 */
	public static function get($user_id) {
		$photos = array();
		$official_photo = UserPhoto::get($user_id, UserPhoto::OFFICIAL);
		$uploaded_photo = UserPhoto::get($user_id, UserPhoto::UPLOADED);
		if ($official_photo) {
			$photos[] = $official_photo;
		}
		if ($uploaded_photo) {
			$photos[] = $uploaded_photo;
		}
		return new self($photos);
	}
	
} 
