<?php
class Login extends BASE_Controller{

    public $verify_code_params = array('width' => 113, 'height' => 46);
    public function __construct(){
        parent::__construct();
        $this->load->library('lib_verifycode',$this->verify_code_params);
    }

    public function index(){
        if ($this->expert_info) {
            header('location:/expert/getlist');
            exit;
        }
        $this->display('login/login.html');
    }

    //验证码
    public function verifycode() {
        $this->lib_verifycode->get_code();
    }

    //登陆方法
    public function doLogin(){
        if ($this->expert_info) {
            redirect('/index/index');
        }

        if ($this->input->post()) {
            //验证验证码
            $code = $this->input->post('code');
            if ($code == '') {
                $this->ajax_return(array(),'验证码不能为空！', 100001);
            }

            $check_res = $this->lib_verifycode->check_code($code);
            if (!$check_res) {
                $this->ajax_return(array(),'验证码错误，请正确输入验证码！', 100002);
            }

            $data = array(
                'account' => trim($this->input->post('account')),
                'password' => trim($this->input->post('password')),
            );

            if (empty($data['account'])) {
                $this->ajax_return(array(),'登录失败，账号不能为空！', 100003);
            }
            if (empty($data['password'])) {
                $this->ajax_return(array(),'登录失败，密码不能为空！', 100004);
            }

            $res = $this->login_model->login($data);

            if ($res['result'] == $this->_succ) {
                $this->ajax_return(array(),'登录成功');
            } else {
                if ($res['info']) {
                    $this->ajax_return(array(),$res['info'], 100005);
                }

                $this->ajax_return(array(),'登录失败，用户名或密码错误！', 100006);
            }
        }
    }

    public function logout(){
        $this->login_model->logout();
        header('location:/login/index');
        exit;
    }
}