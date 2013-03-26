<?php
namespace DemoBackOffice\Model\Entity{

	class AccessType{

		public static $FORBIDDEN = 1;
		public static $READONLY = 1;
		public static $EDIT = 1;
		public $type;

		public function __construct($type){
			$this->type = $type;
		}

		public function canRead(){
			return ($this->type >= self::$READONLY);
		}

		public function canEdit(){
			return ($this->type >= self::$EDIT);
		}

		public function setAdminMode(){
			$this->type = self::$EDIT;
		}
	}

}
