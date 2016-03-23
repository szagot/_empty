<?php
/**
 * System
 *
 * @author Daniel Bispo <szagot@gmail.com>
 */

namespace Control;

use
    Config\Request,
    Config\Uri,
    Config\Sessao,
    Model\DataBaseModel\Usuarios;

class System
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
            'estilos'     =>
                $request->getFile( 'base.css', Request::CSS, [
                    // Raiz para diretório de arquivos gerais (Mixed)
                    'raiz' => $request->getRaiz( Request::MIXED )
                ] )
                . $request->getFile( 'system.css', Request::CSS ),
            'bodyId'      => 'admin'
        ] );

        // Formulário de Login
        $request->showFile( 'admin.html', Request::HTML, [
            // Raiz do sistema
            'raiz' => $uri->getRaiz()
        ] );

        // Footer
        $request->showFile( 'footer.html', Request::HTML );

        return false;
    }
}