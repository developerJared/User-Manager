<?php

namespace App\Http\Controllers\App;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash as Hash;
use App\User;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthenticateController extends Controller
{
    public function __construct()
    {
        // Apply the jwt.auth middleware to all methods in this controller
        // except for the authenticate method. We don't want to prevent
        // the user from retrieving their token if they don't already have it
        //$this->middleware('jwt.auth', ['except' => ['authenticate','index','checkToken']]);
    }

    public function index(Request $request)
    {
        $url = $request->url();
        if (strpos($url,'app') !== false) {
            return 'Please POST username and password or pin.';
        }
    }

    /*****
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * IF this is not working or if hash is all of a sudden returning false,
     * then check the cost value in the database. make sure that
     * you are only hashing once in the create and
     * update method. Check User Model.
     *
     */
    public function authenticate(Request $request)
    {
        //PIN AUTH
        if($request->has('pin')){
            if( $request->has('id')){
                $user = User::where('id', $request->only(['id']))->first();
                if($user != null){
                    $userPin =  $user['pin'];
                    $givenPin = $request->only(['pin'])['pin'];
                    if(Hash::check((string)$givenPin , (string) $userPin)){
                        $token = JWTAuth::fromUser($user);
                        return response()->json(['token'=>$token,'id' => $user['id']],200);
                    }else{
                        return response()->json(['error' => 'Please provide correct pin'], 401);
                    }
                }else{
                    return response()->json(['error' => 'Please enter valid userID'], 401);
                }
            }else{
                return response()->json(['error' => 'If providing Pin, pass UserID'], 401);
            }
        }

        //PASS AUTH
        if($request->has('password')){
            if($request->has('username')){
                try {
                    $user = User::where('username', $request->only('username')['username'])->first();
                    if($user == null){
                        return response()->json(['error' => 'invalid_credentials'], 401);
                    }
                    if (Hash::check($request->input('password'), $user['password'])){
                        $token = JWTAuth::fromUser($user);
                        // if no errors are encountered we can return a JWT
                        return response()->json(['token'=>$token,'id' => $user['id']],200);
                    }else{
                        return response()->json(['error' => 'could_not_authenticate_password'], 500);
                    }
                } catch (JWTException $e) {
                    // something went wrong
                    return response()->json(['error' => 'could_not_create_token'], 500);
                }
            }else{
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        }else{
            return  response()->json(['error' => 'For pin auth pass User ID, for password auth pass username only'], 401);
        }
    }


    public function checkToken (Request $request){
        $value = $request->header('Authorization');
        //dd($value);
        if($value != null && strpos($value, 'Bearer') !== false) {
            $value = trim(substr($value,6));
            // this will set the token on the object
            JWTAuth::parseToken();
            // and you can continue to chain methods
            //$user = JWTAuth::parseToken()->authenticate();
            $user = JWTAuth::toUser($value);
            return response()->json(['id' => $user['id']],200);
        }else{
            return response()->json(['error' => 'invalid_credentials'], 401);
        }
    }
}
