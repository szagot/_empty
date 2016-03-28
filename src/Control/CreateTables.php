<?php
/**
 * Cria as tabelas do sistema, conforme seus dados em Model\DataBaseTables\Tabela
 *      POST /createtables {Auth Basic}
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 */

namespace Control;

use Config\Uri,
    App\Msg,
    App\Auth,
    Conn\Connection,
    Conn\CreateTable,
    App\Config;
use Conn\Query;

class CreateTables
{
    /** @var array Tabelas registradas para criação */
    private static $registeredTables = [
        'Usuarios',
    ];

    /**
     * Gera a tabelas do BD do sistema.
     * Autorizado apenas para email e senha padrões
     *
     * @param Uri $uri
     */
    public static function iniciar( Uri $uri )
    {
        // Verifica se está autorizado a executar essa ação
        if ( ! Auth::basic( Config::getAPIData()['user'], Config::getAPIData()['pass'] ) || $uri->getMethod() != 'POST' )
            Msg::api( 'Acesso Negado', Msg::HEADER_DADOS_INVALIDOS );

        // É pra apagar as tabelas antes de criá-las?
        $drop = true;

        $conn = new Connection(
            Config::getBdData()['bd'],
            Config::getBdData()['host'],
            Config::getBdData()['user'],
            Config::getBdData()['pass']
        );

        // Iniciando registro de tabelas
        foreach ( self::$registeredTables as $table ) {
            $pathTable = 'Model\DataBaseTables\\' . $table;

            $tabela = new CreateTable( $conn );
            $tabela->setTable( $table, $drop );

            // Criando a tabela
            $newTable = new $pathTable;
            foreach ( $newTable->getFields() as $fieldName => $fieldProp ) {
                $tabela->addField( $fieldName, $fieldProp[ 'type' ], $fieldProp[ 'length' ], $fieldProp['defaultValue'] );
                // É chave primária?
                if ( $fieldProp[ 'primaryKey' ] )
                    $tabela->setPrimaryKey( $fieldName, $fieldProp[ 'increment' ] );
                // É chave?
                if ( $fieldProp[ 'key' ] )
                    $tabela->addKey( $fieldName );
                // É chave única?
                if ( $fieldProp[ 'unique' ] )
                    $tabela->addUniqueKey( $fieldName );
                // É Full Text?
                if ( $fieldProp[ 'fullText' ] )
                    $tabela->addFullTextKey( $fieldName );
            }

            // Cria a tabela
            $msg = $tabela->create( CreateTable::COLLATE_UTF8, CreateTable::ENGINE_MYISSAM );

            // Deu erro?
            if ( $msg !== true )
                Msg::api( [
                    'msg'    => $msg,
                    'data'   => Query::getLog()
                ], Msg::HEADER_DADOS_INVALIDOS );

            // Tem registros iniciais?
            $initialRecords = $newTable->getRegistrationModel();
            if ( count( $initialRecords ) > 0 ) {
                $pathModel = 'Model\DataBaseModel\\' . $table;

                if ( ! $pathModel::insert( $initialRecords ) )
                    Msg::api( [
                        'msg'    => $pathModel::getErros(),
                        'data'   => Query::getLog()
                    ], Msg::HEADER_DADOS_INVALIDOS );
            }
        }

        // Tabelas criadas
        Msg::api( [
            'msg'    => 'Tabelas Criadas',
            'data'   => Query::getLog()
        ] );

    }
}