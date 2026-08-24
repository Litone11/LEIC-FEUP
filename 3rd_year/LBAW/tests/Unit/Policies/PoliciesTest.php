<?php

namespace Tests\Unit\Policies;

use App\Models\Admin;
use App\Models\Invitation;
use App\Models\Notification;
use App\Models\Tag;
use App\Models\TaskComment;
use App\Models\TaskGroup;
use App\Models\TaskList;
use App\Models\TaskThread;
use App\Policies\AdminPolicy;
use App\Policies\InvitationPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TagPolicy;
use App\Policies\TaskCommentPolicy;
use App\Policies\TaskGroupPolicy;
use App\Policies\TaskListPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use PHPUnit\Framework\TestCase;

class PoliciesTest extends TestCase
{
    public function test_admin_policy_requires_admin(): void
    {
        $policy = new AdminPolicy();
        $admin = $this->makeUser(1, true);
        $user = $this->makeUser(2, false);
        $adminModel = new Admin();

        $this->assertTrue($policy->viewAny($admin));
        $this->assertFalse($policy->viewAny($user));
        $this->assertTrue($policy->update($admin, $adminModel));
        $this->assertFalse($policy->update($user, $adminModel));
    }

    public function test_user_policy_allows_self_or_admin_updates(): void
    {
        $policy = new UserPolicy();
        $admin = $this->makeUser(1, true);
        $owner = $this->makeUser(2, false);
        $other = $this->makeUser(3, false);

        $this->assertTrue($policy->viewAny($admin));
        $this->assertFalse($policy->viewAny($owner));
        $this->assertTrue($policy->update($owner, $owner));
        $this->assertTrue($policy->update($admin, $owner));
        $this->assertFalse($policy->update($other, $owner));
    }

    public function test_project_policy_membership_and_coordinator(): void
    {
        $policy = new ProjectPolicy();
        $admin = $this->makeUser(1, true);
        $member = $this->makeUser(2, false);
        $outsider = $this->makeUser(3, false);
        $project = $this->makeProject([2], 2, [2 => 'coordinator']);

        $this->assertTrue($policy->view($admin, $project));
        $this->assertTrue($policy->view($member, $project));
        $this->assertFalse($policy->view($outsider, $project));

        $this->assertTrue($policy->update($member, $project));
        $this->assertFalse($policy->update($outsider, $project));

        $this->assertTrue($policy->coordinatorPermission($member, $project));
        $this->assertTrue($policy->updateMembers($member, $project));
        $this->assertFalse($policy->coordinatorPermission($outsider, $project));
    }

    public function test_task_policy_rules(): void
    {
        $policy = new TaskPolicy();
        $admin = $this->makeUser(1, true);
        $coordinator = $this->makeUser(2, false);
        $responsible = $this->makeUser(3, false);
        $assignee = $this->makeUser(4, false);
        $outsider = $this->makeUser(5, false);

        $project = $this->makeProject([2, 3, 4], 2, [
            2 => 'coordinator',
            3 => 'normal',
            4 => 'normal',
        ]);

        $task = $this->makeTask([
            2 => 'coordinator',
            3 => 'normal',
            4 => 'normal',
        ]);
        $task->task_responsible_id = 3;
        $task->assignee_id = 4;

        $this->assertTrue($policy->viewAny($admin));
        $this->assertFalse($policy->viewAny($outsider));
        $this->assertTrue($policy->view($coordinator, $task));
        $this->assertTrue($policy->view($responsible, $task));
        $this->assertTrue($policy->view($assignee, $task));
        $this->assertFalse($policy->view($outsider, $task));

        $this->assertTrue($policy->create($admin, $project));
        $this->assertTrue($policy->create($responsible, $project));
        $this->assertFalse($policy->create($outsider, $project));

        $this->assertTrue($policy->update($coordinator, $task));
        $this->assertTrue($policy->update($responsible, $task));
        $this->assertFalse($policy->update($assignee, $task));

        $this->assertTrue($policy->manageDependencies($coordinator, $task));
        $this->assertTrue($policy->manageDependencies($responsible, $task));
        $this->assertFalse($policy->manageDependencies($assignee, $task));
    }

    public function test_tag_policy_uses_project_coordinator(): void
    {
        $policy = new TagPolicy();
        $admin = $this->makeUser(1, true);
        $coordinator = $this->makeUser(2, false);
        $outsider = $this->makeUser(3, false);
        $project = $this->makeProject([2], 2, [2 => 'coordinator']);

        $tag = new Tag();
        $tag->setRelation('project', $project);

        $this->assertTrue($policy->create($admin, $project));
        $this->assertTrue($policy->create($coordinator, $project));
        $this->assertFalse($policy->create($outsider, $project));

        $this->assertTrue($policy->delete($coordinator, $tag));
        $this->assertFalse($policy->delete($outsider, $tag));
    }

    public function test_notification_policy_receiver_or_admin(): void
    {
        $policy = new NotificationPolicy();
        $admin = $this->makeUser(1, true);
        $owner = $this->makeUser(2, false);
        $other = $this->makeUser(3, false);

        $notification = new Notification();
        $notification->receiver_id = 2;

        $this->assertTrue($policy->view($admin, $notification));
        $this->assertTrue($policy->update($owner, $notification));
        $this->assertFalse($policy->delete($other, $notification));
    }

    public function test_invitation_policy_sender_receiver_or_admin(): void
    {
        $policy = new InvitationPolicy();
        $admin = $this->makeUser(1, true);
        $receiver = $this->makeUser(2, false);
        $sender = $this->makeUser(3, false);
        $other = $this->makeUser(4, false);

        $invitation = new Invitation();
        $invitation->receiver_id = 2;
        $invitation->sender_id = 3;

        $this->assertTrue($policy->view($admin, $invitation));
        $this->assertTrue($policy->view($receiver, $invitation));
        $this->assertTrue($policy->view($sender, $invitation));
        $this->assertFalse($policy->view($other, $invitation));
        $this->assertTrue($policy->accept($receiver, $invitation));
        $this->assertFalse($policy->accept($sender, $invitation));
    }

    public function test_task_comment_policy_delete(): void
    {
        $policy = new TaskCommentPolicy();
        $admin = $this->makeUser(1, true);
        $owner = $this->makeUser(2, false);
        $coordinator = $this->makeUser(3, false);
        $outsider = $this->makeUser(4, false);

        $task = $this->makeTask([3 => 'coordinator']);
        $thread = new TaskThread();
        $thread->setRelation('task', $task);

        $comment = new TaskComment();
        $comment->user_id = 2;
        $comment->setRelation('thread', $thread);

        $this->assertTrue($policy->delete($admin, $comment));
        $this->assertTrue($policy->delete($owner, $comment));
        $this->assertTrue($policy->delete($coordinator, $comment));
        $this->assertFalse($policy->delete($outsider, $comment));
    }

    public function test_task_group_policy_delete(): void
    {
        $policy = new TaskGroupPolicy();
        $admin = $this->makeUser(1, true);
        $creator = $this->makeUser(2, false);
        $coordinator = $this->makeUser(3, false);
        $outsider = $this->makeUser(4, false);

        $project = $this->makeProject([2, 3], 3, [3 => 'coordinator']);
        $group = new TaskGroup();
        $group->created_by = 2;
        $group->setRelation('project', $project);

        $this->assertTrue($policy->delete($admin, $group));
        $this->assertTrue($policy->delete($creator, $group));
        $this->assertTrue($policy->delete($coordinator, $group));
        $this->assertFalse($policy->delete($outsider, $group));
    }

    public function test_task_list_policy_delete(): void
    {
        $policy = new TaskListPolicy();
        $admin = $this->makeUser(1, true);
        $creator = $this->makeUser(2, false);
        $coordinator = $this->makeUser(3, false);
        $outsider = $this->makeUser(4, false);

        $project = $this->makeProject([2, 3], 3, [3 => 'coordinator']);
        $group = new TaskGroup();
        $group->setRelation('project', $project);

        $list = new TaskList();
        $list->created_by = 2;
        $list->setRelation('group', $group);

        $this->assertTrue($policy->delete($admin, $list));
        $this->assertTrue($policy->delete($creator, $list));
        $this->assertTrue($policy->delete($coordinator, $list));
        $this->assertFalse($policy->delete($outsider, $list));
    }

    private function makeUser(int $id, bool $admin): TestUser
    {
        $user = new TestUser();
        $user->user_id = $id;
        $user->admin = $admin;
        return $user;
    }

    private function makeProject(array $members, ?int $coordinatorId, array $roles): TestProject
    {
        $project = new TestProject();
        $project->memberIds = $members;
        $project->coordinatorId = $coordinatorId;
        $project->roleMap = $roles;
        return $project;
    }

    private function makeTask(array $roles): TestTask
    {
        $task = new TestTask();
        $task->roleMap = $roles;
        return $task;
    }
}

class TestUser extends \App\Models\User
{
    public bool $admin = false;

    public function isAdmin(): bool
    {
        return $this->admin;
    }
}

class TestProject extends \App\Models\Project
{
    public array $memberIds = [];
    public ?int $coordinatorId = null;
    public array $roleMap = [];

    public function hasMember(int $userId): bool
    {
        return in_array($userId, $this->memberIds, true);
    }

    public function getCoordinator()
    {
        if ($this->coordinatorId === null) {
            return null;
        }

        $user = new TestUser();
        $user->user_id = $this->coordinatorId;
        return $user;
    }

    public function getCoordinatorId()
    {
        return $this->coordinatorId;
    }

    public function roleOf(int $userId): ?string
    {
        return $this->roleMap[$userId] ?? null;
    }
}

class TestTask extends \App\Models\Task
{
    public array $roleMap = [];

    public function roleOf($userId)
    {
        return $this->roleMap[$userId] ?? null;
    }
}
