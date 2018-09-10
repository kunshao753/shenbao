<?php
class Corp_model extends BASE_Model{

    const TABLE_CORP = 'corporate_information';
    const TABLE_PROJECT = 'project_information';

    public function __construct(){
        parent::__construct(self::TABLE_CORP);
    }

    public function get_list($where='',$fields='',$orderBy='',$offset=0,$limit=20){
        $join = array(self::TABLE_PROJECT.' as project' => self::TABLE_CORP.'.user_id = project.user_id');
        return $this->fetch_all($where,$fields,$orderBy,'',$offset,$limit,$join);
    }
}