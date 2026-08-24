<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Admin;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskThread;
use App\Models\SuspendProject;
use App\Models\BlockUser;
use App\Models\RegularUser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        Gate::authorize('admin-access');
        $user = auth()->user();
        $activeTab = $request->get('tab', 'projects');
        $userSearch = trim((string) $request->get('user_search', ''));

        $activeUsersQuery = User::where('is_deleted', false);

        // Statistics
        $stats = [
            'total_projects'    => Project::count(),
            'active_projects'   => Project::active()->count(),
            'archived_projects' => Project::archived()->count(),
            'suspended_projects'=> Project::whereHas('suspensionEntries')->count(),
            'total_users'       => (clone $activeUsersQuery)->count(),
            'admin_users'       => (clone $activeUsersQuery)->whereHas('adminEntry')->count(),
            'blocked_users'     => (clone $activeUsersQuery)->whereHas('latestBlock')->count(),
            'total_tasks'       => Task::count(),
            'completed_tasks'   => Task::where('status', 'Done')->count(),
        ];

        // User list
        $users = User::with(['adminEntry', 'latestBlock'])
            ->when($userSearch, function ($q) use ($userSearch) {
                $q->where(function ($inner) use ($userSearch) {
                    $inner->where('username', 'ILIKE', "%{$userSearch}%")
                        ->orWhere('email', 'ILIKE', "%{$userSearch}%");
                });
            })
            ->orderBy('username')
            ->get()
            ->map(fn($u) => [
                'id'          => $u->user_id,
                'username'    => $u->username,
                'email'       => $u->email,
                'is_admin'    => $u->isAdmin(),
                'blocked'     => $u->isBlocked(),
                'block_reason'=> $u->activeBlockReason(),
                'deleted'     => $u->isDeleted(),
            ])
            ->toArray();

        // Projects list for admin view
        $projects = Project::withTaskStats()
            ->withMemberCount()
            ->with('latestSuspension')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($p) => [
                'id'                => $p->project_id,
                'name'              => $p->name,
                'status'            => $p->status_label,
                'progress'          => $p->progress,
                'members'           => $p->members,
                'created_at'        => $p->created_at?->format('M d, Y'),
                'is_suspended'      => $p->isSuspended(),
                'suspension_reason' => $p->suspensionReason(),
                'is_archived'       => (bool) $p->is_archived,
            ])
            ->toArray();

        return view('pages.admin_dashboard', [
            'user'  => $user,
            'stats' => $stats,
            'users' => $users,
            'projects'   => $projects,
            'activeTab'  => $activeTab,
            'userSearch' => $userSearch,
        ]);
    }



    // Update user fields + admin role
    public function update(Request $request)
    {
        $this->authorize('admin-access');

        $admin = $this->user();
        $user = User::findOrFail($request->id);

        if ($user->isDeleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta conta já foi eliminada.',
            ], 400);
        }

        $user->update([
            'username' => $request->username,
            'email'    => $request->email,
        ]);

        $requestedAdmin = filter_var($request->is_admin, FILTER_VALIDATE_BOOLEAN);
        if ($requestedAdmin) {
            $user->makeAdmin();
        } else {
            $user->removeAdmin();
        }
        
        return response()->json([
            'success'  => true,
            'is_admin' => $user->isAdmin(),
        ]);
    }

    // Block user
    public function block(Request $request, User $user)
    {
        $admin = $this->user();
        // Only admins can block accounts
        abort_unless($admin->isAdmin(), 403);

        if ($user->isDeleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta conta já foi eliminada.',
            ], 400);
        }

        // Avoid duplicate block entries for the same user
        if ($user->isBlocked()) {
            return response()->json([
                'success' => false,
                'message' => 'User is already blocked.',
            ], 400);
        }

        // Reason is mandatory and short
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:250'],
        ]);

        // Persist block record with timestamps and actor
        $block = BlockUser::create([
            'reason'       => $validated['reason'],
            'blocked_at'   => now(),
            'unblocked_at' => now(),
            'admin_id'     => $admin->user_id,
            'user_id'      => $user->user_id,
        ]);

        return response()->json([
            'success'      => true,
            'blocked'      => true,
            'block_reason' => $block->reason,
            'blocked_at'   => $block->blocked_at,
        ]);
    }

    // Unblock user
    public function unblock(User $user)
    {
        $admin = $this->user();
        // Only admins can unblock accounts
        abort_unless($admin->isAdmin(), 403);

        if ($user->isDeleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta conta já foi eliminada.',
            ], 400);
        }

        // Fetch latest block; if none, nothing to do
        $block = $user->latestBlock()->first();
        if (!$block) {
            return response()->json([
                'success' => false,
                'message' => 'User is not blocked.',
            ], 400);
        }

        // Removing the block entry reactivates the account
        $block->delete();

        return response()->json([
            'success'      => true,
            'blocked'      => false,
            'block_reason' => null,
            'blocked_at'   => null,
        ]);
    }

    // ============================================================
    // DELETE USER (non-admin only)
    // ============================================================
    public function destroy(User $user)
    {
        $admin = $this->user();
        abort_unless($admin->isAdmin(), 403);

        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível eliminar outro administrador.',
            ], 400);
        }

        if ($user->isDeleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta conta já foi eliminada.',
            ], 400);
        }

        DB::transaction(function () use ($user) {
            // Anonymize data to keep FK integrity and prevent reuse
            DB::statement('CALL proc_anonymize_user(?)', [$user->user_id]);

            // Remove block history for this user
            $user->blockEntries()->delete();

            // Remove admin record if any stray exists
            $user->removeAdmin();

            // Set a random password to disable login attempts on anonymized account
            $user->update([
                'password' => Hash::make(Str::random(40)),
                'is_deleted' => true,
            ]);
        });

        return response()->json([
            'success' => true,
            'deleted' => true,
        ]);
    }

    // Search users (AJAX)
    public function searchUsers(Request $request)
    {
        $this->authorize('admin-access');

        $query = trim((string) $request->get('query', ''));

        $users = User::with(['adminEntry', 'latestBlock'])
            ->when($query, function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('username', 'ILIKE', "%{$query}%")
                        ->orWhere('email', 'ILIKE', "%{$query}%");
                });
            })
            ->orderBy('username')
            ->limit(50)
            ->get()
            ->map(fn($u) => [
                'id'           => $u->user_id,
                'username'     => $u->username,
                'email'        => $u->email,
                'is_admin'     => $u->isAdmin(),
                'blocked'      => $u->isBlocked(),
                'block_reason' => $u->activeBlockReason(),
                'deleted'      => $u->isDeleted(),
            ]);

        return response()->json(['users' => $users]);
    }

    // Create new user (admin action)
    public function createNewUser(Request $request)
    {
        $admin = $this->user();
        abort_unless($admin->isAdmin(), 403);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:250', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:250', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'isAdmin'  => ['nullable'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $u = User::create([
                'username' => $data['username'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            RegularUser::create(['user_id' => $u->user_id]);
            return $u;
        });

        return redirect()->route('admin.dashboard', ['tab' => 'users'])
            ->with('success', 'Utilizador criado com sucesso.');
    }
}
