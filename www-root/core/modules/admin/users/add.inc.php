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
 * Allows administrators to add new users to the entrada_auth.user_data table.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Matt Simpson <matt.simpson@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/

if ((!defined("PARENT_INCLUDED")) || (!defined("IN_USERS"))) {
	exit;
} elseif ((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
	header("Location: ".ENTRADA_URL);
	exit;
} elseif (!$ENTRADA_ACL->amIAllowed("user", "create", false)) {
	$ERROR++;
	$ERRORSTR[]	= "Your account does not have the permissions required to use this feature of this module.<br /><br />If you believe you are receiving this message in error please contact <a href=\"mailto:".html_encode($AGENT_CONTACTS["administrator"]["email"])."\">".html_encode($AGENT_CONTACTS["administrator"]["name"])."</a> for assistance.";

	echo display_error();

	application_log("error", "Group [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["group"]."] and role [".$_SESSION["permissions"][$ENTRADA_USER->getAccessId()]["role"]."] does not have access to this module [".$MODULE."]");
} else {
	$BREADCRUMB[] = array("url" => ENTRADA_URL."/admin/users?".replace_query(array("section" => "add")), "title" => "Adding User");

	$PROCESSED_ACCESS = array();
	$PROCESSED_ACCESS["app_id"] = AUTH_APP_ID;
	$PROCESSED_ACCESS["private_hash"] = generate_hash(32);

	$PROCESSED_DEPARTMENTS = array();

	echo "<h1>Adding User</h1>\n";

	// Error Checking
	switch ($STEP) {
		case 2 :
			$permissions_only = false;
			$permissions = json_decode($_POST["permissions"], true);
			$permissions = json_decode($permissions["acl"], true);

			$in_departments = json_decode($_POST["my_departments"], true);
			$in_departments = json_decode($in_departments["list"], true);

			/**
			 * Required field "organisation_id" / Organisation.
			 */
			if (isset($permissions[0]["org_id"]) && $default_organisation_id = clean_input($permissions[0]["org_id"], array("trim", "int"))) {
				$PROCESSED["organisation_id"] = $default_organisation_id;
			} else {
				$ERROR++;
				$ERRORSTR[] = "As least one <strong>Organisation</strong> is required.";
			}

			/*
			 * Non-Required field "clinical" / Clinical.
			 */
			if (!isset($_POST["clinical"])) {
				$PROCESSED["clinical"] = 0;
			} else {
				$PROCESSED["clinical"] = 1;
			}

			/*
			 * Required field "account_active" / Account Status.
			 */
			if ((isset($_POST["account_active"])) && ($_POST["account_active"] == "true")) {
				$PROCESSED_ACCESS["account_active"] = "true";
			} else {
				$PROCESSED_ACCESS["account_active"] = "false";
			}

			/**
			 * Required field "access_starts" / Access Start (validated through validate_calendars function).
			 * Non-required field "access_finish" / Access Finish (validated through validate_calendars function).
			 */
			$access_date = validate_calendars("access", true, false);
			if ((isset($access_date["start"])) && ((int) $access_date["start"])) {
				$PROCESSED_ACCESS["access_starts"] = (int) $access_date["start"];
			}

			if ((isset($access_date["finish"])) && ((int) $access_date["finish"])) {
				$PROCESSED_ACCESS["access_expires"] = (int) $access_date["finish"];
			} else {
				$PROCESSED_ACCESS["access_expires"] = 0;
			}

			/**
			 * Non-required (although highly recommended) field for staff / student number.
			 */
			if ((isset($_POST["number"])) && ($number = clean_input($_POST["number"], array("trim", "int")))) {
				$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `number` = ".$db->qstr($number);
				$result	= $db->GetRow($query);
				if ($result) {
					$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_access` WHERE `user_id` = ".$db->qstr($result["id"])." AND `app_id` = ".$db->qstr(AUTH_APP_ID);
					$sresult = $db->GetRow($query);
					if ($sresult) {
						$ERROR++;
						$ERRORSTR[] = "The Staff / Student number that you are trying to add already exists in the system, and already has access to this application under the username <strong>".$result["username"]."</strong> [<a href=\"mailto:".$result["email"]."\">".$result["email"]."</a>].";
					} else {
						if ((isset($_POST["username"])) && ($username = clean_input($_POST["username"], "credentials")) && ($username != $result["username"])) {
							$PROCESSED["number"]	= $number;
							$PROCESSED["username"]	= $username;

							$ERROR++;
							$ERRORSTR[] = "The Staff / Student number that you have provided already exists in the system, but belongs to a different username (<strong>".html_encode($result["username"])."</strong>).";
						} else {
							/**
							 * Just add permissions for this account.
							 */
							$permissions_only = true;
							$PROCESSED_ACCESS["user_id"] = (int) $result["id"];

							$PROCESSED = $result;
						}
					}
				} else {
					$PROCESSED["number"] = $number;
				}
			} else {
				$NOTICE++;
				$NOTICESTR[] = "There was no faculty, staff or student number attached to this profile. If this user is a affiliated with the University, please make sure you add this information.";

				$PROCESSED["number"] = 0;
			}

			/**
			 * If this user already exists, and permissions just need to be set, then do not continue into
			 * this section.
			 */
			if (!$permissions_only) {
				/**
				 * Required field "username" / Username.
				 */
				if ((isset($_POST["username"])) && ($username = clean_input($_POST["username"], "credentials"))) {
					$query = "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `username` = ".$db->qstr($username);
					$result	= $db->GetRow($query);
					if ($result) {
						$query		= "SELECT * FROM `".AUTH_DATABASE."`.`user_access` WHERE `user_id` = ".$db->qstr($result["id"])." AND `app_id` = ".$db->qstr(AUTH_APP_ID);
						$sresult	= $db->GetRow($query);
						if ($sresult) {
							$ERROR++;
							$ERRORSTR[] = "The username that you are trying to add already exists in the system, and already has access to this application under the staff / student number <strong>".$result["number"]."</strong> [<a href=\"mailto:".$result["email"]."\">".$result["email"]."</a>]";
						} else {
							if ((isset($_POST["number"])) && ($number = clean_input($_POST["number"], array("trim", "int"))) && ($number != $result["number"])) {
								$PROCESSED["number"]	= $number;
								$PROCESSED["username"]	= $username;

								$ERROR++;
								$ERRORSTR[] = "The username that you have provided already exists in the system, but belongs to a different Staff / Student number (<strong>".html_encode($result["number"])."</strong>).";
							} else {
								$permissions_only				= true;
								$PROCESSED_ACCESS["user_id"]	= (int) $result["id"];

								$PROCESSED						= $result;

								unset($NOTICE, $NOTICESTR);
							}
						}
					} else {
						if ((strlen($username) >= 3) && (strlen($username) <= 24)) {
							$PROCESSED["username"] = $username;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The username field must be between 3 and 24 characters.";
						}
					}
				} else {
					$ERROR++;
					$ERRORSTR[] = "You must provide a valid username for this user to login with. We suggest that you use their University NetID if at all possible.";
				}

				if ((!$permissions_only) && (!$ERROR)) {
                    /**
                     * Required field "password" / Password.
                     */
					if ((isset($_POST["password"])) && ($password = clean_input($_POST["password"], "trim"))) {
						if ((strlen($password) >= 6) && (strlen($password) <= 24)) {
							$PROCESSED["password"] = $password;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The password field must be between 6 and 24 characters.";
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "You must provide a valid password for this user to login with.";
					}

					if ($PROCESSED_ACCESS["group"] == "student") {
						if (isset($_POST["entry_year"]) && isset($_POST["grad_year"])) {
							$entry_year = clean_input($_POST["entry_year"],"int");
							$grad_year = clean_input($_POST["grad_year"],"int");
							$sanity_start = 1995;
							$sanity_end = (fetch_first_year() + 1);
							if ($grad_year <= $sanity_end && $grad_year >= $sanity_start) {
								$PROCESSED["grad_year"] = $grad_year;
							} else {
								$ERROR++;
								$ERRORSTR[] = "You must provide a valid graduation year.";
							}
							if ($entry_year <= $sanity_end && $entry_year >= $sanity_start) {
								$PROCESSED["entry_year"] = $entry_year;
							} else {
								$ERROR++;
								$ERRORSTR[] = "You must provide a valid program entry year.";
							}
						}
					}

					/**
					 * Non-required field "prefix" / Prefix.
					 */
					if ((isset($_POST["prefix"])) && (@in_array($prefix = clean_input($_POST["prefix"], "trim"), $PROFILE_NAME_PREFIX))) {
						$PROCESSED["prefix"] = $prefix;
					} else {
						$PROCESSED["prefix"] = "";
					}

					/**
					 * Non-required field "office_hours" / Office Hours.
					 */
					if ((isset($_POST["office_hours"])) && ($office_hours = clean_input($_POST["office_hours"], array("notags","encode", "trim")))) {
						$PROCESSED["office_hours"] = ((strlen($office_hours) > 100) ? substr($office_hours, 0, 97)."..." : $office_hours);
					} else {
						$PROCESSED["office_hours"] = "";
					}

					/**
					 * Required field "firstname" / Firstname.
					 */
					if ((isset($_POST["firstname"])) && ($firstname = clean_input($_POST["firstname"], "trim"))) {
						$PROCESSED["firstname"] = $firstname;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The firstname of the user is a required field.";
					}

					/**
					 * Required field "lastname" / Lastname.
					 */
					if ((isset($_POST["lastname"])) && ($lastname = clean_input($_POST["lastname"], "trim"))) {
						$PROCESSED["lastname"] = $lastname;
					} else {
						$ERROR++;
						$ERRORSTR[] = "The lastname of the user is a required field.";
					}

					/**
					 * Non-Required field "gender" / Gender.
					 */
					if (isset($_POST["gender"]) && in_array((int) $_POST["gender"], array(1, 2))) {
						$PROCESSED["gender"] = (int) $_POST["gender"];
					}

					if (!isset($PROCESSED["gender"])) {
						$PROCESSED["gender"] = 0;
					}

					/**
					 * Required field "email" / Primary E-Mail.
					 */
					if ((isset($_POST["email"])) && ($email = clean_input($_POST["email"], "trim", "lower"))) {
						if (@valid_address($email)) {
							$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `email` = ".$db->qstr($email);
							$result	= $db->GetRow($query);
							if ($result) {
								$ERROR++;
								$ERRORSTR[] = "The e-mail address <strong>".html_encode($email)."</strong> already exists in the system for username <strong>".html_encode($result["username"])."</strong>. Please provide a unique e-mail address for this user.";
							} else {
								$PROCESSED["email"] = $email;
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "The primary e-mail address you have provided is invalid. Please make sure that you provide a properly formatted e-mail address.";
						}
					} else {
						$ERROR++;
						$ERRORSTR[] = "The primary e-mail address is a required field.";
					}

					/**
					 * Non-required field "email_alt" / Alternative E-Mail.
					 */
					if ((isset($_POST["email_alt"])) && ($email_alt = clean_input($_POST["email_alt"], "trim", "lower"))) {
						if (@valid_address($email_alt)) {
							$PROCESSED["email_alt"] = $email_alt;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The alternative e-mail address you have provided is invalid. Please make sure that you provide a properly formatted e-mail address or leave this field empty if you do not wish to display one.";
						}
					} else {
						$PROCESSED["email_alt"] = "";
					}

					/**
					 * Non-required field "telephone" / Telephone Number.
					 */
					if ((isset($_POST["telephone"])) && ($telephone = clean_input($_POST["telephone"], "trim")) && (strlen($telephone) >= 10) && (strlen($telephone) <= 25)) {
						$PROCESSED["telephone"] = $telephone;
					} else {
						$PROCESSED["telephone"] = "";
					}

					/**
					 * Non-required field "fax" / Fax Number.
					 */
					if ((isset($_POST["fax"])) && ($fax = clean_input($_POST["fax"], "trim")) && (strlen($fax) >= 10) && (strlen($fax) <= 25)) {
						$PROCESSED["fax"] = $fax;
					} else {
						$PROCESSED["fax"] = "";
					}

					/**
					 * Non-required field "address" / Address.
					 */
					if ((isset($_POST["address"])) && ($address = clean_input($_POST["address"], array("trim", "ucwords"))) && (strlen($address) >= 6) && (strlen($address) <= 255)) {
						$PROCESSED["address"] = $address;
					} else {
						$PROCESSED["address"] = "";
					}

					/**
					 * Non-required field "city" / City.
					 */
					if ((isset($_POST["city"])) && ($city = clean_input($_POST["city"], array("trim", "ucwords"))) && (strlen($city) >= 3) && (strlen($city) <= 35)) {
						$PROCESSED["city"] = $city;
					} else {
						$PROCESSED["city"] = "";
					}



					/**
					 * Non-required field "postcode" / Postal Code.
					 */
					if ((isset($_POST["postcode"])) && ($postcode = clean_input($_POST["postcode"], array("trim", "uppercase"))) && (strlen($postcode) >= 5) && (strlen($postcode) <= 12)) {
						$PROCESSED["postcode"] = $postcode;
					} else {
						$PROCESSED["postcode"] = "";
					}

					if ((isset($_POST["country_id"])) && ($tmp_input = clean_input($_POST["country_id"], "int"))) {
						$query = "SELECT * FROM `global_lu_countries` WHERE `countries_id` = ".$db->qstr($tmp_input);
						$result = $db->GetRow($query);
						if ($result) {
							$PROCESSED["country_id"] = $tmp_input;
						} else {
							$ERROR++;
							$ERRORSTR[] = "The selected country does not exist in our countries database. Please select a valid country.";

							application_log("error", "Unknown countries_id [".$tmp_input."] was selected. Database said: ".$db->ErrorMsg());
						}
					} else {
						$ERROR++;
						$ERRORSTR[]	= "You must select a country.";
					}

					if ((isset($_POST["prov_state"])) && ($tmp_input = clean_input($_POST["prov_state"], array("trim", "notags")))) {
						$PROCESSED["province_id"] = 0;
						$PROCESSED["province"] = "";

						if (ctype_digit($tmp_input) && ($tmp_input = (int) $tmp_input)) {
							if ($PROCESSED["country_id"]) {
								$query = "SELECT * FROM `global_lu_provinces` WHERE `province_id` = ".$db->qstr($tmp_input)." AND `country_id` = ".$db->qstr($PROCESSED["country_id"]);
								$result = $db->GetRow($query);
								if (!$result) {
									$ERROR++;
									$ERRORSTR[] = "The province / state you have selected does not appear to exist in our database. Please selected a valid province / state.";
								}
							}

							$PROCESSED["province_id"] = $tmp_input;
						} else {
							$PROCESSED["province"] = $tmp_input;
						}

						$PROCESSED["prov_state"] = ($PROCESSED["province_id"] ? $PROCESSED["province_id"] : ($PROCESSED["province"] ? $PROCESSED["province"] : ""));
					}

					/**
					 * Non-required field "notes" / General Comments.
					 */
					if ((isset($_POST["notes"])) && ($notes = clean_input($_POST["notes"], array("trim", "notags")))) {
						$PROCESSED["notes"] = $notes;
					} else {
						$PROCESSED["notes"] = "";
					}
				}
			}

			if ($ENTRADA_ACL->amIAllowed(new UserResource(null, $PROCESSED["organisation_id"]), 'create')) {
				if ($permissions_only) {
					foreach ($permissions as $perm) {
						if (!$perm["org_id"]) {
							$ERROR++;
							$ERRORSTR[] = "Please assign an organisation for all permissions.";
						} elseif (!$perm["group_id"]) {
							$ERROR++;
							$ERRORSTR[] = "Please assign a group for all permissions.";
						} elseif (!$perm["role_id"]) {
							$ERROR++;
							$ERRORSTR[] = "Please assign a role for all permissions.";
						} else {
							$query = "SELECT g.`group_name`, r.`role_name`
									FROM `" . AUTH_DATABASE . "`.`system_groups` g, `" . AUTH_DATABASE . "`.`system_roles` r,
										`" . AUTH_DATABASE . "`.`system_group_organisation` gho, `" . AUTH_DATABASE . "`.`organisations` o
									WHERE gho.`groups_id` = " . $perm["group_id"] . " AND g.`id` = " . $perm["group_id"] . " AND
									r.`id` = " . $perm["role_id"] . " AND o.`organisation_id` = " . $perm["org_id"];
							$group_role = $db->GetRow($query);
							$PROCESSED_ACCESS["group"] = $group_role["group_name"];
							$PROCESSED_ACCESS["role"] = $group_role["role_name"];
							$PROCESSED_ACCESS["organisation_id"] = $perm["org_id"];

							if ($PROCESSED_ACCESS["group"] == "faculty") {
								if (isset($perm["clinical"])) {
									$PROCESSED["clinical"] = clean_input($perm["clinical"], array("trim", "int"));
									$query = "	UPDATE `" . AUTH_DATABASE . "`.`user_data`
												SET `clinical` = " . $PROCESSED["clinical"] . "
												WHERE `id` = " . $PROCESSED_ACCESS["user_id"] . "
												LIMIT 1";
									if (!$db->Execute($query)) {
										$ERROR++;
										$ERRORSTR[] = "Failed to set the clinical field." . $query . " DB said: " . $db->ErrorMsg();
									}
								}
							}

							if ($PROCESSED_ACCESS["group"] == "student") {
								if (isset($perm["entry_year"]) && isset($perm["grad_year"])) {
									$entry_year = clean_input($perm["entry_year"],"int");
									$grad_year = clean_input($perm["grad_year"],"int");
									$sanity_start = 1995;
									$sanity_end = (fetch_first_year() + 1);
									if ($grad_year <= $sanity_end && $grad_year >= $sanity_start) {
										$PROCESSED["grad_year"] = $grad_year;
									} else {
										$ERROR++;
										$ERRORSTR[] = "You must provide a valid graduation year.";
									}
									if ($entry_year <= $sanity_end && $entry_year >= $sanity_start) {
										$PROCESSED["entry_year"] = $entry_year;
									} else {
										$ERROR++;
										$ERRORSTR[] = "You must provide a valid program entry year.";
									}
									if (!$ERROR) {
										$query = "	UPDATE `" . AUTH_DATABASE . "`.`user_data`
													SET `grad_year` = " . $PROCESSED["grad_year"] . ",
													`entry_year` = " . $PROCESSED["entry_year"] . "
													WHERE `id` = " . $PROCESSED_ACCESS["user_id"] . "
													LIMIT 1";
										if (!$db->Execute($query)) {
											$ERROR++;
											$ERRORSTR[] = "Failed to set the entry and grad year." . $query . " DB said: " . $db->ErrorMsg();
										}
									}
								}
							}

							if ($db->AutoExecute(AUTH_DATABASE.".user_access", $PROCESSED_ACCESS, "INSERT")) {
								if (($PROCESSED_ACCESS["group"] == "medtech") || ($PROCESSED_ACCESS["role"] == "admin")) {
									application_log("error", "USER NOTICE: A new user (".$PROCESSED["firstname"]." ".$PROCESSED["lastname"].") was added to ".APPLICATION_NAME." as ".$PROCESSED_ACCESS["group"]." > ".$PROCESSED_ACCESS["role"].".");
								}

								/**
								* Handle the inserting of user data into the user_departments table
								* if departmental information exists in the form.
								* NOTE: This is also done below (line 375)... arg.
								*/
								if (isset($_POST["my_departments"]) && $in_departments = json_decode($_POST["my_departments"], true)) {
									$in_departments = json_decode($in_departments["list"], true);
										if (is_array($in_departments)) {
										foreach ($in_departments as $key => $department_id) {
											$department_id = clean_input($department_id, "int");
											if ($department_id) {
												$query = "SELECT * FROM `" . AUTH_DATABASE . "`.`user_departments` WHERE `user_id` = " . $db->qstr($PROCESSED_ACCESS["user_id"]) . " AND `dep_id` = " . $db->qstr($department_id);
												$result = $db->GetRow($query);
												if (!$result) {
													$PROCESSED_DEPARTMENTS[] = $department_id;
												}
											}
										}
									}
								}

								if (@count($PROCESSED_DEPARTMENTS)) {
									foreach($PROCESSED_DEPARTMENTS as $department_id) {
										if (!$db->AutoExecute(AUTH_DATABASE.".user_departments", array("user_id" => $PROCESSED_ACCESS["user_id"], "dep_id" => $department_id), "INSERT")) {
											application_log("error", "Unable to insert proxy_id [".$PROCESSED_ACCESS["user_id"]."] into department [".$department_id."]. Database said: ".$db->ErrorMsg());
										}
									}
								}


								/**
								 * Add user to cohort if they're a student
                                 *
                                 * @todo Wow, this is just plain lazy. Someone needs to fix this dirty bit.
								 */
								if ($PROCESSED_ACCESS["group"] == "student") {
                                    $query = "  SELECT a.`group_id`
                                                FROM `groups` AS a
                                                JOIN `group_organisations` AS b
                                                ON a.`group_id` = b.`group_id`
                                                AND b.`organisation_id` = ".$db->qstr($PROCESSED_ACCESS["organisation_id"])."
                                                WHERE a.`group_name` = 'Class of ".$PROCESSED_ACCESS["role"]."'
                                                AND a.`group_type` = 'cohort'
                                                AND a.`group_active` = 1";
									$group_id = $db->GetOne($query);
									if ($group_id) {
                                        $query = "SELECT * FROM `group_members` WHERE `group_id`=".$db->qstr($group_id)." AND `proxy_id`=".$db->qstr($PROCESSED_ACCESS["user_id"]);
                                        $result = $db->GetRow($query);
                                        if ($result) {
                                            /**
                                             * Update existing group_member record.
                                             */
                                            $gmember = array(
                                                'member_active' => 1,
                                                'updated_date' => time(),
                                                'updated_by' => $ENTRADA_USER->getID()
                                            );

                                            $db->AutoExecute("group_members", $gmember, "UPDATE", "gmember_id = ".$result["gmember_id"]);
                                        } else {
                                            /**
                                             * Create new group_member record.
                                             */
                                            $gmember = array(
                                                'group_id' => $group_id,
                                                'proxy_id' => $PROCESSED_ACCESS["user_id"],
                                                'start_date' => 0,
                                                'finish_date' => 0,
                                                'member_active' => 1,
                                                'entrada_only' => 1,
                                                'updated_date' => time(),
                                                'updated_by' => $ENTRADA_USER->getID()
                                            );

                                            $db->AutoExecute("group_members", $gmember, "INSERT");
                                        }
									}
								}

								$url			= ENTRADA_URL."/admin/users";

								$SUCCESS++;
								$SUCCESSSTR[]	= "You have successfully given this existing user access to this application.<br /><br />You will now be redirected to the users index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

								$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

								application_log("success", "Gave [".$PROCESSED_ACCESS["group"]." / ".$PROCESSED_ACCESS["role"]."] permissions to user id [".$PROCESSED_ACCESS["user_id"]."].");
							} else {
								$ERROR++;
								$ERRORSTR[]	= "Unable to give existing user access permissions to this application. ".$db->ErrorMsg();

								application_log("error", "Error giving existing user access to application id [".AUTH_APP_ID."]. Database said: ".$db->ErrorMsg());
							}
						}
					}
				} else {
					if (!$ERROR) {
					/**
					 * Now change the password to the MD5 value, just before it was inserted.
					 */
						$PROCESSED["password"] = md5($PROCESSED["password"]);
						$PROCESSED["email_updated"] = time();
						if (($db->AutoExecute(AUTH_DATABASE.".user_data", $PROCESSED, "INSERT")) && ($PROCESSED_ACCESS["user_id"] = $db->Insert_Id())) {
							foreach ($permissions as $perm) {
								if (!$perm["org_id"]) {
									$ERROR++;
									$ERRORSTR[] = "Please assign an organisation for all permissions.";
								} elseif (!$perm["group_id"]) {
									$ERROR++;
									$ERRORSTR[] = "Please assign a group for all permissions.";
								} elseif (!$perm["role_id"]) {
									$ERROR++;
									$ERRORSTR[] = "Please assign a role for all permissions.";
								} else {
									$query = "SELECT g.`group_name`, r.`role_name`
											FROM `" . AUTH_DATABASE . "`.`system_groups` g, `" . AUTH_DATABASE . "`.`system_roles` r,
												`" . AUTH_DATABASE . "`.`system_group_organisation` gho, `" . AUTH_DATABASE . "`.`organisations` o
											WHERE gho.`groups_id` = " . $perm["group_id"] . " AND g.`id` = " . $perm["group_id"] . " AND
											r.`id` = " . $perm["role_id"] . " AND o.`organisation_id` = " . $perm["org_id"];
									$group_role = $db->GetRow($query);
									$PROCESSED_ACCESS["group"] = $group_role["group_name"];
									$PROCESSED_ACCESS["role"] = $group_role["role_name"];
									$PROCESSED_ACCESS["organisation_id"] = $perm["org_id"];

									if ($PROCESSED_ACCESS["group"] == "student") {
										if (isset($perm["entry_year"]) && isset($perm["grad_year"])) {
											$entry_year = clean_input($perm["entry_year"],"int");
											$grad_year = clean_input($perm["grad_year"],"int");
											$sanity_start = 1995;
                							$sanity_end = (fetch_first_year() + 1);
											if ($grad_year <= $sanity_end && $grad_year >= $sanity_start) {
												$PROCESSED["grad_year"] = $grad_year;
											} else {
												$ERROR++;
												$ERRORSTR[] = "You must provide a valid graduation year.";
											}
											if ($entry_year <= $sanity_end && $entry_year >= $sanity_start) {
												$PROCESSED["entry_year"] = $entry_year;
											} else {
												$ERROR++;
												$ERRORSTR[] = "You must provide a valid program entry year.";
											}
											if (!$ERROR) {
												$query = "	UPDATE `" . AUTH_DATABASE . "`.`user_data`
															SET `grad_year` = " . $PROCESSED["grad_year"] . ",
															`entry_year` = " . $PROCESSED["entry_year"] . "
															WHERE `id` = " . $PROCESSED_ACCESS["user_id"] . "
															LIMIT 1";
												if (!$db->Execute($query)) {
													$ERROR++;
													$ERRORSTR[] = "Failed to set the entry and grad year." . $query . " DB said: " . $db->ErrorMsg();
												}
											}
										}
									}

									if ($PROCESSED_ACCESS["group"] == "faculty") {
										if (isset($perm["clinical"])) {
											$PROCESSED["clinical"] = clean_input($perm["clinical"], array("trim", "int"));
											$query = "	UPDATE `" . AUTH_DATABASE . "`.`user_data`
														SET `clinical` = " . $PROCESSED["clinical"] . "
														WHERE `id` = " . $PROCESSED_ACCESS["user_id"] . "
														LIMIT 1";
											if (!$db->Execute($query)) {
												$ERROR++;
												$ERRORSTR[] = "Failed to set the clinical field." . $query . " DB said: " . $db->ErrorMsg();
											}
										}
									}

									$PROCESSED_ACCESS["private_hash"] = generate_hash(32);

									if (!$ERROR) {
										if ($db->AutoExecute(AUTH_DATABASE.".user_access", $PROCESSED_ACCESS, "INSERT")) {
											if (($PROCESSED_ACCESS["group"] == "medtech") || ($PROCESSED_ACCESS["role"] == "admin")) {
												application_log("error", "USER NOTICE: A new user (".$PROCESSED["firstname"]." ".$PROCESSED["lastname"].") was added to ".APPLICATION_NAME." as ".$PROCESSED_ACCESS["group"]." > ".$PROCESSED_ACCESS["role"].".");
											}

                                            /**
                                             * Add user to cohort if they're a student
                                             *
                                             * @todo Wow, this is just plain lazy. Someone needs to fix this dirty bit.
                                             */
                                            if ($PROCESSED_ACCESS["group"] == "student") {
                                                $query = "  SELECT a.`group_id`
                                                            FROM `groups` AS a
                                                            JOIN `group_organisations` AS b
                                                            ON a.`group_id` = b.`group_id`
                                                            AND b.`organisation_id` = ".$db->qstr($PROCESSED_ACCESS["organisation_id"])."
                                                            WHERE a.`group_name` = 'Class of ".$PROCESSED_ACCESS["role"]."'
                                                            AND a.`group_type` = 'cohort'
                                                            AND a.`group_active` = 1";
                                                $group_id = $db->GetOne($query);
                                                if ($group_id) {
                                                    $query = "SELECT * FROM `group_members` WHERE `group_id`=".$db->qstr($group_id)." AND `proxy_id`=".$db->qstr($PROCESSED_ACCESS["user_id"]);
                                                    $result = $db->GetRow($query);
                                                    if ($result) {
                                                        /**
                                                         * Update existing group_member record.
                                                         */
                                                        $gmember = array(
                                                            'member_active' => 1,
                                                            'updated_date' => time(),
                                                            'updated_by' => $ENTRADA_USER->getID()
                                                        );

                                                        $db->AutoExecute("group_members", $gmember, "UPDATE", "gmember_id = ".$result["gmember_id"]);
                                                    } else {
                                                        /**
                                                         * Create new group_member record.
                                                         */
                                                        $gmember = array(
                                                            'group_id' => $group_id,
                                                            'proxy_id' => $PROCESSED_ACCESS["user_id"],
                                                            'start_date' => 0,
                                                            'finish_date' => 0,
                                                            'member_active' => 1,
                                                            'entrada_only' => 1,
                                                            'updated_date' => time(),
                                                            'updated_by' => $ENTRADA_USER->getID()
                                                        );

                                                        $db->AutoExecute("group_members", $gmember, "INSERT");
                                                    }
                                                }
                                            }

											/**
											 * Handle the inserting of user data into the user_departments table
											 * if departmental information exists in the form.
											 */
											if (isset($_POST["my_departments"]) && $in_departments = json_decode($_POST["my_departments"], true)) {
												$in_departments = json_decode($in_departments["list"], true);
													if (is_array($in_departments)) {
													foreach ($in_departments as $key => $department_id) {
														$department_id = clean_input($department_id, "int");
														if ($department_id) {
															$query = "SELECT * FROM `" . AUTH_DATABASE . "`.`user_departments` WHERE `user_id` = " . $db->qstr($PROCESSED_ACCESS["user_id"]) . " AND `dep_id` = " . $db->qstr($department_id);
															$result = $db->GetRow($query);
															if (!$result) {
																$PROCESSED_DEPARTMENTS[] = $department_id;
															}
														}
													}
												}
											}

											if (@count($PROCESSED_DEPARTMENTS)) {
												foreach($PROCESSED_DEPARTMENTS as $key => $department_id) {
													if (!$db->AutoExecute(AUTH_DATABASE.".user_departments", array("user_id" => $PROCESSED_ACCESS["user_id"], "dep_id" => $department_id), "INSERT")) {
														application_log("error", "Unable to insert proxy_id [".$PROCESSED_ACCESS["user_id"]."] into department [".$department_id."]. Database said: ".$db->ErrorMsg());
													}
												}
											}

											$url			= ENTRADA_URL."/admin/users";

											$SUCCESS++;
											$SUCCESSSTR[]	= "You have successfully created a new user in the authentication system, and have given them <strong>".$PROCESSED_ACCESS["group"]."</strong> / <strong>".$PROCESSED_ACCESS["role"]."</strong> access.<br /><br />You will now be redirected to the users index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

											$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

											application_log("success", "Gave [".$PROCESSED_ACCESS["group"]." / ".$PROCESSED_ACCESS["role"]."] permissions to user id [".$PROCESSED_ACCESS["user_id"]."].");
										} else {
											$ERROR++;
											$ERRORSTR[]	= "Unable to give this new user access permissions to this application. ".$db->ErrorMsg();

											application_log("error", "Error giving new user access to application id [".AUTH_APP_ID."]. Database said: ".$db->ErrorMsg());
										}
									} else {
										echo display_error();
									}
								}
							}
						} else {
							$ERROR++;
							$ERRORSTR[] = "Unable to create a new user account at this time. The MEdTech Unit has been informed of this error, please try again later.";

							application_log("error", "Unable to create new user account. Database said: ".$db->ErrorMsg());
						}
					}
				}
			} else {
				$ERROR++;
				$ERRORSTR[] = "You do not have permission to create a user with those details. Please try again with a different organisation.";

				application_log("error", "Unable to create new user account because this user didn't have permissions to create with the selected organisation ID. This should only happen if the request is tampered with.");
			}
			if ($ERROR) {
				$STEP = 1;
			} else {
				$url			= ENTRADA_URL."/admin/users";
				$ONLOAD[]		= "setTimeout('window.location=\\'".$url."\\'', 5000)";

				/**
				 * If there are permissions only we may not have the information that we require,
				 * so query the database to get it.
				 */
				if ($permissions_only) {
					$query	= "SELECT * FROM `".AUTH_DATABASE."`.`user_data` WHERE `id` = ".$db->qstr($PROCESSED_ACCESS["user_id"]);
					$result	= $db->GetRow($query);
					if ($result) {
						$PROCESSED = array();
						$PROCESSED["firstname"]	= $result["firstname"];
						$PROCESSED["lastname"]	= $result["lastname"];
						$PROCESSED["username"]	= $result["username"];
						$PROCESSED["email"]		= $result["email"];
					}
				}

				if ((isset($_POST["send_notification"])) && ((int) $_POST["send_notification"] == 1)) {
					$PROXY_ID = $PROCESSED_ACCESS["user_id"];
					do {
						$HASH = generate_hash();
					} while($db->GetRow("SELECT `id` FROM `".AUTH_DATABASE."`.`password_reset` WHERE `hash` = ".$db->qstr($HASH)));

					if ($db->AutoExecute(AUTH_DATABASE.".password_reset", array("ip" => $_SERVER["REMOTE_ADDR"], "date" => time(), "user_id" => $PROXY_ID, "hash" => $HASH, "complete" => 0), "INSERT")) {
						// Send welcome & password reset e-mail.
						$notification_search	= array("%firstname%", "%lastname%", "%username%", "%password_reset_url%", "%application_url%", "%application_name%");
						$notification_replace	= array($PROCESSED["firstname"], $PROCESSED["lastname"], $PROCESSED["username"], PASSWORD_RESET_URL."?hash=".rawurlencode($PROXY_ID.":".$HASH), ENTRADA_URL, APPLICATION_NAME);

						$message = str_ireplace($notification_search, $notification_replace, ((isset($_POST["notification_message"])) ? html_encode($_POST["notification_message"]) : $DEFAULT_NEW_USER_NOTIFICATION));

						if (!@mail($PROCESSED["email"], "New User Account: ".APPLICATION_NAME, $message, "From: \"".$AGENT_CONTACTS["administrator"]["name"]."\" <".$AGENT_CONTACTS["administrator"]["email"].">\nReply-To: \"".$AGENT_CONTACTS["administrator"]["name"]."\" <".$AGENT_CONTACTS["administrator"]["email"].">")) {
							$NOTICE++;
							$NOTICESTR[] = "The user was successfully added; however, we could not send them a new account e-mail notice. The MEdTech Unit has been informed of this problem, please send this new user a password reset notice manually.<br /><br />You will now be redirected back to the user index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

							application_log("error", "New user [".$PROCESSED["username"]."] was given access to OCR but the e-mail notice failed to send.");
						}
					} else {
						$NOTICE++;
						$NOTICESTR[] = "The user was successfully added; however, we could not send them a new account e-mail notice. The MEdTech Unit has been informed of this problem, please send this new user a password reset notice manually.<br /><br />You will now be redirected back to the user index; this will happen <strong>automatically</strong> in 5 seconds or <a href=\"".$url."\" style=\"font-weight: bold\">click here</a> to continue.";

						application_log("error", "New user [".$PROCESSED["username"]."] was given access to OCR but the e-mail notice failed to send. Database said: ".$db->ErrorMsg());
					}
				}
			}
			break;
		case 1 :
		default :
			continue;
			break;
	}

	// Display Page.
	switch ($STEP) {
		case 2 :
			if ($NOTICE) {
				echo display_notice();
			}
			if ($SUCCESS) {
				echo display_success();
			}
			break;
		case 1 :
		default :
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/selectchained.js\"></script>\n";
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/picklist.js\"></script>\n";
			$HEAD[] = "<script type=\"text/javascript\" src=\"".ENTRADA_URL."/javascript/elementresizer.js\"></script>\n";

			$i = count($HEAD);

			$ONLOAD[] = "setMaxLength()";
			if (isset($_GET["id"]) && $_GET["id"] && ($proxy_id = clean_input($_GET["id"], array("int")))) {
				$ONLOAD[] = "findExistingUser('id', '".$proxy_id."')";
			}
			$ONLOAD[] = "toggle_visibility_checkbox($('send_notification'), 'send_notification_msg')";
			$ONLOAD[] = "provStateFunction()";

			$DEPARTMENT_LIST = array();
			$query = "	SELECT a.`department_id`, a.`department_title`, a.`organisation_id`, b.`entity_title`
						FROM `".AUTH_DATABASE."`.`departments` AS a
						LEFT JOIN `".AUTH_DATABASE."`.`entity_type` AS b
						ON a.`entity_id` = b.`entity_id`
						ORDER BY a.`department_title`";

			$results = $db->GetAll($query);
			if ($results) {
				foreach($results as $key => $result) {
					$DEPARTMENT_LIST[$result["organisation_id"]][] = array("department_id"=>$result['department_id'], "department_title" => $result["department_title"], "entity_title" => $result["entity_title"]);
				}
			}

			if ($ERROR) {
				echo display_error();
			}

			if ($NOTICE) {
				echo display_notice();
			}
			?>
			<script type="text/javascript">
			var glob_type = null;
			function findExistingUser(type, value) {
				if (type && value) {
					var url = '<?php echo ENTRADA_RELATIVE; ?>/admin/<?php echo $MODULE; ?>?section=search&' + type + '=' + value;
					if (type == 'id') {
						type = 'number';
					}

					if ($(type + '-default')) {
						$(type + '-default').hide();
					}

					if ($(type + '-searching')) {
						$(type + '-searching').show();
					}

					glob_type = type;

					new Ajax.Request(url, {method: 'get', onComplete: getResponse});
				}
			}

			function getResponse(request) {
				if ($(glob_type + '-default')) {
					$(glob_type + '-default').show();
				}

				if ($(glob_type + '-searching')) {
					$(glob_type + '-searching').hide();
				}

				var data = request.responseJSON;

				if (data) {
					$('username').disable().setValue(data.username);
					$('firstname').disable().setValue(data.firstname);
					$('lastname').disable().setValue(data.lastname);
					$('email').disable().setValue(data.email);
					$('number').disable().setValue(data.number);
					$('password').disable().setValue('********');
					$('prefix').disable().setValue(data.prefix);
					$('email_alt').disable().setValue(data.email_alt);
					$('telephone').disable().setValue(data.telephone);
					$('fax').disable().setValue(data.fax);
					$('address').disable().setValue(data.address);
					$('city').disable().setValue(data.city);

					if ($('country')) {
						$('country').disable().setValue(data.country);
					} else if($('country_id')) {
						$('country_id').disable().setValue(data.country_id);

						provStateFunction(data.country_id, data.province_id);
					}

					$('postcode').disable().setValue(data.postcode);
					$('notes').disable().setValue(data.notes);

					$('send_notification_msg').hide();
					$('send_notification').checked = false;

					var notice = document.createElement('div');
					notice.id = 'display-notice';
					notice.addClassName('display-notice');
					notice.innerHTML = data.message;

					$('addUser').insert({'before' : notice});
				}
			}

			function provStateFunction(country_id, province_id) {
				var url_country_id = '<?php echo ((!isset($PROCESSED["country_id"]) && defined("DEFAULT_COUNTRY_ID") && DEFAULT_COUNTRY_ID) ? DEFAULT_COUNTRY_ID : 0); ?>';
				var url_province_id = '<?php echo ((!isset($PROCESSED["province_id"]) && defined("DEFAULT_PROVINCE_ID") && DEFAULT_PROVINCE_ID) ? DEFAULT_PROVINCE_ID : 0); ?>';

				if (country_id != undefined) {
					url_country_id = country_id;
				} else if ($('country_id')) {
					url_country_id = $('country_id').getValue();
				}

				if (province_id != undefined) {
					url_province_id = province_id;
				} else if ($('province_id')) {
					url_province_id = $('province_id').getValue();
				}

				var url = '<?php echo webservice_url("province"); ?>?countries_id=' + url_country_id + '&prov_state=' + url_province_id;

				new Ajax.Updater($('prov_state_div'), url, {
					method:'get',
					onComplete: function (init_run) {

						if ($('prov_state').type == 'select-one') {
							$('prov_state_label').removeClassName('form-nrequired');
							$('prov_state_label').addClassName('form-required');
							if (!init_run) {
								$("prov_state").selectedIndex = 0;
							}
						} else {
							$('prov_state_label').removeClassName('form-required');
							$('prov_state_label').addClassName('form-nrequired');
							if (!init_run) {
								$("prov_state").clear();
							}
						}
					}
				});
			}
			</script>


			<form id="addUser" action="<?php echo ENTRADA_URL; ?>/admin/users?section=add&amp;step=2" method="post" onsubmit="$('username').enable()">
				<table style="width: 100%" cellspacing="1" cellpadding="1" border="0" summary="New Profile">
					<colgroup>
						<col style="width: 3%" />
						<col style="width: 25%" />
						<col style="width: 72%" />
					</colgroup>
					<tfoot>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="3" style="border-top: 2px #CCCCCC solid; padding-top: 5px; text-align: right">
								<input type="submit" class="button" value="Add User" />
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<td colspan="3">
								<h2>Account Details</h2>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="number" class="form-nrequired">Staff / Student Number</label></td>
							<td>
								<input type="text" id="number" name="number" value="<?php echo ((isset($PROCESSED["number"])) ? html_encode($PROCESSED["number"]) : ""); ?>" style="width: 250px" maxlength="25" onblur="findExistingUser('number', this.value)" />
								<span id="number-searching" class="content-small" style="display: none;"><img src="<?php echo ENTRADA_RELATIVE ?>/images/indicator.gif" /> Searching system for this number... </span>
								<span id="number-default" class="content-small">(<strong>Important:</strong> Required when ever possible)</span>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="username" class="form-required">Username</label></td>
							<td>
								<input type="text" id="username" name="username" value="<?php echo ((isset($PROCESSED["username"])) ? html_encode($PROCESSED["username"]) : ""); ?>" style="width: 250px" maxlength="25" onblur="findExistingUser('username', this.value)" />
								<span id="username-searching" class="content-small" style="display: none;"><img src="<?php echo ENTRADA_RELATIVE ?>/images/indicator.gif" /> Searching system for this username... </span>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="password" class="form-required">Password</label></td>
							<td><input type="text" id="password" name="password" value="<?php echo ((isset($PROCESSED["password"])) ? html_encode($PROCESSED["password"]) : generate_password(8)); ?>" style="width: 250px" maxlength="25" /></td>
						</tr>
						<tr>
							<td colspan="3">
								<h2>Account Options</h2>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td style="vertical-align: top"><label for="account_active" class="form-required">Account Status</label></td>
							<td>
								<select id="account_active" name="account_active" style="width: 209px">
									<option value="true"<?php echo (((!isset($PROCESSED_ACCESS["account_active"])) || ($PROCESSED_ACCESS["account_active"] == "true")) ? " selected=\"selected\"" : ""); ?>>Active</option>
									<option value="false"<?php echo (($PROCESSED_ACCESS["account_active"] == "false") ? " selected=\"selected\"" : ""); ?>>Disabled</option>
								</select>
							</td>
						</tr>
									<?php echo generate_calendars("access", "Access", true, true, ((isset($PROCESSED["access_starts"])) ? $PROCESSED["access_starts"] : time()), true, false, 0); ?>
						<tr>
							<td colspan="3">
								<h2>Personal Information</h2>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="prefix" class="form-nrequired">Prefix</label></td>
							<td>
								<select id="prefix" name="prefix" style="width: 55px; vertical-align: middle; margin-right: 5px">
									<option value=""<?php echo ((!$result["prefix"]) ? " selected=\"selected\"" : ""); ?>></option>
									<?php
									if ((@is_array($PROFILE_NAME_PREFIX)) && (@count($PROFILE_NAME_PREFIX))) {
										foreach($PROFILE_NAME_PREFIX as $key => $prefix) {
											echo "<option value=\"".html_encode($prefix)."\"".(((isset($PROCESSED["prefix"])) && ($PROCESSED["prefix"] == $prefix)) ? " selected=\"selected\"" : "").">".html_encode($prefix)."</option>\n";
										}
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="firstname" class="form-required">Firstname</label></td>
							<td><input type="text" id="firstname" name="firstname" value="<?php echo ((isset($PROCESSED["firstname"])) ? html_encode($PROCESSED["firstname"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="35" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="lastname" class="form-required">Lastname</label></td>
							<td><input type="text" id="lastname" name="lastname" value="<?php echo ((isset($PROCESSED["lastname"])) ? html_encode($PROCESSED["lastname"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="35" /></td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="gender" class="form-nrequired">Gender</label></td>
							<td>
								<select name="gender" id="gender" style="width: 256px">
									<option value="0"<?php echo ($PROCESSED["gender"] == 0 ? " selected=\"selected\"" : ""); ?>>Not Specified</option>
									<option value="1"<?php echo ($PROCESSED["gender"] == 1 ? " selected=\"selected\"" : ""); ?>>Female</option>
									<option value="2"<?php echo ($PROCESSED["gender"] == 2 ? " selected=\"selected\"" : ""); ?>>Male</option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="email" class="form-required">Primary E-Mail</label></td>
							<td>
								<input type="text" id="email" name="email" value="<?php echo ((isset($PROCESSED["email"])) ? html_encode($PROCESSED["email"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="128" />
								<span class="content-small">(<strong>Important:</strong> Official e-mail accounts only)</span>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="email_alt" class="form-nrequired">Alternative E-Mail</label></td>
							<td><input type="text" id="email_alt" name="email_alt" value="<?php echo ((isset($PROCESSED["email_alt"])) ? html_encode($PROCESSED["email_alt"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="128" /></td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="telephone" class="form-nrequired">Telephone Number</label></td>
							<td>
								<input type="text" id="telephone" name="telephone" value="<?php echo ((isset($PROCESSED["telephone"])) ? html_encode($PROCESSED["telephone"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="25" />
								<span class="content-small">(<strong>Example:</strong> 613-533-6000 x74918)</span>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="fax" class="form-nrequired">Fax Number</label></td>
							<td>
								<input type="text" id="fax" name="fax" value="<?php echo ((isset($PROCESSED["fax"])) ? html_encode($PROCESSED["fax"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="25" />
								<span class="content-small">(<strong>Example:</strong> 613-533-3204)</span>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>

						<tr>
							<td>&nbsp;</td>
							<td><label for="country_id" class="form-required">Country</label></td>
							<td>
								<?php
								$countries = fetch_countries();
								if ((is_array($countries)) && (count($countries))) {
									echo "<select id=\"country_id\" name=\"country_id\" style=\"width: 256px\" onchange=\"provStateFunction();\">\n";
									echo "<option value=\"0\">-- Select Country --</option>\n";
									foreach ($countries as $country) {
										echo "<option value=\"".(int) $country["countries_id"]."\"".(((!isset($PROCESSED["country_id"]) && ($country["countries_id"] == DEFAULT_COUNTRY_ID)) || ($PROCESSED["country_id"] == $country["countries_id"])) ? " selected=\"selected\"" : "").">".html_encode($country["country"])."</option>\n";
									}
									echo "</select>\n";
								} else {
									echo "<input type=\"hidden\" id=\"countries_id\" name=\"countries_id\" value=\"0\" />\n";
									echo "Country information not currently available.\n";
								}
								?>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label id="prov_state_label" for="prov_state_div" class="form-nrequired">Province / State</label></td>
							<td>
								<div id="prov_state_div">Please select a <strong>Country</strong> from above first.</div>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="city" class="form-nrequired">City</label></td>
							<td>
								<input type="text" id="city" name="city" value="<?php echo ((isset($PROCESSED["city"])) ? html_encode($PROCESSED["city"]) : "Kingston"); ?>" style="width: 250px; vertical-align: middle" maxlength="35" />
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><label for="address" class="form-nrequired">Address</label></td>
							<td>
								<input type="text" id="address" name="address" value="<?php echo ((isset($PROCESSED["address"])) ? html_encode($PROCESSED["address"]) : ""); ?>" style="width: 250px; vertical-align: middle" maxlength="255" />
							</td>
						</tr>


						<tr>
							<td>&nbsp;</td>
							<td><label for="postcode" class="form-nrequired">Postal Code</label></td>
							<td>
								<input type="text" id="postcode" name="postcode" value="<?php echo ((isset($PROCESSED["postcode"])) ? html_encode($PROCESSED["postcode"]) : "K7L 3N6"); ?>" style="width: 250px; vertical-align: middle" maxlength="7" />
								<span class="content-small">(<strong>Example:</strong> K7L 3N6)</span>
							</td>
						</tr>

						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td style="vertical-align: top"><label for="office_hours">Office Hours</label></td>
							<td>
								<textarea id="office_hours" name="office_hours" style="width: 254px; height: 40px;" maxlength="100"><?php echo (isset($PROCESSED["office_hours"]) && $PROCESSED["office_hours"] ? html_encode($PROCESSED["office_hours"]) : ""); ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td style="vertical-align: top"><label for="notes" class="form-nrequired">General Comments</label></td>
							<td>
								<textarea id="notes" class="expandable" name="notes" style="width: 246px; height: 75px"><?php echo ((isset($PROCESSED["notes"])) ? html_encode($PROCESSED["notes"]) : ""); ?></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<h2>Permissions</h2>
							</td>
						</tr>
						<?php
								if (strtolower($ENTRADA_USER->getActiveGroup()) == "medtech" && strtolower($ENTRADA_USER->getActiveRole()) == "admin") {
									$query		= "	SELECT DISTINCT o.`organisation_id`, o.`organisation_title`
													FROM `" . AUTH_DATABASE . "`.`organisations` o";
								} else {
									$query		= "	SELECT DISTINCT o.`organisation_id`, o.`organisation_title`
													FROM `".AUTH_DATABASE."`.`user_access` ua
													JOIN `" . AUTH_DATABASE . "`.`organisations` o
													ON ua.`organisation_id` = o.`organisation_id`
													WHERE ua.`user_id` = " . $db->qstr($ENTRADA_USER->getID()) . "
													AND ua.`app_id` = " . $db->qstr(AUTH_APP_ID);								}
								$results	= $db->GetAll($query);

								if ($results) {
									 ?>
						<tr>
							<td colspan="3">
								<table class="org_table" style="width:100%">
									<tbody>
										<tr>
											<td style="padding-top:10px">
											<label for="organisations"><strong>Organisation</strong></label><br />
											<select id="organisations" name="organisations" style="width:200px">
										<?php
											foreach($results as $result) {
												echo build_option($result["organisation_id"], ucfirst($result["organisation_title"]), $selected);
											}
										?>
											</select>
											</td>
											<td style="padding-top:10px;">
											<label for="groups"><strong>Groups</strong></label><br />
											<select id="groups" name="groups" style="width:200px">
												<option value="0">Select a Group</option>
											</select>
											</td>
											<td style="padding-top:10px">
											<label for="roles"><strong>Role</strong></label><br />
											<select id="roles" name="roles" style="width:200px">
												<option value="0">Select a Role</option>
											</select>
											</td>
										</tr>
										<tr>
											<td>&nbsp;</td>
											<td>&nbsp;</td>
											<td style="text-align: right;"><br /><input id="add_permissions" name="add_permissions" type="button" value="Add" /></td>
										</tr>
									</tbody>
								</table>
								<hr />
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="3">
								<?php
									foreach($results as $result) { ?>
									<table class="tableList" style="display: none; width: 100%;" id="<?php echo "perm_organisation_" . $result["organisation_id"]; ?>" >
										<caption><h2 style="text-align: left;"><?php echo $result["organisation_title"]; ?></h2></caption>
										<colgroup>
											<col style="width: 15%" />
											<col style="width: 15%" />
											<col style="width: 60%" />
											<col style="width: 10%" />
										</colgroup>
										<thead>
											<tr>
												<th></th>
												<th></th>
												<th></th>
												<th></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td colspan="4"><h3>Profiles</h3></td>
											</tr>
										</tbody>
										<tfoot>
											<tr>
												<td colspan="4"><h3>Options</h3></td>
											</tr>
											<tr>
												<td>&nbsp;</td>
												<td colspan="3">
													<label for="<?php echo "in_departments_" . $result["organisation_id"]; ?>" style="display: block;">Departments</label><br />
													<select id="<?php echo "in_departments_" . $result["organisation_id"]; ?>" name="<?php echo "in_departments_" . $result["organisation_id"]; ?>" style="">
														<option value="0">-- Select Departments --</option>
													<?php

														foreach($DEPARTMENT_LIST as $organisation_id => $dlist) {
															if ($result["organisation_id"] == $organisation_id){
																foreach($dlist as $d){
																	echo build_option($d["department_id"], $d["department_title"], $selected);
																}
															}
														}
													?>
													</select><br />
													<div id="departments_notice_<?php echo $result["organisation_id"]; ?>" class="content-small"><div style="margin: 5px 0 5px 0"><strong>Note:</strong> Selected departments will appear here.</div></div>
													<hr />
													<ol id="departments_container_<?php echo $result["organisation_id"]; ?>" class="sortableList" style="display: none;">
													</ol>
												</td>
											</tr>
										</tfoot>
									</table>
									<br />
								<?php
									}
								?>
								<input id="my_departments" name="my_departments" type="hidden" value="0" />
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<h2>Notification Options</h2>
							</td>
						</tr>
						<tr>
							<td><input type="checkbox" id="send_notification" name="send_notification" value="1"<?php echo (((empty($_POST)) || ((isset($_POST["send_notification"])) && ((int) $_POST["send_notification"]))) ? " checked=\"checked\"" : ""); ?> style="vertical-align: middle" onclick="toggle_visibility_checkbox(this, 'send_notification_msg')" /></td>
							<td colspan="2"><label for="send_notification" class="form-nrequired">Send this new user a password reset e-mail after adding them.</label></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td colspan="2">
								<div id="send_notification_msg" style="display: block">
									<label for="notification_message" class="form-required">Notification Message</label><br />
									<textarea id="notification_message" name="notification_message" rows="10" cols="65" style="width: 100%; height: 200px"><?php echo ((isset($_POST["notification_message"])) ? html_encode($_POST["notification_message"]) : $DEFAULT_NEW_USER_NOTIFICATION); ?></textarea>
									<span class="content-small"><strong>Available Variables:</strong> %firstname%, %lastname%, %username%, %password_reset_url%, %application_url%, %application_name%</span>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="3">&nbsp;</td>
						</tr>
					</tbody>
				</table>
				<input id="permissions" name="permissions" type="hidden" value="" />
			</form>
			<div style="display: none;" id="entry_grad_year_container">
				<div id="entry_year_container" style="float: left; margin-right: 20px;">
					<label for="entry_year" class="form-required" style="display: block;">Year of Program Entry</label>&nbsp;
					<select id="entry_year" name="entry_year" style="width: 140px">
					<?php
					$selected_year = (isset($PROCESSED["entry_year"])) ? $PROCESSED["entry_year"] : (date("Y", time()) - ((date("m", time()) < 7) ?  1 : 0));
					for($i = fetch_first_year(); $i >= 1995; $i--) {
						$selected = $selected_year == $i;
						echo build_option($i, $i, $selected);
					}
					?>
					</select>
				</div>
   				&nbsp;&nbsp;
				<div id="grad_year_container" style="float: left;">
					<label for="grad_year" class="form-required" style="display: block;">Expected Graduation Year</label>&nbsp;
					<select id="grad_year" name="grad_year" style="width: 140px;">
					<?php
					for($i = (fetch_first_year() + 1); $i >= 1995; $i--) {
						$selected = (isset($PROCESSED["grad_year"]) && $PROCESSED["grad_year"] == $i);
						echo build_option($i, $i, $selected);
					}
					?>
					</select>
				</div>
			</div>
			<style>
				td {
					text-align: left;
					white-space: normal;
				}
				table.tableList tbody tr td {
					text-align: left;
					white-space: normal;
				}

				th {
					color: #11335D; font-family: 'Century Gothic',Helvetica,Arial,sans-serif; font-size: 13px;
				}

				li a.remove_dept {
					display: block;
					float: right;
				}
			</style>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var my_departments = {};
					my_departments.list = [];

					$('select[name^=in_departments_]').live("change", function() {
						var dept_id = $(this).val();
						var org_id = $(this).attr("id").split("_")[2];
						var remove_link = "<a class=\"remove_dept\" href=\"\"><img src=\"" + "<?php echo ENTRADA_URL; ?>" + "/images/action-delete.gif\"></a>";
						var content = "<li id=\"dept_" + dept_id + "\"><img src=\"" + "<?php echo ENTRADA_URL; ?>" + "/images/icon-apartment.gif\">" + $(this).find(":selected").text() + remove_link + "</li>";
						$('#departments_container_' + org_id).append(content);
						$('#departments_notice_' + org_id).hide();
						$('#departments_container_' + org_id).show();
						my_departments.list.push(dept_id);
						$('input[name=my_departments]').val(JSON.stringify(my_departments));
						//reset the select list
						$('#in_departments_' + org_id + ' option[value=' + dept_id + ']').remove();
						$('select[name^=in_departments_]').val(0);
					});

					$('a.remove_dept').live("click", function(event) {
						event.preventDefault();
						var dept_id = $(this).closest("li").attr("id").split("_")[1];
						var dept_text = $(this).closest("li").text();
						var org_id = $(this).closest("ol").attr("id").split("_")[2];
						$(this).closest("li").remove();
						if ($('#departments_container_' + org_id).children().size() == 0) {
							$('#departments_notice_' + org_id).show();
						}
						for(var i=0; i<my_departments.list.length; i++) {
							if (my_departments.list[i] == dept_id) {
								my_departments.list.splice(i, 1);
							}
						}
						$('input[name=my_departments]').val(JSON.stringify(my_departments));
						//add the dept back to the select list
						var option = $("<option></option>").text(dept_text);
						$(option).attr("value", dept_id);
						$('#in_departments_' + org_id).append(option);

						//now resort the select list by dept title
						var my_options = $('#in_departments_' + org_id + ' option');
						my_options.sort(function(a,b) {
							if (a.text > b.text) return 1;
							else if (a.text < b.text) return -1;
							else return 0
						});
						$('#in_departments_' + org_id).empty().append( my_options );
						$('select[name^=in_departments_]').val(0);
					});

					$('select[name=organisations]').live("change", function() {
						$('select[name=groups]').children().remove();
						$("#groups").append('<option value=\"0\">Select a Group</option>')
						var url = "<?php echo ENTRADA_URL . "/api/organisation-groups.api.php"; ?>";
						$.get(	url, { organisation_id: $(this).val() },
								function(data){
									for (var key in data) {
										if (data.hasOwnProperty(key)) {
										$("#groups").append('<option value=\"' + key + '\">' + data[key]+ '</option>');
										}
									}
									filterGroups();
								}, "json");
					});
					$('select[name=groups]').live("change", function() {
						$('select[name=roles]').children().remove();
						$("#roles").append('<option value=\"0\">Select a Role</option>')
						var url = "<?php echo ENTRADA_URL . "/api/organisation-roles.api.php"; ?>";
						$.get(	url, { organisation_id: $('#organisations option:selected').val(), group_id: $(this).val() },
								function(data){
									for (var key in data) {
										if (data.hasOwnProperty(key)) {
										$("#roles").append('<option value=\"' + key + '\">' + data[key]+ '</option>');
										}
									}
								}, "json");
					});

					$('#organisations option:first').attr('selected', true).change();

					var permissions = {}; //JSON objects to hold array of JSON objects containing this new user's permissions
					permissions.acl = [];

					$("input[name=add_permissions]").live("click", function() {
						var group_id = $('#groups').val();
						var role_id = $('#roles').val();
						var org_id = $('#organisations').val();

						if (org_id == null || org_id == 0) {
							alert("Please select an organisation");
						} else if (group_id == null || group_id == 0) {
							alert("Please select a group.");
						} else if (role_id == null || role_id == 0) {
							alert("Please select a role.")
						} else {
							var group_text = $('#groups option[value=' + group_id + ']').text();
							$('#groups option[value=' + group_id + ']').remove();
							var role_text = $('#roles option[value=' + role_id + ']').text();
							$('select[name=roles]').children().remove();
							var options = "";
							var clinical = 0;
							var entry_year = 0;
							var grad_year = 0;
							if (group_text == "Faculty") {
								options = "<input id=\"clinical_"  + org_id + "\" name=\"clinical_"  + org_id + "\" type=\"checkbox\" checked /><label for=\"clincal" + org_id + "\">This new user is a <strong>clinical</strong> faculty member.</label>";
								clinical = 1;
							} else if (group_text == "Student") {
								options = $('#entry_grad_year_container').html();
								entry_year = $('#entry_year').val();
								grad_year = $('#grad_year').val();
							}
							$('#perm_organisation_' + $('#organisations').val() + ' > tbody:last').append('<tr id=\"' + org_id + '_' + group_id + '_' + role_id + '\"><td></td><td>' + group_text + " / " + role_text + '</td><td>' + options + '</td><td><a class=\"remove_perm\" href=\"\"><img src="<?php echo ENTRADA_URL; ?>/images/action-delete.gif"></a></td></tr>');
							$('#perm_organisation_' + $('#organisations').val()).show();

							var temp_permissions = {"org_id" : org_id, "group_id" : group_id, "role_id" : role_id, "clinical" : clinical, "entry_year" : entry_year, "grad_year" : grad_year};
							permissions.acl.push(temp_permissions);

							$('input[name=permissions]').val(JSON.stringify(permissions));
						}
					});

					$('input[id^=clinical_]').live("change", function() {
						var org_id = $(this).attr("id").split("_")[1];
						for (i = 0; i < permissions.acl.length; i++) {
							//check the permissions array for the faculty role for this org
							if (permissions.acl[i].org_id == org_id
								&& permissions.acl[i].group_id == 3) {
								if ($(this).is(':checked')) {
									permissions.acl[i].clinical = 1;
								} else {
									permissions.acl[i].clinical = 0;
								}
							}
						}
						$('input[name=permissions]').val(JSON.stringify(permissions));
					});

					$('select[id=entry_year]').live("change", function() {
						var org_id = $(this).closest('table').attr("id").split("_")[2]
						for (i = 0; i < permissions.acl.length; i++) {
							if (permissions.acl[i].org_id == org_id) {
								permissions.acl[i].entry_year = $(this).val();
							}
						}
						$('input[name=permissions]').val(JSON.stringify(permissions));
					});

					$('select[id=grad_year]').live("change", function() {
						var org_id = $(this).closest('table').attr("id").split("_")[2];
						for (i = 0; i < permissions.acl.length; i++) {
							if (permissions.acl[i].org_id == org_id) {
								permissions.acl[i].grad_year = $(this).val();
							}
						}
						$('input[name=permissions]').val(JSON.stringify(permissions));
					});

					$("a.remove_perm").live("click", function(e) {
						e.preventDefault();
						var row_id = $(this).closest("tr").attr("id");
						var org_id = row_id.split("_")[0];
						var group_id = row_id.split("_")[1];
						var role_id = row_id.split("_")[2];
						for(var i=0; i<permissions.acl.length; i++) {
							if (permissions.acl[i].org_id == org_id &&
								permissions.acl[i].group_id == group_id &&
								permissions.acl[i].role_id == role_id) {
								permissions.acl.splice(i, 1);
							}
						}
						$('input[name=permissions]').val(JSON.stringify(permissions));

						if (org_id == $('#organisations').val()) {
							//add the group back to the select list
							var group_role = $(this).closest("tr").children()[1];
							group_role = $(group_role).text();
							var group_text = $.trim(group_role.split("/")[0]);
							var option = $("<option></option>").text(group_text);
							$(option).attr("value", group_id);
							$('#groups').append(option);

							//now resort the select list by group title
							var my_options = $('#groups option');
							my_options.sort(function(a,b) {
								if (a.text > b.text) return 1;
								else if (a.text < b.text) return -1;
								else return 0
							});
						}

						var myTable = $(this).closest("table");
						$(this).closest("tr").remove();
						if ($(myTable)[0].rows.length <= 4) {
							$("#department_callback" + org_id).children().remove();
							$(myTable).hide();
							my_departments.list.splice(0,my_departments.dept_list.length);
							$('input[name=my_departments]').val(0);
							$('#departments_container_' + org_id).children().remove();
						}
					});

					function filterGroups() {
						if ($('#groups option').length > 0) {
							var current_org = $('#organisations').val();
							for (i = 0; i < permissions.acl.length; i++) {
								if (permissions.acl[i].org_id == current_org) {
									$('#groups option[value=' + permissions.acl[i].group_id + ']').remove();
								}
							}
							//now resort the option list
							var my_options = $('#groups option');
							my_options.sort(function(a,b) {
								if (a.text > b.text) return 1;
								else if (a.text < b.text) return -1;
								else return 0
							});
						}
					}
				});
				</script>
			<?php
		break;
	} //end display switch
} //end else
