<?php

namespace App\Http\Controllers\PHPSite;

use Illuminate\Http\Request;
use App;
use App\Http\Controllers\Controller;
use App\Http\Controllers\App\UsersController as appUsers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Base\ConfigurationController;

class AuthenticateController extends Controller
{
    protected $redirectPath = 'site/users';

    public function __construct(appUsers $appUsers){
        $this->middleware('jwt.auth', ['except' => ['authenticate','index']]);
        $this->appUsers = $appUsers;
    }

     /**************************************************
     *  This portion is for anything trying to access
     *  this site outside of the php scope. It is
     *  Required to pass in a token header
     *  to authenticate against.
     **************************************************/

    public function index(Request $request)
    {
        $value = $request->header('authorization');
        if($value != null && strpos($value, 'Bearer') !== false) {
            JWTAuth::parseToken();

            $user = JWTAuth::parseToken()->authenticate();
            $token = JWTAuth::getToken();
            session(['token' => $token,'user' => $user]);
            return redirect('site/users');
        }else{
            return view('site.login',[
                'lab'       =>ConfigurationController::getLocalLab(),
                'packhouse' =>ConfigurationController::getLocalPackhouse()
            ]);
        }
    }

    /****************************************************************
     * Site HTML Authentication from blade template
     * @param Request $request  Information From login form
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     ****************************************************************/

    public function authenticate(Request $request)
    {
        //return redirect('site/users');
        $credentials = $request->only(['username', 'password']);
        try {
            if ($token = JWTAuth::attempt($credentials)){
                $user = JWTAuth::toUser($token);
                session(['token' => $token,'user'=>$user]);
                return redirect('site/users');
            }

            flash()->error("Invalid Credentials");
            return redirect('site/login');

        } catch (JWTException $e) {
           flash()->error("Could not create token.");
        }
    }

}
