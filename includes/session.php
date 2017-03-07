<?php

	//A class to help work with sessions
	//In our case, primarily to manage logging users in and out


	//Keep in mind when working with sessions that is is generally
	//inadvisable to store DB-related objects in that session

	class Session{
		private $logged_in=false;
		public $user_id;
		public $message;

		function __construct(){
			session_start();
			$this->check_message();
			$this->check_login();

			// if($this->logged_in){
			//
			// }else{
			//
			// }
		}

		public function is_logged_in(){
			return $this->logged_in;
		}

		public function login($user){
			//database should find user based on username/password

			if($user){
				$this->user_id = $_SESSION['user_id'] = $user->id;
				$this->logged_in = true;
			}
		}

		public function logout(){
			unset($_SESSION['user_id']);
			unset($this->user_id);
			$this->logged_in =  false;
		}

		public function message($msg=""){
			if(!empty($msg)){
				//then this is "set message"
				//make sure you understand why $this->message=$msg wouldnt work
				$_SESSION['message'] = $msg;
			}else{
				//then this is a "get message"
				return $this->message;
			}
		}

		private function check_login(){
			if(isset($_SESSION['user_id'])){
				$this->user_id = $_SESSION['user_id'];
				$this->logged_in = true;
			}else{
				unset($this->user_id);
				$this->logged_in = false;
			}
		}

		private function check_message(){
			//Is there is a message stored in the session?
			if(isset($_SESSION['message'])){
				//Add it as an attribute and erase the stored version
				$this->message=$_SESSION['message'];
				unset($_SESSION['message']);
			}else{
				$this->message="";
			}
		}

	}

	$session = new Session();
	$message = $session->message();
?>
