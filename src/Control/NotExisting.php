<?php
/**
 * 404
 *
 * @author Daniel Bispo <szagot@gmail.com>
 */

namespace Control;

use
    Config\Request,
    Config\Uri;

class NotExisting
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
            'title'       => 'Empty Project | Página Não Encontrada',
            'description' => '404 Page Not Found',
            'tags'        => '404',
            'estilos'     => ''
                . $request->getFile( 'base.css', Request::CSS, [ 'raiz' => $request->getRaiz( Request::MIXED ) ] )
                . $request->getFile( 'site.css', Request::CSS ),
            'bodyId'      => 'pagina-404'
        ] );

        // Formulário de Login
        $request->showFile( '404.html', Request::HTML, [
            // Raiz do sistema
            'raiz' => $uri->getRaiz()
        ] );

        // Footer
        $request->showFile( 'footer.html', Request::HTML );

        return false;
    }
}