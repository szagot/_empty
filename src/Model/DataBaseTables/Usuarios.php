<?php
/**
 * Tabela de Usuários
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2016
 */

namespace Model\DataBaseTables;

use App\Auth;
use Conn\CreateTable;

class Usuarios implements IModel
{
    const
        ATIVO = 1,
        INATIVO = 0;

    /** @var string Registra o ultimo erro ocorrido */
    private $erro;

    /** @var array Campos */
    private $fields = [
        'nick'  => [
            'type'         => CreateTable::TYPE_CHAR,
            'length'       => 20,
            'primaryKey'   => true,
            'increment'    => false,
            'key'          => false,
            'unique'       => false,
            'fullText'     => false,
            'defaultValue' => null,
            'value'        => null
        ],
        'nome'  => [
            'type'         => CreateTable::TYPE_CHAR,
            'length'       => 50,
            'primaryKey'   => false,
            'increment'    => false,
            'key'          => false,
            'unique'       => false,
            'fullText'     => false,
            'defaultValue' => null,
            'value'        => null
        ],
        'senha' => [
            'type'         => CreateTable::TYPE_VARCHAR,
            'length'       => 100,
            'primaryKey'   => false,
            'increment'    => false,
            'key'          => false,
            'unique'       => false,
            'fullText'     => false,
            'defaultValue' => null,
            'value'        => null
        ],
        'ativo' => [
            'type'         => CreateTable::TYPE_TINYINT,
            'length'       => 1,
            'primaryKey'   => false,
            'increment'    => false,
            'key'          => false,
            'unique'       => false,
            'fullText'     => false,
            'defaultValue' => null,
            'value'        => null
        ]
    ];

    public function getFields()
    {
        return $this->fields;
    }

    public function getRegistrationModel()
    {
        return [
            new self( 'admin', 'Administrador', 'admin', self::ATIVO )
        ];
    }

    public function getErro()
    {
        return $this->erro;
    }

    public function validaCamposObrigatorios( $update = false )
    {
        // É para atualização?
        if ( $update )
            // Pelo menos 1 campo foi informado?
            return (
                ! is_null( $this->getNick() ) ||
                ! is_null( $this->getNome() ) ||
                ! is_null( $this->getSenha() ) ||
                ! is_null( $this->getAtivo() )
            );

        // Se for Insert, coloca o Ativo no padrão, caso não informado
        if ( is_null( $this->getAtivo() ) )
            $this->setAtivo();

        // TODOS os campos obrigatórios foram informados?
        return (
            ! is_null( $this->getNick() ) &&
            ! is_null( $this->getNome() ) &&
            ! is_null( $this->getSenha() )
        );
    }

    /**
     * Usuarios constructor. Somente seta as variáveis que forem declaradas
     *
     * @param string $nick
     * @param string $nome
     * @param string $senha
     * @param int    $ativo
     */
    public function __construct( $nick = null, $nome = null, $senha = null, $ativo = null )
    {
        if ( ! is_null( $nick ) )
            $this->setNick( $nick );
        if ( ! is_null( $nome ) )
            $this->setNome( $nome );
        if ( ! is_null( $senha ) )
            $this->setSenha( $senha );
        if ( ! is_null( $ativo ) )
            $this->setAtivo( $ativo );
    }


    /**
     * @return mixed
     */
    public function getNick()
    {
        return $this->fields[ 'nick' ][ 'value' ];
    }

    /**
     * @param string $nick
     *
     * @return bool
     */
    public function setNick( $nick )
    {
        if ( $this->validaNick( $nick ) ) {
            $this->fields[ 'nick' ][ 'value' ] = $nick;

            return true;
        }

        $this->erro = 'Nick inválido. São aceitos somente letras, numeros, traço, underline e ponto,'
            . 'sendo que deve começar com uma letra e ter no máximo 20 caracteres.';

        return false;
    }

    /**
     * @return string
     */
    public function getNome()
    {
        return $this->fields[ 'nome' ][ 'value' ];
    }

    /**
     * @param string $nome
     *
     * @return bool
     */
    public function setNome( $nome )
    {
        if ( $this->validaNome( $nome ) ) {
            $this->fields[ 'nome' ][ 'value' ] = $nome;

            return true;
        }

        $this->erro = 'Nome inválido. Deve ter de 2 a 50 caracteres.';

        return false;
    }

    /**
     * @return string
     */
    public function getSenha()
    {
        return $this->fields[ 'senha' ][ 'value' ];
    }

    /**
     * @param string $senha
     *
     * @return bool
     */
    public function setSenha( $senha )
    {
        if ( $this->validaSenha( $senha ) ) {
            $this->fields[ 'senha' ][ 'value' ] = Auth::hash( $senha );

            return true;
        }

        $this->erro = 'Senha inválida. Deve ter de 5 a 15 caracteres.';

        return false;
    }

    /**
     * @return int
     */
    public function getAtivo()
    {
        return $this->fields[ 'ativo' ][ 'value' ];
    }

    /**
     * @param int $ativo
     */
    public function setAtivo( $ativo = self::INATIVO )
    {
        $this->fields[ 'ativo' ][ 'value' ] = ( $ativo == self::ATIVO ) ? self::ATIVO : self::INATIVO;
    }

    /**
     * Valida a senha do usuário. Deve ter de 5 a 15 caracteres
     *
     * @param string $senha Senha a ser validada
     *
     * @return boolean
     */
    public function validaSenha( $senha )
    {
        return ( strlen( $senha ) >= 5 && strlen( $senha ) <= 15 );
    }

    /**
     * Valida o nick do usuário. Somente letras, numeros, traço, underline e ponto,
     * devendo começar com uma letra e ter no máximo 20 caracteres
     *
     * @param string $nick Nick a ser validado
     *
     * @return boolean
     */
    public function validaNick( $nick )
    {
        return preg_match( '/^[a-z][a-z0-9\._-]{0,20}$/i', $nick );
    }

    /**
     * Valida o nome do usuário. Deve ter de 2 a 50 caracteres
     *
     * @param string $nome Nome a ser validado
     *
     * @return boolean
     */
    public function validaNome( $nome )
    {
        return ( strlen( $nome ) > 1 && strlen( $nome ) <= 50 );
    }

}