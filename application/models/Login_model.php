<?php

class Login_model extends BASE_Model {

    const SALES_KEY = 'kepu-china';
    /**
     * 管理员id
     * @var
     */
    private $_admin_id = '';

    public function __construct() {
        $this->load->model('Expert_model','expert_model');
        parent::__construct();
    }

    /**
     * 用户登录处理
     * @param array $data
     * @return boolean|Ambigous <multitype:, multitype:mixed string >
     */
    public function login($data) {
        if (!is_array($data) || !isset($data['account']) || !isset($data['password'])) return false;

        $condition = array(
            'account' => $data['account'],
        );

        $user_info_res = $this->expert_model->fetch_row($condition);
        if (empty($user_info_res)) {
            return $this->_formatreturndata(false, '账号有误！');
        }

        if (isset($user_info_res['is_delete']) && $user_info_res['is_delete'] <> 0) {
            if ($user_info_res['is_delete'] == 1) {
                return $this->_formatreturndata(false, '该账户已暂停，请联系管理员！');
            }

            if ($user_info_res['is_delete'] == 2) {
                return $this->_formatreturndata(false, '账号异常，请联系管理员！');
            }
        }

        if (isset($user_info_res['account'])) {
            if ($user_info_res['password'] == md5($data['password'])) {
                $this->set_login_success_cookie($user_info_res);
                return $this->_formatreturndata(true, $user_info_res['id']);
            }
        }
        return $this->_formatreturndata(false, '登录失败，密码有误');
    }

    public function logout() {
        setcookie('ex_name', '', time() - 3600, '/');
        setcookie('ex_id', '', time() - 3600, '/');
        setcookie('ex_account', '', time() - 3600, '/');
        setcookie('sigcode', '', time() - 3600, '/');
        return $this->_formatreturndata(true);
    }

    public function set_post_cookie() {
        $validation_route = array();
        $cookies = $this->input->post('cookie');
        if ($this->input->post() && !empty($cookies)) {
            if (isset($validation_route[lib_router::ret_site()][lib_router::ret_controller()][lib_router::ret_action()])) {

                $cookies_arr = explode(';', $cookies);
                $cookie_keys = array('ex_name', 'ex_id', 'ex_account', 'sigcode');
                foreach ($cookies_arr as $value) {
                    $cookie_item = explode('=', $value);
                    if (in_array($cookie_item[0], $cookie_keys)) {
                        $_COOKIE[$cookie_item[0]] = $cookie_item[1];
                    }
                }
            }
        }
    }

    public function is_login() {
        //post传递cookie进行ajax操作
        $this->set_post_cookie();
        $admin_sigcode = '';
        if (!empty($_COOKIE['sigcode'])) $admin_sigcode = $_COOKIE['sigcode'];

        if ($admin_sigcode) {
            $id = $this->get_admin_id();
            $admin_uname = $_COOKIE['ex_name'];
            $account = $_COOKIE['ex_account'];
            $sigcode = $this->_get_sigcode($id, $admin_uname, $account);
            if ($admin_sigcode == $sigcode) {
                $user_info_res = $this->expert_model->fetch_by_id($id);
                if (isset($user_info_res['id'])) {
                    return $this->_formatreturndata(true, $user_info_res);
                }
            }
        }
        return $this->_formatreturndata(false);
    }

    private function _get_sigcode($user_id, $user_name, $account) {
        return md5($user_id . $user_name . $account . self::SALES_KEY);
    }

    public function get_admin_id() {
        if ($this->_admin_id) {
            return $this->_admin_id;
        }

        $sign_code = $this->input->cookie('sigcode');
        $uid =  $this->input->cookie('ex_id');
        $name = $this->input->cookie('ex_name');
        $account = $this->input->cookie('ex_account');
        $true_sig_code = $this->_get_sigcode($uid, $name ,$account);

        if ($sign_code == $true_sig_code) {
            $this->_admin_id = $uid;
            return $uid;
        } else {
            return false;
        }
    }

    /**
     * 设置用户登录成功需要设置的cookie信息
     * @author:
     *
     * @param $user_info_res
     */
    public function set_login_success_cookie($user_info_res) {
        $sigcode = $this->_get_sigcode($user_info_res['id'], $user_info_res['name'],$user_info_res['account']);
        setcookie('ex_id', $user_info_res['id'], time() + 10800, '/');
        setcookie('ex_name', $user_info_res['name'], time() + 10800, '/');
        setcookie('ex_account', $user_info_res['account'], time() + 10800, '/');
        setcookie('sigcode', $sigcode, time() + 10800, '/');
    }
}