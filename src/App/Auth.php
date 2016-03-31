<?php
/**
 * Controle de autorização do sistema.
 * Inclui verificação de Auth Basic para API's
 *
 * @author Daniel Bispo <szagot@gmail.com>
 */

namespace App;

use Model\DataBaseModel\Usuarios;

class Auth
{
    // Chave de encriptação
    private static $chave = [
        // Ida
        'a' => ' =abegiostz@63,1*572ABEGIOSTZ48#9|0&?$',
        // Volta (espelho)
        'b' => '= @63,1*572abegiostz48#9|0&?$ABEGIOSTZ'
    ];


    /**
     * Retorna um hash md5 acrescentando a chave ao texto informado
     *
     * @param string $texto Texto a ser encriptado
     *
     * @return string Hash
     */
    public static function hash( $texto )
    {
        return md5( self::makePass( $texto ) );
    }

    /**
     * Retorna um hash base64 acrescentando a chave ao texto informado
     *
     * @param string $texto Texto a ser encriptado
     *
     * @return string Hash
     */
    public static function hashEncode( $texto )
    {
        return base64_encode( self::makePass( $texto ) );
    }

    /**
     * Retorna um texto a partir de uma senha encriptada com hash
     *
     * @param string $senha Texto a ser encriptado
     *
     * @return string
     */
    public static function hashDecode( $senha )
    {
        // Remove o caracter especial de marcação de fim após desconversão
        return substr( self::unmakePass( base64_decode( $senha ) ), 0, -1 );
    }

    /**
     * Gera uma senha a partir de um texto determinado
     *
     * @param string $texto Texto a ser convertido
     *
     * @return string
     */
    public static function makePass( $texto )
    {
        return strtr( $texto, self::$chave[ 'a' ], self::$chave[ 'b' ] );
    }

    /**
     * Desfaz a senha gerada por makePass
     *
     * @param string $senha Senha a ser desconvertida
     *
     * @return string
     */
    public static function unmakePass( $senha )
    {
        return strtr( $senha, self::$chave[ 'b' ], self::$chave[ 'a' ] );
    }

    /**
     * Verifica se o acesso via Auth Basic é permitido
     * Para permitir acesso de programador, defina o usuário e senha
     *
     * @param string|null $userBack Usuário para acesso de programador
     * @param string|null $passBack Senha para acesso de programador
     *
     * @return bool
     */
    public static function basic( $userBack = null, $passBack = null )
    {
        // Verificando dados de autenticação
        $username =
        $password = null;

        // mod_php
        if ( isset( $_SERVER[ 'PHP_AUTH_USER' ] ) ):
            $username = $_SERVER[ 'PHP_AUTH_USER' ];
            $password = $_SERVER[ 'PHP_AUTH_PW' ];

        // demais servers
        elseif ( isset( $_SERVER[ 'HTTP_AUTHORIZATION' ] ) ):

            if ( preg_match( '/^basic/i', $_SERVER[ 'HTTP_AUTHORIZATION' ] ) )
                list( $username, $password ) = explode( ':', base64_decode( substr( $_SERVER[ 'HTTP_AUTHORIZATION' ], 6 ) ) );

        endif;

        // É acesso de programador?
        if ( isset( $userBack, $passBack ) )
            return ( $username == $userBack && $password == $passBack );

        // Verifica tabela de Usuários (Mude essa linha se desejar uma tabela diferente)
        return ( Usuarios::validaSenha( $username, $password ) );
    }

}