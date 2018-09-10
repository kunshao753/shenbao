<?php
class Result_model extends BASE_Model{

    const TABLE_RESULT = 'result';

    public function __construct(){
        parent::__construct(self::TABLE_RESULT);
    }
}