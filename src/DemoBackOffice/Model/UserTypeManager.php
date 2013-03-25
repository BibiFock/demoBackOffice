<?php
namespace DemoBackOffice\Model{

	use Silex\Application;
	use Doctrine\DBAL\Connection;
	use DemoBackOffice\Model\Entity\UserType;
	use Exception;

	class UserTypeManager{

		protected $db;

		public function __construct(Connection $connection){
			$this->db = $connection;
		}

		public function deleteUserType(UserType $userType){
			if($userType->canDelete()){
				$stmt = $this->db->executeQuery("delete from type_user where id_type_user=?", array( $userType->id));
			}
		}

		/**
		 * @throw exception if type_user asked not found
		 */
		private function searchUserType($by, $value){
			$sql = "select id_type_user, type_user, DATE_FORMAT( date_modification_type_user,  '%Y-%m-%d %h:%i:%s' ) date, description_type_user FROM type_user where";
			if($by == "id") $sql .= " id_type_user=?";
			else if($by == "name") $sql .= " type_user=?";
			else throw new Exception("Bad search parameters");
			$stmt = $this->db->executeQuery($sql, array($value));
			if(!$typeUser = $stmt->fetch()) return new UserType( "", "", "", "");
			return new UserType($typeUser['id_type_user'], $typeUser['type_user'], $typeUser['description_type_user'], $typeUser['date']);
		}

		public function getUserTypeById($id){
			return $this->searchUserType('id', $id);
		}

		public function getUserTypeByName($name){
			return $this->searchUserType('name', $name);
		}

		public function saveUserType($name, $description, $new = false){
			$typeUser = $this->getUserTypeByName($name);
			$typeUser->name = $name;
			$typeUser->description = $description;
			$typeUser->update = date('Y-m-d H:i:s');
			if($typeUser->id != ''){
				if($new) throw new Exception('Type user name already used');
				$sql = <<<SQL
update type_user 
set type_user=?, date_modification_type_user=?, description_type_user=?
where id_type_user=?
SQL;
				$params = array( $typeUser->name, $typeUser->update, $typeUser->description, $typeUser->id);
			}else{
				$sql = <<<SQL
insert into type_user (type_user, date_creation_type_user, date_modification_type_user, description_type_user) 
values (?, NOW(), NOW(), ?)
SQL;
				$params = array( $name, $description);
			}
			$this->db->executequery($sql, $params);
			if($typeUser == null) $typeUser = $this->getUserTypeByName($name);
			return $typeUser; 
		}

		/**
		 *	return a type_user list
		 */
		public function loadUserTypes(){
			$stmt = $this->db->executequery('select id_type_user, type_user, date_modification_type_user, description_type_user from type_user order by type_user asc');
			if (!$typeUsers = $stmt->fetchall())  return array(); 
			for ($i = 0; $i < count($typeUsers); $i++) {
				$typeUsers[$i] = new UserType($typeUsers[$i]['id_type_user'], $typeUsers[$i]['type_user'], $typeUsers[$i]['description_type_user'], $typeUsers[$i]['date_modification_type_user']);
			}
			return $typeUsers;
		}

	}

}
