<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\CrisisReport;
use App\Models\Student;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrisisReportPolicy
{
    use HandlesAuthorization;

    public function viewAsStudent(Student $student, CrisisReport $report): bool
    {
        return $report->student_id === $student->student_id;
    }

    public function viewAsAdmin(Admin $admin, CrisisReport $report): bool
    {
        return (bool) $admin->active;
    }

    public function verify(Admin $admin, CrisisReport $report): bool
    {
        if (!$admin->active) return false;
        $perms = (array) ($admin->permissions ?? []);
        return in_array('verify_crisis', $perms, true) || $admin->role === 'super_admin';
    }

    public function reject(Admin $admin, CrisisReport $report): bool
    {
        return $this->verify($admin, $report);
    }

    public function update(Student $student, CrisisReport $report): bool
    {
        return $report->student_id === $student->student_id
            && $report->report_status === 'pending';
    }
}
