<?php

namespace App\Http\Controllers;

use App\Models\RegularUser;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function show()
    {
        
        $user = $this->user();
        $profile = $user->regularProfile;
        $projectIds = $user->projectIds();
        $this->authorize('view',$user);

        $stats = [
            'projects'        => $projectIds->count(),
            'completed_tasks' => $user->completedTaskCount(),
            'active_tasks'    => $user->activeTaskCount(),
        ];
        $joinedAt = $user->created_at
            ? $user->created_at->format('d M Y')
            : null;


        return view('pages.profile', [
            'user'                => $user,
            'stats'               => $stats,
            'recentProjects'      => $user->recentProjects(),
            'profilePicUrl'       => $user->profilePictureUrl(),
            'canRemoveProfilePic' => FileController::hasCustomProfile($profile?->profile_pic ?? null),
            'defaultProfilePicUrl'  => FileController::defaultProfile(),
            'hasPendingProfilePic'  => false,
            'joinedAt' => $joinedAt,
            'showPasswordFields' => false,
        ]);
    }


    public function update(Request $request)
    {
        $user = $this->user();
        $this->authorize('update',$user);
        $profile = $user->regularProfile ?? new RegularUser(['user_id' => $user->user_id]);
        if (! $profile->exists) {
            $profile->save();
        }

        // -----------------------------
        // VALIDATION
        // -----------------------------
        $validated = $request->validate([
            'username' => [
                'required', 'string', 'max:250',
                Rule::unique('users', 'username')->ignore($user->user_id, 'user_id'),
            ],
            'email' => [
                'required', 'email', 'max:250',
                Rule::unique('users', 'email')->ignore($user->user_id, 'user_id'),
            ],
            'profile_pic' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'remove_profile_pic'=> ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['disponível', 'offline', 'customizável'])],
            'custom_status' => [
                'nullable',
                'string',
                'max:60',
                Rule::requiredIf($request->status === 'customizável'),
            ],
        ]);

        // -----------------------------
        // UPDATE BASIC USER FIELDS
        // -----------------------------
        $user->updateBasicInfo($validated);

        // -----------------------------
        // PASSWORD CHANGE
        // -----------------------------
        if ($request->filled('password')) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
                'password'         => ['required', 'confirmed', 'min:8'],
            ]);

            $user->password = Hash::make($request->password);
            $user->save();
        }

        // -----------------------------
        // PROFILE PICTURE UPDATE
        // -----------------------------
        if ($request->boolean('remove_profile_pic')) {

            FileController::deleteProfile($profile->profile_pic);
            $profile->setProfilePicture(null);

        } elseif ($request->hasFile('profile_pic')) {
            try {
                FileController::deleteProfile($profile->profile_pic);

                $path = FileController::storeProfile($request->file('profile_pic'));
                $profile->setProfilePicture($path);

            } catch (\Throwable $e) {
                return back()
                    ->withErrors(['profile_pic' => 'Erro ao carregar a imagem. Tenta novamente.'])
                    ->withInput();
            }
        }

        // -----------------------------
        // STATUS UPDATE
        // -----------------------------
        $profile->status = $validated['status'];

        if ($validated['status'] === 'customizável') {
            $profile->custom_status = $validated['custom_status'];
        } else {
            $profile->custom_status = null;
        }

        $profile->save();

        return redirect()->route('profile')->with('status', 'Perfil atualizado com sucesso.');
    }
    public function destroy()
    {
        $user = $this->user();
        $this->authorize('delete', $user);
        $coordinatorProjectsCount = $user->projects()
        ->wherePivot('user_role', 'coordinator')
        ->count();

    if ($coordinatorProjectsCount > 0) {
        return redirect()->back()
            ->with('error', 'Não podes eliminar a conta porque és coordenador algum projeto.');
    }

        DB::transaction(function () use ($user) {
            DB::statement('CALL proc_anonymize_user(?)', [$user->user_id]);
        });
        //without this we would enter in the anonymus account
        Auth::logout();

        return redirect()->route('home')->with('status', 'Conta eliminada com sucesso');
    }

    public function showUserProfile(User $user)
    {
        return response()->json([
            'username'      => $user->username,
            'email'         => $user->email,
            'status'        => $user->regularProfile?->status ?? 'offline',
            'custom_status' => $user->regularProfile?->custom_status,
            'profile_pic'   => $user->profilePictureUrl(),
            'joined_at'     => optional($user->created_at)->format('d M Y'),
        ]);
    }
}
