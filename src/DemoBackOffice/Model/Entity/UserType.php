<?php
namespace DemoBackOffice\Model\Entity{

	class UserType{

		public $id, $name, $description, $update;

		public function __construct($id, $name, $description, $update){
			$this->id = $id;
			$this->name = $name; 
			$this->description = $description; 
			$this->update = $update; 
		}

		public function isSuperAdmin(){
			return ($this->id == 1);
		}

		public function canDelete(){
			//if the group isn't a super admin
			return ($this->id > 2); 
		}
	}

}
