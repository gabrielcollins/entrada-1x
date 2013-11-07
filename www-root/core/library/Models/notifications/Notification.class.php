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
 * This file contains all of the functions used within Entrada.
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 *
*/
require_once("Models/utility/SimpleCache.class.php");

/**
 * Class to model Notification instances including basic data and relationships to users/content
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2012 Queen's University. All Rights Reserved.
 */
class Notification {
	private $notification_id;
	private $nuser_id;
	private $notification_body;
	private $proxy_id;
	private $sent;
	private $digest;
	private $sent_date;

	function __construct(	$notification_id,
							$nuser_id,
							$notification_body,
							$proxy_id,
							$sent,
							$digest,
							$sent_date) {

		$this->notification_id = $notification_id;
		$this->nuser_id = $nuser_id;
		$this->notification_body = $notification_body;
		$this->proxy_id = $proxy_id;
		$this->sent = $sent;
		$this->digest = $digest;
		$this->sent_date = $sent_date;

		//be sure to cache this whenever created.
		$cache = SimpleCache::getCache();
		$cache->set($this,"Notification",$this->notification_id);
	}

	/**
	 * Returns the id of the notification
	 * @return int
	 */
	public function getID() {
		return $this->notification_id;
	}

	/**
	 * Returns the nuser_id of the `notification_user` record for the user who was/will be sent the notification
	 * @return int
	 */
	public function getNotificationUserID() {
		return $this->nuser_id;
	}

	/**
	 * Returns the body of the notification email
	 * @return string
	 */
	public function getNotificationBody() {
		return $this->notification_body;
	}

	/**
	 * Returns the proxy of the person who made the change/comment which the notification is regarding
	 * @return int
	 */
	public function getProxyID() {
		return $this->proxy_id;
	}

	/**
	 * Returns a boolean value which represents whether the notification has been emailed to the recipient or not.
	 * @return bool
	 */
	public function getSentStatus() {
		return (bool) $this->sent;
	}

	/**
	 * Returns a unix timestamp which represents when the notification was emailed to the recipient .
	 * @return bool
	 */
	public function getDigest() {
		return $this->digest;
	}

	/**
	 * Returns a unix timestamp which represents when the notification was emailed to the recipient .
	 * @return bool
	 */
	public function getSentDate() {
		return $this->sent_date;
	}

	/**
	 * Returns an Notification specified by the provided ID
	 * @param int $notification_id
	 * @return Notification
	 */
	public static function get($notification_id) {
		global $db;
		$cache = SimpleCache::getCache();
		$notification = $cache->get("Notification",$notification_id);
		if (!$notification) {
			$query = "SELECT * FROM `notifications` WHERE `notification_id` = ".$db->qstr($notification_id);
			$result = $db->getRow($query);
			if ($result) {
				$notification = self::fromArray($result);
			}
		}
		return $notification;
	}

	/**
	 * Returns an Notification specified by the provided ID
	 * @param int $notification_id
	 * @return Notification
	 */
	public static function getAllPending($nuser_id, $digest = false) {
		global $db;
		$query = "SELECT * FROM `notifications`
					WHERE `nuser_id` = ".$db->qstr($nuser_id)."
					AND `sent` = 0
					".($digest ? "AND `digest` = 1" : "");
		$results = $db->getAll($query);
		if ($results) {
			$notifications = array();
			foreach ($results as $result) {
				$notifications[] = self::fromArray($result);
			}
			return $notifications;
		}
		return false;
	}

	/**
	 * Creates a new notification and returns its id.
	 *
	 * @param int $nuser_id
	 * @param int $proxy_id
	 * @param int $record_id
	 * @return int $notification_id
	 */
	public static function add($nuser_id, $proxy_id, $record_id, $subcontent_id = 0) {
		global $db, $ENTRADA_TEMPLATE;

		$notification_user = NotificationUser::getByID($nuser_id);
		if ($notification_user) {
			if ($notification_user->getDigestMode()) {
				$notification_body = $notification_user->getContentBody($record_id);
				$sent = false;

				$new_notification = array(	"nuser_id" => $nuser_id,
											"notification_body" => $notification_body,
											"proxy_id" => $proxy_id,
											"sent" => 0,
											"digest" => 1,
											"sent_date" => 0);
				$db->AutoExecute("notifications", $new_notification, "INSERT");
				if (!($notification_id = $db->Insert_Id())) {
					application_log("error", "There was an issue attempting to add a notification record to the database. Database said: ".$db->ErrorMsg());
				} else {
					$new_notification["notification_id"] = $notification_id;
					$notification = self::fromArray($new_notification);
					$notification_user->setNextNotificationDate();
					return $notification;
				}
			} else {
				switch ($notification_user->getContentType()) {
					case "logbook_rotation" :
						$search = array("%AUTHOR_FULLNAME%",
										"%OWNER_FULLNAME%",
										"%ROTATION_NAME%",
										"%CONTENT_BODY%",
										"%URL%",
										"%UNSUBSCRIBE_URL%",
										"%APPLICATION_NAME%",
										"%ENTRADA_URL%");
						$replace = array(html_encode(get_account_data("wholename", $proxy_id)),
										html_encode(get_account_data("wholename", $notification_user->getRecordProxyID())),
										html_encode($notification_user->getContentTitle()),
										html_encode($notification_user->getContentBody($record_id)),
										html_encode($notification_user->getContentURL()),
										html_encode(ENTRADA_URL."/profile?section=notifications&id=".$nuser_id."&action=unsubscribe"),
										html_encode(APPLICATION_NAME),
										html_encode(ENTRADA_URL));
						$notification_body = file_get_contents($ENTRADA_TEMPLATE->absolute()."/email/notification-logbook-rotation-".($notification_user->getProxyID() == $notification_user->getRecordProxyID() ? "student" : "admin").".xml");
						$notification_body = str_replace($search, $replace, $notification_body);
					break;
					case "evaluation" :
					case "evaluation_overdue" :
                        $query = "SELECT * FROM `evaluations` AS a
                                    JOIN `evaluation_forms` AS b
                                    ON a.`eform_id` = b.`eform_id`
                                    JOIN `evaluations_lu_targets` AS c
                                    ON b.`target_id` = c.`target_id`
                                    WHERE a.`evaluation_id` = ".$db->qstr($record_id);
						$evaluation = $db->GetRow($query);
                        if ($evaluation) {
                            $search = array("%UC_CONTENT_TYPE_NAME%",
                                            "%CONTENT_TYPE_NAME%",
                                            "%CONTENT_TYPE_SHORTNAME%",
                                            "%UC_CONTENT_TYPE_SHORTNAME%",
                                            "%EVALUATOR_FULLNAME%",
                                            "%CONTENT_TITLE%",
                                            "%EVENT_TITLE%",
                                            "%CONTENT_BODY%",
                                            "%CONTENT_START%",
                                            "%CONTENT_FINISH%",
                                            "%CONTENT_LOCKOUT%",
                                            "%MANDATORY_STRING%",
                                            "%URL%",
                                            "%APPLICATION_NAME%",
                                            "%LOCKOUT_STRING%",
                                            "%ENTRADA_URL%");
                            if (strpos($notification_user->getContentTypeName(), "assessment") !== false) {
                                $content_type_shortname = "assessment";
                            } else {
                                $content_type_shortname = "evaluation";
                            }
                            if (array_search($evaluation["target_shortname"], array("preceptor", "rotation_core", "rotation_elective")) !== false && $subcontent_id) {
                                $query = "SELECT * FROM `".CLERKSHIP_DATABASE."`.`events` WHERE `event_id` = ".$db->qstr($subcontent_id);
                                $clerkship_event = $db->GetRow($query);
                                if ($clerkship_event) {
                                    if ($evaluation["target_shortname"] != "rotation_elective") {
                                        $evaluation["evaluation_start"] = ($clerkship_event["event_finish"] - (86400 * 5));
                                        $evaluation["evaluation_finish"] = $clerkship_event["event_finish"] + CLERKSHIP_EVALUATION_TIMEOUT;
                                    }
                                    $evaluation["evaluation_lockout"] = $clerkship_event["event_finish"] + CLERKSHIP_EVALUATION_LOCKOUT;
                                    $event_title = $clerkship_event["event_title"];
                                } else {
                                    $event_title = "";
                                    $evaluation["evaluation_lockout"] = $evaluation["evaluation_finish"];
                                }
                            } else {
                                $event_title = "";
                                $evaluation["evaluation_lockout"] = $evaluation["evaluation_finish"] + (defined('EVALUATION_LOCKOUT') && EVALUATION_LOCKOUT ? EVALUATION_LOCKOUT : 0);
                            }
                            $mandatory = $evaluation["evaluation_mandatory"];
                            $evaluation_start = date(DEFAULT_DATE_FORMAT, $evaluation["evaluation_start"]);
                            $evaluation_finish = date(DEFAULT_DATE_FORMAT, $evaluation["evaluation_finish"]);
                            $evaluation_lockout = date(DEFAULT_DATE_FORMAT, $evaluation["evaluation_lockout"]);
                            $organisation_id = get_account_data("organisation_id", $proxy_id);
                            $content_url = $notification_user->getContentURL();
                            $replace = array(	html_encode(ucwords($notification_user->getContentTypeName())),
                                                html_encode($notification_user->getContentTypeName()),
                                                html_encode($content_type_shortname),
                                                html_encode(ucfirst($content_type_shortname)),
                                                html_encode(get_account_data("wholename", $notification_user->getProxyID())),
                                                html_encode($notification_user->getContentTitle()),
                                                html_encode($event_title),
                                                html_encode($notification_user->getContentBody($record_id)),
                                                html_encode($evaluation_start),
                                                html_encode($evaluation_finish),
                                                html_encode($evaluation_lockout),
                                                html_encode((isset($mandatory) && $mandatory ? "mandatory" : "non-mandatory")),
                                                html_encode($content_url),
                                                html_encode(APPLICATION_NAME),
                                                html_encode((defined('EVALUATION_LOCKOUT') && EVALUATION_LOCKOUT ? "\nAccess to this evaluation will be closed as of ".$evaluation_lockout."." : "")),
                                                html_encode(ENTRADA_URL));
                            if ($evaluation["target_shortname"] == "rotation_core") {
                                $notification_body = file_get_contents($ENTRADA_TEMPLATE->absolute()."/email/notification-rotation-core-evaluation-".($evaluation["evaluation_finish"] >= time() || $evaluation["evaluation_start"] >= strtotime("-1 day") ? "release" : "overdue").".xml");
                            } elseif ($evaluation["target_shortname"] == "preceptor") {
                                $notification_body = file_get_contents($ENTRADA_TEMPLATE->absolute()."/email/notification-preceptor-evaluation-".($evaluation["evaluation_finish"] >= time() || $evaluation["evaluation_start"] >= strtotime("-1 day") ? "release" : "overdue").".xml");
                            } else {
                                $notification_body = file_get_contents($ENTRADA_TEMPLATE->absolute()."/email/notification-evaluation-".($evaluation["evaluation_finish"] >= time() || $evaluation["evaluation_start"] >= strtotime("-1 day") ? "release" : "overdue").".xml");
                            }
                            $notification_body = str_replace($search, $replace, $notification_body);
                        }
					break;
					case "evaluation_threshold" :
						$search = array("%UC_CONTENT_TYPE_NAME%",
										"%CONTENT_TYPE_NAME%",
										"%CONTENT_TYPE_SHORTNAME%",
										"%EVALUATOR_FULLNAME%",
										"%CONTENT_TITLE%",
										"%URL%",
										"%APPLICATION_NAME%",
										"%ENTRADA_URL%");
						if (strpos($notification_user->getContentTypeName(), "assessment") !== false) {
							$content_type_shortname = "assessment";
						} else {
							$content_type_shortname = "evaluation";
						}
						$evaluation = $db->GetRow("SELECT * FROM `evaluations` WHERE `evaluation_id` = ".$db->qstr($notification_user->getRecordID()));
						$evaluation_start = date(DEFAULT_DATE_FORMAT, $evaluation["evaluation_start"]);
						$evaluation_finish = date(DEFAULT_DATE_FORMAT, $evaluation["evaluation_finish"]);
						$replace = array(	html_encode(ucwords($notification_user->getContentTypeName())),
											html_encode($notification_user->getContentTypeName()),
											html_encode($content_type_shortname),
											html_encode(get_account_data("wholename", $proxy_id)),
											html_encode($notification_user->getContentTitle()),
											html_encode($notification_user->getContentURL()."&pid=".$record_id),
											html_encode(APPLICATION_NAME),
											html_encode(ENTRADA_URL));
						$notification_body = file_get_contents($ENTRADA_TEMPLATE->absolute()."/email/notification-evaluation-threshold.xml");
						$notification_body = str_replace($search, $replace, $notification_body);
					break;
					case "evaluation_request" :
						$search = array("%UC_CONTENT_TYPE_NAME%",
										"%CONTENT_TYPE_NAME%",
										"%CONTENT_TYPE_SHORTNAME%",
										"%TARGET_FULLNAME%",
										"%CONTENT_TITLE%",
										"%CONTENT_BODY%",
										"%URL%",
										"%APPLICATION_NAME%",
										"%ENTRADA_URL%");
						if (strpos($notification_user->getContentTypeName(), "assessment") !== false) {
							$content_type_shortname = "assessment";
						} else {
							$content_type_shortname = "evaluation";
						}
						$evaluation = $db->GetRow("SELECT * FROM `evaluations` WHERE `evaluation_id` = ".$db->qstr($notification_user->getRecordID()));
						$evaluation_start = date(DEFAULT_DATE_FORMAT, $evaluation["evaluation_start"]);
						$evaluation_finish = date(DEFAULT_DATE_FORMAT, $evaluation["evaluation_finish"]);
						$replace = array(	html_encode(ucwords($notification_user->getContentTypeName())),
											html_encode($notification_user->getContentTypeName()),
											html_encode($content_type_shortname),
											html_encode(get_account_data("wholename", $proxy_id)),
											html_encode($notification_user->getContentTitle()),
											html_encode($notification_user->getContentBody($record_id)),
											html_encode($notification_user->getContentURL()."&proxy_id=".$proxy_id),
											html_encode(APPLICATION_NAME),
											html_encode(ENTRADA_URL));
						$notification_body = file_get_contents($ENTRADA_TEMPLATE->absolute()."/email/notification-evaluation-request.xml");
						$notification_body = str_replace($search, $replace, $notification_body);
					break;
					default :
						$search = array("%UC_CONTENT_TYPE_NAME%",
										"%CONTENT_TYPE_NAME%",
										"%AUTHOR_FULLNAME%",
										"%CONTENT_TITLE%",
										"%CONTENT_BODY%",
										"%URL%",
										"%UNSUBSCRIBE_URL%",
										"%DIGEST_URL%",
										"%APPLICATION_NAME%",
										"%ENTRADA_URL%");

						$replace = array(	html_encode(ucwords($notification_user->getContentTypeName())),
											html_encode($notification_user->getContentTypeName()),
											html_encode(get_account_data("wholename", $proxy_id)),
											html_encode($notification_user->getContentTitle()),
											html_encode($notification_user->getContentBody($record_id)),
											html_encode($notification_user->getContentURL()),
											html_encode(ENTRADA_URL."/profile?section=notifications&id=".$nuser_id."&action=unsubscribe"),
											html_encode(ENTRADA_URL."/profile?section=notifications&id=".$nuser_id."&action=digest-mode"),
											html_encode(APPLICATION_NAME),
											html_encode(ENTRADA_URL));
						$notification_body = file_get_contents($ENTRADA_TEMPLATE->absolute()."/email/notification-default.xml");
						$notification_body = str_replace($search, $replace, $notification_body);
					break;
				}
				$new_notification = array(	"nuser_id" => $nuser_id,
											"notification_body" => $notification_body,
											"proxy_id" => $proxy_id,
											"sent" => false,
											"digest" => 0,
											"sent_date" => 0);
				$db->AutoExecute("notifications", $new_notification, "INSERT");
				if (!($notification_id = $db->Insert_Id())) {
					application_log("error", "There was an issue attempting to add a notification record to the database. Database said: ".$db->ErrorMsg());
				} else {
					$new_notification["notification_id"] = $notification_id;
					$notification = self::fromArray($new_notification);
					$notification_user->setNextNotificationDate();
					return $notification;
				}
			}
		}
		return false;
	}
	/**
	 * Creates a new notification and returns its id.
	 *
	 * @param int $nuser_id
	 * @return Notification
	 */
	public static function addDigest($nuser_id) {
		global $db, $ENTRADA_TEMPLATE;

		require_once("Models/utility/Template.class.php");

		$notification_user = NotificationUser::getByID($nuser_id);
		if ($notification_user) {
			$notifications = self::getAllPending($nuser_id, 1);
			$activity_count = count($notifications);
			if ($notifications && $activity_count) {
				$notification_template = file_get_contents($ENTRADA_TEMPLATE->absolute()."/email/notification-default-digest.xml");
				$search = array(	"%UC_CONTENT_TYPE_NAME%",
									"%CONTENT_TYPE_NAME%",
									"%COMMENTS_NUMBER_STRING%",
									"%CONTENT_TITLE%",
									"%URL%",
									"%UNSUBSCRIBE_URL%",
									"%APPLICATION_NAME%",
									"%ENTRADA_URL%");
				$replace = array(	html_encode(ucwords($notification_user->getContentTypeName())),
									html_encode($notification_user->getContentTypeName()),
									html_encode(($activity_count > 1 ? $activity_count." new comments have" : "A new comment has")),
									html_encode($notification_user->getContentTitle()),
									html_encode($notification_user->getContentURL()),
									html_encode(ENTRADA_URL."/profile?section=notifications&id=".$nuser_id."&action=unsubscribe"),
									html_encode(APPLICATION_NAME),
									html_encode(ENTRADA_URL));
				$notification_body = str_replace($search, $replace, $notification_template);
				$new_notification = array(	"nuser_id" => $nuser_id,
											"notification_body" => $notification_body,
											"proxy_id" => 0,
											"sent" => false,
											"sent_date" => 0,
											"digest" => 1);
				$db->AutoExecute("notifications", $new_notification, "INSERT");
				if (!($notification_id = $db->Insert_Id())) {
					application_log("error", "There was an issue attempting to add a notification record to the database. Database said: ".$db->ErrorMsg());
				} else {
					$new_notification["notification_id"] = $notification_id;
					foreach ($notifications as $processed_notification) {
						$processed_notification->setSentStatus(true);
					}
					$notification = self::fromArray($new_notification);
					$notification_user->setNextNotificationDate();
					return $notification;
				}
			}
		}
		return false;
	}

	static public function fromArray($array) {
		return new Notification($array["notification_id"], $array["nuser_id"], $array["notification_body"], $array["proxy_id"], $array["sent"], $array["digest"], $array["sent_date"]);
	}

	private function setSentStatus($sent) {
		global $db;
		if ($sent == $this->sent) {
			return false;
		} else {
			if (!$db->AutoExecute("notifications", array("sent" => $sent, "sent_date" => time()), "UPDATE", "`notification_id` = ".$db->qstr($this->notification_id))) {
				application_log("error", "There was an issue attempting to update the `sent` value for a notification record in the database. Database said: ".$db->ErrorMsg());
				return false;
			}
		}
		return true;
	}

	/**
	 * This function sends an email out to the user referenced by the notification_user record,
	 * and returns whether sending the email was successful or not.
	 * @return bool
	 */
	public function send() {
		global $db, $AGENT_CONTACTS;
		require_once("Models/utility/TemplateMailer.class.php");
		$query = "SELECT a.`proxy_id`, b.`firstname`, b.`lastname`, b.`email`, a.`content_type`, a.`record_id`, a.`record_proxy_id` FROM `notification_users` AS a
					JOIN `".AUTH_DATABASE."`.`user_data` AS b
					ON a.`proxy_id` = b.`id`
					WHERE a.`nuser_id` = ".$db->qstr($this->nuser_id);
		$user = $db->GetRow($query);
		if ($user) {
			$template = new Template();
			$template->loadString($this->notification_body);
			$mail = new TemplateMailer(new Zend_Mail());
			$mail->addHeader("X-Section", APPLICATION_NAME." Notifications System", true);

			$from = array("email"=>$AGENT_CONTACTS["agent-notifications"]["email"], "firstname"=> APPLICATION_NAME." Notification System","lastname"=>"");
			$to = array("email"=>$user["email"], "firstname"=> $user["firstname"],"lastname"=> $user["lastname"]);

                        try{
                            $mail->send($template,$to,$from,DEFAULT_LANGUAGE);
                            if ($this->setSentStatus(true)) {
				application_log("success", "A [".$user["content_type"]."] notification has been sent to a user [".$user["proxy_id"]."] successfully.");
				return true;
                            }
                        } catch (Zend_Mail_Transport_Exception $e) {
                            system_log_data("error", "Unable to send [".$user["content_type"]."] notification to user [".$user["proxy_id"]."]. Template Mailer said: ".$e->getMessage());
                        }
		}

		return false;
	}
}