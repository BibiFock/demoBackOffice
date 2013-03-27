<?php
namespace DemoBackOffice\Model{

	use Silex\Application;
	use Doctrine\DBAL\Connection;
	use DemoBackOffice\Model\Entity\UserType;
	use DemoBackOffice\Model\Entity\Section;
	use DemoBackOffice\Model\Entity\AccessType;
	use Exception;

	class UserTypeManager{

		protected $db;

		public function __construct(Connection $connection){
			$this->db = $connection;
		}

		public function deleteUserType(UserType $userType){
			if(!$userType->isSuperAdmin()){
				//TODO check if user have right yet
				$stmt = $this->db->executeQuery("delete from type_user where id_type_user=?", array( $userType->id));
			}else throw new Exception('You can\'t delete this right');
		}

		private function getUserTypeAccess(UserType $userType){
			$stmt =$this->db->executeQuery("SELECT id_type_access, id_section from access where id_type_user=?", array($userType->id));
			if (!$accessType = $stmt->fetchall())  return array();
		   return $accessType;	
		}

		/**
		 * load all userType access
		 */
		protected function loadUserTypeAccess(UserType $userType){
			$userType->purgeAccess();
			if($userType->id > 0){
				$accessType = $this->getUserTypeAccess($userType);
				for ($i = 0; $i < count($accessType); $i++) {
					$userType->addAccess($accessType[$i]['id_section'], new AccessType($accessType[$i]['id_type_access']));
				}
			}
			return $userType;
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
			if(!$userType = $stmt->fetch()) return new UserType( "", "", "", "");
			$user = new UserType($userType['id_type_user'], $userType['type_user'], $userType['description_type_user'], $userType['date']);
			return $this->loadUserTypeAccess($user);
		}

		public function getUserTypeById($id){
			return $this->searchUserType('id', $id);
		}

		public function getUserTypeByName($name){
			return $this->searchUserType('name', $name);
		}

		protected function saveUserTypeAccess(UserType $userType, $access){
			if(count($access) > 0){
				$rows = $this->getUserTypeAccess($userType);
				for($i = 0, $oldUserType = array(); $i < count($rows); $i++) {
					$oldUserType[$rows[$i]['id_section']] = $rows[$i]['id_type_access'];
				}
				$dateMaj = date('Y-m-d H:i:s');
				foreach($access as $sectionId => $accessType){
					//minimum right is to read
					if($accessType->canRead()){
						$params = array($accessType->type, date('Y-m-d H:i:s'), $sectionId, $userType->id);
						if(!isset($oldUserType[$sectionId])){
							$sql = "insert into access(date_creation_access, id_type_access, date_modification_access, id_section, id_type_user ) values (?,?,?,?,?)";
							array_unshift($params, date('Y-m-d H:i:s'));
						}else{
							$sql = "update access set id_type_access=?, date_modification_access=? where id_section=? and id_type_user=?";
						}
						$this->db->executeQuery($sql, $params);
					}	
				}
				$this->db->executeQuery( "delete from access where date_modification_access < ?", array($dateMaj));
			}
			$userType = $this->loadUserTypeAccess($userType);
			return $userType;
		}

		public function saveUserType($id, $name, $description, $access, $new = false){
			$userType = $this->getUserTypeByName($name);
			$userType->name = $name;
			$userType->description = $description;
			$userType->update = date('Y-m-d H:i:s');
			if($userType->id != '' || $id != ''){
				if($new || ($userType-> id != '' && $userType->id != $id)) throw new Exception('Name:"'.$name.'" already used for another right');
				$userType->id = $id;
				if($userType->isSuperAdmin()) throw new Exception('This is admin right it cannot be updated');
				$sql = <<<SQL
update type_user 
set type_user=?, date_modification_type_user=?, description_type_user=?
where id_type_user=?
SQL;
				$params = array( $userType->name, $userType->update, $userType->description, $userType->id);
			}else{
				$sql = <<<SQL
insert into type_user (type_user, date_creation_type_user, date_modification_type_user, description_type_user) 
values (?, NOW(), NOW(), ?)
SQL;
				$params = array( $name, $description);
			}
			$this->db->executequery($sql, $params);
			if($userType == null) $userType = $this->getUserTypeByName($name);
			return $this->saveUserTypeAccess( $userType, $access); 
		}

		/**
		 *	return a type_user list
		 */
		public function loadUserTypes(){
			$stmt = $this->db->executequery('select id_type_user, type_user, date_modification_type_user, description_type_user from type_user order by type_user asc');
			if (!$userTypes = $stmt->fetchall())  return array(); 
			for ($i = 0; $i < count($userTypes); $i++) {
				$userTypes[$i] = new UserType($userTypes[$i]['id_type_user'], $userTypes[$i]['type_user'], $userTypes[$i]['description_type_user'], $userTypes[$i]['date_modification_type_user']);
			}
			return $userTypes;
		}

	}

}
