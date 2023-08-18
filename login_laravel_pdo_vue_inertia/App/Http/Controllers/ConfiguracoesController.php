<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracoesModel;
use App\Models\Entidades\Usuario;
use Inertia\Inertia;

final class ConfiguracoesController extends TemplateLayoutController{

  public function carregar_pagina($redirecionar = false){
    if($redirecionar){
      //Redireciona para si mesmo, motivo: limpar a requisição.
      header('Location: /configuracoes');
      die;
    }
    $requisicao = $this->get_requisicao();
    if($requisicao->has('id')){
      //Caso a pessoa coloque algum id na URL para confundir, redireciona.
      //Esta página não usa id da URL.
      header('Location: /configuracoes');
      die;
    }

    $this->conferir_se_o_usuario_esta_logado();
    $valores = $this->get_valores();
    $sessao = session();

    /* Especificando a página do sistema para os links e outras tags */
    $valores['template_layout']['pagina_do_sistema'] = 'configuracoes';

    /* Mostrando mensagem caso exista alguma */
    if($sessao->has('mensagem_template')){
      $valores['template_layout']['mensagem'] = $sessao->get('mensagem_template');
      $sessao->forget('mensagem_template');
      $sessao->save();
    }

    /* Variável que guarda a mensagem da página começa inicialmente vazia */
    $mensagem = '';

    /* $mostrar_configuracoes a princípio é true */
    $mostrar_configuracoes = true;

    /* Colocando valores nas listas de fuso horários e de visuais */
    $fuso_horarios = $this->criar_array_de_fuso_horarios();
    $valores['configuracoes']['fuso_horarios'] = $fuso_horarios;
    $visuais = $this->criar_array_de_temas_visuais();
    $valores['configuracoes']['visuais'] = $visuais;

    /* Colocando valores iniciais nas variáveis para não ficarem undefined no Vue */
    $valores['configuracoes']['fuso_horario'] = '';
    $valores['configuracoes']['visual'] = '';
    $valores['configuracoes']['nome_de_usuario'] = '';
    $valores['configuracoes']['exibir_sexo_no_perfil'] = '';
    $valores['configuracoes']['exibir_email_no_perfil'] = '';
    $valores['configuracoes']['aba_inicial'] = 'aba_preferencias';

    $configuracoes_model = new ConfiguracoesModel();

    /* Verificando se o usuário está logado */
    if($this->get_usuario_logado() === null){
      $mensagem = 'Você precisa entrar para poder acessar esta página.';
      unset($valores['configuracoes']['fuso_horarios']);
      unset($valores['configuracoes']['visuais']);
      unset($valores['configuracoes']['fuso_horario']);
      unset($valores['configuracoes']['visual']);
      unset($valores['configuracoes']['nome_de_usuario']);
      unset($valores['configuracoes']['exibir_sexo_no_perfil']);
      unset($valores['configuracoes']['exibir_email_no_perfil']);
      unset($valores['configuracoes']['aba_inicial']);
      $mostrar_configuracoes = false;
    }else{
      $pk_usuario = $this->get_usuario_logado()->get_pk_usuario();

      /* Consultando e mostrando informações do usuário */
      $array_resultado = $configuracoes_model->selecionar_usuario($pk_usuario);
      if(isset($array_resultado['mensagem_do_model'])){
        $mensagem = $array_resultado['mensagem_do_model'];
        $mostrar_configuracoes = false;
      }else{
        $usuario = $array_resultado[0];
        $valores['configuracoes']['fuso_horario'] = $usuario->get_fuso_horario();
        $valores['configuracoes']['visual'] = $usuario->get_visual();
        $valores['configuracoes']['nome_de_usuario'] = $usuario->get_nome_de_usuario();
        $valores['configuracoes']['exibir_sexo_no_perfil'] = $usuario->get_exibir_sexo_no_perfil();
        $valores['configuracoes']['exibir_email_no_perfil'] = $usuario->get_exibir_email_no_perfil();
      }

      if(!$mostrar_configuracoes){
        $valores['configuracoes']['fuso_horarios'] = array();
        $valores['configuracoes']['visuais'] = array();
        $valores['configuracoes']['fuso_horario'] = '';
        $valores['configuracoes']['visual'] = '';
        $valores['configuracoes']['nome_de_usuario'] = '';
        $valores['configuracoes']['exibir_sexo_no_perfil'] = '';
        $valores['configuracoes']['exibir_email_no_perfil'] = '';
        $valores['configuracoes']['aba_inicial'] = '';
      }
    }

    /* Se houver mensagem na sessão, deve ser mostrada */
    if($sessao->has('mensagem_da_pagina_configuracoes')){
      $mensagem = $sessao->get('mensagem_da_pagina_configuracoes');
      $sessao->forget('mensagem_da_pagina_configuracoes');
      $sessao->save();
    }

    $valores['configuracoes']['mostrar_configuracoes'] = $mostrar_configuracoes;
    $valores['configuracoes']['mensagem_da_pagina'] = $mensagem;
    return Inertia::render('configuracoes/configuracoes', $valores);
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

  public function escolher_fuso_horario_ajax(){
    $this->conferir_se_o_usuario_esta_logado();
    if($this->get_usuario_logado() === null){
      $mensagem = 'Você precisa entrar para poder realizar esta operação.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $requisicao = $this->get_requisicao();
    $fuso_horario = $requisicao->post('fuso_horario');
    $fuso_horarios_permitidos = $this->criar_array_de_fuso_horarios();
    if(!in_array($fuso_horario, array_keys($fuso_horarios_permitidos))){
      $mensagem = 'O fuso horário escolhido não foi configurado para este sistema ou é um fuso';
      $mensagem .= ' horário inválido. Por favor, selecione outro fuso horário.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $pk_usuario = $this->get_usuario_logado()->get_pk_usuario();

    $configuracoes_model = new ConfiguracoesModel();
    $configuracoes_model->salvar_fuso_horario($fuso_horario, $pk_usuario);

    $retorno = array();
    echo json_encode($retorno);
    die;
  }

  public function escolher_visual_ajax(){
    $this->conferir_se_o_usuario_esta_logado();
    if($this->get_usuario_logado() === null){
      $mensagem = 'Você precisa entrar para poder realizar esta operação.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $requisicao = $this->get_requisicao();
    $visual = $requisicao->post('visual');
    $visuais_permitidos = $this->criar_array_de_temas_visuais();
    if(!in_array($visual, array_keys($visuais_permitidos))){
      $mensagem = 'O visual escolhido não é um visual válido. Por favor, selecione outro visual.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $pk_usuario = $this->get_usuario_logado()->get_pk_usuario();

    $configuracoes_model = new ConfiguracoesModel();
    $configuracoes_model->salvar_visual($visual, $pk_usuario);

    $retorno = array();
    echo json_encode($retorno);
    die;
  }

  public function editar_nome_de_usuario_ajax(){
    $this->conferir_se_o_usuario_esta_logado();
    if($this->get_usuario_logado() === null){
      $mensagem = 'Você precisa entrar para poder realizar esta operação.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $requisicao = $this->get_requisicao();
    $nome_de_usuario = trim($requisicao->post('nome_de_usuario') ?? '');
    if($nome_de_usuario === ''){
      $mensagem = 'O campo nome de usuário não pode ficar em branco.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $usuario = new Usuario();
    $caracteres_permitidos = $usuario->caracteres_permitidos_para_nome_de_usuario();
    $array_caracteres_do_nome_do_usuario = mb_str_split($nome_de_usuario, 1);

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
        $retorno['mensagem_de_falha'] = $mensagem;
        echo json_encode($retorno);
        die;
      }
    }

    $configuracoes_model = new ConfiguracoesModel();
    $pk_usuario = $this->get_usuario_logado()->get_pk_usuario();

    $array_resultado = $configuracoes_model->verificar_disponibilidade_de_nome_de_usuario($nome_de_usuario,
      $pk_usuario);
    if(isset($array_resultado['mensagem_do_model'])){
      $mensagem = $array_resultado['mensagem_do_model'];
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $minimo = $usuario->quantidade_minima_de_caracteres('nome_de_usuario');
    $maximo = $usuario->quantidade_maxima_de_caracteres('nome_de_usuario');
    $quantidade = mb_strlen($nome_de_usuario);

    if($quantidade < $minimo){
      $mensagem = "O campo nome de usuário precisa ter no mínimo $minimo caracteres.";
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    if($quantidade > $maximo){
      $mensagem = "O campo nome de usuário não pode ultrapassar $maximo caracteres.";
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $array_resultado = $configuracoes_model->salvar_nome_de_usuario($nome_de_usuario, $pk_usuario);
    if(isset($array_resultado['mensagem_do_model'])){
      $mensagem = $array_resultado['mensagem_do_model'];
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $this->get_usuario_logado()->set_nome_de_usuario($nome_de_usuario);
    $sessao = session();
    if($sessao->has('nome_de_usuario')){
      $sessao->put('nome_de_usuario', $nome_de_usuario);
      $sessao->save();
    }

    $retorno['nome_de_usuario'] = $nome_de_usuario;
    echo json_encode($retorno);
    die;
  }

  public function exibir_nao_exibir_sexo_no_perfil_ajax(){
    $this->conferir_se_o_usuario_esta_logado();
    if($this->get_usuario_logado() === null){
      $mensagem = 'Você precisa entrar para poder realizar esta operação.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $requisicao = $this->get_requisicao();
    $opcao_escolhida = $requisicao->post('opcao_escolhida');

    $usuario = new Usuario();
    $array_opcoes = $usuario->enum_exibir_sexo_no_perfil();

    if(!in_array($opcao_escolhida, array_keys($array_opcoes))){
      $mensagem = 'A opção escolhida não é uma opção válida. Por favor, selecione outra opção.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $pk_usuario = $this->get_usuario_logado()->get_pk_usuario();

    $configuracoes_model = new ConfiguracoesModel();
    $configuracoes_model->salvar_escolha_de_exibicao_de_sexo_no_perfil($opcao_escolhida, $pk_usuario);

    $retorno = array();
    echo json_encode($retorno);
    die;
  }

  public function exibir_nao_exibir_email_no_perfil_ajax(){
    $this->conferir_se_o_usuario_esta_logado();
    if($this->get_usuario_logado() === null){
      $mensagem = 'Você precisa entrar para poder realizar esta operação.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $requisicao = $this->get_requisicao();
    $opcao_escolhida = $requisicao->post('opcao_escolhida');

    $usuario = new Usuario();
    $array_opcoes = $usuario->enum_exibir_email_no_perfil();

    if(!in_array($opcao_escolhida, array_keys($array_opcoes))){
      $mensagem = 'A opção escolhida não é uma opção válida. Por favor, selecione outra opção.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $pk_usuario = $this->get_usuario_logado()->get_pk_usuario();

    $configuracoes_model = new ConfiguracoesModel();
    $configuracoes_model->salvar_escolha_de_exibicao_de_email_no_perfil($opcao_escolhida, $pk_usuario);

    $retorno = array();
    echo json_encode($retorno);
    die;
  }

  public function mudar_senha_ajax(){
    $this->conferir_se_o_usuario_esta_logado();
    if($this->get_usuario_logado() === null){
      $mensagem = 'Você precisa entrar para poder realizar esta operação.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $requisicao = $this->get_requisicao();
    $senha_atual = $requisicao->post('senha_atual');
    $nova_senha = $requisicao->post('nova_senha');
    $nova_senha_novamente = $requisicao->post('nova_senha_novamente');

    if($senha_atual === null or $senha_atual === ''){
      $mensagem = 'O campo senha atual não pode ficar em branco.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    if(!password_verify($senha_atual, $this->get_usuario_logado()->get_senha())){
      $mensagem = 'A senha atual digitada não está correta.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    if($nova_senha === null or $nova_senha === ''){
      $mensagem = 'O campo nova senha não pode ficar em branco.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $usuario = new Usuario();
    $minimo = $usuario->quantidade_minima_de_caracteres('senha');
    $maximo = $usuario->quantidade_maxima_de_caracteres('senha');
    $quantidade = mb_strlen($nova_senha);

    if($quantidade < $minimo){
      $mensagem = "O campo nova senha precisa ter no mínimo $minimo caracteres.";
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }
    if($quantidade > $maximo){
      $mensagem = "O campo nova senha não pode ultrapassar $maximo caracteres.";
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    if($nova_senha_novamente === null or $nova_senha_novamente === ''){
      $mensagem = 'O campo nova senha novamente não pode ficar em branco.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    if($nova_senha_novamente !== $nova_senha){
      $mensagem = 'O valor do campo nova senha novamente precisa ser o mesmo valor do campo nova';
      $mensagem .= ' senha. Essa validação existe para te ajudar a não preencher errado.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $nova_senha = $this->criptografar_senha_do_usuario($nova_senha);
    $pk_usuario = $this->get_usuario_logado()->get_pk_usuario();

    $configuracoes_model = new ConfiguracoesModel();
    $configuracoes_model->salvar_nova_senha($nova_senha, $pk_usuario);

    $retorno = array();
    echo json_encode($retorno);
    die;
  }

}
