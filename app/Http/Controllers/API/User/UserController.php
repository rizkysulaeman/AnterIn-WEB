<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
class UserController extends Controller
{   
    use HasApiTokens;
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
        
            'nama'=>'required',
            'nohp'=>'required',
            'email'=>'required|email',
            'password'=>'required',
            'role_id'=>'required',
            'alamat',
            'gaji',


        ]);



        if ($validator->fails()){
            return response()->json([
                'success'=>false,
                'message' =>'Data Yang Anda Masukan Salah!',
                'data' => $validator->errors()
            ]);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        $tokenResult = $user->createToken('auth_token');
        $success['token'] = $tokenResult->plainTextToken; 
        $success['nama'] = $user->nama;
        return response()->json([
            'success' => true,
            'message'=>'Data Berhasil Diinput',
            'data'=>$success


        ]);
    }



    public function login(Request $request)
    {
      
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $auth = Auth::user();
            
            // abaikan error ini guys
            $tokenResult = $auth->createToken('auth_token');
            $success['token'] = $tokenResult->plainTextToken; 
            $success['nama'] = $auth->nama;
            $success['role_id'] = $auth->role_id;

            return response()->json([
                'success' => true,
                'message' => 'Login Berhasil',
                'data' => $success
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Kesalahan Data !',
            ], 401);
        }

        //Logout
    }
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        $auth = Auth::user();
        $success['nama'] = $auth->nama;

        return response()->json([
            'message' => 'Berhasil Logout',
            'Akun Yang Logout Adalah' => $success,
        ], 200);
    }


    public function index(Request $request){
        $user = $request->user();
        if ($user){
            return response()->json([
                'message' => true,
                'data' => $user
            ],200);
        }else{
            return response()->json([
                'message' => false,
                'data' => 'Data User Tidak Ditemukan',
            ],404);
        }
    }

    public function datasemuauser(){
        $user = User::all();
        if ($user){
            return response()->json([
                'message' => true,
                'data' => $user
            ],200);
        }else{
            return response()->json([
                'message' => false,
                'data' => 'Data User Tidak Ditemukan',
            ],404);
        }
    }

    public function updatenohp(Request $request){
        $user = $request->user();

        $request->validate([
            'nohp' => 'required',
        ]);

        if (!$user){
            return response()->json([
                'message' => 'Data User Tidak Ditemukan',
                'data' => 'Data User Tidak Ditemukan',
            ],404);
        }

        $user->update($request->only(['nohp']));

        return response()->json([
            'message' => 'Data Berhasil Diupdate',
            'data' => $user,
            'succes'=>true
        ], 200);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'alamat' => 'required',
        ]);

        if (!$user) {
            return response()->json([
                'message' => 'Data User Tidak / Ada Bermasalah',
                 'succes'=>false
            ], 404);
        }
        $user->update($request->only(['alamat']));
        return response()->json([
            'message' => 'Data Berhasil Iinput',
            'data' => $user,
            'succes'=>true
        ], 200);
    }

    public function updategajikurir(Request $request, $nama_kurir){
  
        $user = User::where('nama', $nama_kurir)->first();
    
        if (!$user) {
            return response()->json([
                'message' => 'Kurir tidak ditemukan',
                'success' => false
            ], 404);
        }
    
        $request->validate([
            'gaji' => 'required|numeric', 
        ]);
    
        $gaji_sekarang = $user->gaji;
        $gaji_baru = $gaji_sekarang + $request->gaji;
        $user->update(['gaji' => $gaji_baru]);
    
        return response()->json([
            'message' => 'Data Gaji Kurir Berhasil Ditambahkan',
            'data' => $user,
            'success' => true
        ], 200);
    }

    public function delete($id){
        $user = User::find($id);
        if (!$user){
            return response()->json([
                'message' => 'Delete Error: User Not Found',
            ],404);
        }
        $user->delete();
        $success['nama'] = $user->nama;
        return response()->json([
            'message' => 'Delete Success',
            'data' => $success
        ],200);          
    }

    public function getdatakurir(){
        $kurir = User::where('role_id', 3)->get();
        return response()->json([
            'message' => true,
            'data' => $kurir
        ],200);
    }
}

