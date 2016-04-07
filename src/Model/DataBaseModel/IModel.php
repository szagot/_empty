<?php
/**
 * * Interface com modelo para controle de dados da tabela
 *
 * @author    Daniel Bispo <daniel@tmw.com.br>
 * @copyright Copyright (c) 2016, TMW E-commerce Solutions
 */

namespace Model\DataBaseModel;


interface IModel
{
    /**
     * Retorna os registros da tabela
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public static function get( $limit = null, $offset = null );

    /**
     * Pega apenas o o registro especificado. Necessário para o GET específico
     *
     * @param mixed $id - Chave primária ou única
     *
     * @return array|object
     */
    public static function getId( $id );

    /**
     * Retorna o total de registros de uma tabela
     *
     * @return int
     */
    public static function getQtdeReg();

    /**
     * Seta o limite da pesquisa
     *
     * @param int $limit
     */
    public static function setLimit( $limit );

    /**
     * Seta o offset da pesquisa
     *
     * @param int $offset
     */
    public static function setOffset( $offset );

    /**
     * Insere um ou mais registros na tabela.
     *
     * @param array $records Cada registro deve estar no formato de sua respectiva classe.
     *                       Exemplo: $records[ instanceof Tabela, ... ]
     *
     * @return bool
     */
    public static function insert( $records );

    /**
     * Altera um ou mais registros na tabela.
     *
     * @param array $records Cada registro deve estar no formato de sua respectiva classe,
     *                       sendo que a chave de cada registro deve ser o valor do filtro para a alteração
     *                       daquele registro. Exemplo: $records[ 'identificador' => instanceof Tabela, ... ]
     *
     * @return bool
     */
    public static function update( $records );

    /**
     * Apaga um ou mais registros
     *
     * @param array $ids Este parametro deve conter o filtro para deleção do registro
     *
     * @return mixed
     */
    public static function delete( $ids );

    /**
     * Pega os erros gerados ou um array vazio em caso de não haver erros
     *
     * @param bool $apenasUltimo Se TRUE retorna apenas o último registro
     *
     * @return array
     */
    public static function getErros( $apenasUltimo = true );

    /**
     * Seta a conexão com o banco de dados do Dashboard, para ser usada no self::get()
     */
    public static function setConn();
}