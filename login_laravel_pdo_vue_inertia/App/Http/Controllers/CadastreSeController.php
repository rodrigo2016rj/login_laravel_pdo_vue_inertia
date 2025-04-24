<?php

namespace App\Http\Controllers;

use App\Models\CadastreSeModel;
use App\Models\Entidades\Usuario;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ConfirmacaoDeConta;
use Inertia\Inertia;
use Exception;

final class CadastreSeController extends TemplateLayoutController{

  public function carregar_pagina($redirecionar = false){
    if($redirecionar){
      //Redireciona para si mesmo, motivo: limpar a requisição.
      header('Location: /cadastre-se');
      die;
    }

    $this->conferir_se_o_usuario_esta_logado();
    $valores = $this->get_valores();
    $sessao = session();

    /* Especificando a página do sistema para os links e outras tags */
    $valores['template_layout']['pagina_do_sistema'] = 'cadastre-se';

    /* Mostrando mensagem caso exista alguma */
    if($sessao->has('mensagem_template')){
      $valores['template_layout']['mensagem'] = $sessao->get('mensagem_template');
      $sessao->forget('mensagem_template');
      $sessao->save();
    }

    /* Variável que guarda a mensagem da página começa inicialmente vazia */
    $mensagem = '';

    /* Carregando lista de sexos */
    $usuario = new Usuario();
    $valores['cadastre_se']['array_sexos'] = $usuario->enum_sexo();

    /* Colocando valores iniciais nas variáveis para não ficarem undefined no Vue */
    $valores['cadastre_se']['nome_de_usuario'] = '';
    $valores['cadastre_se']['email'] = '';
    $valores['cadastre_se']['sexo'] = '';

    /* Recolocando valores preenchidos previamente pelo usuário no formulário */
    if($sessao->has('backup_do_formulario_da_pagina_cadastre_se')){
      $backup = $sessao->get('backup_do_formulario_da_pagina_cadastre_se');
      $valores['cadastre_se']['nome_de_usuario'] = $backup['nome_de_usuario'];
      $valores['cadastre_se']['email'] = $backup['email'];
      $valores['cadastre_se']['sexo'] = $backup['sexo'];
      $sessao->forget('backup_do_formulario_da_pagina_cadastre_se');
      $sessao->save();
    }

    /* Se houver mensagem na sessão, deve ser mostrada */
    if($sessao->has('mensagem_da_pagina_cadastre_se')){
      $mensagem = $sessao->get('mensagem_da_pagina_cadastre_se');
      $sessao->forget('mensagem_da_pagina_cadastre_se');
      $sessao->save();
    }

    $valores['cadastre_se']['mensagem_da_pagina'] = $mensagem;
    return Inertia::render('cadastre-se/cadastre-se', $valores);
  }

  public function entrar(){
    $this->entrar_padronizado();
    $this->carregar_pagina(true);
    die;
  }

  public function sair(){
    $this->sair_padronizado();
    $this->carregar_pagina(true);
    die;
  }

  public function cadastrar(){
    $sessao = session();
    $cadastre_se_model = new CadastreSeModel();
    $usuario = new Usuario();

    /* Obtendo valores do formulário */
    $requisicao = $this->get_requisicao();
    $nome_de_usuario = trim($requisicao->post('nome_de_usuario') ?? '');
    $email = trim($requisicao->post('email') ?? '');
    $senha = $requisicao->post('senha');
    $senha_novamente = $requisicao->post('senha_novamente');
    $sexo = trim($requisicao->post('sexo') ?? '');

    /* Removendo espaços vazios do e-mail digitado caso existam */
    $email = str_replace(' ', '', $email);

    /* Fazendo backup do formulário */
    $backup_do_formulario['nome_de_usuario'] = $nome_de_usuario;
    $backup_do_formulario['email'] = $email;
    $backup_do_formulario['sexo'] = $sexo;
    $sessao->put('backup_do_formulario_da_pagina_cadastre_se', $backup_do_formulario);
    $sessao->save();

    /* Validações */
    if($nome_de_usuario === ''){
      $mensagem = 'O campo nome de usuário precisa ser preenchido.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    $array_caracteres_do_nome_do_usuario = mb_str_split($nome_de_usuario, 1);
    $caracteres_permitidos = $usuario->caracteres_permitidos_para_nome_de_usuario();
    foreach($array_caracteres_do_nome_do_usuario as $caractere){
      if(strpos($caracteres_permitidos, $caractere) === false){
        if($caractere === ' '){
          $caractere = 'espaço';
        }
        $mensagem = 'O valor escolhido para o nome de usuário não é válido.';
        $mensagem .= " O nome de usuário não pode ter o caractere $caractere.";
        $mensagem .= ' O nome de usuário só pode ter os seguintes caracteres:';
        $caracteres_permitidos = implode(' ', (str_split($caracteres_permitidos, 1)));
        $mensagem .= " $caracteres_permitidos";
        $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
        $sessao->save();
        $this->carregar_pagina(true);
        die;
      }
    }
    $array_resultado = $cadastre_se_model->verificar_disponibilidade_de_nome_de_usuario($nome_de_usuario);
    if(isset($array_resultado['mensagem_do_model'])){
      $mensagem = $array_resultado['mensagem_do_model'];
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    $minimo = $usuario->quantidade_minima_de_caracteres('nome_de_usuario');
    $maximo = $usuario->quantidade_maxima_de_caracteres('nome_de_usuario');
    $quantidade = mb_strlen($nome_de_usuario);
    if($quantidade < $minimo){
      $mensagem = "O campo nome de usuário precisa ter no mínimo $minimo caracteres.";
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    if($quantidade > $maximo){
      $mensagem = "O campo nome de usuário não pode ultrapassar $maximo caracteres.";
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }

    if($email === ''){
      $mensagem = 'O campo e-mail precisa ser preenchido.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    $quantidade_de_arrobas = substr_count($email, '@');
    if($quantidade_de_arrobas > 1){
      $mensagem = 'O campo e-mail precisa ter somente um caractere @.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    if($quantidade_de_arrobas < 1){
      $mensagem = 'O campo e-mail precisa ter pelo menos um caractere @.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    if(str_starts_with($email, '@')){
      $mensagem = 'A parte antes do @ no campo e-mail precisa ser preenchida.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    $parte_do_arroba_e_apos = strstr($email, '@');
    if($parte_do_arroba_e_apos === '@'){
      $mensagem = 'A parte após o @ no campo e-mail, domínio do e-mail, precisa ser preenchida.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    if(strpos($parte_do_arroba_e_apos, '.') === false){
      $mensagem = 'A parte após o @ no campo e-mail, domínio do e-mail, não foi preenchida';
      $mensagem .= ' corretamente. Está faltando o caractere . (ponto).';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    if(strpos($parte_do_arroba_e_apos, '.') === 1){
      $mensagem = 'A parte após o @ no campo e-mail, domínio do e-mail, não foi preenchida';
      $mensagem .= ' corretamente. Está faltando a parte antes do ponto.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    $parte_do_ponto_e_apos = strstr($parte_do_arroba_e_apos, '.');
    if($parte_do_ponto_e_apos === '.'){
      $mensagem = 'A parte após o @ no campo e-mail, domínio do e-mail, não foi preenchida';
      $mensagem .= ' corretamente. Está faltando a parte após o ponto.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    $array_resultado = $cadastre_se_model->verificar_disponibilidade_de_email($email);
    if(isset($array_resultado['mensagem_do_model'])){
      $mensagem = $array_resultado['mensagem_do_model'];
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    $minimo = $usuario->quantidade_minima_de_caracteres('email');
    $maximo = $usuario->quantidade_maxima_de_caracteres('email');
    $quantidade = mb_strlen($email);
    if($quantidade < $minimo){
      $mensagem = "O campo e-mail precisa ter no mínimo $minimo caracteres.";
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    if($quantidade > $maximo){
      $mensagem = "O campo e-mail não pode ultrapassar $maximo caracteres.";
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }

    if($senha === null or $senha === ''){
      $mensagem = 'O campo senha precisa ser preenchido.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    $minimo = $usuario->quantidade_minima_de_caracteres('senha');
    $maximo = $usuario->quantidade_maxima_de_caracteres('senha');
    $quantidade = mb_strlen($senha);
    if($quantidade < $minimo){
      $mensagem = "O campo senha precisa ter no mínimo $minimo caracteres.";
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    if($quantidade > $maximo){
      $mensagem = "O campo senha não pode ultrapassar $maximo caracteres.";
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }

    if($senha_novamente === null or $senha_novamente === ''){
      $mensagem = 'O segundo campo de senha precisa ser preenchido.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    if($senha_novamente !== $senha){
      $mensagem = 'O valor do segundo campo de senha precisa ser o mesmo valor do primeiro campo';
      $mensagem .= ' de senha. Essa validação existe para te ajudar a não preencher errado.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }

    if($sexo === ''){
      $mensagem = 'O sexo precisa ser informado.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    if(!array_key_exists($sexo, $usuario->enum_sexo())){
      $mensagem = 'O valor escolhido para o sexo não é válido.';
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }

    /* Criptografias */
    $senha = $this->criptografar_senha_do_usuario($senha);

    /* Chave para operações via link (operação confirmar conta) */
    $chave = $this->criar_chave_para_operacoes_via_link();

    /* Momento atual sem fuso horário, pois no banco de dados armazeno sem fuso horário (timezone) */
    $sem_fuso_horario = new DateTimeZone('GMT');
    $objeto_date_time = new DateTime('now', $sem_fuso_horario);
    $momento_atual = $objeto_date_time->format('Y-m-d H:i:s');

    /* Formando o objeto usuário */
    $usuario->set_nome_de_usuario($nome_de_usuario);
    $usuario->set_email($email);
    $usuario->set_senha($senha);
    $usuario->set_chave_para_operacoes_via_link($chave);
    $usuario->set_momento_do_cadastro($momento_atual);
    $usuario->set_sexo($sexo);

    /* Cadastrar usuário no banco de dados */
    $array_resultado = $cadastre_se_model->cadastrar_usuario($usuario);
    if(isset($array_resultado['mensagem_do_model'])){
      $mensagem = $array_resultado['mensagem_do_model'];
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }else{
      $sessao->forget('backup_do_formulario_da_pagina_cadastre_se');

      /* Obtendo valores da configuração deste sistema */
      $mailer_smtp_username = config('mail.mailers.smtp.username');
      $mailer_smtp_password = config('mail.mailers.smtp.password');

      /* Validando valores da configuração deste sistema */
      if($mailer_smtp_username === null){
        $mensagem = 'Seu cadastro foi realizado com sucesso, porém houve um erro e não foi';
        $mensagem .= " possível enviar o link de confirmação para o seu e-mail ($email).";
        $mensagem .= ' Está faltando a configuração MAIL_USERNAME no arquivo .env deste sistema.';
        $mensagem .= ' Informe o ocorrido aos responsáveis deste sistema.';
        $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
        $sessao->save();
        $this->carregar_pagina(true);
        die;
      }
      if($mailer_smtp_username === ''){
        $mensagem = 'Seu cadastro foi realizado com sucesso, porém houve um erro e não foi';
        $mensagem .= " possível enviar o link de confirmação para o seu e-mail ($email).";
        $mensagem .= ' Está faltando um valor para a configuração MAIL_USERNAME no arquivo .env';
        $mensagem .= ' deste sistema. Informe o ocorrido aos responsáveis deste sistema.';
        $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
        $sessao->save();
        $this->carregar_pagina(true);
        die;
      }
      if($mailer_smtp_password === null){
        $mensagem = 'Seu cadastro foi realizado com sucesso, porém houve um erro e não foi';
        $mensagem .= " possível enviar o link de confirmação para o seu e-mail ($email).";
        $mensagem .= ' Está faltando a configuração MAIL_PASSWORD no arquivo .env deste sistema.';
        $mensagem .= ' Informe o ocorrido aos responsáveis deste sistema.';
        $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
        $sessao->save();
        $this->carregar_pagina(true);
        die;
      }
      if($mailer_smtp_password === ''){
        $mensagem = 'Seu cadastro foi realizado com sucesso, porém houve um erro e não foi';
        $mensagem .= " possível enviar o link de confirmação para o seu e-mail ($email).";
        $mensagem .= ' Está faltando um valor para a configuração MAIL_PASSWORD no arquivo .env';
        $mensagem .= ' deste sistema. Informe o ocorrido aos responsáveis deste sistema.';
        $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
        $sessao->save();
        $this->carregar_pagina(true);
        die;
      }

      $valores_do_email['email'] = $email;
      $valores_do_email['ip'] = $_SERVER['REMOTE_ADDR'];
      $valores_do_email['pk_usuario'] = $array_resultado['pk_usuario'];
      $valores_do_email['chave'] = $chave;
      $mensagem_de_email = new ConfirmacaoDeConta($valores_do_email);

      try{
        Mail::to($email)->send($mensagem_de_email);
      }catch(Exception $excecao){
        // Uma causa possível é configuração errada no arquivo .env
        Log::error($excecao->getMessage());
        $mensagem = 'Seu cadastro foi realizado com sucesso, porém houve um erro e não foi';
        $mensagem .= " possível enviar o link de confirmação para o seu e-mail ($email).";
        $mensagem .= ' Informe o ocorrido aos responsáveis deste sistema e peça para';
        $mensagem .= ' eles consultarem o arquivo de log do sistema.';
        $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
        $sessao->save();
        $this->carregar_pagina(true);
        die;
      }

      $mensagem = 'Seu cadastro foi realizado com sucesso, confirme sua conta pelo link enviado';
      $mensagem .= " para o seu e-mail ($email).";
      $sessao->put('mensagem_da_pagina_cadastre_se', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
  }

}
