<?php
namespace DemoBackOffice\Model\Entity{

	class UserType{

		private $sections;
		public $id, $name, $description, $update;

		/**
		 * @param	string	id
		 * @param	string	name
		 * @param	string description
		 * @param	string update
		 */
		public function __construct($id, $name, $description, $update){
			$this->id = $id;
			$this->name = $name; 
			$this->description = $description; 
			$this->update = $update; 
			$this->sections = array();
		}

		/**
		 * purge current right access
		 */
		public function purgeAccess(){
			$this->sections = array();
		}

		/**
		 * add access in right
		 * @param AccessType
		 */
		public function addAccess($sectionId, AccessType $accessType){
			$this->sections[$sectionId] = $accessType;
		}

		/**
		 * Get the right access to a section
		 * @param	string SectionId
		 * @return AccessType
		 */
		public function getAccessToSection($sectionId){
			if( isset($this->sections[$sectionId])) $accessType = $this->sections[$sectionId];
			else $accessType = new AccessType(AccessType::$FORBIDDEN);
			if($this->isSuperAdmin() ) $accessType->setAdminMode();
			return $accessType;
		}
		
		/**
		 *@return bool true if this is the admin role
		 */
		public function isSuperAdmin(){
			return ($this->id == 1);
		}

	}

}
