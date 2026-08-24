<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskDependencyController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminProjectController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\TaskListController;
use App\Http\Controllers\Mail\MailController;
use App\Http\Controllers\GoogleController;
use App\Http\Middleware\RejectBlockedUser;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ResetPasswordController;

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CalendarController;

Broadcast::routes(['middleware' => ['web', 'auth']]);

// ---------------------------------------------
// Public Marketing Pages
// ---------------------------------------------
Route::get('/', HomeController::class)->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/features', 'pages.features')->name('features');
Route::view('/contact', 'pages.contact')->name('contact');

// ---------------------------------------------
// Emails
// ---------------------------------------------
Route::post('/send', [MailController::class, 'send']);

// ---------------------------------------------
// Password reset
// ---------------------------------------------
Route::get('/forgot-password', [ResetPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// ---------------------------------------------
// Authentication
// ---------------------------------------------
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
});

Route::controller(LogoutController::class)->group(function () {
    Route::get('/logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});

// ---------------------------------------------
// Google OAuth
// ---------------------------------------------
Route::controller(GoogleController::class)->group(function () {
    Route::get('auth/google', 'redirect')->name('google-auth');
    Route::get('auth/google/call-back', 'callbackGoogle')->name('google-call-back');
});

// ---------------------------------------------
// Signed invite accept (short link)
// ---------------------------------------------
Route::get('/invite/accept', [InvitationController::class, 'accept'])
    ->name('invites.accept')
    ->middleware('signed');

// ---------------------------------------------
// Authenticated
// ---------------------------------------------
Route::middleware(['auth', RejectBlockedUser::class])->group(function () {
    // Admin
    Route::get('/admin/dashboard', [AdminDashboardController::class,'__invoke'])->name('admin.dashboard');
    Route::get('/admin/projects/{project}', [AdminProjectController::class, 'show'])->name('admin.projects.show');
    Route::post('/admin/projects/{project}/suspend', [AdminProjectController::class, 'suspend'])->name('admin.projects.suspend');
    Route::post('/admin/projects/{project}/unsuspend', [AdminProjectController::class, 'unsuspend'])->name('admin.projects.unsuspend');
    Route::delete('/admin/projects/{project}', [AdminProjectController::class, 'destroy'])->name('admin.projects.destroy');

    Route::post('/admin/users/update', [AdminDashboardController::class, 'update'])->name('admin.users.update');
    Route::post('/admin/users/create', [AdminDashboardController::class, 'createNewUser'])->name('admin.users.create');
    Route::get('/admin/users/search', [AdminDashboardController::class, 'searchUsers'])->name('admin.users.search');
    Route::post('/admin/users/{user}/block', [AdminDashboardController::class, 'block'])->name('admin.users.block');
    Route::post('/admin/users/{user}/unblock', [AdminDashboardController::class, 'unblock'])->name('admin.users.unblock');
    Route::delete('/admin/users/{user}', [AdminDashboardController::class, 'destroy'])->name('admin.users.destroy');

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Projects
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects');
    Route::get('/projects/archived', [ProjectController::class, 'archived'])->name('projects.archived');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::post('/projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('/projects/{project}/unarchive', [ProjectController::class, 'unarchive'])->name('projects.unarchive');
    Route::get('/projects/{project}/settings', [ProjectController::class, 'settings'])->name('projects.settings');
    Route::patch('/projects/{project}/settings', [ProjectController::class, 'updateSettings'])->name('projects.settings.update');
    Route::post('/projects/{project}/favorite', [ProjectController::class, 'toggleFavorite'])->name('projects.favorite');
    Route::get('/projects/{project}/analytics', [ProjectController::class, 'workload'])->name('projects.analytics');

    // Project invitations (internal)
    Route::get('/i/a', [ProjectController::class, 'acceptInvite'])->middleware('signed')->name('projects.invites.accept');
    Route::get('/i/r', [ProjectController::class, 'rejectInvite'])->middleware('signed')->name('projects.invites.reject');

    // Project members
    Route::get('/projects/{project}/members', [ProjectController::class, 'members'])->name('projects.members');
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('projects.members.remove');
    Route::post('/projects/{project}/members/add', [ProjectController::class, 'addMembers'])->name('projects.members.add');
    Route::post('/projects/{project}/members/invite', [ProjectController::class, 'inviteMember'])->name('projects.members.invite');
    Route::patch('/projects/{project}/members/{user}/promote', [ProjectController::class, 'promoteMember'])->name('projects.members.promote');

    // Project forum
    Route::get('/projects/{project}/forum', [ForumController::class, 'index'])->name('projects.forum');
    Route::post('/projects/{project}/forum', [ForumController::class, 'store'])->name('projects.forum.store');
    Route::get('/projects/{project}/forum/{topic}', [ForumController::class, 'show'])->name('projects.forum.topic');
    Route::post('/projects/{project}/forum/{topic}/reply', [ForumController::class, 'reply'])->name('projects.forum.reply');
    Route::post('/projects/{project}/forum/{topic}/like', [ForumController::class, 'like'])->name('projects.forum.like');

    // Tasks
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Tags
    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
    Route::get('/tags/task', [TagController::class, 'taskTags']);
    Route::post('/tags/task', [TagController::class, 'attachTag']);
    Route::delete('/tags/task/{tag}', [TagController::class, 'detachTag']);

    // Task comments
    Route::get('/tasks/{task}/comments', [TaskCommentController::class, 'index'])->name('tasks.comments.index');
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::delete('/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');

    // Task dependencies
    Route::get('/tasks/{task}/dependencies', [TaskDependencyController::class, 'index'])->name('tasks.dependencies.index');
    Route::post('/tasks/{task}/dependencies', [TaskDependencyController::class, 'store'])->name('tasks.dependencies.store');
    Route::delete('/tasks/{task}/dependencies/{dependency}', [TaskDependencyController::class, 'destroy'])->name('tasks.dependencies.destroy');

    // Task groups / lists
    Route::post('/projects/{project}/task-groups', [TaskListController::class, 'storeGroup'])->name('projects.task-groups.store');
    Route::delete('/projects/{project}/task-groups/{taskGroup}', [TaskListController::class, 'destroyGroup'])->name('projects.task-groups.destroy');
    Route::post('/projects/{project}/task-lists', [TaskListController::class, 'storeList'])->name('projects.task-lists.store');
    Route::post('/projects/{project}/task-lists/{taskList}/assign', [TaskListController::class, 'assign'])->name('projects.task-lists.assign');
    Route::post('/projects/{project}/tasks/{task}/task-list', [TaskListController::class, 'assignFromCard'])->name('projects.task-lists.assign-card');
    Route::delete('/projects/{project}/task-lists/{taskList}', [TaskListController::class, 'destroyList'])->name('projects.task-lists.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile', [ProfileController::class, 'destroy'])->name('profile.delete');
    Route::get('/users/{user}/profile', [ProfileController::class, 'showUserProfile']);

    // Search
    Route::get('/search', SearchController::class)->name('search');
    Route::get('/search/projects', [SearchController::class, 'projects']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.delete');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'calendar'])->name('calendar');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

    // Invitations
    Route::get('/invitations', [InvitationController::class, 'index'])->name('invitations');
    Route::post('/invitations/{invitation}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{invitation}/decline', [InvitationController::class, 'decline'])->name('invitations.decline');
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
