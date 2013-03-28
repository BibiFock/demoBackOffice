<?php    
namespace DemoBackOffice\Model\Entity{
	use Symfony\Component\Security\Core\User\UserInterface;
	use DemoBackOffice\Model\Entity\UserType;

	class User implements UserInterface{

		public $id, $username, $password, $type, $update;
		private $sections;

		/**
		 * constructor
		 * @param	string	id
		 * @param	string	username
		 * @param	string	password
		 * @param	object	type
		 * @param	string	update
		 * @param	array(object)	section
		 */
		public function __construct($id, $username, $password, $type, $update, $sections = array() ){
			$this->id = $id;
			$this->username = $username;
			$this->password = $password;
			$this->type = $type;
			$this->update = $update;
			$this->sections = $sections;
		}

		/**
		 * return a acces to a section by is name
		 * @param sectionName
		 * @return AccessType
		 */
		public function getAccessBySectionName($sectionName){
			//if user is super admin always return access
			if($this->isSuperAdmin()) return new AccessType(AccessType::$EDIT);
			//for othe users
			for ($i = 0; $i < count($this->sections); $i++) {
				if($this->sections[$i]->name == $sectionName){
					return $this->getSectionAccess($this->sections[$i]);
				}
			}
			return new AccessType(AccessType::$FORBIDDEN);
		}
		
		/**
		 * get admin sections, that user can read
		 * @param bool	isAdmin
		 * @return	Section section
		 */	
		protected function getSections($isAdmin = true){
			for ($i = 0, $return =  array(); $i < count($this->sections); $i++) {
				 if($this->canRead($this->sections[$i]) && $this->sections[$i]->isAdminSection() == $isAdmin) $return[] = $this->sections[$i];
			}
			return $return;
		}

		/**
		 *
		 * @param Section section
		 * @return AccessType
		 */
		protected function getSectionAccess(Section $section){
			return $this->type->getAccessToSection($section->id);
		}

		/**
		 * check if user can read a section
		 * @param section section
		 * @return	bool
		 */
		public function canRead(Section $section){
			return ($this->isSuperAdmin() || $this->getSectionAccess($section)->canRead());
		}

		/**
		 * @return array of section user, that user can read or edit
		 */
		public function getUserSections(){
			return $this->getSections(false);
		}

		/**
		 * @return array of section admin, that user can read or edit
		 */
		public function getAdminSections(){
			return $this->getSections(true);
		}

		/**
		 * load some sections
		 * @param array<Section>
		 */
		public function loadSections($sections){
			if(is_array($sections)){
				$this->sections = $sections;
			}
		}

		/**
		 * @return bool
		 */
		public function isSuperAdmin(){
			return $this->id == 1;
		}

		/** User Interface implementation usefull for login **/
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
