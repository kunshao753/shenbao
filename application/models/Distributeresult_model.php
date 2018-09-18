<?php
class Distributeresult_model extends BASE_Model{

    const TABLE_DR = 'distribute_result';

    public function __construct(){
        parent::__construct(self::TABLE_DR);
    }
}