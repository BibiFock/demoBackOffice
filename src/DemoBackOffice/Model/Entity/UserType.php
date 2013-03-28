<?php
namespace DemoBackOffice\Model\Entity{

	class UserType{

		private $sections;
		public $id, $name, $description, $update;

		public function __construct($id, $name, $description, $update){
			$this->id = $id;
			$this->name = $name; 
			$this->description = $description; 
			$this->update = $update; 
			$this->sections = array();
		}

		public function purgeAccess(){
			$this->sections = array();
		}

		public function addAccess($sectionId, AccessType $accessType){
			$this->sections[$sectionId] = $accessType;
		}

		public function getAccessToSection($sectionId){
			if( isset($this->sections[$sectionId])) $accessType = $this->sections[$sectionId];
			else $accessType = new AccessType(AccessType::$FORBIDDEN);
			if($this->isSuperAdmin() ) $accessType->setAdminMode();
			return $accessType;
		}
		
		public function isSuperAdmin(){
			return ($this->id == 1);
		}

	}

}
