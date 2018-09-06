<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class BASE_Controller extends CI_Controller {
    public $user_info;

    public function __construct() {
        parent::__construct();
        $this->load->model('Expert_model','expert_model');
        $this->load->model('Login_model','login_model');

        self::_init();

    }

    protected function _init()
    {
        $path_set = array(
            'login/index',
            'login/verifycode'
        );
        $admin_uid = $this->login_model->get_admin_id();
        $path = $this->router->directory . $this->router->class . "/" . $this->router->method;
        if(in_array($path,$path_set) && !$admin_uid) {
            return;
        }
        if (!$admin_uid) {
            header('Location:login/index');
            exit;
        }

        //用户信息
        $this->user_info = $this->expert_model->fetch_by_id($admin_uid);

        $this->assign('user_info', $this->user_info);
    }

    public function assign($key,$val) {
        $this->ci_smarty->assign($key,$val);
    }

    public function display($html) {
        $this->ci_smarty->display($html);
    }


    public function ajax_return($data=array(), $msg='',$code=0,$type='json'){
        $result = array();
        $result['data'] = $data;
        $result['msg'] = $msg;
        $result['code'] = $code;

        $callback = $this->input->get('callback');

        //返回数据类型
        if(strtoupper($type)=='JSON') {
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $return   = empty($callback) ? json_encode($result) : ' ' . $callback . "(" . json_encode($result) . ");";
                exit($return);
            }elseif(strtoupper($type)=='XML'){
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                $return   = empty($callback) ? xml_encode($result) : ' ' . $callback . "(" . xml_encode($result) . ");";
                exit($return);
            }elseif(strtoupper($type)=='EVAL'){
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($result);
            }
    }
}
