<?php
/**
 * Sai do sistema
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2015
 */

namespace Control;

use
    Config\Uri,
    Config\Sessao;

class Logout
{
    /**
     * Inicializa o módulo de Logout
     *
     * @param Uri $uri
     */
    public static function iniciar( Uri $uri )
    {
        Sessao::iniciar()->destruir();
        Login::iniciar( $uri );
    }
}