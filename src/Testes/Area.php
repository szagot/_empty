<?php
/**
 * Area de Testes
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2015
 */

namespace Testes;

use
    Config\Uri,
    App\Msg,
    Conn\Query,
    App\Config,
    App\Auth,
    Model\DataBaseModel\Usuarios,
    Model\DataBaseTables\Usuarios as TUsuarios;


class Area
{
    /**
     * Testa os módulos do sistema
     *
     * @param Uri $uri
     */
    public static function iniciar( Uri $uri )
    {
        // Verifica se está autorizado a executar essa ação
        if ( ! Auth::basic( Config::getAPIData()->user, Config::getAPIData()->pass ) )
            Msg::api( 'Acesso Negado', Msg::HEADER_DADOS_INVALIDOS );

        switch ( strtolower( $uri->getUri()->opcao ) ) {
            // Testando tabela de Usuarios
            case 'usuarios':
                self::testUsuarios( $uri );
                break;

            // Nenhum teste válido selecionado
            default:
                Msg::api( ':P', Msg::HEADER_DADOS_INVALIDOS );
        }
    }

    /**
     * Testa tabela de usuários
     *      Modelo de GET:
     *      http://localhost/_empty/_testes/api_test.php?req=R0VUpy9fZW1wdHkvdGVzdGUvdXN1YXJpb3Onc3phZ290QGdtYWlsLmNvbadENXAxZDNypw==
     *
     *      Modelo de GET especificando user:
     *      http://localhost/_empty/_testes/api_test.php?req=R0VUpy9fZW1wdHkvdGVzdGUvdXN1YXJpb3Mvc3phZ290p3N6YWdvdEBnbWFpbC5jb22nRDVwMWQzcqc=
     *
     *      Modelo de POST:
     *      http://localhost/_empty/_testes/api_test.php?req=UE9TVKcvX2VtcHR5L3Rlc3RlL3VzdWFyaW9zp3N6YWdvdEBnbWFpbC5jb22nRDVwMWQzcqd7InVzdWFyaW9zIjpbeyJuaWNrIjoidGVzdGUiLCJub21lIjoiS2FtaWxlIFDtbWVudGEiLCJwYXNzIjoidGVzdGUiLCJhdGl2byI6MX0seyJuaWNrIjoidGVzdGUyIiwibm9tZSI6Ik91dHJvIFRlc3RlIiwicGFzcyI6InRlc3RlIn1dfQ==
     *
     *      Modelo de PUT:
     *      http://localhost/_empty/_testes/api_test.php?req=UFVUpy9fZW1wdHkvdGVzdGUvdXN1YXJpb3Onc3phZ290QGdtYWlsLmNvbadENXAxZDNyp3sidXN1YXJpb3MiOnsia2FtaWxlIjp7Im5pY2siOiJrYW1pbGUiLCJub21lIjoiS2FtaWxlIFBpbWVudGEiLCJwYXNzIjoidGVzdGFuZG8iLCJhdGl2byI6MX0sInRlc3RlIjp7Im5pY2siOiJ0ZXN0ZSIsIm5vbWUiOiJPdXRybyBUZXN0ZSJ9fX0=
     *
     *      Modelo de DELETE:
     *      http://localhost/_empty/_testes/api_test.php?req=REVMRVRFpy9fZW1wdHkvdGVzdGUvdXN1YXJpb3Onc3phZ290QGdtYWlsLmNvbadENXAxZDNyp1sidGVzdGUiLCJ0ZXN0ZTIiXQ==
     *
     *      Modelo de DELETE especificando 1 nick apenas:
     *      http://localhost/_empty/_testes/api_test.php?req=REVMRVRFpy9fZW1wdHkvdGVzdGUvdXN1YXJpb3MvdGVzdGWnc3phZ290QGdtYWlsLmNvbadENXAxZDNypw==
     *
     * @param Uri $uri
     */
    private static function testUsuarios( Uri $uri )
    {
        switch ( $uri->getMethod() ) {
            // Teste de inserção
            case 'POST':
                // Pega o body
                $usuarios = $uri->getBody();

                // Dados informados?
                if ( ! isset( $usuarios->usuarios ) || count( $usuarios->usuarios ) == 0 )
                    Msg::api( 'Informe ao menos 1 usuário para ser inserido', Msg::HEADER_DADOS_INVALIDOS );

                // Monta array com os dados no formato TUsuarios
                $users = [ ];
                foreach ( $usuarios->usuarios as $user )
                    if ( isset( $user->nick ) )
                        $users[] = new TUsuarios( $user->nick, $user->nome, $user->pass, $user->ativo );

                // Insere os usuários e dá o retorno
                $result = Usuarios::insert( $users );
                Msg::api( Usuarios::getErros( false ), $result ? Msg::HEADER_POST_OK : Msg::HEADER_DADOS_INVALIDOS );
                break;

            // Teste de update
            case 'PUT':
            case 'PATH':
                // Pega o body
                $usuarios = $uri->getBody();

                // Dados informados?
                if ( ! isset( $usuarios->usuarios ) || count( $usuarios->usuarios ) == 0 )
                    Msg::api( 'Informe ao menos 1 usuário para ser alterado', Msg::HEADER_DADOS_INVALIDOS );

                // Monta array com os dados no formato TUsuarios
                $users = [ ];
                foreach ( $usuarios->usuarios as $search => $user )
                    if ( isset( $user->nick ) && is_string( $search ) )
                        $users[ $search ] = new TUsuarios( $user->nick, $user->nome, $user->pass, $user->ativo );

                // Tenta atualizar e da o retorno
                $result = Usuarios::update( $users );
                Msg::api( Usuarios::getErros( false ), $result ? Msg::HEADER_POST_OK : Msg::HEADER_DADOS_INVALIDOS );
                break;

            // Teste de deleção
            case 'DELETE':
                // Pega o body
                $usuarios = $uri->getBody();

                // Verifica se foi especificado um nick na URI
                $nickEspecifico = isset( $uri->getCaminho()->detalhe ) && ! empty( $uri->getCaminho()->detalhe );

                // Tem nick ou body?
                if ( ! $nickEspecifico && count( $usuarios ) == 0 )
                    Msg::api( 'Informe o id a ser deletado na URI ou pelo menos 1 ID no body', Msg::HEADER_DADOS_INVALIDOS );

                // Deleta, priorizando URI. Se URI ão contiver um nick especificado, usa o body, com vários nicks
                $result = Usuarios::delete( $nickEspecifico ? $uri->getCaminho()->detalhe : $usuarios );

                // Retorno
                Msg::api( Usuarios::getErros( false ), $result ? Msg::HEADER_POST_OK : Msg::HEADER_DADOS_INVALIDOS );

                break;

            // Teste de listagem
            default:
                // Mostra os dados do usuário da URI ou, caso não especificado, pega todos
                Msg::api( Usuarios::get(
                    ( isset( $uri->getCaminho()->detalhe ) && ! empty( $uri->getCaminho()->detalhe ) )
                        ? $uri->getCaminho()->detalhe : null
                ) );
        }

    }

}