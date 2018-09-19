<?php
class Corp_model extends BASE_Model{

    const TABLE_CORP = 'corporate_information';

    public $CI;
    public function __construct(){
        parent::__construct(self::TABLE_CORP);
        $this->CI = & get_instance();
        $this->CI->load->model('Expert_model','expert_model');
        $this->CI->load->model('Settings_model','settings_model');
        $this->CI->load->model('Distribute_model','distribute_model');
        $this->CI->load->model('Distributeresult_model','distribute_result_model');
    }

    public function get_list($project_name='',$review_status='',$is_admin=0,$expert_info=array(),$offset=0,$page_size=0){

        if($page_size == 0){
            $page_size = 9999;
        }

        $base_condition = array();
        if(!empty($project_name)){
            $base_condition[] = "p.project_name like '%{$project_name}%'";
        }
        if(!$is_admin){
            if(!empty($expert_info['group_id'])){
                //查询当前专家组所绑定的项目
                $user_id_res = $this->CI->distribute_model->fetch_all(array('group_id'=>$expert_info['group_id']),'user_id');
                $user_id_arr = array();
                if(!empty($user_id_res)){
                    foreach($user_id_res as $value){
                        $user_id_arr[] = $value['user_id'];
                    }
                    $user_id_str = implode(',',$user_id_arr);
                    $base_condition[] = "c.user_id in ({$user_id_str})";
                }
            }else{
                $base_condition[] = "c.user_id = {$expert_info['id']}";
            }
        }
        $base_condition[] = "audit_status in (1,3,4)";
        $base_condition[] = "c.user_id=p.user_id";
        $base_where = implode(' and ',$base_condition);
        $base_fields = 'c.id,c.user_id,p.project_name,c.name,c.mobile,c.signup_resouce,c.contestant_identity,c.company_name,p.product_type,p.product_form_val,c.audit_status';
        $base_sql = "select {$base_fields} from corporate_information c,project_information p where {$base_where}";

        $fields = 'a.id,a.user_id,a.project_name,a.name,a.mobile,a.signup_resouce,a.contestant_identity,a.company_name,a.product_type,a.product_form_val,a.audit_status';
        $where = '';
        if($is_admin) {
            $table_join = 'distribute';
            if (!empty($review_status)) {
                if ($review_status == 1) {
                    $where = "where d.review_status is null";
                } else {
                    $where = "where d.review_status={$review_status}";
                }
            }
            $fields .= ',d.review_status';
        }else{
            $where = "where expert_id={$expert_info['id']}";
            $table_join = 'distribute_result';
            if(!empty($review_status)){
                $where .= " and d.status={$review_status}";
            }
            $fields .= ',d.status';
        }
        $sql = "select $fields from ($base_sql) as a left join $table_join d on d.user_id=a.user_id {$where} order by a.id desc limit $offset,$page_size ";
        $sql_count = "select count(*) as count from ($base_sql) as a left join $table_join d on d.user_id=a.user_id {$where}";

        $data = $this->exec_sql($sql);
        $count = $this->exec_sql($sql_count);

        //查询评分结果
        if(!empty($data)){
            foreach($data as $key => $value){
                $user_id = $value['user_id'];
                $result_res = $this->CI->distribute_result_model->fetch_all(array('user_id'=>$user_id),'expert_name,result');
                $res_arr = $res_arr_str = array();
                if(!empty($result_res)){
                    foreach($result_res as $k1 => $v1){
                        $expert_name = $v1['expert_name'];
                        $result = !empty($v1['result']) ? json_decode($v1['result'],true) : array();
                        $res_arr[] = array($expert_name => array_sum($result));
                    }
                }
                if(!empty($res_arr)){
                    foreach($res_arr as $v2){
                        foreach($v2 as $k3 => $v3){
                            $v3 = $v3 == 0 ? "" : $v3;
                            $res_arr_str[] = $k3 . ':' .$v3;
                        }
                    }
                }
                $data[$key]['result'] = implode('<br>',$res_arr_str);
            }
        }
        $return = array(
            'data' => $data,
            'count' => $count[0]['count']
        );
        return $return;
    }
}