<?php
class Projectphoto_model extends BASE_Model{

    const TABLE_PHOTO = 'project_photo';

    public function __construct(){
        parent::__construct(self::TABLE_PHOTO);
    }
}