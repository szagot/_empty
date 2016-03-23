<?php
/**
 * Home
 *
 * @author Daniel Bispo <szagot@gmail.com>
 */

namespace Control;

use
    Config\Request,
    Config\Uri,
    Config\Sessao,
    Model\DataBaseModel\Usuarios;

class Home
{
    /**
     * Inicializa o módulo de login
     *
     * @param Uri $uri
     *
     * @return bool Retorno de sucesso ou de falha
     */
    public static function iniciar( Uri $uri )
    {
        $request = Request::iniciar();

        // Header
        $request->showFile( 'header.html', Request::HTML, [
            'title'       => 'Empty Project',
            'description' => 'Tela Inicial',
            'tags'        => 'home, inicial',
            'estilos'     => ''
                . $request->getFile( 'base.css', Request::CSS, [ 'raiz' => $request->getRaiz( Request::MIXED ) ] )
                . $request->getFile( 'site.css', Request::CSS ),
            'bodyId'      => 'pagina-home'
        ] );

        // Formulário de Login
        $request->showFile( 'home.html', Request::HTML );

        // Footer
        $request->showFile( 'footer.html', Request::HTML );

        return false;
    }
}