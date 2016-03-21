<?php
/**
 * Controle de I/O da tabela de Usuários
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2015
 */

namespace Model\DataBaseModel;

use Conn\Connection,
    Conn\Query,
    App\Config,
    App\Auth,
    Model\DataBaseTables\Usuarios as TUsuarios;

class Usuarios implements IModel
{
    const
        ATIVO = 1,
        INATIVO = 0;

    private static
        $conn,
        $usuarios,
        $erros = [ ],
        $registrosAfetados = 0;

    /**
     * @param string|null $nick Pega apenas o usuário com o nick selecionado
     *
     * @return array
     */
    public static function get( $nick = null )
    {
        if ( is_null( self::$usuarios ) ) {
            self::setConn();
            self::$usuarios = Query::exec( 'SELECT * FROM Usuarios ORDER BY nome' );
        }

        // Nick informado?
        if ( ! is_null( $nick ) ) {
            foreach ( self::get() as $user )
                // Encontrou o nick pesquisado?
                if ( isset( $user[ 'nick' ] ) && $user[ 'nick' ] == $nick )
                    return $user;

            // Não encontrou o nick
            return [ ];
        }

        // Retorna todos os usuários
        return self::$usuarios;
    }

    /**
     * Pega apenas os usuários ativos
     *
     * @return array
     */
    public
    static function getActive()
    {
        $tempUsers = [ ];
        foreach ( self::get() as $user )
            if ( $user[ 'ativo' ] == 1 )
                $tempUsers[] = $user;

        return $tempUsers;
    }

    /**
     * Pega apenas os usuários inativos
     *
     * @return array
     */
    public
    static function getInactive()
    {
        $tempUsers = [ ];
        foreach ( self::get() as $user )
            if ( $user[ 'ativo' ] != 1 )
                $tempUsers[] = $user;

        return $tempUsers;
    }

    public static function insert( $records = [ ] )
    {
        self::$registrosAfetados = 0;

        // Possui registros?
        if ( ! is_array( $records ) || count( $records ) == 0 ) {
            self::$erros[] = 'Informe pelo menos 1 registro a ser adicionado';

            return false;
        }

        $query = '';
        $data = [ ];
        foreach ( $records as $index => $record ) {
            // Valida o formato
            if ( ! $record instanceof TUsuarios ) {
                self::$erros[] = "Formato do registro na chave {$index} é inválido. Ele deve ser uma tabela de Usuários.";

                continue;
            }

            // Verifica se o usuário já existe
            $testUser = self::get( $record->getNick() );
            if ( isset( $testUser[ 'nick' ] ) ) {
                self::$erros[] = "Já existe um usuário com o nick {$record->getNick()}";

                continue;
            }

            if ( ! $record->validaCamposObrigatorios() ) {
                self::$erros[] = 'Nick, Nome e Senha são obrigatórios';

                continue;
            }

            // Monta a query
            $query .= ( ! empty( $query ) ? ', ' : '' ) . "(:nick_{$index}, :nome_{$index}, :senha_{$index}, :ativo_{$index})";
            $data[ "nick_{$index}" ] = $record->getNick();
            $data[ "nome_{$index}" ] = $record->getNome();
            $data[ "senha_{$index}" ] = $record->getSenha();
            $data[ "ativo_{$index}" ] = $record->getAtivo();
        }

        if ( count( $data ) > 0 ) {
            // Tenta cadastrar os usuário
            if ( ! Query::exec( "INSERT INTO usuarios VALUES {$query}", $data ) ) {
                self::$erros[] = Query::getLog( true )[ 'errorMsg' ];

                return false;
            }

            // Salva a quantidade de usuários inseridos
            self::$registrosAfetados = (int) Query::getLog( true )[ 'rowsAffected' ];

            // Se foi cadastrado, pega usuários novamente
            self::$usuarios = null;
            self::get();

            return true;
        }

        // Não cadastrou nenhum usuário
        return false;
    }

    public static function update( $records = [ ] )
    {
        self::$registrosAfetados = 0;

        // Possui registros?
        if ( ! is_array( $records ) || count( $records ) == 0 ) {
            self::$erros[] = 'Informe pelo menos 1 registro a ser alterado';

            return false;
        }

        $query = '';
        foreach ( $records as $nick => $record ) {
            // Valida o formato
            if ( ! $record instanceof TUsuarios ) {
                self::$erros[] = "Formato do registro {$nick} é inválido. Ele deve ser uma tabela de Usuários.";

                continue;
            }

            // Verifica se o usuário buscado existe
            $updateUser = self::get( $nick );
            if ( ! isset( $updateUser[ 'nick' ] ) ) {
                self::$erros[] = "O nick {$nick} informado não existe.";

                continue;
            }

            // Verifica se o novo nick já não existe, no caso de ser solicitada alteração de nick
            if ( $nick != $record->getNick() ) {
                // Verifica se o usuário já existe
                $testUser = self::get( $record->getNick() );
                if ( isset( $testUser[ 'nick' ] ) ) {
                    self::$erros[] = "Já existe um usuário com o nick {$record->getNick()}";

                    continue;
                }
            }

            // Pelo menos 1 campo tem q estar preenchido para alteração
            if ( ! $record->validaCamposObrigatorios( true ) ) {
                self::$erros[] = "Informe pelo menos 1 campos para alteração no registro {$nick}";

                continue;
            }

            // Monta a query
            $data = [ 'search' => $nick ];
            $query = '';
            if ( ! is_null( $record->getNick() ) ) {
                $data[ 'nick' ] = $record->getNick();
                $query .= ( ! empty( $query ) ? ', ' : '' ) . 'nick = :nick';
            }
            if ( ! is_null( $record->getNome() ) ) {
                $data[ 'nome' ] = $record->getNome();
                $query .= ( ! empty( $query ) ? ', ' : '' ) . 'nome = :nome';
            }
            if ( ! is_null( $record->getSenha() ) ) {
                $data[ 'senha' ] = $record->getSenha();
                $query .= ( ! empty( $query ) ? ', ' : '' ) . 'senha = :senha';
            }
            if ( ! is_null( $record->getAtivo() ) ) {
                $data[ 'ativo' ] = $record->getAtivo();
                $query .= ( ! empty( $query ) ? ', ' : '' ) . 'ativo = :ativo';
            }

            // Tenta fazer a atualização do mesmo
            if ( ! Query::exec( "UPDATE usuarios SET {$query} WHERE nick = :search", $data ) ) {
                self::$erros[] = Query::getLog( true )[ 'errorMsg' ];

                continue;
            }

            self::$registrosAfetados += (int) Query::getLog( true )[ 'rowsAffected' ];
        }

        // Se houve alteração, pega usuários novamente
        if ( self::$registrosAfetados > 0 ) {
            self::$usuarios = null;
            self::get();

            return true;
        }

        return false;
    }

    public static function delete( $nicks )
    {
        self::$registrosAfetados = 0;

        // Se for um único nick a ser deletado, altera para um array
        if ( is_string( $nicks ) )
            $nicks = [ $nicks ];

        // Possui nicks para deletar?
        if ( ! is_array( $nicks ) || count( $nicks ) == 0 ) {
            self::$erros[] = "Informe pelo menos 1 nick a ser deletado";

            return false;
        }

        // Monta filtro
        $where = '';
        foreach ( $nicks as $index => $nick ) {
            // Verifica se o Nick está dentro do padrão
            if ( ! self::validaNick( $nick ) ) {
                self::$erros[] = "Nick {$nick} inválido. O nick deve ter de 1 a 20 caracteres, entre letras, números, "
                    . 'traços, pontos e/ou underlines.';

                return false;
            }

            // Verifica se o usuário existe
            $deleteUser = self::get( $nick );
            if ( ! isset( $deleteUser[ 'nick' ] ) ) {
                self::$erros[] = "O nick {$nick} informado não existe.";

                // Embora emita um aviso, porém não se trata de um erro, já que o objetivo é deletar o registro.
                return true;
            }

            $where .= ( $where == '' ) ? "nick = :{$index}" : " OR nick = :{$index}";
        }

        // Tenta deletar o(s) usuário(s)
        if ( ! Query::exec( "DELETE FROM usuarios WHERE {$where}", $nicks ) ) {
            self::$erros[] = Query::getLog( true )[ 'errorMsg' ];

            return false;
        }

        // Salva a quantidade de usuários deletados
        self::$registrosAfetados = (int) Query::getLog( true )[ 'rowsAffected' ];

        // Apaga o(s) usuário(s) do registro local
        foreach ( self::$usuarios as $index => $usuario )
            if ( in_array( $usuario[ 'nick' ], $nicks ) )
                unset( self::$usuarios[ $index ] );
        // Reorganiza a lista
        self::$usuarios = array_values( self::$usuarios );

        return true;
    }

    /**
     * Valida a senha do usuário. Deve ter de 5 a 15 caracteres
     *
     * @param string $senha Senha a ser validada
     * @param string $nick  Nick a ser validado
     *
     * @return boolean
     */
    public static function validaSenha( $senha, $nick = null )
    {
        // Se nick não informado, valida apenas o formato da senha
        if ( ! isset( $nick ) )
            return ( strlen( $senha ) >= 5 && strlen( $senha ) <= 15 );

        // Nick é válido?
        if ( ! self::validaNick( $nick ) )
            return false;

        // Verifica se o usuário e a senha batem
        $user = self::get( $nick );

        return isset( $user[ 'senha' ] ) && $user[ 'senha' ] == Auth::hash( $senha ) && $user[ 'ativo' ] == self::ATIVO;

    }

    /**
     * Valida o nick do usuário. Somente letras, numeros, traço, underline e ponto,
     * devendo começar com uma letra e ter no máximo 20 caracteres
     *
     * @param string $nick Nick a ser validado
     *
     * @return boolean
     */
    public static function validaNick( $nick )
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
    public static function validaNome( $nome )
    {
        return ( strlen( $nome ) > 1 && strlen( $nome ) <= 50 );
    }


    public static function getErros( $apenasUltimo = true )
    {
        return $apenasUltimo ? end( self::$erros ) : self::$erros;
    }

    /**
     * Pega qtde de suários afetados (deletados, inseridos ou alterados) na última ação
     *
     * @return int
     */
    public static function getRegistrosAfetados()
    {
        return self::$registrosAfetados;
    }


    public static function setConn()
    {
        // Efetua a conexão apenas uma vez no BD
        if ( ! self::$conn ) {
            // Pega os dados do BD
            $bdData = Config::getBdData();
            // Encontrados?
            if ( isset( $bdData->bd, $bdData->host, $bdData->user, $bdData->pass ) ) {
                self::$conn = new Connection(
                    $bdData->bd,
                    $bdData->host,
                    $bdData->user,
                    $bdData->pass
                );

                Query::setConn( self::$conn );
            }
        }
    }

}