<?php
class Project_model extends BASE_Model{

    const TABLE_PROJECT = 'project_information';

    public function __construct(){
        parent::__construct(self::TABLE_PROJECT);
    }
}