<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Closure;
use Illuminate\Validation\Validator;
use Maarheeze\Compass\Alerts\AlertMuter;

class MuteAlertRequest extends AlertActionRequest
{
    /**
     * @return list<Closure(Validator): void>
     */
    public function after(AlertMuter $alertMuter): array
    {
        return [
            function (Validator $validator) use ($alertMuter): void {
                if (!$alertMuter->canMute($this->alert())) {
                    $this->noLongerPossible($validator);
                }
            },
        ];
    }
}
