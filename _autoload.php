<?php
/**
 * Gerenciador de Abertura de Classes
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2015
 */

// Habilitar debug?
$debug = false;

ini_set( 'display_errors', $debug ? 'On' : 'Off' );
error_reporting( $debug ? E_ALL : 0 );

// Seta a Zona local
date_default_timezone_set( 'America/Sao_Paulo' );

// Constante RAIZ contém a raiz do sistema geral
define( 'RAIZ', '' );
// Constante RAIZ_LOCAL contém a raiz do local.
// As requisições de URI serão sempre "RAIZ_LOCAL . RAIZ" quando executadas em "localhost"
define( 'RAIZ_LOCAL', '_empty' );

// Tempo de duração da seção em minutos
define( 'TEMPO_SESSAO', 40 );

// Configura o caminho da Classe
spl_autoload_register( function ( $classe ) {
    $caminho = __DIR__ . DIRECTORY_SEPARATOR
        // Se o namespace for do projeto principal, vai para src, senão para terceiros (vendor)
        . 'src' . DIRECTORY_SEPARATOR
        // Corrige caminho
        . str_replace( '\\', DIRECTORY_SEPARATOR, $classe ) . '.php';

    // Requisita a Classe caso ela exista
    if ( file_exists( $caminho ) )
        require_once $caminho;
    else
        die( "<meta name='error' content='A classe $classe não existe.' />" );
} );