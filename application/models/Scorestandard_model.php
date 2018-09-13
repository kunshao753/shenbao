<?php
class Scorestandard_model extends BASE_Model{

    const TABLE_SCORESTANDAD = 'score_standard';

    public function __construct(){
        parent::__construct(self::TABLE_SCORESTANDAD);
    }

    public function get_list($where='',$fields='',$orderBy='',$offset=0,$limit=20){
        $join = array('expert_group as eg' => self::TABLE_SCORESTANDAD.'.group_id = eg.id');
        return $this->fetch_all($where,$fields,$orderBy,'',$offset,$limit,$join);
    }

    public function get_data($where='',$fields='',$orderBy='',$offset=0,$limit=20){
        return $this->fetch_all($where,$fields,$orderBy,'',$offset,$limit);
    }
}