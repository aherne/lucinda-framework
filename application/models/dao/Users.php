<?php
require_once("entities/User.php");

class Users  {
	/**
	 * Creates/Updates user.
	 *
	 * @param User $user
	 */
	public function add(User $user) {
		// insert user
		DB::execute("
				INSERT INTO users (id, email, password, name)
				VALUES (:id, :email, :password, :name)
				ON DUPLICATE KEY UPDATE password=:password, name=:name
			",array(
					":id"=>$user->id,
					":email"=>$user->email,
					":password"=>$user->password,
					":name"=>$user->name
			));

		// empty rights
		DB::execute("DELETE FROM users_departments WHERE user_id=:user_id",array(":user_id"=>$user->id));

		// add rights
		foreach($user->rights as $right) {
			DB::execute(
					"INSERT INTO users_departments (user_id, department_id, level_id, group_id) VALUES (:user_id, :department_id, :level_id, :group_id)",
					array(
							":user_id"=>$user->id,
							":department_id"=>$right->department->id,
							":level_id"=>$right->level->id,
							":group_id"=>$right->group
					));
		}
	}

	/**
	 * Removes all users not in list.
	 *
	 * @param integer[] $ids List of user ids.
	 */
	public function removeAllBut($ids) {
		DB::execute("DELETE FROM users WHERE id NOT IN (".implode(",",$ids).")");
	}

	/**
	 * Gets aggregate information about users and departments.
	 *
	 * @return array Associative array with key = user id and value = array with key='user' and value = User struct; key = 'departments' value=array(department_id:level_id)
	 */
	public function getAllDetailed() {
		$tmp = DB::execute("
        SELECT t1.*, t2.department_id, t2.level_id, t2.group_id FROM users AS t1
        INNER JOIN users_departments AS t2 ON t1.id = t2.user_id
        ORDER BY t1.name ASC")->toList();
		$output = array();
		foreach($tmp as $item) {
			if(!isset($output[$item["id"]])) {
				$object = new User();
				$object->id = $item["id"];
				$object->name = $item["name"];
				$object->email = $item["email"];
				$output[$item["id"]] = array(
						"user"=>$object,
						"departments"=>array($item["department_id"]=>array(
								"level"=>$item["level_id"],
								"group"=>$item["group_id"]
						))
				);
			} else {
				$output[$item["id"]]["departments"][$item["department_id"]]=array(
						"level"=>$item["level_id"],
						"group"=>$item["group_id"]
				);
			}
		}
		return $output;
	}

	/**
	 * Gets user info by id.
	 *
	 * @param integer $id
	 * @return User
	 */
	public function getInfo($id) {
		$tmp = DB::execute("SELECT * FROM users WHERE id=:id",array(":id"=>$id))->toRow();
		$object = new User();
		$object->id = $tmp["id"];
		$object->name = $tmp["name"];
		$object->email = $tmp["email"];

		return $object;
	}

	/**
	 * Logs in user.
	 *
	 * @param string $email
	 * @param string $password
	 * @return NULL|User
	 */
	public function login($email, $password) {
		$tmp = DB::execute("SELECT * FROM users WHERE email=:email AND password=:password",array(":email"=>$email,":password"=>md5($password)))->toRow();
		if(!$tmp) return null;

		$object = new User();
		$object->id = $tmp["id"];
		$object->name = $tmp["name"];
		$object->email = $tmp["email"];

		return $object;
	}

	/**
	 * Checks if resource is allowed for user.
	 *
	 * @param integer $userID
	 * @param integer $resourceID
	 * @return boolean
	 */
	public function isAllowed($userID, $resourceID) {
		$results = DB::execute("
            SELECT
            t1.resource_id, t3.priority AS resource_priority, t4.priority AS user_priority
            FROM rights AS t1
            INNER JOIN users_departments AS t2 ON t2.user_id=:user_id AND t1.department_id=t2.department_id
            INNER JOIN levels AS t3 ON t1.level_id=t3.id
            INNER JOIN levels AS t4 ON t2.level_id=t4.id
            WHERE t1.resource_id = :resource_id
            ",array(":user_id"=>$userID,":resource_id"=>$resourceID))->toList();
		$output = array();
		foreach($results as $item) {
			if($item["user_priority"] >= $item["resource_priority"]) {
				$output[$item["resource_id"]] = $item["resource_id"];
			} else {
				if(isset($output[$item["resource_id"]])) {
					unset($output[$item["resource_id"]]);
				}
			}
		}
		return (empty($output)?false:true);
	}

	/**
	 * Checks if user belongs to department & level or above
	 *
	 * @param integer $userID
	 * @param string $department
	 * @param string $level
	 *
	 * @return boolean
	 */
	public function belongsTo($userID, $department, $level) {
		$result = DB::execute("
            SELECT
    			t1.id
            FROM users_departments AS t1
            INNER JOIN departments AS t2 ON t1.department_id=t2.id
            WHERE t1.user_id = :user_id AND t2.name=:department AND t1.level_id >= (SELECT id FROM levels WHERE name=:level)
            ",array(":user_id"=>$userID, ":department"=>$department, ":level"=>$level))->toValue();
		return (empty($result)?false:true);
	}
}