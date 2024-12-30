<?php

namespace App\Http\Controllers\UserApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Telegram\Bot\Laravel\Facades\Telegram;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

// Models
use App\Models\User;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Helpers\Firebase;

class Commands extends Controller
{
    private $max_size_file;
    private $allowed_file_type;

    public function __construct()
    {
        $this->max_size_file = 7000000; // 7 Mb
        $this->allowed_file_type = ['jpg','jpeg','png'];
    }
    /**
     * @OA\POST(
     *     path="/api/v1/user/create",
     *     summary="Create user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User create is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User created"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Item is exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="This email or username is already been used")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="The name field is required"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */  
    public function createUser(Request $request){
        try{
            $validator = Validation::getValidateCreateUser($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $check = Generator::checkUser($request->username, $request->email);

                if(!$check){
                    $id = Generator::getUUID();
                    $profile_image = null;

                    if ($request->hasFile('file')) {
                        $file = $request->file('file');
                        if ($file->isValid()) {
                            $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
    
                            $file_ext = $file->getClientOriginalExtension();
                            // Validate file type
                            if (!in_array($file_ext, $this->allowed_file_type)) {
                                return response()->json([
                                    'status' => 'failed',
                                    'message' => Generator::getMessageTemplate("custom", 'The file must be a '.implode(', ', $this->allowed_file_type).' file type'),
                                ], Response::HTTP_UNPROCESSABLE_ENTITY);
                            }
                            // Validate file size
                            if ($file->getSize() > $this->max_size_file) {
                                return response()->json([
                                    'status' => 'failed',
                                    'message' => Generator::getMessageTemplate("custom", 'The file size must be under '.($this->max_size_file/1000000).' Mb'),
                                ], Response::HTTP_UNPROCESSABLE_ENTITY);
                            }
            
                            // Helper: Upload inventory image
                            try {
                                $profile_image = Firebase::uploadFile('user', $id, $request->username, $file, $file_ext); 
                            } catch (\Exception $e) {
                                return response()->json([
                                    'status' => 'error',
                                    'message' => Generator::getMessageTemplate("unknown_error", null),
                                ], Response::HTTP_INTERNAL_SERVER_ERROR);
                            }
                        } else {
                            return response()->json([
                                'status' => 'failed',
                                'message' => Generator::getMessageTemplate("custom", "image is not valid"),
                            ], Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                    }

                    $user = User::create([
                        'id' => $id,
                        'firebase_id' => $request->firebase_id,
                        'fullname' => $request->fullname,
                        'username'  => $request->username,
                        'email' => $request->email,
                        'password' => $request->password,
                        'gender' => $request->gender,
                        'image_url' => $profile_image,
                        'born_at' => $request->born_at,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => null,
                        'deleted_at' => null
                    ]);
            
                    if($user){
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("custom", 'account is registered'),
                            'data' => $user
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => Generator::getMessageTemplate("unknown_error", null)
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("conflict", 'email or username')
                    ], Response::HTTP_CONFLICT);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
 
    /**
     * @OA\PUT(
     *     path="/api/v1/user/edit",
     *     summary="Edit user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User update is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="account updated"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Item is exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="This email or username is already been used")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="The name field is required"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */ 
    public function updateUser(Request $request){
        try{
            $validator = Validation::getValidateUpdateUser($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $user_id = $request->user()->id;

                $user = User::where('id',$user_id)->update([
                    'fullname' => $request->fullname,
                    'email' => $request->email,
                    'gender' => $request->gender,
                    'born_at' => $request->born_at,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
        
                if($user > 0){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("update", 'account')
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'user'),
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/user/edit_telegram_id",
     *     summary="Edit user telegram ID",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User telegram id update is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Telegram id updated"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="The name field is required"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */ 
    public function updateTelegramId(Request $request){
        try{
            $validator = Validation::getValidateUpdateTelegramID($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $user_id = $request->user()->id;
                $user_data = User::getProfile($user_id);
                $telegram_user_id_old = $user_data->telegram_user_id;

                $user = User::where('id',$user_id)->update([
                    'telegram_user_id' => $request->telegram_user_id,
                ]);

                if($user > 0){
                    if($telegram_user_id_old != null){
                        $response = Telegram::sendMessage([
                            'chat_id' => $telegram_user_id_old,
                            'text' => "Hello $user_data->username,\nYour account has been signout from this device",
                            'parse_mode' => 'HTML'
                        ]);
                    }
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("update", 'telegram ID')
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'user'),
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/user/edit_telegram_id_qrcode",
     *     summary="Edit user telegram ID via QR Code",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User telegram id update is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Telegram id updated"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="The name field is required"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */ 
    public function updateTelegramIdQRCode(Request $request){
        try{
            $validator = Validation::getValidateUpdateTelegramID($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $user_id = $request->id;
                $user_data = User::getProfile($user_id);
                $telegram_user_id_old = $user_data->telegram_user_id;

                $user = User::where('id',$user_id)->update([
                    'telegram_user_id' => $request->telegram_user_id,
                ]);

                if($user > 0){
                    if($telegram_user_id_old != null){
                        $response = Telegram::sendMessage([
                            'chat_id' => $telegram_user_id_old,
                            'text' => "Hello $user_data->username,\nYour account has been signout from this device",
                            'parse_mode' => 'HTML'
                        ]);
                    }
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("update", 'telegram ID')
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'user'),
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * @OA\PUT(
     *     path="/api/v1/user/edit_timezone",
     *     summary="Edit user timezone",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User timezone update is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Timezone updated"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="The name field is required | timezone is invalid"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */ 
    public function updateTimezone(Request $request){
        try{
            $validator = Validation::getValidateUpdateUserTimezone($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $timezone = $request->timezone;

                if(Validation::isValidUTCOffset($timezone)){
                    $user_id = $request->user()->id;

                    $user = User::where('id',$user_id)->update([
                        'timezone' => $timezone,
                    ]);
            
                    if($user > 0){
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("update", 'timezone')
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("not_found", 'user')
                        ], Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'result' => Generator::getMessageTemplate("custom", 'timezone is invalid')
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * @OA\PUT(
     *     path="/api/v1/user/image",
     *     summary="Edit user profile image",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User image update is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Profile image updated"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="The name field is required | timezone is invalid"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */ 
    public function updateImage(Request $request){
        try{
            $validator = Validation::getValidateUpdateImageUser($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $user_id = $request->user()->id;

                $user = User::where('id',$user_id)->update([
                    'image_url' => $request->image_url,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);

                if($user > 0){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("update", 'profile image')
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'user')
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
