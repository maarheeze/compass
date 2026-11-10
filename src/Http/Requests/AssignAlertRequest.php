<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Closure;
use Illuminate\Validation\Validator;
use Maarheeze\Compass\Alerts\AlertAssigner;
use Maarheeze\Compass\Authentication\AuthenticatedUser;

class AssignAlertRequest extends AlertActionRequest
{
    /**
     * @return list<Closure(Validator): void>
     */
    public function after(AlertAssigner $alertAssigner, AuthenticatedUser $authenticatedUser): array
    {
        return [
            function (Validator $validator) use ($alertAssigner, $authenticatedUser): void {
                if ($alertAssigner->canAssignToMe($this->alert(), $authenticatedUser->user())) {
                    return;
                }

                $this->noLongerPossible($validator);
            },
        ];
    }
}
