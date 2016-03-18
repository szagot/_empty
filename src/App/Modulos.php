<?php
/**
 * Controle de Módulos homologados
 *
 * @author    Daniel Bispo <daniel@tmw.com.br>
 */

namespace App;

use
    Config\Uri,
    Control\Login,
    Config\Sessao;

class Modulos
{
    /** @var array $modulos Controle de módulos homologaoos. 'página' => 'Módulo' */
    private static $modulos = [
        # Área de Testes
        'teste'                => '\Testes\Area',
        # Cria as tabelas do sistema
        'createtables'         => '\Control\CreateTables',

        # Login do sistema
        'login'                => '\Control\Login',
        # Logout do sistema
        'logout'               => '\Control\Logout',
        'sair'                 => '\Control\Logout',
    ];

    private static $modulosSemSeguranca = [
        'teste',
        'createtables',
        'login'
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
            // O módulo não existe, mas está logado. Vai pro dashboard
            header( 'Location: ' . $uri->getRaiz() . 'dashboard' );
    }

}
