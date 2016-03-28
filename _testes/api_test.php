<?php
/**
 * Para funcionar bem, a diretiva abaixo deve estar configurada assim no PHP
 *      suhosin.get.max_value_length=2000
 *
 * Isso permitirá ter um limite de GET de 2000 caracteres, e não apenas de 514 como é o padrão no CPanel
 *
 * Caminho do php.ini no SSH:
 *      /usr/local/lib/php.ini
 *
 * Para editar:
 *      vi /usr/local/lib/php.ini
 *      Pressione [insert] para entrar no modo de edição.
 *      Adicione ou altere a linha desejada.
 *      Pressione [esc] para sair do modo de edição
 *      Digite :qw e pressione [enter] para salvar e sair
 *
 * Para Localizar uma linha
 *      Com o arquivo aberto, pressione /palavra-a-ser-pesquisada
 *      Exemplo: /suhosin
 */

// Pega a requisição se houver
$req = '';
$reqValues = [
    0 => null,
    1 => null,
    2 => null,
    3 => null,
    4 => null
];
$get = filter_input( INPUT_GET, 'req' );
if ( $get != '' ) {
    $req = utf8_encode( base64_decode( $get ) );
    $reqValues = explode( '§', $req );
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>API Test</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
    <script type="text/javascript">
        var req = {};

        req.executando = false;
        req.lastJson = '';

        // Adiciona caracters em textarea
        req.insertAtCursor = function ( myField, myValue ) {
            //IE support
            if ( document.selection ) {
                var temp;
                myField.focus();
                sel      = document.selection.createRange();
                temp     = sel.text.length;
                sel.text = myValue;
                if ( myValue.length == 0 ) {
                    sel.moveStart( 'character', myValue.length );
                    sel.moveEnd( 'character', myValue.length );
                } else {
                    sel.moveStart( 'character', - myValue.length + temp );
                }

                sel.select();
            }

            //MOZILLA/NETSCAPE support
            else if ( myField.selectionStart || myField.selectionStart == '0' ) {
                var startPos           = myField.selectionStart;
                var endPos             = myField.selectionEnd;
                myField.value          = myField.value.substring( 0, startPos ) + myValue + myField.value.substring( endPos, myField.value.length );
                myField.selectionStart = startPos + myValue.length;
                myField.selectionEnd   = startPos + myValue.length;
            } else {
                myField.value += myValue;
            }

        };

        // HTML Entities
        req.stripHTML = function ( dirtyString ) {
            var container = document.createElement( 'div' );
            var text      = document.createTextNode( dirtyString );
            container.appendChild( text );
            return container.innerHTML;
        };

        // Embeleza o JSon
        req.jsonConvert = function ( json ) {
            var makeReturn = '';
            if ( json != null && (typeof json).match( /(array|object)/i ) ) {
                var eArray = false;
                for ( var pos in json ) {
                    eArray = pos.match( /^[0-9]+$/ );
                    break;
                }

                makeReturn += ( eArray ? '<small>' + json.length + ' item(s)</small>' : '') + '<ul class="grupo ' + ( eArray ? 'array' : 'object') + '">';

                for ( var pos in json ) {
                    makeReturn += '<li class="item">'
                        + ( eArray ? '' : ( '<b>"' + pos + '"</b>: ' ) )
                        + req.jsonConvert( json[ pos ] )
                        + '</li>';
                }
                makeReturn += '</ul>';

            } else {
                var tipo      = 'string',
                    jsonTeste = '@' + json;

                if ( json == null )
                    tipo = 'null';
                else if ( jsonTeste.match( /^@true$/i ) )
                    tipo = 'boolean true';
                else if ( jsonTeste.match( /^@false$/i ) )
                    tipo = 'boolean false';
                else if ( jsonTeste.match( /^@[0-9\.]+$/i ) )
                    tipo = 'number';

                makeReturn = '<span class="' + tipo + '">'
                    + (tipo == 'string' ? '"' : '')
                    + (tipo == 'null' ? 'null' : req.stripHTML( json ) )
                    + (tipo == 'string' ? '"' : '')
                    + '</span>';
            }

            return makeReturn;
        };

        req.execute = function ( $return, method, url, user, pass, body ) {
            if ( ! req.executando ) {
                // Método é válido?
                if ( ! method in [ 'GET', 'POST', 'PUT', 'DELETE', 'PATCH' ] )
                    method = 'get';

                // Url é válida?
                if ( ! url ) {
                    $return.html( 'Informe uma URL.' );
                    return false;
                }

                // Compacta body
                if ( body )
                    body = body
                    // Remove quebras de linha
                        .replace( /[\n\r]+/g, '' )
                        // Remove tabulações e espaços extras
                        .replace( /[\s\t]+/g, ' ' )
                        // Remove espaços antes e depois de fechamentos
                        .replace( /\s*([\{\[\}\]\:\,])\s*/g, '$1' );

                req.executando = true;

                $.ajax( {
                    type       : method,
                    url        : url,
                    dataType   : 'JSON',
                    data       : body,
                    processData: false,
                    contentType: 'application/json',
                    xhrFields  : {
                        withCredentials: true
                    },
                    beforeSend : function ( xhr ) {
                        xhr.setRequestHeader( "Authorization", "Basic " + btoa( user + ":" + pass ) );
                    },
                    success    : function ( data ) {
                        var requisicao = (window.location.href).replace( /\?.*$/, '' ) + '?req='
                            + btoa( method + '§' + url + '§' + user + '§' + pass + '§' + body );

                        if ( requisicao.length > 2000 )
                            requisicao = (window.location.href).replace( /\?.*$/, '' ) + '?req='
                                + btoa( method + '§' + url + '§' + user + '§' + pass + '§'
                                    + '/* Body muito longo. Limite: 2000 caracteres */' );

                        req.lastJson = req.stripHTML( JSON.stringify( data ) );

                        $return.html(
                            '<h3>URL:</h3><a class="btn bloco" href="' + requisicao + '">' + requisicao + '</a>'
                            + '<h3>Return:</h3><textarea class="bloco" readonly>' + req.lastJson + '</textarea>'
                            + '<h3>Beautify:</h3>' + req.jsonConvert( data )
                        );

                    },
                    error      : function ( request, status, err ) {
                        $return.html( req.jsonConvert( {
                            erro  : request.responseJSON ? request.responseJSON : err,
                            status: request.status ? request.status : status
                        } ) );
                    },
                    complete   : function () {
                        req.executando = false;
                    }
                } );
            }
        };

        $( function () {

            $( '#api' ).submit( function ( e ) {
                e.preventDefault();

                if ( ! req.executando ) {

                    $( '#execute' ).hide( 'fast' );

                    $( '#return' ).html( 'Aguarde...' );
                    req.execute(
                        $( '#return' ),
                        $( '#method' ).val(),
                        $( '#url' ).val(),
                        $( '#user' ).val(),
                        $( '#pass' ).val(),
                        $( '#body' ).val()
                    );
                }

            } );

            setInterval( function () {
                if ( req.executando )
                    $( '#execute' ).hide( 'fast' );
                else
                    $( '#execute' ).show( 'fast' );
            }, 500 );

            $( '#return' ).on( 'click', '.item', function ( e ) {
                e.stopPropagation();
                e.preventDefault();
            } );
            $( '#return' ).on( 'click', '.grupo', function ( e ) {
                e.stopPropagation();
                var $grupo = $( this );
                $( '>.item', $grupo ).toggle( 'fast', function () {
                    if ( $( '>.item', $grupo ).eq( 0 ).length && $( '>.item', $grupo ).eq( 0 ).is( ':hidden' ) )
                        $grupo.addClass( 'closed' );
                    else
                        $grupo.removeClass( 'closed' );
                } );
            } );

        } );
    </script>

    <style type="text/css">

        * {
            margin: 0;
            padding: 0;

            word-wrap: break-word;

            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        body {
            font: 14px Arial, sans-serif;
            padding: 20px;
            color: #555;
        }

        h1, h2, h3 {
            display: block;
            margin: 20px 0;
        }

        h3 {
            margin-bottom: 5px;
        }

        small {
            color: #999;
        }

        small::before {
            content: '// ';
        }

        .bloco {
            max-width: 700px;
        }

        .bloco::after {
            content: '';
            display: block;
            clear: both;
        }

        select, input, textarea {
            display: block;
            width: 100%;
            padding: 5px 10px;
        }

        textarea {
            height: 180px;
            resize: vertical;
        }

        textarea[readonly] {
            height: 100px;
            background: #f4f4f4;
            resize: none;
        }

        .campo {
            float: left;
            width: 100%;
            padding-bottom: 20px;
        }

        .campo.little {
            width: 30%;
            padding-right: 20px;
        }

        .campo.big {
            width: 70%;
        }

        .campo.middle {
            width: 50%;
            padding-right: 20px;
        }

        .campo.middle:last-child {
            padding-right: 0;
        }

        .btn {
            display: block;
            padding: 5px;
            margin: 0 0 20px;
            cursor: pointer;
            background: #428bca;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.3);
            border: 1px solid #357ebd;
            text-align: left;
            border-radius: 3px;
            color: #fff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, .2);
            font: 14px Arial, sans-serif;
            text-decoration: none;
        }

        .btn:hover {
            background: #3071a9;
        }

        .btn:active {
            background: #3071a9;
            border-color: #285e8e;
            box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.2);
            text-shadow: 0 1px 2px rgba(0, 0, 0, .8);
        }

        #return, textarea {
            font: 14px 'Courier New', Courier, monospace;
            line-height: 24px;
        }

        .grupo, .item {
            display: block;
            list-style: none;
        }

        .grupo {
            margin: 0 40px;
        }

        .grupo::before {
            cursor: pointer;
        }

        .grupo.array::before {
            content: '▼ [';
            display: block;
            margin-left: -20px;
        }

        .grupo.array::after {
            content: '  ]';
            display: block;
            margin-left: -20px;
        }

        .grupo.object::before {
            content: '▼ {';
            display: block;
            margin-left: -20px;
        }

        .grupo.object::after {
            content: '  }';
            display: block;
            margin-left: -20px;
        }

        .grupo.closed::after, .grupo.closed::before {
            display: inline;
        }

        .grupo.closed::after {
            margin-left: 0;
        }

        .grupo.array.closed::before {
            content: '► [  ...';
        }

        .grupo.object.closed::before {
            content: '► {  ...';
        }

        .string {
            color: #0e0b74;
        }

        .number {
            color: #853191;
            font-weight: bold;
        }

        .boolean {
            color: #9b0909;
            font-weight: bold;
        }

        .boolean.true {
            color: #2c9b17;
            font-weight: bold;
        }

        .null {
            color: #999;
            font-weight: bold;
        }

    </style>
</head>
<body>

<h1>API Test</h1>

<form id="api">
    <div class="bloco url">
        <label class="campo little" title="method">
            <select id="method">
                <option <?= ( $req == '' || $reqValues[ 0 ] == 'GET' ) ? 'selected' : '' ?>>GET</option>
                <option <?= ( $reqValues[ 0 ] == 'POST' ) ? 'selected' : '' ?>>POST</option>
                <option <?= ( $reqValues[ 0 ] == 'PUT' ) ? 'selected' : '' ?>>PUT</option>
                <option <?= ( $reqValues[ 0 ] == 'DELETE' ) ? 'selected' : '' ?>>DELETE</option>
                <option <?= ( $reqValues[ 0 ] == 'PATCH' ) ? 'selected' : '' ?>>PATCH</option>
            </select>
        </label>
        <label class="campo big" title="URI"><input type="text" id="url"
                                                    value="<?= isset( $reqValues[ 1 ] ) ? $reqValues[ 1 ] : '' ?>"
                                                    placeholder="Recurso"></label>
    </div>
    <div class="bloco auth">
        <label class="campo middle" title="Basic Auth User ID"><input type="password" id="user"
                                                                      value="<?= isset( $reqValues[ 2 ] ) ? $reqValues[ 2 ] : '' ?>"
                                                                      placeholder="Basic Auth User ID"></label>
        <label class="campo middle" title="Basic Auth Pass"><input type="password" id="pass"
                                                                   value="<?= isset( $reqValues[ 3 ] ) ? $reqValues[ 3 ] : '' ?>"
                                                                   placeholder="Basic Auth Pass"></label>
    </div>
    <div class="bloco body">
        <label class="campo">
            <textarea id="body" title="Body da Requisição (JSON)" placeholder="Body da Requisição (JSON)" onkeydown="
                // Acrescentando TAB
                var e = event || evt;
                var charCode = e.which || e.keyCode;

                if (charCode == 9 ) {
                    req.insertAtCursor(this, '    ');
                    return false;
                }
            "><?= isset( $reqValues[ 4 ] ) ? $reqValues[ 4 ] : '' ?></textarea></label>
        <button type="submit" class="btn" id="execute">Executar</button>
        <script>
            $( function () {
                <?= ( $req != '' ) ? '$("#execute").click();' : '' ?>
            } );
        </script>
    </div>
</form>
<h2>Retorno da Requisição:</h2>
<div id="return"></div>
</body>
</html>