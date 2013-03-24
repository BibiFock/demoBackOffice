<?php
namespace DemoBackOffice\Model{

	class Section{

		public $id, $name, $update, $content;

		public function __construct($id, $name, $update, $content){
			$this->id = $id;
			$this->name = $name; 
			$this->update = $update;
			$this->content = $content;
		}

	}

}
