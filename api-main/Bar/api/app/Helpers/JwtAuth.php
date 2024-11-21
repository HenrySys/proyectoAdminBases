<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class JwtAuth{
    private $key;
    function __construct(){
        $this->key="bearertoken"; //Llave privada
    }
    public function getToken($email,$password){
        $hashed_password = hash('sha256', $password); // Asegúrate de que el hash sea el mismo
        $user = User::where(['email' => $email, 'password_hash' => $hashed_password])->first();
        //var_dump($user);
        if(is_object($user)){
            /**Payload Llave publica*/
            $token=array(
                'iss'=>$user->id,
                'username'=>$user->username,
                'email'=>$user->email,
                'userType'=>$user->userType,
                'iat'=>time(),
                'exp'=>time()+(2000)
            );
            $data=JWT::encode($token,$this->key,'HS256');
        }else{
            $data=array(
                'status'=>401,
                'message'=>'Datos de autenticación incorrectos'
            );
        }
        return $data;
    }
    public function checkToken($jwt,$getId=false){
        $authFlag=false;
        if(isset($jwt)){
            try{
                $decoded=JWT::decode($jwt,keyOrKeyArray: new Key($this->key,'HS256'));
            }catch(\DomainException $ex){
                $authFlag=false;
            }catch(ExpiredException $ex){
                $authFlag=false;
            }
            if(!empty($decoded)&&is_object($decoded)&&isset($decoded->iss)){
                $authFlag=true;
            }
            if($getId && $authFlag){
                return $decoded;
            }
        }
        return $authFlag;
    }
}
