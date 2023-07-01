<?php

namespace Visualbuilder\EmailTemplates;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Visualbuilder\EmailTemplates\Listeners\UserLoginListener;
use Visualbuilder\EmailTemplates\Listeners\UserRegisteredListener;
use Visualbuilder\EmailTemplates\Mail\UserVerifyEmail;

class EmailTemplatesAuthServiceProvider extends ServiceProvider
{
    
    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        
        if(config('email-templates.send_emails.verification')){
            //Override default Laravel VerifyEmail notification toMail function
            VerifyEmail::toMailUsing(function (User $user, string $verificationUrl) {
                return (new UserVerifyEmail($user , $verificationUrl ));
            });
        }

    }
}