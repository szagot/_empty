<?php
/**
 * Métodos para tipos de mensagem
 *
 * @author    Daniel Bispo <szagot@gmail.com>
* @copyright Copyright (c) 2015
 */

namespace App;


class Msg
{
    const
        // HEADER OK
        HEADER_GET_OK = 200,
        HEADER_POST_OK = 201,
        HEADER_PUT_OK = 204,
        HEADER_PATCH_OK = 204,
        HEADER_DELETE_OK = 204,

        // HEADER ERROR
        HEADER_DADOS_INVALIDOS = 400,
        HEADER_NAO_AUTORIZADO = 401,
        HEADER_RECURSO_INEXISTENTE = 404,
        HEADER_ERRO_FATAL = 500;

    /**
     * Emite uma mensagem de erro
     *
     * @param string    $message Mensagem
     * @param bool|true $ocult   Deve ser oculta (com meta tag)
     * @param bool|true $fatal   É um erro fatal?
     */
    public static function error( $message = '', $ocult = true, $fatal = true )
    {
        // Caso o header ainda não tenha sido enviado, configura a saída para HTML/UTF-8
        if ( ! headers_sent() )
            header( 'Content-Type: text/html; charset=utf-8' );

        // É um erro fatal que deve estar oculto (Padrão)
        if ( $ocult && $fatal )
            die( "<meta name='error' content='{$message}' />" );

        // É um erro fatal, mas não deve estar oculto
        elseif ( ! $ocult && $fatal )
            die( "<div class='return error'>$message</div>" );

        // Não é um erro fatal (não interrompe script), mas deve estar oculto
        elseif ( $ocult && ! $fatal )
            echo "<meta name='error' content='{$message}' />";

        // Erro normal. Emite e segue script.
        else
            echo "<div class='return error'>$message</div>";
    }

    /**
     * Emite uma mensagem de retorno em JSON para uso em APIs
     *
     * @param mixed $message Mensagem a ser impressa. Pode ser um texto ou um array
     * @param int   $header  Http Code para o retorno
     */
    public static function api( $message, $header = self::HEADER_GET_OK )
    {
        $status = true;
        if ( ! headers_sent() ) {
            header( 'WWW-Authenticate: Basic realm="API"' );
            header( 'Content-Type: application/json' );

            switch ( $header ):
                // OK
                case 200:
                    header( 'HTTP/1.0 200 OK' );
                    break;
                case 201:
                    header( 'HTTP/1.0 201 Created' );
                    break;
                case 204:
                    header( 'HTTP/1.0 204 No Content' );
                    break;
                // Erros
                case 400:
                    header( 'HTTP/1.0 400 Bad Request' );
                    $status = false;
                    break;
                case 401:
                    header( 'HTTP/1.0 401 Unauthorized' );
                    $status = false;
                    break;
                case 404:
                    header( 'HTTP/1.0 404 Not Found' );
                    $status = false;
                    break;
                default:
                    header( 'HTTP/1.0 500 Internal Server Error' );
                    $status = false;
            endswitch;
        }

        $saida = [
            'status'   => $status,
            'response' => $message
        ];
        die( json_encode( $saida ) );
    }
}