<?php
namespace Imedia\Sms;

use Imedia\Sms\Provider;

class Events
{
    public static function registerProvider(): array
    {
        $providers = [
            new Provider\Fake(),
            new Provider\SmsTraffic()
        ];

        return $providers;
    }
}
