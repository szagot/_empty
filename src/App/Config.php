<?php
/**
 * Configurações do Sistema e do Banco de Dados
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 */

namespace App;

use Config\Uri;


class Config
{
    private static $dbData = [

        // Dados para quando o acesso for local
        'local'  => [
            'bd'   => '_empty',
            'host' => 'localhost',
            'user' => 'root',
            'pass' => ''
        ],

        // Dados do sistema
        'system' => [
            'bd'   => '',
            'host' => 'localhost',
            'user' => '',
            'pass' => ''
        ],

    ];

    /**
     * Pega as configurações do BD
     *
     * @return null|object
     */
    public static function getBdData()
    {
        // É local?
        if ( ( new Uri() )->eLocal() )
            // Retorna as configurações locais do sistema
            return (object) self::$dbData[ 'local' ];

        // Retorna as configurações do módulo
        return (object) self::$dbData[ 'system' ];
    }
}