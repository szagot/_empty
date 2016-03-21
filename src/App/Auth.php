<?php
/**
 * Controle de autorização do sistema
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 */

namespace App;

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


}