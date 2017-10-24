<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Event;
use App\Models\Donation;

class DonationPolicy extends BasePolicy
{
    /**
     * Determine whether the user can manage the donation. (delete/update)
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Donation  $donation
     * @return mixed
     */
    public function manage(User $user, Donation $donation)
    {
        return ($user->id === $donation->user_id && $donation->status == Donation::NOT_ACCEPT)
            || $user->can('manage', $donation->event);
    }
}
