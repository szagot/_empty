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

        // Dados para API de Testes
        'api'    => [
            'user' => 'szagot@gmail.com',
            'pass' => 'D5p1d3r'
        ]

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

    /**
     * Retorna os dados para acesso Auth Basic
     * @return object
     */
    public static function getAPIData()
    {
        // É local?
        if ( ( new Uri() )->eLocal() )
            // Retorna as configurações locais do sistema
            return (object) self::$dbData[ 'api' ];

        // Retorna as configurações do módulo do sistema.
        // ATENÇÃO! Troque essa parte pelos dados do seu BD para maior segurança.
        return (object) self::$dbData[ 'api' ];
    }
}