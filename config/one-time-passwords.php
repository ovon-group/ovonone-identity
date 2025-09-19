<?php

return [
    /*
    |--------------------------------------------------------------------------
    | One-Time Password Model
    |--------------------------------------------------------------------------
    |
    | This is the model that will be used to store one-time passwords.
    |
    */
    'model' => \Spatie\OneTimePasswords\Models\OneTimePassword::class,

    /*
    |--------------------------------------------------------------------------
    | One-Time Password Notification
    |--------------------------------------------------------------------------
    |
    | This is the notification class that will be used to send one-time passwords.
    |
    */
    'notification' => \App\Notifications\OneTimePasswordNotification::class,

    /*
    |--------------------------------------------------------------------------
    | Password Generator
    |--------------------------------------------------------------------------
    |
    | This class will be used to generate the actual one-time passwords.
    |
    */
    'password_generator' => \Spatie\OneTimePasswords\Support\PasswordGenerators\NumericOneTimePasswordGenerator::class,

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    |
    | Here you can specify the actions that will be used by the package.
    |
    */
    'actions' => [
        'create_one_time_password' => \Spatie\OneTimePasswords\Actions\CreateOneTimePasswordAction::class,
        'consume_one_time_password' => \Spatie\OneTimePasswords\Actions\ConsumeOneTimePasswordAction::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Expiration Time
    |--------------------------------------------------------------------------
    |
    | This is the default expiration time for one-time passwords in minutes.
    |
    */
    'default_expires_in_minutes' => 2,

    /*
    |--------------------------------------------------------------------------
    | Allow Multiple Passwords
    |--------------------------------------------------------------------------
    |
    | If set to true, multiple one-time passwords can exist for a user at once.
    | If set to false, creating a new password will delete all existing ones.
    |
    */
    'allow_multiple_passwords' => false,

    /*
    |--------------------------------------------------------------------------
    | Origin Enforcer
    |--------------------------------------------------------------------------
    |
    | This class will be used to enforce the origin of one-time password requests.
    |
    */
    'origin_enforcer' => \Spatie\OneTimePasswords\Support\OriginInspector\DefaultOriginEnforcer::class,
];
