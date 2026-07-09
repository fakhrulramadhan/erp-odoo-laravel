<?php

namespace App\Services\Notification;

use App\Models\{GlobalNotification, User};
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    public function broadcast(int $companyId, array $data): GlobalNotification
    {
        $notification = GlobalNotification::create([
            ...$data,
            'company_id' => $companyId,
            'is_broadcast' => true,
        ]);

        $userIds = User::where('company_id', $companyId)->pluck('id');
        $notification->users()->attach($userIds);

        return $notification;
    }

    public function sendToUsers(array $userIds, array $data): GlobalNotification
    {
        $notification = GlobalNotification::create($data);
        $notification->users()->attach($userIds);
        return $notification;
    }

    public function getUserNotifications(User $user, ?int $perPage = 20)
    {
        return $user->notifications()->paginate($perPage);
    }

    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAsRead(User $user, int $notificationId): void
    {
        $notification = GlobalNotification::findOrFail($notificationId);
        $notification->markAsRead($user);
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications()->update([
            'notification_user.read_at' => now(),
        ]);
    }
}
