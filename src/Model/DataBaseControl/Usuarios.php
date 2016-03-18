<?php
/**
 * Controle de I/O da tabela de Usuários
 *
 * @author    Daniel Bispo <daniel@tmw.com.br>
 * @copyright Copyright (c) 2015
 */

namespace Model\DataBaseControl;

use Conn\Connection,
    Conn\Query,
    App\Config,
    App\Auth;

class Usuarios
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
     * Pega todos os usuários cadastrados ou o usuário de nick informado
     *
     * @param string|null $nick Pega apenas o usuário com o nick selecionado
     *
     * @return array
     */
    public static function get( $nick = null )
    {
        if ( ! self::$usuarios ) {
            self::setConn();
            self::$usuarios = Query::exec( 'SELECT * FROM usuarios ORDER BY nome' );
        }

        // Nick informado?
        if ( ! is_null( $nick ) ) {
            // Nick válido?
            if ( self::validaNick( $nick ) ) {
                foreach ( self::get() as $user )
                    // Encontrou o nick pesquisado?
                    if ( isset( $user[ 'nick' ] ) && $user[ 'nick' ] == $nick )
                        return $user;
            }

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
    public static function getActive()
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
    public static function getInactive()
    {
        $tempUsers = [ ];
        foreach ( self::get() as $user )
            if ( $user[ 'ativo' ] != 1 )
                $tempUsers[] = $user;

        return $tempUsers;
    }

    /**
     *  Seta/Inclui um usuário ao sistema de gestão (Dashboard)
     *
     * @param string $nick  Nick do usuário (padrão letras, números, pontos, taços e/ou underline)
     * @param string $nome  Nome do usuário
     * @param string $senha Senha do usuário
     * @param int    $ativo Está ativo?
     *
     * @return bool Em caso de falha, o retorno será FALSE e um log de erros poderá ser recuperado com getErros()
     */
    public static function insert( $nick, $nome, $senha, $ativo = self::ATIVO )
    {
        self::$registrosAfetados = 0;

        // Verifica se o Nick e a senha estão dentro dos padrões
        if ( ! self::validaNick( $nick ) || ! self::validaSenha( $senha ) || ! self::validaNome( $nome ) ) {
            self::$erros[] = 'Nick, Nome ou Senha inválidos. O nick deve ter de 1 a 20 caracteres, entre letras, números, '
                . 'traços, pontos e/ou underlines. O nome não pode estar vazio e a senha deve ter de 6 a 15 caracteres.';

            return false;
        }

        // Verifica se o usuário já existe
        $testUser = self::get( $nick );
        if ( isset( $testUser[ 'nick' ] ) ) {
            self::$erros[] = "Já existe um usuário com o nick {$nick}";

            return false;
        }

        // Garante que ativo estará no formato certo
        $ativo = ( $ativo == self::ATIVO ) ? self::ATIVO : self::INATIVO;

        // Monta array de dados
        $data = [
            'nick'  => $nick,
            'nome'  => $nome,
            'senha' => Auth::hash( $senha ),
            'ativo' => $ativo
        ];

        // Tenta cadastrar o usuário
        if ( ! Query::exec( 'INSERT INTO usuarios VALUES(:nick, :nome, :senha, :ativo)', $data ) ) {
            self::$erros[] = Query::getLog( true )[ 'errorMsg' ];

            return false;
        }

        // Salva a quantidade de usuários inseridos
        self::$registrosAfetados = (int) Query::getLog( true )[ 'rowsAffected' ];

        // Se foi cadastrado, adiciona o usuario ao array já baixado
        self::$usuarios = null;
        self::get();

        return true;
    }

    /**
     * Efetua a alteração no valor dos campos do nick informado
     *
     * @param string $nick   Nick a ser alterado
     * @param array  $campos Campos a serem alterados ([ 'campo' => 'novo valor' ])
     *
     * @return bool
     */
    public static function update( $nick, $campos = [ ] )
    {
        self::$registrosAfetados = 0;

        // Pelo menos um campo foi informado?
        if ( ! is_array( $campos ) && count( $campos ) == 0 ) {
            self::$erros[] = 'Informe pelo menos 1 campo para alteração';

            return false;
        }

        // Nick é válido?
        if ( ! self::validaNick( $nick ) ) {
            self::$erros[] = "Nick {$nick} inválido. O nick deve ter de 1 a 20 caracteres, entre letras, números, "
                . 'traços, pontos e/ou underlines.';

            return false;
        }

        // Verifica se o usuário existe
        $updateUser = self::get( $nick );
        if ( ! isset( $updateUser[ 'nick' ] ) ) {
            self::$erros[] = "O nick {$nick} informado não existe.";

            return true;
        }

        // Inicia a análise de cada campo informado
        $altera = '';
        foreach ( $campos as $campo => $valor ) {
            $deveAlterar = true;
            switch ( $campo ) {
                case 'nick':
                    // Verifica se é válido
                    if ( ! self::validaNick( $valor ) ) {
                        self::$erros[] = "O nick {$valor} informado não está dentro dos parâmetros: "
                            . 'O nick deve ter de 1 a 20 caracteres, entre letras, números, traços, pontos e/ou underlines';

                        return false;
                    }

                    // Verifica se o nick informado não é o mesmo cadastrado
                    if ( $nick == $valor ) {
                        $deveAlterar = false;
                        continue;
                    }

                    // Verifica se o nick informado já existe
                    $verificaNick = self::get( $valor );
                    if ( isset( $verificaNick[ 'nick' ] ) ) {
                        self::$erros[] = "Já existe um usuário com o nick {$valor} cadastrado.";

                        return false;
                    }

                    break;

                case 'nome':
                    // Nome é válido?
                    if ( ! self::validaNome( $valor ) ) {
                        self::$erros[] = "O nome {$valor} informado não está dentro dos parâmetros: "
                            . 'não deve estar vazio e ter até 50 caracteres.';

                        return false;
                    }

                    break;

                case 'senha':
                    // Se o valor informado é null, ele não altera
                    if ( empty( $valor ) ) {
                        $deveAlterar = false;
                        continue;
                    }

                    // Senha é válida?
                    if ( ! self::validaSenha( $valor ) ) {
                        self::$erros[] = 'A senha informada não está dentro dos parâmetros: '
                            . 'ela deve ter de 6 a 15 caracteres.';

                        return false;
                    }

                    // Coloca a senha com hash
                    $campos[ $campo ] = Auth::hash( $valor );

                    break;

                case 'ativo':
                    $campos[ $campo ] = ( $valor == self::ATIVO ) ? self::ATIVO : self::INATIVO;
                    break;

                // Campo não é válido
                default:
                    self::$erros[] = "Campo {$campo} é inválido. Campos válidos são: "
                        . 'nome, senha e ativo.';

                    return false;

            }

            // Acrescenta o campo na lista de alteração se o valor for diferente do atual
            if ( $deveAlterar && $updateUser[ $campo ] != $valor )
                $altera .= ( $altera == '' ) ? "{$campo} = :{$campo}" : ", {$campo} = :{$campo}";

            // Caso o valor seja o mesmo, exclui o campo da alteração
            else
                unset( $campos[ $campo ] );
        }

        // Será necessária alguma alteração?
        if ( $altera == '' )
            return true;

        // Adiciona o nick pesquisado
        $campos[ 'nick' ] = $nick;

        // Tenta efetuar a alteração no nick informado
        if ( ! Query::exec( "UPDATE usuarios SET {$altera} WHERE nick = :nick", $campos ) ) {
            self::$erros[] = Query::getLog( true )[ 'errorMsg' ];

            return false;
        }

        // Salva a quantidade de usuários alterados
        self::$registrosAfetados = (int) Query::getLog( true )[ 'rowsAffected' ];

        // Se foi alterado, altera o usuario ao array já baixado
        foreach ( self::$usuarios as $index => $usuario )
            if ( $usuario[ 'nick' ] == $nick ) {
                foreach ( $campos as $campo => $valor )
                    self::$usuarios[ $index ][ $campo ] = $valor;

                break;
            }

        return true;
    }

    /**
     * Deleta um ou mais usuários informados pelo nick
     *
     * @param string|array $nicks Nick ou um array de Nicks a serem deletados
     *
     * @return bool
     */
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


    /**
     * Pega os erros gerados ou um array vazio em caso de não haver erros
     *
     * @param bool $apenasUltimo Se TRUE retorna apenas o último registro
     *
     * @return array
     */
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


    /**
     * Seta a conexão com o banco de dados do Dashboard
     */
    private static function setConn()
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

    /**
     * Planos constructor.
     */
    private function __construct()
    {
    }
}