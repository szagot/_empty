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
        # Cria as tabelas do sistema
        # ATENÇÃO!!! Comente esta linha quando for colocar em produção
        'createtables' => '\Control\CreateTables',

        # 404
        '404'          => '\Control\NotExisting',

        # API Area (Auth Basic)
        'api'          => '\API\Area',

        # Login do sistema
        'login'        => '\Control\Login',
        # Logout do sistema
        'logout'       => '\Control\Logout',
        'sair'         => '\Control\Logout',

        # Home
        ''             => '\Control\Home',
        'home'         => '\Control\Home',

        # Modelo de Página segura
        'admin'        => '\Control\System',
        
    ];

    /** @var array Lista com os módulos que não precisam de verificação de login */
    private static $modulosSemSeguranca = [
        '404',
        'createtables',
        'api',
        'login',
        '',
        'home',

    ];

    /**
     * Inicializa o módulo se este existir
     *
     * @param Uri $uri
     */

    public static function iniciar( Uri $uri )
    {
        // Pega a página da URI
        $modulo = strtolower( $uri->pagina );

        // Página não existe
        if ( ! key_exists( $modulo, self::$modulos ) )
            header( 'Location: ' . $uri->getRaiz() . '404' );

        // Módulo não é seguro, ou é segura e está logado
        elseif ( in_array( $modulo, self::$modulosSemSeguranca ) || Login::verificaLogin() ) {
            // Restarta contagem da sessão
            Sessao::iniciar()->setInicioSessao( true );
            // Inicializa módulo
            $modulo = self::$modulos[ $modulo ];
            $modulo::iniciar( $uri );
        }

        // Se não está logado, vai para o login
        else
            header( 'Location: ' . $uri->getRaiz() . 'login?retorno=' . $uri->pagina
                . ( isset( $uri->opcao ) ? ( '/' . $uri->opcao ) : '' )
                . ( isset( $uri->detalhe ) ? ( '/' . $uri->detalhe ) : '' ) );

    }

}
