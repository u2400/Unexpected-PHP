<?php
class Api extends MY_Controller
{
	public function __construct() {
		parent::__construct();
		$this->load->model('my_sql');
		$this->salt = 'y$VUlqDBaWWxb&s##x@4KG5Gl4HQ#zHP52&#DinzwAr8hC7C19';
		header("Access-Control-Allow-Origin: *");
    }

    public function login() {
    	//注册变量
    	foreach ($_POST as $arg) {
    		$$arg = $this->post($arg);
    	}

    	//进行过滤
    	$username = preg_replace("/(\"|\'|\\\\|\;|\-|\/)/", "\\$1", $username);
    	$password = $this->do_password_hash($password);
    	if( preg_match("/\s/", $username)) {
    		die(json_encode(array('mes' => "你的请求中包含有危险参数", "error"=>"0")));
    	}

    	//进行查询
    	$sql = "select * from users where username = '$username' and '$password'";
    	$back = $this->my_sql->free_sql($sql);
    	if($back->num != 0) {
			//授予session.
			$_SESSION['username'] = $username;
			die(json_encode(array('mes'=>"登录成功", "error"=>"0")));
    	}
    	else {
			die(json_encode(array('mes'=>"用户名或者密码不正确", "error"=>"1")));
    	}
    }

	public function upload() {
		$this->check_login();
		//从session中获取用户名,将上传的文件移动至上传目录
		$username = $_SESSION['username'];
		var_dump($_FILES);
		if( preg_match("/(\.htaccess)/", $_FILES["avatar"]["name"]) ) {
			die(json_encode(array('mes'=>'非法文件名', 'error'=>'1')));
		}
		else {
			move_uploaded_file($_FILES["avatar"]["tmp_name"], "../upload/" . $_FILES["avatar"]["name"]);
		}
		
		$tmp = $_FILES["avatar"]["name"];
		preg_match("/(.{1,50})\.(.{0,5})/",$tmp);
		$tmp = new HeadPortrait($tmp[1], $tmp[2]);

		//获取序列化后的内容,写入数据库
		$headportrait = serialize($tmp);
		$sql = "update users set headportrait = ? where username = ?";
		$this->my_sql->free_sql($sql,array($headportrait, $username));
		die(json_encode(array('mes'=>'上传成功', 'error'=>'0')));
	}
	
	public function registeruser() {
		$this->check_login();
		$username = $_POST['username'];
		$password = $_POST['password'];
		$sql  = " select * from users where username = ? ";
		$back = $this->my_sql->free_sql($sql,$data);

		if($back->num === 0) {
			$sql = "insert into users (username, password) value ( ? , ? )";
			$sql = $this->my_sql->free_sql($sql,array($username,$this->do_password_hash($password)));
		}
		else {
			die(json_encode(array('mes'=>"用户已存在", "error"=>"1")));
		}
		die(json_encode(array('mes'=>"用户注册成功", "error"=>"0")));
	}

	public function deleteuser() {
		$this->check_login();
		$username = $_POST['username'];

		//查询序列化后的图片数据, 并反序列化.
		$back = $sql = "select * from users where username = ?";
		$Head = $back->row()->headportrait;
		$Head = @unserialize($Head);
		$Head->delete = 1;
		if( preg_match("/(\/|\\\\|\.)/",$Head->name) || preg_match("/(\/|\\\\|\.)/",$Head->type) ) {
			echo "用户删除失败!";
			throw new Exception("unknow error!");
		}
		else{
			$sql = " delete from users where username = ? ";
			$this->my_sql->free_sql($sql,array($username));
		}
	}

	public function showusername() {
		$this->check_login();
		$sql = "select username from users";
		$back = $this->my_sql->free_sql($sql);
		// 将用户的数据json化返回给前端.
		$json = [];
		foreach ($back->result() as $row)
        {
            $json[] = $row;
		}
		echo json_encode($json);
	}

	private function do_password_hash($password) {
		$salt = $this->salt;
		return md5("$password$salt",true);
	}

	private function check_login() {
		if(!$_SESSION['username']) {
			die(json_encode(array('mes'=>"请先登陆", "error"=>"1")));
		}
	}
}

class HeadPortrait {
	public $name; //img name
	public $type; //jpg or jpeg or gif
	public $delete = 0;

	function __destruct() {
		if ($this->delete === 1) {
			unlink("./upload/".$this->name.$this->type);
		}
	}

	function __construct($name, $type) {
		$this->name = $name;
		$this->$type = $type;
		$this->delete = 0;
	}
}