<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\BlockUser;
use App\Http\Controllers\FileController;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    public $timestamps  = false;

    protected $fillable = ['username', 'email', 'password', 'is_deleted'];
    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
            'is_deleted' => 'boolean',
        ];
    }

    // --------------------------
    // ADMIN RELATION
    // --------------------------
    public function adminEntry()
    {
        return $this->hasOne(Admin::class, 'user_id', 'user_id');
    }

    public function isAdmin(): bool
    {
        return $this->adminEntry()->exists();
    }

    // --------------------------
    // PROFILE RELATION
    // --------------------------
    public function regularProfile()
    {
        return $this->hasOne(RegularUser::class, 'user_id', 'user_id');
    }

    public function profilePictureUrl(): string
    {
        return FileController::get('profile', $this->user_id);
    }

    // --------------------------
    // PROJECTS RELATION
    // --------------------------
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'related_to', 'user_id', 'project_id')
            ->withPivot('user_role', 'is_favorite');
    }

    public function projectIds()
    {
        return $this->projects()->pluck('project.project_id');

    }

    public function completedTaskCount()
    {
        return Task::whereIn('project_id', $this->projectIds())
            ->where('status', 'Done')
            ->count();
    }

    public function activeTaskCount()
    {
        return Task::whereIn('project_id', $this->projectIds())
            ->where('status', '!=', 'Done')
            ->count();
    }

    public function recentProjects($limit = 3)
    {
        return $this->projects()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($p) => [
                'name'        => $p->name,
                'description' => $p->description,
                'created_at'  => $p->created_at?->format('d M Y'),
            ]);
    }
    public function updateBasicInfo(array $data)
    {
        $this->update([
            'username' => $data['username'],
            'email'    => $data['email'],
        ]);
    }

    public function makeAdmin()
    {
        return Admin::firstOrCreate(['user_id' => $this->user_id]);
    }
    public function makeRegularUser()
    {
        return RegularUser::firstOrCreate(['user_id' => $this->user_id]);
    }
    public function removeAdmin()
    {
        return Admin::where('user_id', $this->user_id)->delete();
    }

    public function notifications()
    {
        return Notification::where('receiver_id', $this->user_id);
    }
    public function normalNotifications()
    {
        return $this->notifications()
            ->orderByDesc('created_at')
            ->get();
    }

     public function invitations()
    {
        return Invitation::where('receiver_id', $this->user_id)
                                 ->where('is_accepted', false)
                                 ->with('project', 'sender')
                                 ->get();;
    }

    public function blockEntries()
    {
        return $this->hasMany(BlockUser::class, 'user_id', 'user_id');
    }

    public function latestBlock()
    {
        return $this->hasOne(BlockUser::class, 'user_id', 'user_id')
            ->latest('blocked_at');
    }

    public function isBlocked(): bool
    {
        return (bool) $this->latestBlock;
    }

    public function activeBlockReason(): ?string
    {
        return $this->isBlocked() ? $this->latestBlock?->reason : null;
    }

    public function isDeleted(): bool
    {
        return (bool) $this->is_deleted;
    }
}
