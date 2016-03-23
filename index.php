<?php
/**
 * @author    Daniel Bispo <szagot@gmail.com>
 */

require_once '_autoload.php';

use
    Config\Uri,
    Config\Request,
    Config\Sessao,
    App\Modulos;

// Pega a URI
$uri = new Uri( RAIZ, RAIZ_LOCAL );

// URL sem WWW
if ( $uri->removeWWW() )
    exit;

// Inicializa sessao
Sessao::iniciar( 'empty', TEMPO_SESSAO );

// Inicializa pasta pública
Request::iniciar( $uri->getRaiz() );

// Inicializa módulos
Modulos::iniciar( $uri );
