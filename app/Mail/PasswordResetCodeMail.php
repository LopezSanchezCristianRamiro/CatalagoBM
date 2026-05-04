<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this
            ->subject('Código para recuperar contraseña')
            ->html("
                <h2>Recuperación de contraseña</h2>
                <p>Tu código de verificación es:</p>
                <h1 style='letter-spacing: 4px;'>{$this->code}</h1>
                <p>Este código vence en 10 minutos.</p>
            ");
    }
}