<?php
namespace DemoBackOffice\Model\Entity{

	class Section{

		public $id, $name, $update, $content, $status;

		public function __construct($id, $name, $update, $content, $status){
			$this->id = $id;
			$this->name = $name; 
			$this->update = $update;
			$this->content = $content;
			$this->status = $status;
		}

		public function isAdminSection(){
			return ($this->status == 2);
		}

	}

}
