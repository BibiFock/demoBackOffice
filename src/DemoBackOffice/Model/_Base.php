<?php
namespace DemoBackOffice\Model{

	class Base{

		public function __set($name){
			if(isset($this->$name)) return $this->$name;
			return null;
		}

		public function __set($name, $value){
			if(isset($this->$name)) $this->$name = $value;
		}

		public function getProperty($name){
			$this->__get($name);
		}
	}

}
