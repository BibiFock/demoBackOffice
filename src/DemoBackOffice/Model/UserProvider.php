<?php
namespace DemoBackOffice\Model{

	use Silex\Application;
	use Symfony\Component\Security\Core\User\UserProviderInterface;
	use Symfony\Component\Security\Core\User\UserInterface;
	//use Symfony\Component\Security\Core\User\User;
	use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
	use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
	use Exception;
	use DemoBackOffice\Model\Entity\User;

	class UserProvider implements UserProviderInterface{

		protected $app;

		public function __construct(Application $app){
			$this->app = $app;
		}

		/** UserProviderInterface * */
		public function loadUserByUsername($username){
			$stmt = $this->app['db']->executeQuery('SELECT id_user, login_user, password_user, id_type_user FROM user WHERE login_user = ? ', array(strtolower($username)));
			if (!$user = $stmt->fetch()) {
				throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
			}

			return new User($user['login_user'], $user['password_user'], array('ROLE_ADMIN'));
			//return new User($user['login_user'], $user['password_user'], array('ROLE_ADMIN'), true, true, true, true);
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
