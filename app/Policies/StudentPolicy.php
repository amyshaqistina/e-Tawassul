<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\NextOfKin;
use App\Models\Student;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
{
    use HandlesAuthorization;

    /**
     * Admins can view any student record.
     */
    public function viewByAdmin(Admin $admin, Student $student): bool
    {
        return (bool) $admin->active;
    }

    /**
     * NOK can view a student record if they are the registered guardian.
     */
    public function viewByNok(NextOfKin $nok, Student $student): bool
    {
        return $nok->student_id === $student->student_id;
    }

    /**
     * A student can only view their own record.
     */
    public function viewSelf(Student $auth, Student $student): bool
    {
        return $auth->student_id === $student->student_id;
    }
}
