<?php
/**
 * Interface com modelo de criação de tabela.
 *
 * Modelo de criação de tabelas em \Control\CreateTables
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2016
 */

namespace Model\DataBaseTables;


interface IModel
{
    /**
     * Crie um variável para retorno de erros
     * Exemplo:
     *      private $erro;
     */

    /**
     * Crie uma variável com o detalhe de cada campo
     * Exemplo:
     *       private $fields = [
     *           'id'  => [
     *              'type'         => CreateTable::TYPE_INT,
     *              'length'       => 11,
     *              'primaryKey'   => true,
     *              'increment'    => true,
     *              'key'          => false,
     *              'unique'       => false,
     *              'fullText'     => false,
     *              'defaultValue' => null,
     *              'value'        => null
     *           ],
     *      ];
     */

    /**
     * Pega os campos da tabela
     *
     * @return array
     */
    public function getFields();

    /**
     * Retorna um array com campos de registro iniciais para criação de tabelas.
     *
     * @return array Se estiver vazio, nada deve ser cadastrado como modelo
     */
    public function getRegistrationModel();

    /**
     * Valida os campos obrigatórios da tabela, pra ver se estão declarados
     *
     * @param bool $update Para atualização, basta que pelo menos 1 campo tenha sido declarado
     *
     * @return bool
     */
    public function validaCamposObrigatorios($update = false);

    /**
     * Retorna o último erro gerado
     *
     * @return string
     */
    public function getErro();
}