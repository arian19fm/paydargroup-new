<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

/** Maps Faq abilities onto permissions (faqs.view / faqs.manage). */
class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('faqs.view');
    }

    public function view(User $user, Faq $model): bool
    {
        return $user->can('faqs.view');
    }

    public function create(User $user): bool
    {
        return $user->can('faqs.manage');
    }

    public function update(User $user, Faq $model): bool
    {
        return $user->can('faqs.manage');
    }

    public function delete(User $user, Faq $model): bool
    {
        return $user->can('faqs.manage');
    }
}
