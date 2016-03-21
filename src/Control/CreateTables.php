<?php
/**
 * Cria as tabelas do sistema
 *      /createtables?access=chave-de-acesso
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 */

namespace Control;

use Config\Uri,
    App\Msg,
    Conn\Connection,
    Conn\CreateTable,
    App\Config;

class CreateTables
{
    private static $registeredTables = [
        'Usuarios',
    ];

    /**
     * Gera a tabelas do BD do sistema.
     * Autorizado apenas se informado o parametro access como Sz4g-0tNVM
     *
     * @param Uri $uri
     */
    public static function iniciar( Uri $uri )
    {
        // Verifica se está autorizado a executar essa ação
        $authAccess = $uri->getParam( 'access' );
        if ( $authAccess != 'Sz4g-0t' )
            Msg::api( 'Acesso Negado', Msg::HEADER_DADOS_INVALIDOS );

        // É pra apagar as tabelas antes de criá-las?
        $drop = true;

        $conn = new Connection(
            Config::getBdData()->bd,
            Config::getBdData()->host,
            Config::getBdData()->user,
            Config::getBdData()->pass
        );

        // Iniciando registro de tabelas
        foreach ( self::$registeredTables as $table ) {
            $pathTable = 'Model\DataBaseTables\\' . $table;

            $tabela = new CreateTable( $conn );
            $tabela->setTable( $table, $drop );

            // Criando a tabela
            $newTable = new $pathTable;
            foreach ( $newTable->getFields() as $fieldName => $fieldProp ) {
                $tabela->addField( $fieldName, $fieldProp[ 'type' ], $fieldProp[ 'legth' ] );
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
            $msg = $tabela->create();

            // Deu erro?
            if ( $msg !== true )
                Msg::api( $msg, Msg::HEADER_DADOS_INVALIDOS );

            // Tem registros iniciais?
            $initialRecords = $newTable->getRegistrationModel();
            if ( count( $initialRecords ) > 0 ) {
                $pathModel = 'Model\DataBaseModel\\' . $table;

                if( ! $pathModel::insert( $initialRecords ) )
                    Msg::api( $pathModel::getErros() );
            }
        }

        // Tabelas criadas
        Msg::api( 'Tabelas criadas' );

    }
}