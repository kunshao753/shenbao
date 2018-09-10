<?php
class Expertgroup_model extends BASE_Model{

    const TABLE_EXPERT_GROUP = 'expert_group';

    public function __construct(){
        parent::__construct(self::TABLE_EXPERT_GROUP);
    }

    public function get_list($where='',$fields='',$orderBy='',$offset=0,$limit=20){
        $join = array('expert as e' => self::TABLE_EXPERT_GROUP.'.id = e.group_id');
        return $this->fetch_all($where,$fields,$orderBy,'',$offset,$limit,$join);
    }
}