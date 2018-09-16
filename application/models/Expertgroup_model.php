<?php
class Expertgroup_model extends BASE_Model{

    const TABLE_EXPERT_GROUP = 'expert_group';

    public function __construct(){
        parent::__construct(self::TABLE_EXPERT_GROUP);
    }
}