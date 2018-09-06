<?php
class BASE_Model extends CI_Model{
    //从库
    protected $slave;
    //主库
    protected $master;
    //表名
    protected $table;

    protected $_succ = 'succ';
    protected $_fail = 'fail';

    //连接引用数组
    static $_instance=array();

    public function __construct($table='',$active_group='default') {
        parent::__construct();
        $this->_init($table,$active_group);
    }

    private function _init($table,$active_group){
        $this->slave = self::get_instance($active_group.'_r');
        $this->master = self::get_instance($active_group.'_w');
        if(!empty($table)){
            $this->set_table($table);
        }
    }


    public static function get_instance($name){
        if(!isset(self::$_instance[$name])){
            $CI = & get_instance();
            self::$_instance[$name] = $CI->load->database($name, TRUE);
        }
        return self::$_instance[$name];
    }

    protected function set_table($table) {
        $this->table = $table;
    }

    public function insert($data = array()) {
        $this->master->insert($this->table, $data);
        $insert_id = $this->master->insert_id();
        return $insert_id;
    }

    public function insert_ignore($data = array()) {
        $sql = $this->get_insert_sql($data);
        $sql = str_replace("INSERT INTO","INSERT IGNORE INTO",$sql);
        $this->master->query($sql);
        $insert_id = $this->master->insert_id();
        return $insert_id;
    }

    public function insert_duplicate($data = array(),$update_str='') {
        $sql = $this->get_insert_sql($data);
        $sql .= " ON DUPLICATE KEY UPDATE {$update_str}";
        return $this->master->query($sql);
    }

    public function insert_batch($data = array()){
        return $this->master->insert_batch($this->table, $data);
    }

    public function get_insert_sql($data = array()) {
        $sql =$this->master->set($data)->get_compiled_insert($this->table);
        return $sql;
    }

    function fetch_row($where = '', $fileds = '*', $orderBy = '', $groupBy = '', $offset = 0, $limit = 1) {
        $data = array();
        if(!empty($where)){
            $this->slave->where($where);
        }
        $this->slave->select($fileds);
        if(!empty($orderBy)){
            $this->slave->order_by($orderBy);
        }
        if(!empty($groupBy)){
            $this->slave->group_by($groupBy);
        }
        if(!empty($offset) && !empty($limit)){
            $this->slave->limit($limit, $offset);
        }elseif(!empty($limit)) {
            $this->slave->limit($limit);
        }
        $query = $this->slave->get($this->table);
        if($query){
            $data = $query->row_array();
        }
        return $data;
    }

    function fetch_master_row($where = '', $fileds = '*', $orderBy = 'id DESC', $groupBy = '', $offset = 0, $limit = 1) {
        $data = array();
        if(!empty($where)){
            $this->slave->where($where);
        }
        $this->slave->select($fileds);
        if(!empty($orderBy)){
            $this->slave->order_by($orderBy);
        }
        if(!empty($groupBy)){
            $this->slave->group_by($groupBy);
        }
        if(!empty($offset) && !empty($limit)){
            $this->slave->limit($limit, $offset);
        }elseif(!empty($limit)) {
            $this->slave->limit($limit);
        }
        $query = $this->slave->get($this->table);
        if($query){
            $data = $query->row_array();
        }
        return $data;
    }

    public function fetch_count($where = '', $group_by = '') {
        $info = $this->fetch_row($where, 'count(*) as cnt', '', $group_by);
        return $info === false ? 0 : intval($info['cnt']);
    }

    function fetch_all($where='',$fileds='*', $orderBy='id DESC',$groupBy='',$offset=0,$limit=0,$join=array()){
        $data = array();
        if(!empty($where)){
            $this->slave->where($where);
        }
        $this->slave->select($fileds);
        if(!empty($orderBy)){
            $this->slave->order_by($orderBy);
        }
        if(!empty($groupBy)){
            $this->slave->group_by($groupBy);
        }
        if(!empty($offset) && !empty($limit)){
            $this->slave->limit($limit, $offset);
        }elseif(!empty($limit)) {
            $this->slave->limit($limit);
        }
        if(!empty($join)){
            foreach($join as $key => $value){
                if(is_array($value)){
                    $this->slave->join($key,$value[0],$value[1]);
                }else{
                    $this->slave->join($key,$value);
                }
            }
        }
        $query = $this->slave->get_where($this->table);
        if ($query->num_rows() > 0) {
            $data = $query->result_array();
        }
        return $data;
    }

    public function update($data, $where ,$escape = TRUE) {
        if (empty($data) || empty($where)) {
            return false;
        }
        $this->master->set($data,'',$escape);
        $this->master->where($where);
        $this->master->update($this->table);
        return $this->master->affected_rows();
    }

    public function delete($where) {
        if (empty($where)) {
            return false;
        }
        $this->master->delete($this->table, $where);
        return $this->master->affected_rows();
    }

    public function exec_sql($sql='',$db_type='default'){
        if(empty($sql)){
            return false;
        }

        if($db_type == 'master'){
            $query = $this->master->query($sql);
        }else{
            $query = $this->slave->query($sql);
        }

        if (!is_object($query)) {
            return $query;
        }

        $result = array();
        if( $query ){
            $result = $query->result_array();
        }
        return $result;
    }

    public function build_sql($config) {
        $condition = array();
        foreach ($config as $k => $v) {
            if (is_array($v)) {
                switch ($v[0]) {
                    case "like":
                        if (is_array($v[1])) {
                            $condition[] = "(" . $k . " LIKE " . "'%{$v[1][0]}%' or " . $k . " LIKE " . "'%{$v[1][1]}')";
                        } else {
                            $condition[] = $k . " LIKE " . "'%{$v[1]}%'";
                        }
                        break;
                    case "gt":
                        $condition[] = $k . " >= " . "'{$v[1]}'";
                        break;
                    case "lt":
                        $condition[] = $k . " <= " . "'{$v[1]}'";
                        break;
                    case "eq":
                        $condition[] = $k . " = " . "'{$v[1]}'";
                        break;
                    case "neq":
                        $condition[] = $k . " != " . "'{$v[1]}'";
                        break;
                    case "between":
                        $str = explode(",", $v[1]);
                        if ($str[0] && $str[1]) {
                            $condition[] = $k . " BETWEEN " . "'{$str[0]}'" . " AND " . "'{$str[1]}'";
                        } else {
                            if ($str[0]) {
                                $condition[] = $k . " >= " . "'{$str[0]}'";
                            }
                            if ($str[1]) {
                                $condition[] = $k . "<=" . "'{$str[1]}'";
                            }
                        }
                        break;
                    case "in":
                        $condition[] = $k . " IN ('" . implode("', '", $v['1']) . "')";
                        break;
                }
            } elseif(strpos($k, '>=')) {
                $condition[] = $k . $v;
            } elseif(strpos($k, '<=')) {
                $condition[] = $k . $v;
            }  else {
                $condition[] = $k . " = '" .  $v ."'";
            }
        }
        $condition = implode(' AND ', $condition);
        return $condition;
    }

    protected function _formatreturndata($is_succ, $info = null) {
        $res = array();
        if ($is_succ) {
            $res['result'] = $this->_succ;
            $res['info'] = $info;
        } else {
            $res['result'] = $this->_fail;
            $res['info'] = $info;
        }
        return $res;
    }
}