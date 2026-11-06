<?php

return [

    /*
    | Nome registrado como responsável pelos encaminhamentos enquanto não houver
    | identificação de usuário. TODO(sso): substituir pela identidade do SSO em
    | App\Support\ResponsavelAtual.
    */
    'responsavel_padrao' => env('OUVIDORIA_RESPONSAVEL_PADRAO', 'Ouvidoria'),

    /*
    | Canal da comunicação interna dos encaminhamentos.
    | 'local' apenas grava as mensagens no banco. TODO(graph): adicionar o
    | driver 'graph' (Microsoft Graph) quando a integração for implementada.
    */
    'comunicacao' => [
        'driver' => env('OUVIDORIA_COMUNICACAO_DRIVER', 'local'),
    ],

];
