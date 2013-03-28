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
			if(!$section->isAdminSection()){
				$stmt = $this->db->executeQuery("delete from section where id_section=?", array( $section->id));
			}else throw new Exception("You cannot delete this section");
		}

		/**
		 * @throw exception if section asked not found
		 */
		private function searchSection($by, $value){
			$sql = "select id_section, name_section, DATE_FORMAT( date_modification_section,  '%Y-%m-%d %h:%i:%s' ) date, content_section, id_status_section FROM section where";
			if($by == "id") $sql .= " id_section=?";
			else if($by == "name") $sql .= " name_section=?";
			else throw new Exception("Bad search parameters");
			$stmt = $this->db->executeQuery($sql, array($value));
			if(!$section = $stmt->fetch()) return new Section( "", "", "", "", "");
			return new Section($section['id_section'], $section['name_section'], $section['date'], $section['content_section'], $section['id_status_section']);
		}

		public function getSectionById($id){
			return $this->searchSection('id', $id);
		}

		public function getSectionByName($name){
			return $this->searchSection('name', $name);
		}

		public function saveSectionContent($id, $content){
			$section = $this->getSectionById($id);
			if($section->id != ''){
				$this->db->executeQuery('update section set content_section=? where id_section=?', array($content, $id));
			}
			return $section;
		}

		public function saveSection($id, $name, $content, $new = false){
			$section = $this->getSectionByName($name);
			$section->name = $name;
			$section->content = $content;
			$section->update = date('Y-m-d H:i:s');
			if($section->id != '' || $id != ''){
				if($new || ($section->id != '' && $id != $section->id)) throw new Exception('Section name already used');
				if($section->isAdminSection()) throw new Exception('Locked section');
				$section->id = $id;
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
			if($section->id == '') $section = $this->getSectionByName($name);
			return $section; 
		}

		/**
		 *	return a section list
		 */
		public function loadSections( $sortByDescType = false, $withoutAdminSection = false){
			$stmt = $this->db->executequery('select id_section, name_section, date_modification_section, content_section, id_status_section from section '.($withoutAdminSection ? 'where id_status_section < 2 ' : '').' order by '.($sortByDescType  ? 'id_satus_section desc, ' : '').'name_section asc');
			if (!$sections = $stmt->fetchall())  return array(); 
			for ($i = 0; $i < count($sections); $i++) {
				$sections[$i] = new Section($sections[$i]['id_section'], $sections[$i]['name_section'], $sections[$i]['date_modification_section'], $sections[$i]['content_section'], $sections[$i]['id_status_section']);
			}
			return $sections;
		}

	}

}
