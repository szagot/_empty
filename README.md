# Sobre o Projeto

Este é um projeto de base. Já possui uma tela simples, pré-configurada, de login.

Também, já está desenvolvido o controle de IO de uma tabela simples de usuário para controle de acesso.

Para gerar as tabelas de base, use a URI `/createtables`, passando via Auth Basic os dados de acesso, conforme configurados em _App/Config_. Isso irá criar TODAS as tabelas do sistema que estiverem homologadas em _Control/CreateTables_, conforme suas configurações em _Model/DataBaseTables/{Tabela}_.

**Obs**: Não esqueça de mudar a raiz do projeto no local em **_autoload.php**


## Pastas do projeto

>       _testes

Área para arquivos de teste. Contém um arquivo **api_test.php** para testar as URIs do sistema.

Os arquivos desta pasta podem ser chamados diretamente, a menos que se comente a linha 4 de _.htaccess_

>       public

Área pública. Contem os arquivios da View do sistema, que são chamados pelos controles em _Control/{Controlador}_

>       src/App

Contém as classes gerenciadoras do Sistema.

São elas:

- **Auth**: Dados de autenticação. Ferramentas de Hash e Auth Basic
- **Config**: Configurações do sistema. Contém os dados do Banco de Dados e Senha mestre para testes de API
- **Ferramentas**: Classe estilo HELPER. Tente isso: `Ferramentas::urlTransform('Eu deveria estar sem espaços e sem acentos');`
- **Modulos**: Classe para registro dos módulos (URI) aceitos, bem como de quais precisam ser protegidos por senha. (Vide _Control/Login_)
- **Msg**: Saída em tela, quer para api (JSON padronizado), quer para erros e avisos do sistema em HTML

>       src/Config

Classes de Configuração. Vide _Config/README.md_

- GitHub Repository: https://github.com/szagot/config

>       src/Conn

Classes de Conexão com o BD. Vide _Conn/README.md_

- GitHub Repository: https://github.com/szagot/conn

>       src/Control

Controle do sistema. Cada classe aqui é chamada por _App/Modulos_, e, por sua vez, gera as telas do sistema.

Já vem pré-configurada com os controles:

- **CreateTables**:  Cria as tabelas do sistema. Esta é acionada pelo serviço `/createtables` e cria todas as tabelas homologadas em seu inicio. Não há necessidade de alterações, salvo para se homologar as tabelas a serem criadas, ou para mudar o tipo do BD a ser criado (por padrão vem configurado como MyISSAM).

- **Login**: Controle da tela de login. Serviço: `/login`

- **Logout** Controle do logout do sistema. Serviço: `/logout` ou `/sair`

>       src/Model

Modelo de negócio.

Contém também dois conjuntos separados especificos para o controle de IO do BD. Cada classe desse conjunto é referente à tabela no BD de mesmo nome.

- **DataBaseModel**: Modelo de Negócio das Tabelas do BD. o CRUD é feito através das classes aqui colocadas. Os dados são tratados com a tabela correspondente em _DataBaseTables_.

- **DataBaseTables**: Formato das tabelas. Indica qual o tipo e tamanho de cada campo e garante um controle melhor sobre o CRUD executado com a tabela correspondente em _DataBaseModel_.

>       src/API

API simples (Auth Basic). É acessada através da uri de base `/api/{tabela}`

As tabelas são gerenciadas nela mesma, sendo que a operação (CRUD) é definido pelo método (GET, POST, PUT e DELETE)