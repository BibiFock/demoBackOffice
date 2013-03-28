<?php    
namespace DemoBackOffice\Model\Entity{
	use Symfony\Component\Security\Core\User\UserInterface;
	use DemoBackOffice\Model\Entity\UserType;

	class User implements UserInterface{

		public $id, $username, $password, $type, $update;
		private $sections;

		public function __construct($id, $username, $password, $type, $update, $sections = array() ){
			$this->id = $id;
			$this->username = $username;
			$this->password = $password;
			$this->type = $type;
			$this->update = $update;
			$this->sections = $sections;
		}

		public function getAccessBySectionName($sectionName){
			if($this->isSuperAdmin()) return new AccessType(AccessType::$EDIT);
			for ($i = 0; $i < count($this->sections); $i++) {
				if($this->sections[$i]->name == $sectionName){
					return $this->getSectionAccess($this->sections[$i]);
				}
			}
			return new AccessType(AccessType::$FORBIDDEN);
		}
				
		protected function getSections($isAdmin = true){
			for ($i = 0, $return =  array(); $i < count($this->sections); $i++) {
				 if($this->canRead($this->sections[$i]) && $this->sections[$i]->isAdminSection() == $isAdmin) $return[] = $this->sections[$i];
			}
			return $return;
		}

		protected function getSectionAccess(Section $section){
			return $this->type->getAccessToSection($section->id);
		}

		public function canRead(Section $section){
			return ($this->isSuperAdmin() || $this->getSectionAccess($section)->canRead());
		}

		public function getUserSections(){
			return $this->getSections(false);
		}

		public function getAdminSections(){
			return $this->getSections(true);
		}

		public function loadSections($sections){
			if(is_array($sections)){
				$this->sections = $sections;
			}
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
