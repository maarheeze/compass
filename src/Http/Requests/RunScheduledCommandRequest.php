<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Maarheeze\Compass\Console\ScheduledCommand;
use Maarheeze\Compass\Console\ScheduledCommands;
use Webmozart\Assert\Assert;

class RunScheduledCommandRequest extends FormRequest
{
    public function mutexName(): string
    {
        return Assert::string($this->validated('mutex_name'));
    }

    /**
     * @return array<string, list<In|string>>
     */
    public function rules(ScheduledCommands $scheduledCommands): array
    {
        return [
            'mutex_name' => [
                'required',
                'string',
                Rule::in($scheduledCommands->all()->map(static function (ScheduledCommand $scheduledCommand): string {
                    return $scheduledCommand->mutexName;
                })),
            ],
        ];
    }
}
