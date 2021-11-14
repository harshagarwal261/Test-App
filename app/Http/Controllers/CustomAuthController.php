<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Hash;
use Session;
use App\User;
use App\Models\Role;
use App\Models\Industry;
use App\Models\UserIndustry;
use App\Models\UserRole;
use App\Models\UserView;
use Illuminate\Support\Facades\Auth;
use DB;

class CustomAuthController extends Controller
{

    public function index()
    {
        return view('auth.login');
    }  
      

    public function customLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
   
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->intended('dashboard')
                        ->withSuccess('Signed in');
        }
  
        return redirect("login")->withSuccess('Login details are not valid');
    }



    public function registration()
    {
		$roles = Role::all();
		$industries = Industry::all();
		
        return view('auth.registration')->with('roles', $roles)->with('industries', $industries);
    }
      

    public function customRegistration(Request $request)
    {  
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
			'role' => 'required',
			'industry' => 'required'
        ]);
           
        $data = $request->all();
        $check = $this->create($data);
         
        return redirect("dashboard")->withSuccess('You have signed-in');
    }


    public function create(array $data)
    {
      $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password'])
      ]);
	  
	  //User Role mapping insertion
	  UserRole::create([
		'user_id' => $user->id,
		'role_id' => $data['role']
	  ]);
	  //User Industry mapping insertion
	  UserIndustry::create([
		'user_id' => $user->id,
		'industry_id' => $data['industry']
	  ]);
	  
	  return $user;
    }    
    

    public function dashboard(Request $request, $roleParam = '', $industryParam = '', $sortParam = '')
    {
		if(Auth::check()){
			
			$roleParam = explode("_", str_replace("role:", "", $roleParam));
			$industryParam = explode("_", str_replace("industry:", "", $industryParam));
			$sortParam = str_replace("sort:", "", $sortParam);
			
			$filteredUsers = array();
			if (!empty($roleParam)) {
				$users = UserRole::select('user_id')->whereIn('role_id', $roleParam)->get();
				if (!empty($users)) {
					foreach ($users as $user) {
						$filteredUsers[] = $user->user_id;
					}
				}
			} 
			
			if (!empty($industryParam)) {
				$users = UserIndustry::select('user_id')->whereIn('industry_id', $industryParam)->get();
				if (!empty($users)) {
					foreach ($users as $user) {
						$filteredUsers[] = $user->user_id;
					}
				}
			}
			
			array_unique($filteredUsers);
			
			if (!empty($filteredUsers)) {
				if (!empty($sortParam)) {
					switch($sortParam) {
						case '1':
							$users = User::select('users.*', DB::raw('SUM(user_views.suggestion_id) As total_views'))->join('user_views','user_views.suggestion_id','=','users.id')->with('role')->with('industry')->where('users.id', '!=', Auth::user()->id)->whereIn("users.id", $filteredUsers)->groupBy('user_views.suggestion_id')->paginate();
							break;
						case '2':
							$users = User::with('role')->with('industry')->where('id', '!=', Auth::user()->id)->whereIn("id", $filteredUsers)->orderBy('created_at', 'asc')->paginate();
							break;
						case '3':
							$users = User::with('role')->with('industry')->where('id', '!=', Auth::user()->id)->whereIn("id", $filteredUsers)->orderBy('profile_score', 'asc')->paginate();
							break;
					}
				} else {
					$users = User::select('users.*', DB::raw('SUM(user_views.suggestion_id) As total_views'))->join('user_views','user_views.suggestion_id','=','users.id')->with('role')->with('industry')->where('users.id', '!=', Auth::user()->id)->whereIn("users.id", $filteredUsers)->groupBy('user_views.suggestion_id')->paginate();
				}
			} else {
				if (!empty($sortParam)) {
					switch($sortParam) {
						case '1':
							$users = User::select('users.*', DB::raw('SUM(user_views.suggestion_id) As total_views'))->join('user_views','user_views.suggestion_id','=','users.id')->with('role')->with('industry')->where('users.id', '!=', Auth::user()->id)->groupBy('user_views.suggestion_id')->paginate();
							break;
						case '2':
							$users = User::with('role')->with('industry')->where('id', '!=', Auth::user()->id)->orderBy('created_at', 'asc')->paginate();
							break;
						case '3':
							$users = User::with('role')->with('industry')->where('id', '!=', Auth::user()->id)->orderBy('profile_score', 'asc')->paginate();
							break;
					}
				} else {
					$users = User::select('users.*', DB::raw('SUM(user_views.suggestion_id) As total_views'))->join('user_views','user_views.suggestion_id','=','users.id')->with('role')->with('industry')->where('users.id', '!=', Auth::user()->id)->groupBy('user_views.suggestion_id')->paginate();
				}
			}
			
			//Get Roles
			$roles = Role::all();
			//Get Industries
			$industries = Industry::all();
			if ($users) {
				foreach ($users as $user) {
					$userView = UserView::where('user_id', '=', Auth::user()->id)->where('suggestion_id', '=', $user->id)->first();
					if (!empty($userView->id)) {
						//Increment views
						$userView->increment('views');
					} else {
						//Insert View entry
						UserView::create([
							'user_id' => Auth::user()->id,
							'suggestion_id' => $user->id,
							'views' => 1
						]);
					}
				}
			}
			
			$url = action([CustomAuthController::class, 'dashboard']);
			
			return view('dashboard')->with('users', $users)->with('roles', $roles)->with('industries', $industries)->with('url', $url)->with('roleParam', $roleParam)->with('industryParam', $industryParam)->with('sortParam', $sortParam);
        }
  
        return redirect("login")->withSuccess('You are not allowed to access');
    }
    

    public function signOut() {
        Session::flush();
        Auth::logout();
  
        return Redirect('login');
    }
}