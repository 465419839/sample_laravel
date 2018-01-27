<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use Mail;

class UsersController extends Controller
{
    public function __construct(){
        $this -> middleware('auth',[
            'except' => ['show','create','store','index','destory','confirmEmail']
        ]);
        $this -> middleware('guest',[
            'only' => ['create']
        ]);
    }

    public function create(){
        return view('users.create');
    }

    public function show(User $user){
        return view('users.show',compact('user'));
    }

    public function store(Request $request){
        $this -> validate($request,[
           'name' => 'required|max:50',
           'email' => 'required|email|unique:users|max:255',
           'password' => 'required|confirmed|min:6'
        ]);
        $user = User::create([
           'name' => $request->name,
           'email' => $request->email,
           'password' => bcrypt($request->password),
        ]);
        $this->sendEmailConfirmationTo($user);
        //Auth::login($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');;
    }

    public function edit(User $user){
        $this ->authorize('update',$user);
        return view("users.edit",compact('user'));
    }

    public function update(User $user,Request $request){
        $this ->authorize('update',$user);
        $this -> validate($request,[
            'name' => 'required|max:30',
            'password' => 'nullable|confirmed|min:6'
        ]);
        $data['name'] = $request -> name;
        if(empty($request -> password)){
            $data['password'] = bcrypt($request->password);
        }
        $user -> update($data);
        session() ->flash('success','个人资料更新成功');
        return redirect() -> route('users.show',$user -> id);
    }

    public function index(){
        $users = User::paginate(10);
        return view('users.index',compact('users'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    protected function sendEmailConfirmationTo($user){
        $view = 'email.confirm';
        $data = compact('user');
        $from = 'sample@test.com';
        $name = 'sample';
        $to = $user -> email;
        $subject = '请进行账户激活';
        Mail::send($view,$data,function($message) use ($from,$name,$to,$subject){
            $message->from($from,$name)->to($to)->subject($subject);
        });
    }

    public function confirmEmail($activate_token){
        $user =User::where('activation_token',$activate_token)->firstOrFail();
        $user -> activated = true ;
        $user -> activation_token = null;
        $user -> save();

        Auth::login($user);
        session() -> flash('success','恭喜激活成功');
        return redirect()->route('users.show',[$user]);
    }
}
