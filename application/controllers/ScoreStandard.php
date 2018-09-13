<?php
class ScoreStandard extends BASE_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model('Scorestandard_model','scorestandard_model');
    }

    public function getList(){
        $page = !empty($this->input->get('page')) ? $this->input->get('page') : 1;
        $page_size = !empty($this->input->get('page_size')) ? $this->input->get('page_size') : 20;;
        $where = 'is_delete=0';
        $offset = ($page - 1) * $page_size;
        $standard_info = $this->scorestandard_model->fetch_all($where,'','','',$offset,$page_size);
        $count = $this->scorestandard_model->fetch_count($where);

        if(!empty($standard_info)){
            foreach($standard_info as $key => $value){
                $standard_id = $value['id'];
                $eg_where = array(
                    'score_standard_id' => $standard_id,
                    'is_delete' => 0
                );
                $standard_info[$key]['group_name'] = '';
                $eg_info = $this->expertgroup_model->fetch_all($eg_where);
                if(!empty($eg_info)){
                    foreach($eg_info as $value1){
                        $group_names[] = $value1['group_name'];
                    }
                    $standard_info[$key]['group_name'] = implode('<br>',$group_names);
                }
            }
        }
        var_dump($standard_info);
        var_dump($count);

        $this->assign('page',$page);
        $this->assign('count',$count);
        $this->assign('data',$standard_info);

        $this->display('standard/list.html');
    }

    public function add(){
        $this->display('standard/add.html');
    }

    public function edit(){
        $id = $this->input->get('id');
        //查询评分标准信息
        $score_standard_info = $this->scorestandard_model->fetch_row(array('id'=>$id));

        if(empty($score_standard_info)){
            return false;
        }
        $score_standard_info['type'] = json_decode($score_standard_info['type'],true);

        $this->assign('score_standard_info',$score_standard_info);
        $this->display('standard/edit.html');
    }

    public function addScoreStandard(){
        $name = $this->input->post('name');
        $standard = $this->input->post('standard');
        $reason = $this->input->post('reason');


        if(empty($name)){
            $this->ajax_return(array(),'评分名称不能为空',5000001);
        }

        if(empty($standard)){
            $this->ajax_return(array(),'评分项不能为空',5000002);
        }

        if(empty($reason)){
            $this->ajax_return(array(),'评分依据不能为空',5000002);
        }
        $standard_reason = array_combine($standard,$reason);

        $insert_data = array(
            'name' => $name,
            'type' => json_encode($standard_reason),
            'create_at' => date('Y-m-d H:i:s')
        );

        $res = $this->scorestandard_model->insert($insert_data);
        if($res){
            $this->ajax_return($res,'添加成功');
        }else{
            $this->ajax_return(array(),'添加失败',5000003);
        }

    }

    public function editScoreStandard(){
        $id = $this->input->post('id');
        $name = $this->input->post('name');
        $standard = $this->input->post('standard');
        $reason = $this->input->post('reason');

        if(empty($id)){
            $this->ajax_return(array(),'id不能为空',5000004);
        }

        if(empty($name)){
            $this->ajax_return(array(),'评分名称不能为空',5000005);
        }

        if(empty($standard)){
            $this->ajax_return(array(),'评分项不能为空',5000006);
        }

        if(empty($reason)){
            $this->ajax_return(array(),'评分项依据不能为空',5000006);
        }
        $standard_reason = array_combine($standard,$reason);
        $update_data = array(
            'name' => $name,
            'type' => json_encode($standard_reason),
        );

        $res = $this->scorestandard_model->update($update_data,array('id'=>$id));

        if($res !== false){
            $this->ajax_return($res,'修改成功');
        }else{
            $this->ajax_return(array(),'修改失败',5000007);
        }

    }

    public function delScoreStandard(){
        $id = $this->input->post('id');
        if(empty($id)){
            $this->ajax_return(array(),'id不能为空',5000008);
        }

        $update_data = array(
            'is_delete' => 1,
        );

        $res = $this->scorestandard_model->update(array('id'=>$id),$update_data);
        if($res){
            $this->ajax_return($res,'删除成功');
        }else{
            $this->ajax_return(array(),'删除失败',5000009);
        }

    }
}