<?php
namespace DemoBackOffice\Model\Entity{

	class Access{

		public $id, $name, $description;
		//1 read only
		//2 manage
		public function __construct($id, $name, $description){
			$this->id = $id;
			$this->name = $name; 
		}

		public function canManage(){
			return($this->id == 2)
		}

		public function canRead(){
			//if the group isn't a super admin
			return ($this->id != 1); 
		}
	}

}
