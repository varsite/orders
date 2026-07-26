<?php

declare(strict_types=1);

namespace Varsite\Orders\Policies;

use Illuminate\Contracts\Auth\Access\Authorizable;

/** Moduł deklaruje identyfikatory uprawnień; o ich posiadaniu decyduje rdzeń (N5). */
final class OrderPolicy
{
    public function viewAny(Authorizable $user): bool
    {
        return $user->can('orders.view');
    }

    public function view(Authorizable $user): bool
    {
        return $user->can('orders.view');
    }

    public function update(Authorizable $user): bool
    {
        return $user->can('orders.update');
    }
}
