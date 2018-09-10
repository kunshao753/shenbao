<?php
class Projectteam_model extends BASE_Model{

    const TABLE_TEAM = 'project_team';

    public function __construct(){
        parent::__construct(self::TABLE_TEAM);
    }
}