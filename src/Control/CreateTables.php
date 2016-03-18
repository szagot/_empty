<?php
/**
 * Cria as tabelas do sistema
 *      /createtables?access=chave-de-acesso
 *
 * @author    Daniel Bispo <daniel@tmw.com.br>
 */

namespace Control;

use Config\Uri,
    App\Msg,
    Conn\Connection,
    Conn\CreateTable,
    Model\DataBaseControl\Usuarios,
    App\Config;

class CreateTables
{
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
        if ( $authAccess != 'Sz4g-0tNVM' )
            Msg::api( 'Acesso Negado', Msg::HEADER_DADOS_INVALIDOS );

        // É pra apagar as tabelas antes de criá-las?
        $drop = true;

        $conn = new Connection(
            Config::getBdData()->bd,
            Config::getBdData()->host,
            Config::getBdData()->user,
            Config::getBdData()->pass
        );

        /**
         * table:usuarios
         */
        $tabela = new CreateTable( $conn );
        $tabela->setTable( 'usuarios', $drop );

        // Campos
        $tabela->addField( 'nick', CreateTable::TYPE_CHAR, 20 );
        $tabela->addField( 'nome', CreateTable::TYPE_CHAR, 50 );
        $tabela->addField( 'senha', CreateTable::TYPE_VARCHAR, 100 );
        $tabela->addField( 'ativo', CreateTable::TYPE_TINYINT, 1, 1 );

        // Chave primária
        $tabela->setPrimaryKey( 'nick', false );

        // Cria a tabela de usuarios
        $msg = $tabela->create();

        // Deu erro?
        if ( $msg !== true )
            Msg::api( $msg, Msg::HEADER_DADOS_INVALIDOS );

        // Popula com usuário principal
        if ( ! Usuarios::insert( 'szagot', 'Daniel Bispo', 'DSpider1981' ) )
            Msg::api( Usuarios::getErros() );

        /**
         * table:publicadores
         */
        $tabela = new CreateTable( $conn );
        $tabela->setTable( 'publicadores', $drop );

        // Campos
        $tabela->addField( 'id', CreateTable::TYPE_INT, 3 );
        $tabela->addField( 'nome', CreateTable::TYPE_CHAR, 200 );
        $tabela->addField( 'estudoAtual', CreateTable::TYPE_TINYINT, 2 );
        $tabela->addField( 'publicador', CreateTable::TYPE_TINYINT, 1, 1 );
        $tabela->addField( 'servo', CreateTable::TYPE_TINYINT, 1, 0 );
        $tabela->addField( 'anciao', CreateTable::TYPE_TINYINT, 1, 0 );
        $tabela->addField( 'pioneiro', CreateTable::TYPE_TINYINT, 1, 0 );

        // Chave primária
        $tabela->setPrimaryKey( 'id' );

        // Cria a tabela de usuarios
        $msg = $tabela->create();

        // Deu erro?
        if ( $msg !== true )
            Msg::api( $msg, Msg::HEADER_DADOS_INVALIDOS );

        /**
         * table:partes
         */
        $tabela = new CreateTable( $conn );
        $tabela->setTable( 'partes', $drop );

        // Campos
        $tabela->addField( 'id', CreateTable::TYPE_INT, 2 );
        $tabela->addField( 'nome', CreateTable::TYPE_CHAR, 200 );
        /**
         * Seções:
         *      0 - TESOUROS DA PALAVRA DE DEUS
         *      1 - FAÇA SEU MELHOR NO MINISTÉRIO
         *      2 - NOSSA VIDA CRISTÃ
         */
        $tabela->addField( 'secao', CreateTable::TYPE_TINYINT, 1, 0 );
        $tabela->addField( 'ordem', CreateTable::TYPE_TINYINT, 1, 0 );

        // Chave primária
        $tabela->setPrimaryKey( 'id' );

        // Cria a tabela de usuarios
        $msg = $tabela->create();

        // Deu erro?
        if ( $msg !== true )
            Msg::api( $msg, Msg::HEADER_DADOS_INVALIDOS );

        /**
         * table:designacoes
         */
        $tabela = new CreateTable( $conn );
        $tabela->setTable( 'designacoes', $drop );

        // Campos
        $tabela->addField( 'data', CreateTable::TYPE_DATE );
        $tabela->addField( 'parte', CreateTable::TYPE_INT, 2 );
        $tabela->addField( 'orador', CreateTable::TYPE_INT, 3 );
        $tabela->addField( 'ajudante', CreateTable::TYPE_INT, 3 );
        $tabela->addField( 'tema', CreateTable::TYPE_VARCHAR, 500 );

        // Chaves
        $tabela->addKey( 'data' );

        // Chaves estrangeiras
        $tabela->addFk( 'orador', 'publicadores', 'id' );
        $tabela->addFk( 'ajudante', 'publicadores', 'id' );
        $tabela->addFk( 'parte', 'partes', 'id' );

        // Cria a tabela de usuarios
        $msg = $tabela->create();

        // Deu erro?
        if ( $msg !== true )
            Msg::api( $msg, Msg::HEADER_DADOS_INVALIDOS );


        // Tabelas criadas
        Msg::api( 'Tabelas criadas' );

    }
}