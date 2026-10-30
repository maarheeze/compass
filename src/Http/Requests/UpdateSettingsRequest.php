<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\ArrayRule;
use Illuminate\Validation\Rules\Enum;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Collections\NotificationSubscriptionCollection;
use Maarheeze\Compass\Models\NotificationSubscription;
use Webmozart\Assert\Assert;

class UpdateSettingsRequest extends FormRequest
{
    public function notificationSubscriptions(): NotificationSubscriptionCollection
    {
        $notificationChannelsByAlertType = $this->validated('notification_channels', []);
        Assert::isArray($notificationChannelsByAlertType);

        $notificationSubscriptions = new NotificationSubscriptionCollection();

        foreach ($notificationChannelsByAlertType as $alertTypeValue => $notificationChannelValues) {
            Assert::string($alertTypeValue);
            Assert::isArray($notificationChannelValues);

            foreach ($notificationChannelValues as $notificationChannelValue) {
                Assert::string($notificationChannelValue);

                $notificationSubscriptions->push(new NotificationSubscription([
                    'alert_type' => AlertType::from($alertTypeValue),
                    'channel' => NotificationChannel::from($notificationChannelValue),
                ]));
            }
        }

        return $notificationSubscriptions;
    }

    /**
     * @return array<string, list<ArrayRule|Enum|string>>
     */
    public function rules(): array
    {
        return [
            'notification_channels' => [
                Rule::array(AlertType::cases()),
            ],
            'notification_channels.*' => [
                'list',
            ],
            'notification_channels.*.*' => [
                'distinct',
                Rule::enum(NotificationChannel::class),
            ],
        ];
    }
}
