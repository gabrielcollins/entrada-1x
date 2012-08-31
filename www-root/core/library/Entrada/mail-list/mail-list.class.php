<?php

abstract class MailingListBase
{

	var $users 			= array();

	var $list_name		= "";

	var $type			= "";

	var $community_id	= 0;

    public function MailingListBase($community_id = 0, $list_type = "inactive") {
    	global $db;
		if ($community_id) {
			$query = "SELECT * FROM `community_mailing_lists` WHERE `community_id` = ".$db->qstr($community_id);
			$result = $db->GetRow($query);

			if ($result) {
				$this->list_name 	= $result["list_name"];
				$this->type 		= $result["list_type"];
				$this->community_id = $result["community_id"];
			} elseif (($list_type == "announcements") || ($list_type == "discussion") || ($list_type == "inactive")) {

				$community_query = "SELECT `community_shortname` FROM `communities` WHERE `community_id` = ".$db->qstr($community_id);

				$list_name = $db->GetOne($community_query);

				if ($list_name) {
					$list_name .= "-community";
					$this->community_id = $community_id;
					$query = "	INSERT INTO `community_mailing_lists`
								SET `list_name` = ".$db->qstr($list_name).",
									`community_id` = ".$db->qstr($community_id).",
									`list_type` = ".$db->qstr($list_type);
					if ($db->Execute($query)) {
						$this->list_name	= $list_name;
						$this->type			= $list_type;
					}
				}
			}

			$query = "	SELECT *
						FROM `community_mailing_list_members`
						WHERE `community_id` = ".$db->qstr($community_id);
			$users = $db->GetAll($query);

			if ($users) {
				foreach ($users as $user) {
					$this->users[$user["proxy_id"]] = Array(
											"proxy_id" => $user["proxy_id"],
											"email" => $user["email"],
											"owner" => (((int)$user["list_administrator"]) == 1 ? true : false),
											"member_active" => $user["member_active"]
										  );
				}
			} else {

				$query = "SELECT b.`email`, b.`id`, a.`member_acl`
						  FROM `community_members` AS a
						  LEFT JOIN `".AUTH_DATABASE."`.`user_data` AS b
						  ON a.`proxy_id` = b.`id`
						  WHERE a.`community_id` = ".$db->qstr($community_id)."
						  AND a.`member_active` = 1";

				$users = $db->GetAll($query);
				$this->users = Array();
				foreach ($users as $user) {
					$this->users[$user["proxy_id"]] = Array(
											"proxy_id" => $user["id"],
											"email" => $user["email"],
											"owner" => (((int)$user["member_acl"]) ? true : false),
											"member_active" => 0
										  );
					$this->base_add_member($user["id"], $user["email"], 0, $user["member_acl"]);
				}
			}
    	}
    }

    public function base_mode_change($type) {
    	global $db;
    	if ($type == "inactive") {
    		$this->type = $type;
    		$query = "	UPDATE `community_mailing_list_members`
    					SET `member_active` = '0'
    					WHERE `community_id` = ".$db->qstr($this->community_id);
    					$db->Execute($query);
    	}
    	if ($type == "inactive" || $type == "discussion" || $type == "announcements") {
    		$query = "	UPDATE `community_mailing_lists`
    					SET `list_type` = ".$db->qstr($type)."
    					WHERE `community_id` = ".$db->qstr($this->community_id);
    		return (bool)$db->Execute($query);
    	}
    	return false;
    }

	public function base_remove_member($proxy_id) {
		global $db;

		$email = $this->users[$proxy_id]["email"];

		$query = "	DELETE FROM `community_mailing_list_members`
					WHERE `community_id` = ".$db->qstr($this->community_id)."
					AND `email` = ".$db->qstr($email);
		$result = $db->Execute($query);
		return $result;
	}

	public function base_add_member($proxy_id, $email, $member_active = 0, $is_admin = 0) {
		global $db;
		$result = $db->Execute("INSERT INTO `community_mailing_list_members`
								SET `proxy_id` = ".$db->qstr($proxy_id).",
								`email` = ".$db->qstr($email).",
								`member_active` = ".$db->qstr($member_active).",
								`list_administrator` = ".$db->qstr($is_admin).",
								`community_id` = ".$db->qstr($this->community_id));
		return $result;
	}

	public function base_edit_member($proxy_id, $is_admin = 0, $member_active = 0) {
		global $db;
		$result = $db->Execute("UPDATE `community_mailing_list_members`
									SET ".( $is_admin != 0 ? ( $member_active != 0 ? "`list_administrator` = ".$db->qstr($is_admin).", " : "`list_administrator` = ".$db->qstr($is_admin)) : "")."
										".( $member_active != 0 ? "`member_active` = ".$db->qstr($member_active) : "")."
									WHERE `proxy_id` = ".$db->qstr($proxy_id)."
									AND `community_id` = ".$db->qstr($this->community_id));
		return $result;
	}
}

class GoogleMailingList extends MailingListBase
{

	var $service = null;

	public function GoogleMailingList($community_id, $type = "inactive") {
    	global $db, $GOOGLE_APPS;
		$query = "SELECT `cmlist_id` FROM `community_mailing_lists` WHERE `community_id` = ".$db->qstr($community_id);
		$result = $db->GetOne($query);
		
		$this->MailingListBase($community_id, $type);
		$client = Zend_Gdata_ClientLogin::getHttpClient($GOOGLE_APPS["admin_username"], $GOOGLE_APPS["admin_password"], Zend_Gdata_Gapps::AUTH_SERVICE_NAME);
		$service = new Zend_Gdata_Gapps($client, $GOOGLE_APPS["domain"]);
		$this->service = $service;
		
		if (!$result && $this->type != "inactive") {

			try{

				$entry = new DomDocument("1.0", "UTF-8");
				$root = $entry->createElement("atom:entry");
				$root = $entry->appendChild($root);
				$root->setAttribute("xmlns:atom", "http://www.w3.org/2005/Atom");
				$root->setAttribute("xmlns:apps", "http://schemas.google.com/apps/2006");
				$root->setAttribute("xmlns:gd", "http://schemas.google.com/g/2005");
				$data = $entry->createElement("apps:property");
				$data = $root->appendChild($data);
				$data->setAttribute("name", "groupId");
				$data->setAttribute("value", $this->list_name);
				$data = $entry->createElement("apps:property");
				$data = $root->appendChild($data);
				$data->setAttribute("name", "groupName");
				$data->setAttribute("value", $this->list_name);
				$data = $entry->createElement("apps:property");
				$data = $root->appendChild($data);
				$data->setAttribute("name", "description");
				$data->setAttribute("value", "");
				$data = $entry->createElement("apps:property");
				$data = $root->appendChild($data);
				$data->setAttribute("name", "emailPermission");
				$data->setAttribute("value", ($this->type == "announcements" ? "Owner" : "Member"));
				$address = "https://apps-apis.google.com/a/feeds/group/2.0/".$GOOGLE_APPS["domain"];

				$service->post($entry->saveXML(), $address);

			} catch (Zend_Gdata_Gapps_ServiceException $e) {
				if (!$e->hasError(Zend_Gdata_Gapps_Error::ENTITY_DOES_NOT_EXIST)) {
					return false;
				}
			}
		}
	}


	public function extended_add_member($email, $is_owner = false) {
		global $GOOGLE_APPS;
		$service = $this->service;

		$service->addRecipientToEmailList($email, $this->list_name);

		if ($is_owner) {
			$address = "https://apps-apis.google.com/a/feeds/group/2.0/".$GOOGLE_APPS["domain"]."/".$this->list_name."/owner";
		    $entry = new DomDocument("1.0", "UTF-8");
			$root = $entry->createElement("atom:entry");
			$root->setAttribute("xmlns:atom", "http://www.w3.org/2005/Atom");
			$root->setAttribute("xmlns:apps", "http://schemas.google.com/apps/2006");
			$root->setAttribute("xmlns:gd", "http://schemas.google.com/g/2005");
			$root = $entry->appendChild($root);
			$data = $entry->createElement("apps:property");
			$data = $root->appendChild($data);
			$data->setAttribute("name", "email");
			$data->setAttribute("value", $email);
			$entry = $service->post($entry->saveXML(), $address);
		}
		return true;
	}

	public function extended_remove_member($proxy_id) {
		global $GOOGLE_APPS;
		$service = $this->service;
		$email = $this->users[$proxy_id]["email"];
		try {
			$entry = $service->removeRecipientFromEmailList($email, $this->list_name);
			return $this->base_remove_member($proxy_id);
		} catch (Exception $e) {
			return false;
		}
	}

	public function extended_edit_member($proxy_id, $is_admin) {
		global $GOOGLE_APPS;
		$service = $this->service;
		$email = $this->users[$proxy_id]["email"];
		try {
			if (!$is_admin) {
				$address = "https://apps-apis.google.com/a/feeds/group/2.0/".$GOOGLE_APPS["domain"]."/".$this->list_name."/owner/".$email;
				$entry = $service->delete($address);
				return $this->base_edit_member($proxy_id, 0);
			} elseif ($is_admin) {
				$address = "https://apps-apis.google.com/a/feeds/group/2.0/".$GOOGLE_APPS["domain"]."/".$this->list_name."/owner";
			    $entry = new DomDocument("1.0", "UTF-8");
				$root = $entry->createElement("atom:entry");
				$root->setAttribute("xmlns:atom", "http://www.w3.org/2005/Atom");
				$root->setAttribute("xmlns:apps", "http://schemas.google.com/apps/2006");
				$root->setAttribute("xmlns:gd", "http://schemas.google.com/g/2005");
				$root = $entry->appendChild($root);
				$data = $entry->createElement("apps:property");
				$data = $root->appendChild($data);
				$data->setAttribute("name", "email");
				$data->setAttribute("value", $email);
				$entry = $service->post($entry->saveXML(), $address);
				return $this->base_edit_member($proxy_id, 1);
			}
		} catch (Exception $e) {
			return false;
		}
	}

    public function extended_mode_change($type) {
    	global $GOOGLE_APPS;

		$service = $this->service;
    	if ($type == "announcements" || $type == "discussion") {
			try{

				$entry = new DomDocument("1.0", "UTF-8");
				$root = $entry->createElement("atom:entry");
				$root = $entry->appendChild($root);
				$root->setAttribute("xmlns:atom", "http://www.w3.org/2005/Atom");
				$root->setAttribute("xmlns:apps", "http://schemas.google.com/apps/2006");
				$root->setAttribute("xmlns:gd", "http://schemas.google.com/g/2005");
				$data = $entry->createElement("apps:property");
				$data = $root->appendChild($data);
				$data->setAttribute("name", "groupId");
				$data->setAttribute("value", $this->list_name);
				$data = $entry->createElement("apps:property");
				$data = $root->appendChild($data);
				$data->setAttribute("name", "groupName");
				$data->setAttribute("value", $this->list_name);
				$data = $entry->createElement("apps:property");
				$data = $root->appendChild($data);
				$data->setAttribute("name", "description");
				$data->setAttribute("value", "");
				$data = $entry->createElement("apps:property");
				$data = $root->appendChild($data);
				$data->setAttribute("name", "emailPermission");
				if ($type == "discussion") {
					$data->setAttribute("value","Member");
		    	} else {
					$data->setAttribute("value","Owner");
		    	}
		    	if ($this->type != "inactive") {
					$address = "https://apps-apis.google.com/a/feeds/group/2.0/".$GOOGLE_APPS["domain"]."/".$this->list_name;
					$service->put($entry->saveXML(), $address);
		    	} else {
					$address = "https://apps-apis.google.com/a/feeds/group/2.0/".$GOOGLE_APPS["domain"];
					$service->post($entry->saveXML(), $address);
		    	}
			} catch (Exception $e) {
				return false;
			}
    	} elseif ($type == "inactive") {
    		try {
    			if ($this->type != "inactive") {
	    			$service->deleteEmailList($this->list_name);
    			}
    		} catch (Exception $e) {
				return false;
			}
    	}
    	return true;
    }
}

class MailingList extends GoogleMailingList
{

	public function MailingList($community_id, $type = "inactive") {
    	$this->GoogleMailingList($community_id, $type);
	}


	public function activate_member($proxy_id, $is_owner = false) {
		global $db;
		$email = $db->GetOne("SELECT `email` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($proxy_id));
		if ($email) {
			if ($this->extended_add_member($email, $is_owner)) {
				return $this->base_edit_member($proxy_id, $is_owner, 1);
			}
		}
		return false;
	}

	public function add_member($proxy_id, $is_owner = false) {
		global $db;
		$email = $db->GetOne("SELECT `email` FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($proxy_id));
		if ($email) {
			return $this->base_add_member($proxy_id, $email, 0, $is_owner);
		}
		return false;
	}

	public function remove_member($proxy_id) {
		if ($this->extended_remove_member($proxy_id)) {
			return $this->base_remove_member($proxy_id);
		}
		return false;
	}

	public function edit_member($proxy_id, $is_admin) {
		if ($this->extended_edit_member($proxy_id, $is_admin)) {
			return $this->base_edit_member($proxy_id, $is_admin);
		}
		return false;
	}

    public function mode_change($type) {
    	if ($this->extended_mode_change($type)) {
			return $this->base_mode_change($type);
		}
		return false;
    }

	public function member_active($proxy_id) {
		return ($this->users[$proxy_id]["member_active"] > 0 ? true : false);
	}

	public function deactivate_member($proxy_id) {
		if ($this->users[$proxy_id]["member_active"] > 0) {
			$this->base_edit_member($proxy_id, 0, '-1');
		} else {
			$this->base_remove_member($proxy_id);
		}
	}

	public function demote_administrator($proxy_id) {
		if (((int)$this->users[$proxy_id]["member_active"]) >= 1) {
			$this->base_edit_member($proxy_id, '-1');
		} else {
			$this->base_edit_member($proxy_id, '0');
		}
	}

	public function promote_administrator($proxy_id) {
		if ($this->users[$proxy_id]["member_active"] > 0) {
			$this->base_edit_member($proxy_id, '2');
		} else {
			$this->base_edit_member($proxy_id, '1');
		}
	}

}

?>