<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class SessionController extends Controller
{
    public function __construct(){
        $this ->middleware('guest',[
            'only' => ['create']
        ]);
    }

    public function create(){
        return view("sessions.create");
    }

    public function store(Request $request){
        $credentials = $this -> validate($request,[
                            'email' => 'required|email|max:255',
                            'password' => 'required',
                        ]);
        //if (Auth::attempt($credentials)) {
        if (Auth::attempt(['email' => $request->email , 'password' => $request->password],$request -> has('remember'))) {
            // 登录成功后的相关操作
            session() ->flash('success','欢迎回来');
            //Auth::user() 方法来获取 当前登录用户 的信息
            return redirect()->intended(route('users.show',[Auth::user()]));
        } else {
            // 登录失败后的相关操作
            session() ->flash('danger','很抱歉，您的邮箱和密码不匹配');
            return redirect() -> back();
        }

    }

    public function destroy(){
        Auth::logout();
        session() -> flash('success','你已经成功退出');
        return redirect('login');
    }
}
