<?php
class Expert_model extends BASE_Model{

    const TABLE_EXPERT = 'expert';

    public function __construct(){
        parent::__construct(self::TABLE_EXPERT);
    }

    public function get_data($where='',$fields='',$orderBy='',$offset=0,$limit=20){
        return $this->fetch_all($where,$fields,$orderBy,'',$offset,$limit);
    }

}