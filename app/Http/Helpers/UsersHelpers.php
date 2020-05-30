<?php

namespace App\Http\Helpers;

use App\Role;
use App\Group;
use App\UserGroup;
use Illuminate\Contracts\Auth\UserProvider;
use PhpSpec\Exception\Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\User;

class UsersHelper
{

    public static function getKey($key, $value, $column = null){
        if($column != null){
            $usrObj = User::where($key,$value)->get();
            return $usrObj->$column;
        }
        return User::where($key,$value)->get()->toArray();
    }

    public static function getCurrentUserRole() {
        $userData = UsersHelper::getCurrentUserFromToken();
        if(isset($userData->roles_id)) {
            try {
                $userRole = Role::where('id', $userData->roles_id)->get()->toArray();
                return $userRole[0]['role'];
            } catch (Exception $e) {
                return "Ensure User Assigned Role or : \r\n $e";
            }
        }else {
            return "Please check Token";
        }

    }

    public static function getCurrentUserGroup() {
        $userData = UsersHelper::getCurrentUserFromToken();
        $userGroup = UserGroup::where('users_id',$userData->id)->get()->toArray();
        $group = Group::where('id',$userGroup[0]['groups_id'])->get()->toArray();
        return $group[0]['group'];
    }

    public static function getCurrentUserFromToken() {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        // the token is valid and we have found the user via the sub claim
        return $user;
    }
     public static function isAdmin($user) {
         $userRole = Role::where('id', $user->roles_id)->get()->toArray();
     if($userRole[0]['role'] == "Administrator"){
         return true;
     }else{
         return false;
     }

     }

     /*public function checkUser($data) {

     }
     public function checkUser($data) {

     }public function checkUser($data) {

 }
     */


}