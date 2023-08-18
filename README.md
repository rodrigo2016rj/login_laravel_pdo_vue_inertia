## Sobre
<p>Este sistema é apenas uma demonstração de: formulário de cadastro, formulário de login, tabela com navegação contínua, página de configurações, abas, validações e níveis de permissão. Tudo feito com Laravel, PDO, Vue, Inertia e MySQL.</p>

<p>Este sistema serve para divulgar o meu trabalho e também serve como material de estudo para outros programadores.</p>

<p>Este sistema foi feito por mim, mas qualquer pessoa é livre para reutilizar e/ou modificar.</p>

<p>Observação: Os dados contidos no banco de dados deste sistema são fictícios.</p>

<br/>

## Instruções
<p>Para ver o resultado em um ambiente de desenvolvimento siga as instruções:</p>

<p>Inicie o MySQL Server.</p>

<p>Utilize o banco de dados contido no arquivo banco_de_dados_usuarios.sql.</p>

<p>Configure o MySQL Server para que o banco de dados deste sistema seja acessado por username root sem senha.</p>

<p>Se você preferir, você pode configurar neste sistema um outro username e uma outra senha.</p>

<p>Configure seu PHP pelo arquivo php.ini e certifique-se de deixar ativado intl e mbstring.</p>

<p>Coloque o diretório login_laravel_pdo_vue_inertia dentro do endereço DocumentRoot do seu Servidor Apache. Exemplo: coloque dentro de htdocs do XAMPP. Geralmente o DocumentRoot é o diretório htdocs do XAMPP e você pode consultar ou mudar o endereço de DocumentRoot pelo arquivo de configuração do Servidor Apache (exemplo: arquivo httpd.conf).</p>

<p>Configure um VirtualHost no Servidor Apache para este sistema.<br/>
Dica: configure com a porta 80 e ServerName localhost, se tiver dúvida procure algum tutorial.<br/>
Se utiliza XAMPP, o arquivo de configuração do Servidor Apache para VirtualHost será apache\conf\extra\httpd-vhosts.conf<br/>
Exemplo de VirtualHost configurado:<br/>
<code>&lt;VirtualHost *:80&gt;</code><br/>
<code>&nbsp;&nbsp;DocumentRoot "C:\Users\Rodrigo\Servidores\XAMPP001\htdocs\login_laravel_pdo_vue_inertia\public"</code><br/>
<code>&nbsp;&nbsp;ServerName localhost</code><br/>
<code>&lt;/VirtualHost&gt;</code></p>

<p>Sugestão: Configure também SSL (Secure Sockets Layer) ou TLS (Transport Layer Security) no seu Servidor Apache para este sistema, pois este sistema possui campos de senha e é importante verificar em desenvolvimento como o sistema ficará ao ser acessado com HTTPS nos navegadores.</p>

<p>Inicie ou reinicie o Servidor Apache.</p>

<p>Dentro do diretório login_laravel_pdo_vue_inertia execute os comandos:<br/>
composer install<br/>
npm install</p>

<p>Renomeie o arquivo .env.example para .env<br/>
Gere a chave APP_KEY do arquivo .env pelo comando: php artisan key:generate</p>

<p>Ainda dentro do diretório login_laravel_pdo_vue_inertia execute os comandos:<br/>
npm run build<br/>
npm run dev</p>

<p>Acesse o endereço http://localhost:80 em um navegador. Se tiver configurado SSL ou TLS, acesse o endereço https://localhost:80 em um navegador.</p>

<br/>

## Outras informações
<p>Configurei no package.json deste projeto, com o Vite, os comandos para os dois tipos de build:<br/>
npm run build para criar a build client-side (recomendada por mim).<br/>
npm run build_ssr para criar a build client-side e a build server-side (não recomendada por mim).</p>

<p>Observação: Não recomendo criar nem utilizar a build server-side, pois não terá a variável global window.<br/>
Se mesmo assim você deseja utilizar o "Node-based Inertia SSR server", os comandos são:<br/>
npm run build_ssr<br/>
php artisan inertia:start-ssr</p>

<p>Aviso importante: Caso tenha utilizado o comando npm run build_ssr e queira voltar a utilizar somente a build client-side, delete o diretório: login_laravel_pdo_vue_inertia\bootstrap\ssr.</p>

<br/>
