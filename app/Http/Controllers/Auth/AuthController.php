<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRegisterRequest;
use App\Http\Requests\StoreTutorRegisterRequest;
use App\Http\Requests\UpdateAuthRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\Auth\AuthControllerService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthControllerService $authControllerService)
    {
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Send register OTP",
    *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password","password_confirmation"},
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OTP sent",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/StandardSuccess"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="otp", type="string")
     *                     )
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function sendRegisterOtp(Request $request)
    {
        $result = $this->authControllerService->sendRegisterOtp($request);

        return $this->respond($result);
    }

    /**
     * @OA\Patch(
     *     path="/api/register/verify",
     *     tags={"Auth"},
    *     summary="Verify register OTP",
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             required={"otp"},
    *             @OA\Property(property="email", type="string"),
    *             @OA\Property(property="temp_token", type="string"),
    *             @OA\Property(property="otp", type="string")
    *         )
    *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified",
    *         @OA\JsonContent(
    *             allOf={
    *                 @OA\Schema(ref="#/components/schemas/StandardSuccess"),
    *                 @OA\Schema(
    *                     @OA\Property(property="data", type="object",
    *                         @OA\Property(property="identifier", type="string"),
    *                         @OA\Property(property="temp_token", type="string")
    *                     )
    *                 )
    *             }
    *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="OTP invalid",
     *         @OA\JsonContent(ref="#/components/schemas/StandardError")
     *     )
     * )
     */
    public function verifyRegisterOtp(Request $request)
    {
        $result = $this->authControllerService->verifyRegisterOtp($request);

        return $this->respond($result);
    }

    /**
     * @OA\Patch(
     *     path="/api/register/resend-otp",
     *     tags={"Auth"},
     *     summary="Resend register OTP",
    *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/StandardSuccess"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="otp", type="string")
     *                     )
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function resendRegisterOtp(Request $request)
    {
        $result = $this->authControllerService->resendRegisterOtp($request);

        return $this->respond($result);
    }

    /**
     * @OA@Post(
     *     path="/api/register/parent",
     *     tags={"Auth"},
     *     summary="Send parent register OTP",
    *     @OA@RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password","password_confirmation"},
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OTP sent",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/StandardSuccess"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="otp", type="string"),
     *                         @OA\Property(property="temp_token", type="string")
     *                     )
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function sendParentRegisterOtp(Request $request)
    {
        $result = $this->authControllerService->sendParentRegisterOtp($request);

        return $this->respond($result);
    }

    /**
     * @OA\Post(
     *     path="/api/register/parent/child",
     *     tags={"Auth"},
     *     summary="Send child email OTP for parent registration",
    *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"temp_token","child_email"},
     *             @OA\Property(property="temp_token", type="string"),
     *             @OA\Property(property="child_email", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/StandardSuccess"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="otp", type="string")
     *                     )
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function sendParentChildOtp(Request $request)
    {
        $result = $this->authControllerService->sendParentChildOtp($request);

        return $this->respond($result);
    }

    /**
     * @OA\Patch(
     *     path="/api/register/parent/child/verify",
     *     tags={"Auth"},
     *     summary="Verify child email OTP and create parent",
    *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"temp_token","child_email","otp"},
     *             @OA\Property(property="temp_token", type="string"),
     *             @OA\Property(property="child_email", type="string"),
     *             @OA\Property(property="otp", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Parent registered",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/StandardSuccess"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="token", type="string")
     *                     )
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function verifyParentChildOtp(Request $request)
    {
        $result = $this->authControllerService->verifyParentChildOtp($request);

        return $this->respond($result);
    }

    /**
     * @OA\Patch(
     *     path="/api/register/student",
     *     tags={"Auth"},
     *     summary="Register student",
    *     @OA\RequestBody(
     *         required=true,
    *         content={
    *             @OA\MediaType(
    *                 mediaType="application/json",
    *                 @OA\Schema(
    *                     required={"email","password","password_confirmation","name","gender","date_of_birth","telephone_number","province","regency","district","subdistrict","street","latitude","longitude","class_id"},
    *                     @OA\Property(property="email", type="string"),
    *                     @OA\Property(property="password", type="string"),
    *                     @OA\Property(property="password_confirmation", type="string"),
    *                     @OA\Property(property="name", type="string"),
    *                     @OA\Property(property="gender", type="string", enum={"male","female"}),
    *                     @OA\Property(property="date_of_birth", type="string", example="2005-01-15"),
    *                     @OA\Property(property="telephone_number", type="string"),
    *                     @OA\Property(property="province", type="string"),
    *                     @OA\Property(property="regency", type="string"),
    *                     @OA\Property(property="district", type="string"),
    *                     @OA\Property(property="subdistrict", type="string"),
    *                     @OA\Property(property="street", type="string"),
    *                     @OA\Property(property="religion", type="string", enum={"not set","islam","kristen","katolik","hindu","buddha","konghucu"}),
    *                     @OA\Property(property="latitude", type="number"),
    *                     @OA\Property(property="longitude", type="number"),
    *                     @OA\Property(property="class_id", type="integer")
    *                 )
    *             ),
    *             @OA\MediaType(
    *                 mediaType="multipart/form-data",
    *                 @OA\Schema(
    *                     required={"email","password","password_confirmation","name","gender","date_of_birth","telephone_number","province","regency","district","subdistrict","street","latitude","longitude","class_id"},
    *                     @OA\Property(property="email", type="string"),
    *                     @OA\Property(property="password", type="string"),
    *                     @OA\Property(property="password_confirmation", type="string"),
    *                     @OA\Property(property="name", type="string"),
    *                     @OA\Property(property="gender", type="string", enum={"male","female"}),
    *                     @OA\Property(property="date_of_birth", type="string"),
    *                     @OA\Property(property="telephone_number", type="string"),
    *                     @OA\Property(property="profile_photo", type="string", format="binary"),
    *                     @OA\Property(property="province", type="string"),
    *                     @OA\Property(property="regency", type="string"),
    *                     @OA\Property(property="district", type="string"),
    *                     @OA\Property(property="subdistrict", type="string"),
    *                     @OA\Property(property="street", type="string"),
    *                     @OA\Property(property="religion", type="string", enum={"not set","islam","kristen","katolik","hindu","buddha","konghucu"}),
    *                     @OA\Property(property="latitude", type="number"),
    *                     @OA\Property(property="longitude", type="number"),
    *                     @OA\Property(property="class_id", type="integer")
    *                 )
    *             )
    *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Student registered",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/StandardSuccess"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="token", type="string")
     *                     )
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function storeStudentRegister(StoreStudentRegisterRequest $request)
    {
        $result = $this->authControllerService->storeStudentRegister($request);

        return $this->respond($result);
    }

    /**
     * @OA\Patch(
     *     path="/api/register/tutor",
     *     tags={"Auth"},
     *     summary="Register tutor",
    *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
    *                     required={"email","password","password_confirmation","name","gender","date_of_birth","telephone_number","province","regency","district","subdistrict","street","latitude","longitude","bank_code","account_number"},
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="password", type="string"),
     *                     @OA\Property(property="password_confirmation", type="string"),
     *                     @OA\Property(property="name", type="string"),
    *                     @OA\Property(property="gender", type="string", enum={"male","female"}),
     *                     @OA\Property(property="date_of_birth", type="string"),
     *                     @OA\Property(property="telephone_number", type="string"),
     *                     @OA\Property(property="province", type="string"),
     *                     @OA\Property(property="regency", type="string"),
     *                     @OA\Property(property="district", type="string"),
     *                     @OA\Property(property="subdistrict", type="string"),
     *                     @OA\Property(property="street", type="string"),
    *                     @OA\Property(property="religion", type="string", enum={"not set","islam","kristen","katolik","hindu","buddha","konghucu"}),
     *                     @OA\Property(property="latitude", type="number"),
     *                     @OA\Property(property="longitude", type="number"),
    *                     @OA\Property(property="bank_code", type="string"),
    *                     @OA\Property(property="account_number", type="string"),
    *                     @OA\Property(property="bank", type="string", deprecated=true, description="Legacy alias of bank_code"),
    *                     @OA\Property(property="rekening", type="string", deprecated=true, description="Legacy alias of account_number")
     *                 )
     *             ),
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
    *                     required={"email","password","password_confirmation","name","gender","date_of_birth","telephone_number","province","regency","district","subdistrict","street","latitude","longitude","bank_code","account_number"},
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="password", type="string"),
     *                     @OA\Property(property="password_confirmation", type="string"),
     *                     @OA\Property(property="name", type="string"),
    *                     @OA\Property(property="gender", type="string", enum={"male","female"}),
     *                     @OA\Property(property="date_of_birth", type="string"),
     *                     @OA\Property(property="telephone_number", type="string"),
     *                     @OA\Property(property="profile_photo", type="string", format="binary"),
     *                     @OA\Property(property="province", type="string"),
     *                     @OA\Property(property="regency", type="string"),
     *                     @OA\Property(property="district", type="string"),
     *                     @OA\Property(property="subdistrict", type="string"),
     *                     @OA\Property(property="street", type="string"),
    *                     @OA\Property(property="religion", type="string", enum={"not set","islam","kristen","katolik","hindu","buddha","konghucu"}),
     *                     @OA\Property(property="latitude", type="number"),
     *                     @OA\Property(property="longitude", type="number"),
    *                     @OA\Property(property="bank_code", type="string"),
    *                     @OA\Property(property="account_number", type="string"),
    *                     @OA\Property(property="bank", type="string", deprecated=true, description="Legacy alias of bank_code"),
    *                     @OA\Property(property="rekening", type="string", deprecated=true, description="Legacy alias of account_number")
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tutor registered",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/StandardSuccess"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="token", type="string")
     *                     )
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function storeTutorRegister(StoreTutorRegisterRequest $request)
    {
        $result = $this->authControllerService->storeTutorRegister($request);

        return $this->respond($result);
    }

    /**
     * @OA\Post(
     *     path="/api/forgot-password",
     *     tags={"Auth"},
     *     summary="Request forgot password OTP",
    *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/StandardSuccess"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data", type="object",
     *                         @OA\Property(property="otp", type="string"),
     *                         @OA\Property(property="temp_token", type="string")
     *                     )
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $result = $this->authControllerService->forgotPassword($request);

        return $this->respond($result);
    }

    /**
     * @OA\Patch(
     *     path="/api/reset-password",
     *     tags={"Auth"},
     *     summary="Reset password",
    *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","password","password_confirmation"},
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset",
     *         @OA\JsonContent(ref="#/components/schemas/StandardSuccess")
     *     )
     * )
     */
    public function resetPassword(UpdateAuthRequest $request)
    {
        $result = $this->authControllerService->resetPassword($request);

        return $this->respond($result);
    }
}
