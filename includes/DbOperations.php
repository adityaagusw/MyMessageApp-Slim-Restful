<?php 

    class DbOperations{

        private $con; 

        function __construct(){
            require_once dirname(__FILE__) . '/DbConnect.php';
            $db = new DbConnect; 
            $this->con = $db->connect(); 
        }

        public function createUser($email, $password, $nama, $alamat){
            if(!$this->isEmailExist($email)){
                $stmt = $this->con->prepare("INSERT INTO users (email, password, nama, alamat) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $email, $password, $nama, $alamat);
                if($stmt->execute()){
                    return USER_CREATED; 
                }else{
                    return USER_FAILURE;
                }
            }
            return USER_EXISTS; 
        }
        
        public function updateUser($nama, $email, $id){
            $stmt = $this->con->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nama, $email, $id);
            if($stmt->execute())
                return true; 
            return false; 
        }
        
        public function kirimPesan($user_id, $pesan){
            $stmt = $this->con->prepare("INSERT INTO message (user_id, pesan) VALUES (?,?)");
            $stmt->bind_param("ss", $user_id, $pesan );
            if($stmt->execute()){
                return USER_CREATED;
            }else{
                return USER_FAILURE;
            }
        }

        public function userLogin($email, $password){
            if($this->isEmailExist($email)){
                $hashed_password = $this->getUsersPasswordByEmail($email); 
                if(password_verify($password, $hashed_password)){
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH; 
                }
            }else{
                return USER_NOT_FOUND; 
            }
        }

        private function getUsersPasswordByEmail($email){
            $stmt = $this->con->prepare("SELECT password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($password);
            $stmt->fetch(); 
            return $password; 
        }

        public function getAllUsers(){
            $stmt = $this->con->prepare("SELECT id, email, nama, alamat FROM users;");
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $nama, $alamat);
            $users = array(); 
            while($stmt->fetch()){ 
                $user = array(); 
                $user['id'] = $id; 
                $user['email']=$email; 
                $user['nama'] = $nama; 
                $user['alamat'] = $alamat; 
                array_push($users, $user);
            }             
            return $users; 
        }

        public function getUserByEmail($email){
            $stmt = $this->con->prepare("SELECT id, email, nama, alamat FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $nama, $alamat);
            $stmt->fetch(); 
            $user = array(); 
            $user['id'] = $id; 
            $user['email']=$email; 
            $user['nama'] = $nama; 
            $user['alamat'] = $alamat; 
            return $user; 
        }

        private function isEmailExist($email){
            $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }
    }