<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Ldms;
use App\Models\NextOfKin;
use App\Models\Student;
use Illuminate\Auth\Access\HandlesAuthorization;

class LDMSPolicy
{
    use HandlesAuthorization;

    /**
     * Student owns/manages their own LDMS until released.
     */
    public function view(Student $student, Ldms $ldms): bool
    {
        return $ldms->student_id === $student->student_id;
    }

    public function update(Student $student, Ldms $ldms): bool
    {
        return $ldms->student_id === $student->student_id && !$ldms->is_released;
    }

    public function delete(Student $student, Ldms $ldms): bool
    {
        return $this->update($student, $ldms);
    }

    /**
     * NOK can view an LDMS only after it has been released by admin trigger.
     */
    public function viewByNok(NextOfKin $nok, Ldms $ldms): bool
    {
        if (!$ldms->is_released) return false;
        return $nok->student_id === $ldms->student_id;
    }

    /**
     * Admin can trigger release of an LDMS — typically tied to verified death.
     */
    public function trigger(Admin $admin, Ldms $ldms): bool
    {
        if (!$admin->active) return false;
        $perms = (array) ($admin->permissions ?? []);
        return in_array('trigger_ldms', $perms, true) || $admin->role === 'super_admin';
    }
}
