<?php
class Expertgroup_model extends BASE_Model{

    const TABLE_EXPERT_GROUP = 'expert_group';

    public function __construct(){
        parent::__construct(self::TABLE_EXPERT_GROUP);
    }

    public function get_relation_list($where='',$fields='',$orderBy='',$offset=0,$limit=20){
        if(empty($where)){
            $where = 'expert_group.is_delete=0';
        }else{
            $where .= 'and e.is_delete=0 and expert_group.is_delete=0';
        }
        $join = array('expert as e' => array(self::TABLE_EXPERT_GROUP.'.id = e.group_id','left'));
        return $this->fetch_all($where,$fields,$orderBy,'',$offset,$limit,$join);
    }


    public function get_data($where='',$fields='',$orderBy='',$offset=0,$limit=20){
        return $this->fetch_all($where,$fields,$orderBy,'',$offset,$limit);
    }
}