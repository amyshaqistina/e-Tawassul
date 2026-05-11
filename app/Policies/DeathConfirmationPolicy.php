<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\DeathConfirmation;
use App\Models\NextOfKin;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * DeathConfirmationPolicy
 *
 * Authorisation rules for the death confirmation workflow:
 *  - NOK may submit only for their own linked student
 *  - NOK may view only their own submitted confirmations
 *  - Active admins may view all confirmations
 *  - Verification requires the `verify_death` permission or super_admin role
 */
class DeathConfirmationPolicy
{
    use HandlesAuthorization;

    public function submit(NextOfKin $nok): bool
    {
        return !empty($nok->student_id)
            && (bool) $nok->emergency_contact_verified;
    }

    public function viewAsAdmin(Admin $admin, DeathConfirmation $confirmation): bool
    {
        return (bool) $admin->active;
    }

    public function viewAsNok(NextOfKin $nok, DeathConfirmation $confirmation): bool
    {
        return (int) $confirmation->nok_id === (int) $nok->nok_id;
    }

    public function verify(Admin $admin, DeathConfirmation $confirmation): bool
    {
        if (!$admin->active) return false;
        $perms = (array) ($admin->permissions ?? []);
        return in_array('verify_death', $perms, true) || $admin->role === 'super_admin';
    }
}
