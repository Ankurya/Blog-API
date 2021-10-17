<?php

namespace App\Http\Controllers\api;

use Exception;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Lcobucci\JWT\Parser;
use App\Models\LikeDislike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
// use Tymon\JWTAuth\Exceptions\JWTException;



class UserController extends Controller
{


    public function authSignUp(Request $request)
    {
        // dd($request->all());

        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|between:2,10',
                'email' => 'required|string|email|unique:users',
                'phone_no' => 'required',
                'gender' => 'required',
                'dob' => 'required',
                'hobbies' => 'required',
                'password' => 'required|string|min:6',
                'confirm_password' => 'required|string|same:password',
                'current_address' => 'required',
                'permanent_address' => 'required',
                'lat' => 'required',
                'long' => 'required',
                'file' => 'required|mimes:mpeg,ogg,mp4,webm,3gp,mov,flv,avi,wmv,ts',
                'thumbnail'  => 'required|mimes:jpg,jpeg,png|max:2048',
                'profile_pic' => 'required|mimes:jpg,jpeg,png|max:2048',

            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }


            $file = $request->file("file");
            $fileName = uniqid() . "_" . $file->getClientOriginalName();
            $destinationPath = 'videos';
            $file->move($destinationPath, $fileName);
            $video[] = $fileName;


            $poster = $request->file('thumbnail');
            $posterName = uniqid() . "_" .   $poster->getClientOriginalName();
            $destinationPath = 'thumbnail';
            $poster->move($destinationPath, $posterName);
            $thumbnail[] = $posterName;



            if ($request->hasFile("profile_pic")) {
                $imagedata = [];
                $image = $request->file("profile_pic");
                $imageName = uniqid() . "_" . $image->getClientOriginalName();
                $destinationPath = 'images';
                $image->move($destinationPath, $imageName);
                $imagedata[] = $imageName;
            }

            $data = [

                'username' => $request->username,
                'email' => $request->email,
                'phone_no' => $request->phone_no,
                'password' => Hash::make($request->password),
                'gender' => $request->gender,
                'dob' => $request->dob,
                'hobbies' => $request->hobbies,
                'current_address' => $request->current_address,
                'permanent_address' => $request->permanent_address,
                'lat' => $request->lat,
                'long' => $request->long,
                'profile_pic' => implode(",",  $imagedata),
                'file' => implode(",",  $video),
                'thumbnail' => implode(",",  $thumbnail),

            ];

            $user = User::create($data);

            return response()->json([
                'status' => true,
                'message' => 'user registered succesfully.',
                'response' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function authLogin(Request $request)
    {

        $credentials = request(['email', 'password']);
        try {
            $validator = Validator::make($request->all(), [

                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            if (Auth::attempt([
                'email' => $request->get("email"),
                'password' => $request->get("password"),
            ])) {
                $user = $request->user();
                $token = JWTAuth::fromUser($user);
                $token_data = User::where('id', $user->id)->update(['access_token' => $token]);
                return response()->json(['status' => true, ' message' => 'Login successfully.', 'response' => $user]);


                // return response()->json(['status' => true, ' message' => 'Login successfully.', 'response' => $user]);
            } else {
                return response()->json(['status' => false, 'message' => 'user credential not found.'], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function profileDetail(Request $request)
    {
        // dd($request->all());

        $user = JWTAuth::user();
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
            ]);
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }
            $lat = 51.0258761;
            $long = 4.4775362;
           $distance =  DB::table("users")

                ->select(
                    "users.id",
                    DB::raw("6371 * acos(cos(radians(" . $lat . ")) 

                        * cos(radians(users.lat)) 

                        * cos(radians(users.long) - radians(" . $long . ")) 

                        + sin(radians(" . $lat . ")) 

                        * sin(radians(users.lat))) AS distance")
                                )

                                ->groupBy("users.id")

                                ->get();
                   
            $user = User::where('id', $user->id)->first();
            if ($user != null) {
                return response()->json(['status' => true, 'message' => 'user details', 'response' => $user,'distance'=>$distance]);
            } else {
                throw new Exception('user not found.');
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = JWTAuth::user();
        try {

            $validator = Validator::make($request->all(), [
                'gender' => 'required',
                'dob' => 'required',
                'hobbies' => 'required',
                'current_address' => 'required',
                'permanent_address' => 'required',
                'file' => 'required|mimes:mpeg,ogg,mp4,webm,3gp,mov,flv,avi,wmv,ts',
                'profile_pic' => 'required|mimes:jpg,jpeg,png|max:2048',
                'thumbnail'  => 'required|mimes:jpg,jpeg,png|max:2048',


            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $update = [
                'gender' => $request->gender,
                'dob' => $request->dob,
                'hobbies' => $request->hobbies,
                'current_address' => $request->current_address,
                'permanent_address' => $request->permanent_address,
            ];

            if ($request->hasFile("file")) {

                $file = $request->file("file");
                $fileName = uniqid() . "_" . $file->getClientOriginalName();
                $destinationPath = 'videos';
                $file->move($destinationPath, $fileName);
                $update["file"] = $fileName;
            }
            if ($request->hasFile("thumbnail")) {

                $poster = $request->file('thumbnail');
                $posterName = uniqid() . "_" .   $poster->getClientOriginalName();
                $destinationPath = 'thumbnails';
                $poster->move($destinationPath, $posterName);
                $update["thumbnail"] = $posterName;
            }

            if ($request->hasFile("profile_pic")) {
                $file = $request->file("profile_pic");
                $imageName = uniqid() . "_" . $file->getClientOriginalName();
                $destinationPath = 'images';
                $file->move($destinationPath, $imageName);
                $update["profile_pic"] = $imageName;
            }

            $user = User::where('id', $user->id)->update($update);
            return response()->json(['status' => true, 'message' => 'Data Updated Successfully.'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteAccount($id)
    {
        $user = User::find($id);
        if ($id->exists()) {
            $user->delete();
            return response()->json(['status' => true, 'message' => 'Deleted Successfully.'], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'Account is not Deleted.'], 400);
        }
    }

    public function changePassword(Request $request)
    {
        $user = JWTAuth::user();
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'id'  => 'required|exists:users,id',
                    'oldpassword' => 'required',
                    'newpassword' => 'required',
                    'confirmpassword' => 'required|same:newpassword'


                ]
                ,
                [
                    'id.exists' => 'User not found.',
                ]
            );

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }
            if (Hash::check($request->oldpassword, $user->password)) {

                User::where('id',$user->id)->update(['password' => Hash::make($request->newpassword)]);

                return response()->json(['status' => 'true', 'message' => 'password updated successfully.']);
            } else {
                throw new Exception("old password cannot be new password.");
            }
        } catch (Exception $e) {

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function forgotPassword(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',

            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $status = Password::sendResetLink(
                $request->only('email')
            );

            $status == Password::RESET_LINK_SENT
                ? back()->with('status', __($status))
                : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);

            return response()->json(['status' => true, 'message' => 'email template sent successfully.']);
        } catch (Exception $e) {

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }


    public function createPost(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'title' => 'required',
                'category' => 'required',
                'description' => 'required',
                'profile_pic' => 'required',

                'profile_pic.*' => 'mimes:jpg,jpeg,png,gif|max:2048',


            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $data = [
                'user_id' => $request->user_id,
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
            ];
            if ($request->hasFile("profile_pic")) {

                $fileData = [];
                foreach ($request->file('profile_pic') as $files) {
                    $imageName = uniqid() . "_" . $files->getClientOriginalName();
                    $destinationPath = 'images';
                    $files->move($destinationPath, $imageName);
                    $fileData[] = $imageName;
                }
            }
            $post = Post::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'profile_pic' =>  implode(",",  $fileData),
            ]);
            return response()->json([
                'status' => true,
                'message' => 'post created succesfully.',
                'response' => $post,
            ]);
        } catch (Exception $e) {

            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function postDetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'post_id' => 'required',

            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $postDetail = Post::where('id', $request->post_id)->first();
            if ($postDetail != null) {
                return response()->json(['status' => true, 'message' => 'post details', 'response' => $postDetail]);
            } else {
                throw new Exception('post not found.');
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function viewPost(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|exists:users,id',

                ],
                [
                    'user_id.exists' => 'User not found.',
                ]
            );

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }
            $user = User::find($request->user_id);
            $posts = $user->posts()->get();

            return response()->json(['status' => true, 'username' => $user->username, 'response' => $posts]);
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }



    public function likeDislike(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|exists:users,id',
                    'post_id' => 'required',
                ],
                [
                    'user_id.exists' => 'User not found.',
                ]
            );

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $like = LikeDislike::where(['user_id' => $request->user_id, 'post_id' =>  $request->post_id])->first();
            if ($like == null) {
                $createLike = LikeDislike::create(['user_id' => $request->user_id, 'post_id' => $request->post_id, 'status' => '1']);
                $like = LikeDislike::all()->count();
                return response()->json(['status' => true, 'message' => 'new like created successfully.', 'like' => $like, 'response' => $createLike]);
            }

            if ($like->status == 1) {
                $like->update(['status' => '0']);
                return response()->json(['status' => true, 'message' => 'post disliked successfully.', 'response' => $like]);
            } else {
                $like->update(['status' => '1']);
                return response()->json(['status' => true, 'message' => 'post liked successfully.', 'response' => $like]);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function commentPost(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|exists:users,id',
                    'post_id' => 'required',
                    'message' => 'required',
                ],
                [
                    'user_id.exists' => 'User not found.',
                ]
            );

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $data = [
                'user_id' => $request->user_id,
                'post_id' => $request->post_id,
                'message' => $request->message,
            ];

            $comment = Comment::create($data);
            return response()->json(['status' => true, 'message' => 'Comment created successfully', 'response' => $comment]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function deletePost(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|exists:users,id',
                    'post_id' => 'required',
                ],
                [
                    'user_id.exists' => 'User not found.',
                ]
            );

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $post = Post::where('id', $request->post_id)->where('user_id', $request->user_id);
            $post->delete();
            return response()->json(['status' => true, 'message' => 'post deleted successfully', 'response' => $post]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updatePost(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|exists:users,id',
                    'post_id' => 'required',
                    'title' => 'required',
                    'category' => 'required',
                    'description' => 'required',
                    'profile_pic' => 'required',
                    'profile_pic.*' => 'mimes:jpg,jpeg,png,gif|max:2048',
                ],
                [
                    'user_id.exists' => 'User not found.',
                ]
            );

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }
            if ($request->hasFile("profile_pic")) {

                $fileData = [];
                foreach ($request->file('profile_pic') as $files) {
                    $imageName = uniqid() . "_" . $files->getClientOriginalName();
                    $destinationPath = 'images';
                    $files->move($destinationPath, $imageName);
                    $fileData[] = $imageName;
                }
            }

            $update = Post::where('id', $request->post_id)->where('user_id', $request->user_id)->update([
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'profile_pic' =>  implode(",",  $fileData),
            ]);
            return response()->json(['status' => true, 'message' => 'post updated successfully', 'response' => $update]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function allUser(Request $request)
    {

        $user = JWTAuth::user();
        try {
            $validator = Validator::make($request->all(), [
            ]);
            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }
                   
            $user = User::all();
            if ($user != null) {
                return response()->json(['status' => true, 'message' => 'user details', 'response' => $user]);
            } else {
                throw new Exception('user not found.');
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

}
