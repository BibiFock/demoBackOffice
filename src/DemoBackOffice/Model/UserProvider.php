<?php
namespace DemoBackOffice\Model{

	use Silex\Application;
	use Symfony\Component\Security\Core\User\UserProviderInterface;
	use Symfony\Component\Security\Core\User\UserInterface;
	use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
	use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
	use DemoBackOffice\Model\Entity\UserType;
	use DemoBackOffice\Model\Entity\User;
	use Exception;

	class UserProvider implements UserProviderInterface{

		protected $app;

		public function __construct(Application $app){
			$this->app = $app;
		}

		public function deleteUser(User $user){
			if($user->isSuperAdmin()) throw new Exception('You cannot delete root account');
			$stmt = $this->app['db']->executeQuery("delete from user where id_user=?", array( $user->id ));
		}

		protected function searchUser($by, $value){
			$sql = 'SELECT id_user, login_user, password_user, id_type_user, date_modification_user from user where ';
			if($by == "id") $sql .= " id_user=?";
			else $sql .= " login_user=?";
			$stmt = $this->app['db']->executeQuery( $sql, array($value));
			if (!$user = $stmt->fetch()) return new User("", "", "", null, "" );
			$userType = $this->app['manager.rights']->getUserTypeById($user['id_type_user']);
			return new User($user['id_user'], $user['login_user'], $user['password_user'], $userType, $user['date_modification_user']);
		}

		public function getUserById($id){
			return $this->searchUser('id', $id);
		}

		public function getUserByName($name){
			return $this->searchUser('name', $name);
		}

		public function saveUser($login, $password, $userType, $new = false){
			$user = $this->getUserByName($login);
			$user->update = date('Y-m-d H:i:s');
			$params = array($login, $password, $user->update, $userType );
			if($user->id != ''){
				if($new) throw new Exception('Username already used');
				$sql = "update user set login_user=?, password_user=?, date_modification_user=?, id_type_user=? where id_user=?";
				$params[] = $user->id;
			}else{
				array_unshift( $params, $this->update);
				$sql = "insert into user(date_creation_user, login_user, password_user, date_modification_user, id_type_user) VALUES(?,?,?,?,?)";
			}
			$this->app['db']->executeQuery($sql, $params);
			return $this->getUserByName($login);
		}

		/**
		 *	return a type_user list
		 */
		public function loadUsers(){
			$stmt = $this->app['db']->executequery('SELECT id_user, login_user, password_user, id_type_user, date_modification_user from user');
			if (!$users = $stmt->fetchall()) return array(); 
			for ($i = 0; $i < count($users); $i++) {
				$user = $users[$i];
				$userType = $this->app['manager.rights']->getUserTypeById($user['id_type_user']);
				$users[$i] = new User($user['id_user'], $user['login_user'], $user['password_user'], $userType, $user['date_modification_user']);
			}
			return $users;
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
		/** UserProviderInterface * */
		public function loadUserByUsername($username){
			$user =  $this->searchUser('name', $username);
			if ($user->id == null) {
				throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
			}
			return $user;
		}
	

		function refreshUser(UserInterface $user) {
			if (!$user instanceof User) {
				throw new UnsupportedUserException(sprintf('Instance of "%s" are not supported'), get_class($user));
			}
			return $this->loadUserByUsername($user->getUsername());
		}

		function supportsClass($class) {
			//return $class === 'Symfony\Component\Security\Core\User\User';
			return $class === 'DemoBackOffice\Model\Entity\User';
		}

	}

}
