<?php
/*
create table session (
	data text,
	sess_id char(32),
	sess_time varchar(32)

	);

*/


// 修改为user,才可以使用自定义session
ini_set('session.save_handler','user');

class DB_session {

		static $pdo;
		//$time不能直接赋动态值，可以在方法里给它赋动态值
		static $time;
	
	public static function start($pdo) {
		self::$pdo = $pdo;
		self::$time = time();
		//__CLASS__ or &$this 回调函数是静态方法时候,如果是普通方法呢
		session_set_save_handler(
			array(__CLASS__,'open'),
			//array(&$this,'open'),
			array(__CLASS__,'close'),
			array(__CLASS__,'read'),
			array(__CLASS__,'write'),
			array(__CLASS__,'destroy'),
			array(__CLASS__,'gc')
			);

		session_start();
	}
	//$name 默认 = PHPSESSID
	private static function open($path,$name) {
		return true;
	}
	// 这里注意是public 否则无法正常执行 为什么
	public static function close() {
		return true;
	}

	private static function read($id) {
		$sql = "select * from session where sess_id = ?";
		$sth = self::$pdo -> prepare($sql);
		$sth -> execute(array($id));
		if(!$result = $sth -> fetch()) {
			return '';
		}
		return $result['data'];
	}
		// 这里注意是public 否则无法正常执行 为什么
	public static function write($id,$data) {
		$sql = "select * from session where sess_id = ?";
		$sth = self::$pdo -> prepare($sql);
		$sth -> execute(array($id));
		if($result = $sth -> fetch()) {
			$sql = "update session set sess_time = ?,data = ? where sess_id = ?";
			$sth = self::$pdo -> prepare($sql);
			$sth -> execute(array(self::$time,$data,$id));
		} else {
			$sql = "insert into session values(?,?,?)";
			$sth = self::$pdo -> prepare($sql);
			$sth -> execute(array($data,$id,self::$time));
		}
		return true;
	}
	// 这里注意是public 否则无法正常执行 为什么
	public static function destroy($id) {
		$sql = "delete from session where sess_id = ?";
		$sth = self::$pdo -> prepare($sql);
		$sth -> execute(array($id));
		return true;
	}
	//$lifetime会被自动赋值
	private static function gc($lifetime) {
		self::$look = $lifetime;
		$sql = "delete from session where sess_time < ?";
		$sth = self::$pdo -> prepare($sql);
		$sth -> execute(array(self::$time - $lifetime));
		return true;
	}
}

DB_session::start(new PDO('mysql:host=localhost;dbname=test','root','123'));
$_SESSION['login'] = 2;

