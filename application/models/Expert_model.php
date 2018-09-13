<?php
class Expert_model extends BASE_Model{

    const TABLE_EXPERT = 'expert';

    public function __construct(){
        parent::__construct(self::TABLE_EXPERT);
    }

    public function get_list($where='',$fields='',$orderBy='',$offset=0,$limit=20){
        $join = array('expert_group as eg' => self::TABLE_EXPERT.'.group_id = eg.id');
        return $this->fetch_all($where,$fields,$orderBy,'',$offset,$limit,$join);
    }
    public function get_data($where='',$fields='',$orderBy='',$offset=0,$limit=20){
        return $this->fetch_all($where,$fields,$orderBy,'',$offset,$limit);
    }

}