<?php
class People_model extends SS_Model{
	
	/**
	 * 当前编辑的“人”对象的id
	 */
	var $id;
	
	/**
	 * people表下的字段及其显示名
	 */
	var $fields=array(
		'character'=>'性质',
		'name'=>'名称',
		'name_en'=>'英文名',
		'abbreviation'=>'简称',
		'gender'=>'性别',
		'id_card'=>'身份证号',
		'work_for'=>'工作单位',
		'position'=>'职位',
		'birthday'=>'生日',
		'city'=>'城市',
		'race'=>'民族'
	);
	
	function __construct() {
		parent::__construct();
	}

	function fetch($id){
		$id=intval($id);
		
		$query="
			SELECT * 
			FROM people
			WHERE company={$this->company->id}
				AND id=$id";
		
		return $this->db->query($query)->row_array();
	}
	
	function add(array $data=array()){
		$data=array_intersect_key($data,$this->fields);
		$data+=uidTime();
		$this->db->insert('people',$data);
		return $this->db->insert_id();
	}
	
	function update($people,$data){
		
		if(is_null($data)){
			return true;
		}
		
		$people_data=array_intersect_key($data, $this->fields);
		
		$people_data['display']=1;
		
		$people_data+=uidTime();
		
		$people_data['company']=$this->company->id;

		return $this->db->update('people',$people_data,array('id'=>$people));
	}
	
	function addProfile($people,$profile_name,$profile_content){
		$data=array(
			'people'=>$people,
			'name'=>$profile_name,
			'content'=>$profile_content
		);
		
		$data+=uidTime(false);
		
		$this->db->insert('people_profile',$data);
		
		return $this->db->insert_id();
	}
	
	function addRelationship($people,$relative,$relation=NULL){
		$data=array(
			'people'=>$people,
			'relative'=>$relative,
			'relation'=>$relation
		);
		
		$data+=uidTime(false);
		
		$this->db->insert('people_relationship',$data);
		
		return $this->db->insert_id();
	}
	
	function addLabel($people,$label_name,$type=NULL){
		$result=$this->db->get_where('label',array('name'=>$label_name));
		
		$label_id=0;
		
		if($result->num_rows()==0){
			$this->db->insert('label',array('name'=>$label_name));
			$label_id=$this->db->insert_id();
		}else{
			$label_id=$result->row()->id;
		}
		
		$this->db->insert('people_label',array('people'=>$people,'label'=>$label_id));
		
		return $this->db->insert_id();
	}
	function isMobileNumber($number){
		if(is_numeric($number) && $number%1==0 && substr($number,0,1)=='1' && strlen($number)==11){
			return true;
		}else{
			return false;
		}
	}
	
	function getRegionByIdcard($idcard){
		$query="SELECT name FROM user_idcard_region WHERE num = '".substr($idcard,0,6)."'";
		$region = $this->db->query($query)->row()->name;
		if($region){
			return $region;
		}else{
			return false;
		}
	}
	
	function verifyIdCard($idcard){
		if(!is_string($idcard) || strlen($idcard)!=18){
			return false;
		}
		$sum=$idcard[0]*7+$idcard[1]*9+$idcard[2]*10+$idcard[3]*5+$idcard[4]*8+$idcard[5]*4+$idcard[6]*2+$idcard[7]+$idcard[8]*6+$idcard[9]*3+$idcard[10]*7+$idcard[11]*9+$idcard[12]*10+$idcard[13]*5+$idcard[14]*8+$idcard[15]*4+$idcard[16]*2;
		$mod = $sum % 11;
		$vericode_dic=array(1, 0, 'x', 9, 8, 7, 6, 5, 4, 3, 2);
		if($vericode_dic[$mod] == strtolower($idcard[17])){
			return true;
		}
	}
	
	function getGenderByIdcard($idcard){
		if(is_string($idcard) && strlen($idcard)==18){
			return $idcard[16] % 2 == 1 ? '男' : '女';
		}else{
			return false;
		}
	}
}
?>