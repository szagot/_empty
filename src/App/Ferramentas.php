<?php
/**
 * [String HELPER]
 *
 * @author    Daniel Bispo <szagot@gmail.com>
* @copyright Copyright (c) 2015
 */

namespace App;


class Ferramentas
{
    /**
     * Cria uma url amigável a partir de uma string
     * (Remove acentos e espaços, e caracteres especiais são transformados em traços)
     *
     * @param string $name Nome a ser convertido
     *
     * @return string
     */
    public static function urlTransform( $name )
    {
        $formato = [
            'a' => 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª',
            'b' => 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 '
        ];

        // Remove acentos e caracteres especiais
        $dados = strtr( utf8_decode( $name ), utf8_decode( $formato[ 'a' ] ), $formato[ 'b' ] );
        // Remove tags de programação e espaços desnecessários
        $dados = strip_tags( trim( $dados ) );
        // Substitui tudo o q não for letras, numeros ou caracteres de url por um -
        $dados = preg_replace( '/[^a-z0-9\._-]/i', '-', $dados );
        // Evita que hajam 2 traços juntos
        $dados = preg_replace( '/-+/', '-', $dados );

        return strtolower( utf8_encode( $dados ) );
    }
}