<?php    
namespace DemoBackOffice\Model\Entity{
	use Symfony\Component\Security\Core\User\UserInterface;

	class User implements UserInterface
	{
		private $username;
		private $password;
		private $roles;

		public function __construct($username, $password,  array $roles)
		{
			$this->username = $username;
			$this->password = $password;
			$this->roles = $roles;
		}

		public function isAdmin(){
			return true;
		}

		public function getRoles()
		{
			return $this->roles;
		}

		public function getPassword()
		{
			return $this->password;
		}

		public function getSalt()
		{
			return null;
		}

		public function getUsername()
		{
			return $this->username;
		}

		public function eraseCredentials()
		{
		}

		public function equals(UserInterface $user)
		{
			if (!$user instanceof User) {
				return false;
			}

			if ($this->password !== $user->getPassword()) {
				return false;
			}

			if ($this->getSalt() !== $user->getSalt()) {
				return false;
			}

			if ($this->username !== $user->getUsername()) {
				return false;
			}

			return true;
		}
	}
}
