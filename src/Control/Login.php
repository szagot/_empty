<?php
/**
 * Controle de Login do Sistema
 *
 * @author Daniel Bispo <szagot@gmail.com>
 */

namespace Control;

use
    Config\Request,
    Config\Uri,
    Config\Sessao,
    Model\DataBaseModel\Usuarios;

class Login
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
        $sessao = Sessao::iniciar();

        // Se estiver logado, retorna pra a a raiz, ou para a url de retorno (?retorno={url})
        if ( self::verificaLogin() ) {
            self::retornaRaiz( $uri );

            return true;
        }

        // Verifica se houve tentativa de login via POST
        $erros = '';
        if ( $uri->getMethod() == 'POST' && $uri->getParam( 'user' ) && $uri->getParam( 'pass' ) ) {
            // Senha válida?
            if ( Usuarios::validaSenha( $uri->getParam( 'user' ), $uri->getParam( 'pass' ) ) ) {
                // Marca sessão como logado
                $sessao->logado = true;
                // Salva os dados do usuário logado na sessão
                $sessao->user = Usuarios::getId( $uri->getParam( 'user' ) );

                self::retornaRaiz( $uri );

                return true;
            } else
                $erros = 'Usuário ou senha inválido';
        }

        $request = Request::iniciar();

        // Header
        $request->showFile( 'header.html', Request::HTML, [
            'title'       => 'Empty Project | Login',
            'description' => 'Tela de login do sistema',
            'tags'        => 'login, empty',
            'jquery'      => $request->getLinkFile( 'jquery-2.2.0.min.js', Request::JS ),
            'estilos'     => $request->getFile( 'base.css', Request::CSS, [
                    'raiz' => $request->getRaiz( Request::MIXED )
                ] )
                . $request->getFile( 'login.css', Request::CSS ),
            'bodyId'      => 'pagina-login'
        ] );

        // Formulário de Login
        $request->showFile( 'login.html', Request::HTML, [
            'raiz'    => $uri->getRaiz(),
            'erros'   => $erros,
            'retorno' => $uri->getParam( 'retorno' ),
            'user'    => $uri->getParam( 'user' )
        ] );

        // Footer
        $request->showFile( 'footer.html', Request::HTML );

        return false;
    }

    public static function verificaLogin()
    {
        return Sessao::iniciar()->chaveExiste( 'logado' );
    }

    /**
     * Retorna para a url raiz ou url de retorno, se esta nao for "logout" ou "sair"
     *
     * @param Uri $uri
     */
    public static function retornaRaiz( Uri $uri )
    {
        header( 'Location: ' . $uri->getRaiz()
            . ( ( ! is_null( $uri->getParam( 'retorno' ) ) && ! preg_match( '/^(sair|logout)$/i', $uri->getParam( 'retorno' ) ) )
                ? $uri->getParam( 'retorno' )
                : '' ) );
    }

}