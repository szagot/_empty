<?php
/**
 * API simples (Auth Basic)
 *
 * Testa o CRUD nas tabelas homologadas. Exemplos:
 *  Read
 *      GET /api/{tabela}[?limit=9999&offset=0]
 *      GET /api/{tabela}/{primaryKey}
 *
 *  Create
 *      POST /api/{tabela}
 *          Body: "{tabela}": [ { "campo1": "valor", "campo2": "valor" } ]
 *
 *  Update
 *      PUT /api/{tabela}
 *          Body: "{tabela}": { "{primaryKey}": { "campo1": "valor", "campo2": "valor" } }
 *
 *  Delete
 *      DELETE /api/{tabela}
 *          Body: ["{primaryKey}", "{primaryKey}"]
 *
 *      DELETE /api/{tabela}/{primaryKey}
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2015
 */

namespace API;

use
    Config\Uri,
    App\Msg,
    Conn\Query,
    App\Config,
    App\Auth;


class Area
{
    /** @var array Tabelas permitidas para testes. Para anular os testes, bas remover ou comentar a linha */
    private static $modulosHomologados = [
        'usuarios',
    ];

    /**
     * Testa os módulos do sistema
     *
     * @param Uri $uri
     */
    public static function iniciar( Uri $uri )
    {
        // Verifica se está autorizado a executar essa ação
        if ( ! Auth::basic( Config::getAPIData()['user'], Config::getAPIData()['pass'] ) )
            Msg::api( 'Acesso Negado', Msg::HEADER_DADOS_INVALIDOS );

        if ( ! in_array( strtolower( $uri->opcao ), self::$modulosHomologados ) )
            Msg::api( ':P', Msg::HEADER_DADOS_INVALIDOS );

        self::testAPI( $uri );
    }

    /**
     * Testa CRUD nas tabelas homologadas
     *
     * @param Uri $uri
     */
    private static function testAPI( Uri $uri )
    {
        $set = strtolower( $uri->opcao );
        $modulo = 'Model\DataBaseModel\\' . ucwords( $set );
        $moduloT = 'Model\DataBaseTables\\' . ucwords( $set );

        switch ( $uri->getMethod() ) {
            // Teste de inserção
            case 'POST':
                // Pega o body
                $json = $uri->getBody();

                // Dados informados?
                if ( ! isset( $json->$set ) || count( $json->$set ) == 0 )
                    Msg::api( 'Informe ao menos 1 registro para ser inserido', Msg::HEADER_DADOS_INVALIDOS );

                // Monta array com os dados no formato TUsuarios
                $records = [ ];
                foreach ( $json->$set as $index => $record ) {
                    $records[ $index ] = new $moduloT();
                    foreach ( $record as $field => $value ) {
                        $funcao = 'set' . ucwords( $field );
                        // Garante existencia de metodo
                        if ( method_exists( $records[ $index ], $funcao ) )
                            $records[ $index ]->$funcao( $value );
                    }
                }

                // Insere os usuários e dá o retorno
                $result = $modulo::insert( $records );
                $resultMethod = Msg::HEADER_POST_OK;
                break;

            // Teste de update
            case 'PUT':
            case 'PATH':
                // Pega o body
                $json = $uri->getBody();

                // Dados informados?
                if ( ! isset( $json->$set ) || count( $json->$set ) == 0 )
                    Msg::api( 'Informe ao menos 1 usuário para ser alterado', Msg::HEADER_DADOS_INVALIDOS );

                // Monta array com os dados no formato TUsuarios
                $records = [ ];
                foreach ( $json->$set as $index => $record ) {
                    $records[ $index ] = new $moduloT();
                    foreach ( $record as $field => $value ) {
                        $funcao = 'set' . ucwords( $field );
                        // Garante existencia de metodo
                        if ( method_exists( $records[ $index ], $funcao ) )
                            $records[ $index ]->$funcao( $value );
                    }
                }

                // Tenta atualizar e da o retorno
                $result = $modulo::update( $records );
                $resultMethod = Msg::HEADER_PUT_OK;
                break;

            // Teste de deleção
            case 'DELETE':
                // Pega o body
                $json = $uri->getBody();

                // Verifica se foi especificado um campo chave na URI
                $primaryKey = isset( $uri->detalhe ) && ! empty( $uri->detalhe );

                // Tem nick ou body?
                if ( ! $primaryKey && count( $json ) == 0 )
                    Msg::api( 'Informe o id a ser deletado na URI ou pelo menos 1 ID no body', Msg::HEADER_DADOS_INVALIDOS );

                // Deleta, priorizando URI. Se URI ão contiver um nick especificado, usa o body, com vários nicks
                $result = $modulo::delete( $primaryKey ? $uri->detalhe : $json );
                $resultMethod = Msg::HEADER_DELETE_OK;

                break;

            // Teste de listagem
            default:
                // Tem paginação?
                if ( $uri->getParam( 'limit' ) )
                    $modulo::setLimit( $uri->getParam( 'limit' ) );
                if ( $uri->getParam( 'offset' ) )
                    $modulo::setOffset( $uri->getParam( 'offset' ) );

                // Mostra os dados do usuário da URI ou, caso não especificado, pega todos
                Msg::api( [
                    'get'     => ( isset( $uri->detalhe ) && ! empty( $uri->detalhe ) )
                        ? $modulo::getId( $uri->detalhe )
                        : $modulo::get(),
                    'detalhe' => Query::getLog( false )
                ] );
        }

        // Emite a saída da execução
        Msg::api( [
            'erros'     => $modulo::getErros( false ),
            'mysqlExec' => Query::getLog( false )
        ], ( count( $modulo::getErros( false ) ) == 0 ) ? $resultMethod : Msg::HEADER_DADOS_INVALIDOS );

    }

}