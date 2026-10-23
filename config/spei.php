<?php

/*
|--------------------------------------------------------------------------
| Cobro por SPEI — datos bancarios y ventana de pago
|--------------------------------------------------------------------------
| La CLABE y el banco NO van en el repositorio: se configuran en .env
|
|   SPEI_CLABE=012345678901234567
|   SPEI_BENEFICIARIO=Club Lobby69
|   SPEI_BANCO=BBVA
|
| Si SPEI_CLABE queda vacía, la pantalla de instrucciones muestra un aviso
| de configuración pendiente en lugar de datos bancarios inventados.
*/

return [
    'clabe'       => env('SPEI_CLABE', ''),
    'beneficiary' => env('SPEI_BENEFICIARIO', 'Club Lobby69'),
    'bank'        => env('SPEI_BANCO', ''),
    'rfc'         => env('SPEI_RFC', ''),

    // Prefijo del concepto que el usuario teclea en su banca en línea.
    'concept_prefix' => env('SPEI_CONCEPTO_PREFIX', 'LOBBY69'),

    // Horas que tiene el usuario para transferir antes de que la orden venza.
    'window_hours' => (int) env('SPEI_VENTANA_HORAS', 24),

    /*
     | Correo que recibe el aviso cuando un socio sube su comprobante.
     | Si queda vacío se usa MAIL_FROM_ADDRESS. Si tampoco hay, no se envía
     | nada y queda un aviso en el log (la acción del socio nunca se bloquea).
     */
    'admin_email' => env('SPEI_ADMIN_EMAIL', ''),

    /*
     | Centavos discriminadores: al precio base se le suman de 1 a 99 centavos
     | derivados del id del pedido. Así dos transferencias del mismo plan dejan
     | de ser indistinguibles en el estado de cuenta y la conciliación es exacta.
     */
    'discriminator' => [
        'enabled' => filter_var(env('SPEI_CENTAVOS_DISCRIMINADORES', true), FILTER_VALIDATE_BOOLEAN),
        'min'     => 1,
        'max'     => 99,
    ],
];
