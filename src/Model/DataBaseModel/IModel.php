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
     * @return array
     */
    public static function get();

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
     * @param array $ids Este parametro deve conter o filtro para deleção do registro
     *
     * @return mixed
     */
    public static function delete( $ids );

    /**
     * Seta a conexão com o banco de dados do Dashboard, para ser usada no self::get()
     */
    public static function setConn();
}