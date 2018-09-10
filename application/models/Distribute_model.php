<?php
class Distribute_model extends BASE_Model{

    const TABLE_DISTRIBUTE = 'distribute';

    public function __construct(){
        parent::__construct(self::TABLE_DISTRIBUTE);
    }
}