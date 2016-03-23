<?php
/**
 * Controle de Módulos homologados
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 */

namespace App;

use
    Config\Uri,
    Control\Login,
    Config\Sessao;

class Modulos
{
    /**
     * Coloque como chave APENAS a página principal.
     * As demais partes da URI serão tratadas no respectivo módulo.
     * Exemplo:
     *      URI         /pagina/opcao/detalhe
     *      $modulos    'pagina' => '\Control\ModuloPagina'
     *
     * @var array Controle de módulos homologados.
     */
    private static $modulos = [
        # Área de Testes
        'teste'        => '\Testes\Area',
        # Cria as tabelas do sistema (API with Auth Basic)
        'createtables' => '\Control\CreateTables',

        # Login do sistema
        'login'        => '\Control\Login',
        # Logout do sistema
        'logout'       => '\Control\Logout',
        'sair'         => '\Control\Logout',

        # Home
        ''             => '\Control\Home',
        'home'         => '\Control\Home',

        # Modelo de Página segura
        'admin'        => '\Control\System'
    ];

    /** @var array Lista com os módulos que não precisam de verificação de login */
    private static $modulosSemSeguranca = [
        'createtables',
        'teste',
        'login',
        '',
        'home'
    ];

    /**
     * Inicializa o módulo se este existir
     *
     * @param Uri $uri
     */

    public static function iniciar( Uri $uri )
    {
        // Pega a página da URI
        $modulo = strtolower( $uri->getUri()->pagina );

        // Se o módulo existir, inicializa o mesmo
        if ( key_exists( $modulo, self::$modulos ) ) {

            // Verifica se o módulo deve ser seguro e se está logado
            if ( in_array( $modulo, self::$modulosSemSeguranca ) || Login::verificaLogin() ) {
                Sessao::iniciar()->setInicioSessao( true );
                $modulo = self::$modulos[ $modulo ];
                $modulo::iniciar( $uri );

            } else
                // Se não está logado ou não é modulo sem seguranca, vai para o login
                header( 'Location: ' . $uri->getRaiz() . 'login?retorno=' . $uri->getUri()->pagina
                    . ( isset( $uri->getUri()->opcao ) ? ( '/' . $uri->getUri()->opcao ) : '' )
                    . ( isset( $uri->getUri()->detalhe ) ? ( '/' . $uri->getUri()->detalhe ) : '' ) );

        } elseif ( ! Login::verificaLogin() )
            // O módulo não existe. Se não estiver logado, tenta se logar
            header( 'Location: ' . $uri->getRaiz() . 'login' );

        else
            // O módulo não existe, mas está logado. Vai pra raiz
            header( 'Location: ' . $uri->getRaiz() );
    }

}
