<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use App\Mail\codeMail;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
    // Laravel authenticate user, if login passvord correct, else redirect with message wrong credentials and don't execute another php code
        $request->authenticate();
    
        $user=Auth::user();
        if($user->no2fa){
    // check user don't use 2 factor auth, lake usually Breeze do
            $request->session()->regenerate();
    
            return redirect()->intended(RouteServiceProvider::HOME);
        }else{
    // put id to session
            $request->session()->put('auth_user',$user->id);
    // generate 4 digit code
            $user->code=rand(1000,9999);
    // put code to session
            $request->session()->put('auth_code',$user->code);
    // save code to user table for debug, it isn't necessary
            $user->save();
    // send code to email, or you can send sms or google auth
            Mail::to($user->email)->send(new codeMail(['code'=>$user->code]));
    // attention! logout user
            Auth::guard('web')->logout();
    // redirect to form with input code
            return redirect('code');
        }
    }
    
    public function code(Request $request){
    // check if session has user id and code
        if($request->session()->has('auth_user') && $request->session()->has('auth_code')){
            return view('auth.code');
        }else{
    // if just open code url without previous steps - redirect to register, or change this to 'login'
            return redirect('register');
        }
    }
    
    public function codestore(Request $request){
    //check code
        if($request->session()->has('auth_user') && $request->session()->get('auth_code') && $request->post('code')==$request->session()->get('auth_code') ){
        //get ath user with id and code - if you store code in user table
            $user=User::where('id', $request->session()->get('auth_user'))->where('code', $request->session()->get('auth_code'))->first();
            if($user){
            //if user exist - authenticate
                Auth::login($user);
            // clear code from session and user table
                $request->session()->forget('auth_user', 'auth_code');
                $user->code=NULL;
                $user->save();
                $request->session()->regenerate();
            // redirect to dashboard 
                return redirect()->intended(RouteServiceProvider::HOME);
            }
        }else{
            return view('auth.code')->with('wrongcode','Wrong code');
        }
    }
    
    public function resendcode(Request $request){
        // resend code if any trouble with email
        if($request->session()->has('auth_user')){
            $user=User::where('id',$request->session()->get('auth_user'))->first();
            $user->code=rand(1000,9999);
            $request->session()->put('auth_code',$user->code);
            Mail::to($user->email)->send(new codeMail(['code'=>$user->code]));
            $user->save();
            
            return redirect('code');
        }else{
        // if just open resendcode url without previous steps - redirect to register, or change this to 'login'
            return redirect('register');
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}