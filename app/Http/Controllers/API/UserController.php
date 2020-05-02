<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Hash;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
////https://www.youtube.com/watch?v=wM4L_yDGqpo&list=PLB4AdipoHpxaHDLIaMdtro1eXnQtl_UvE
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //////$this->authorize('isAdmin');

        ////비디오에는 아래처럼 되어있으나 ==>
        if(\Gate::allows('isAdmin') || \Gate::allows('isAuthor'))
        {
            return User::latest()->paginate(5);
        } 
        
        ////==>멀티파라메타 전송을 시험하기위해 아래처럼 변경 하였으나 pagination에서
        ////에러가 발생하여 해결 방법을 찾는 중.
        // if(\Gate::allows('isAdmin') || \Gate::allows('isAuthor'))
        // {
        //     $cnt = 55;
        //     $result = User::latest()->paginate(5);
        //     return response (['result' => $result, 'count' => $cnt]);           
        // }            
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        return User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'type' => $request['type'],
            'bio' => $request['bio'],
            'photo' => $request['photo'],
            'password' => Hash::make($request['password']),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function profile()
    {
        return auth('api')->user();
    }

    public function updateProfile(Request $request)
    {        
        $user = auth('api')->user();

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|min:8'
        ]);

        $currentPhoto = $user->photo;
           
        if($request->photo != $currentPhoto){
            $name = time().'.' . explode('/', explode(':', substr($request->photo, 0, strpos
                    ($request->photo, ';')))[1])[1];

            \Image::make($request->photo)->save(public_path('img/profile/').$name);

            $request->merge(['photo' => $name]);
            
            $userPhoto = public_path('img/profile/').$currentPhoto;
            
            if(file_exists($userPhoto)){
                @unlink($userPhoto);
            } 
        }

        if(!empty($request->password)){
            $request->merge(['password' => Hash::make($request['password'])]);
        }

        $user->update($request->all());
        return ['message' => 'success'];
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:8'
        ]);

        $user->update($request->all());

        return ['message' => 'update the user info'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('isAdmin');

        $user = User::findOrFail($id);

        $user->delete();

        return ['message' => 'user deleted'];
    }

    public function search()
    {
        if ($search = \Request::get('q')){
            $users = User::where(function($query) use ($search){
                $query->where('name','LIKE', "%$search%")
                      ->orWhere('email','LIKE',"%$search%")
                      ->orWhere('type','LIKE',"%$search%");
            })->paginate(5);
        }else{
            return User::latest()->paginate(5);
        }

        return $users;
    }

}
