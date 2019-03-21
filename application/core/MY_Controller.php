<?php
class MY_Controller extends CI_Controller
{
    protected function post($name)
    {
        if(!isset($_POST["$name"]))
        {
            $post = NULL;
        }
        else
        {
            $post = (string)$_POST["$name"];
        }
        return $post;
    }

    protected function load_page($page,$data = NULL)
    {
        $this->load->view("header",$data);
        $this->load->view("$page",$data);
        $this->load->view("footer",$data);
    }

    //获取用户ip协议中的ip,有时获取内网地址
    protected function Get_ip()
    {
        if (isset($_SERVER['REMOTE_ADDR']))
        {
            $_ip = $_SERVER['REMOTE_ADDR'];
        }
        else
        {
            show_404();
            die();
        }
        if(preg_match('/[^0-9a-zA-Z\.]/',$_ip))
        {
            show_404();
            die();
        }
        return $_ip;
    }

    //登陆成功之后更新cookie
    protected function Set_cookie($uname,$password,$team_token)
    {
        $_ip = $this->Get_ip();
        $_SESSION['uname'] = $uname;
        $_SESSION['team_token'] = $team_token;
        $_SESSION['pass'] = 1;
        $_cookie_ = password_hash("$uname$_ip$password", PASSWORD_DEFAULT);
        setcookie("suLogin",$_cookie_, time()+3600*24,'/');
        $_cookie_ = base64_encode("$uname");
        setcookie("user_name",$_cookie_,time()+3600*24,'/');
    }

    protected function public_check($data,$rule)
    {
        /*
         * 1=>只允许数字
         * 2=>允许数字,字母
         * 3=>允许数字,字母,中文
         * 4=>允许数字,字母,中文,特殊字符-:.@,
         * (\d+)-(\d+) 形式规定字符串长度,中文字符会出现一个字符算三个长度
         * 其他情况为自定义正则表达式
         * 若data传入数组则所有data元素均需要符合规则要求才返回true
         */
        if(is_array($data))
        {
            foreach ($data as $i)
            {
                $back = $this->check_part_1($i,$rule);
                if($back === false)
                {
                    return false;
                }
            }
        }
        else
        {
            $back = $this->check_part_1($data,$rule);
        }
        return $back;
    }

    protected function check_part_1($data,$rule)
    {
        $arr_rule = explode("|",$rule);
        foreach ($arr_rule as $rule)
        {
            switch ($rule)
            {
                case "1":
                    $rule = "/^[0-9]{1,}$/";
                    break;
                case "2":
                    $rule = "/^[0-9a-zA-Z]{1,}$/";
                    break;
                case "3":
                    $rule = "/^[0-9a-zA-Z一-龥]{1,}$/";
                    break;
                case "4":
                    $rule = "/^[0-9a-zA-Z一-龥\-:\.,]{1,}$/";
                    break;
                default:
                    break;
            }
            if(preg_match("/^([0-9]{1,})-([0-9]{1,})$/",$rule,$match))
            {
                if(strlen($data)<$match['1'] || strlen($data)>$match['2'])
                {
                    return false;
                }
            }
            else
            {
                if(!preg_match($rule,$data))
                {
                    return false;
                }
            }
        }
        return true;
    }

    protected function gettime()
    {
        $date = date("Y/m/d h:i:sa");
        return $date;
    }

    protected function getunix()
    {
        $unix = time();
        return $unix;
    }
}