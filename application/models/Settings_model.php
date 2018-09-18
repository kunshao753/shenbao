<?php
class Settings_model extends BASE_Model{

    const TABLE_SETTINGS = 'settings';

    public function __construct(){
        parent::__construct(self::TABLE_SETTINGS);
    }
}