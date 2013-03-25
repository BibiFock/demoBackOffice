<?php
namespace DemoBackOffice\Model{

	use Silex\Application;
	use Doctrine\DBAL\Connection;
	use DemoBackOffice\Model\Entity\Section;
	use Exception;

	class SectionManager{

		protected $db;

		public function __construct(Connection $connection){
			$this->db = $connection;
		}

		public function deleteSection(Section $section){
			$stmt = $this->db->executeQuery("delete from section where id_section=?", array( $section->id));
		}

		/**
		 * @throw exception if section asked not found
		 */
		private function searchSection($by, $value){
			$sql = "select id_section, name_section, DATE_FORMAT( date_modification_section,  '%Y-%m-%d %h:%i:%s' ) date, content_section FROM section where";
			if($by == "id") $sql .= " id_section=?";
			else if($by == "name") $sql .= " name_section=?";
			else throw new Exception("Bad search parameters");
			$stmt = $this->db->executeQuery($sql, array($value));
			if(!$section = $stmt->fetch()) return new Section( "", "", "", "");
			return new Section($section['id_section'], $section['name_section'], $section['date'], $section['content_section']);
		}

		public function getSectionById($id){
			return $this->searchSection('id', $id);
		}

		public function getSectionByName($name){
			return $this->searchSection('name', $name);
		}

		public function saveSection($name, $content, $new = false){
			$section = $this->getSectionByName($name);
			$section->name = $name;
			$section->content = $content;
			$section->update = date('Y-m-d H:i:s');
			if($section->id != ''){
				if($new) throw new Exception('Section name already used');
				$sql = <<<SQL
update section 
set name_section=?, date_modification_section=?, content_section=?
where id_section=?
SQL;
				$params = array( $section->name, $section->update, $section->content, $section->id);
			}else{
				$sql = <<<SQL
insert into section (name_section, date_creation_section, date_modification_section, content_section) 
values (?, NOW(), NOW(), ? )
SQL;
				$params = array( $name, $content);
			}
			$this->db->executequery($sql, $params);
			if($section == null) $section = $this->getSectionByName($name);
			return $section; 
		}

		/**
		 *	return a section list
		 */
		public function loadsections(){
			$stmt = $this->db->executequery('select id_section, name_section, date_modification_section, content_section from section order by name_section asc');
			if (!$sections = $stmt->fetchall())  return array(); 
			for ($i = 0; $i < count($sections); $i++) {
				$sections[$i] = new Section($sections[$i]['id_section'], $sections[$i]['name_section'], $sections[$i]['date_modification_section'], $sections[$i]['content_section']);
			}
			return $sections;
		}

	}

}
