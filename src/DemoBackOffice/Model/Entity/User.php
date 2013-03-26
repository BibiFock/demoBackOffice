<?php    
namespace DemoBackOffice\Model\Entity{
	use Symfony\Component\Security\Core\User\UserInterface;
	use DemoBackOffice\Model\Entity\UserType;

	class User implements UserInterface
	{
		public $id, $username, $password, $type, $update;

		public function __construct($id, $username, $password, $type, $update)
		{
			$this->id = $id;
			$this->username = $username;
			$this->password = $password;
			$this->type = $type;
			$this->update = $update;
		}

		public function isSuperAdmin(){
			return $this->id == 1;
		}

		public function getRoles()
		{
			return array('ROLE_ADMIN');
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
