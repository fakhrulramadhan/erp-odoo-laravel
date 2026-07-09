<?php

namespace App\Enums;

enum WorkflowActionType: string
{
    case Approve = 'approve';
    case Reject = 'reject';
    case Notify = 'notify';
    case AutoAssign = 'auto_assign';
    case AutoCreateTask = 'auto_create_task';
    case AutoCreateJournal = 'auto_create_journal';
    case AutoUpdateStatus = 'auto_update_status';
    case SendEmail = 'send_email';

    public function label(): string
    {
        return match ($this) {
            self::Approve => 'Approve',
            self::Reject => 'Reject',
            self::Notify => 'Notify',
            self::AutoAssign => 'Auto Assign',
            self::AutoCreateTask => 'Auto Create Task',
            self::AutoCreateJournal => 'Auto Create Journal',
            self::AutoUpdateStatus => 'Auto Update Status',
            self::SendEmail => 'Send Email',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
