<?php

namespace App\Http\Controllers;

use App\Models\UsuariosModel;
use App\Models\Entidades\Usuario;
use Inertia\Inertia;

final class UsuariosController extends TemplateLayoutController{
  private const QUANTIDADE_PADRAO_POR_SEGMENTO = 10;

  public function carregar_pagina($redirecionar = false){
    if($redirecionar){
      //Redireciona para si mesmo, motivo: limpar a requisição.
      header('Location: /usuarios');
      die;
    }

    $this->conferir_se_o_usuario_esta_logado();
    $valores = $this->get_valores();
    $sessao = session();

    /* Especificando a página do sistema para os links e outras tags */
    $valores['template_layout']['pagina_do_sistema'] = 'usuarios';

    /* Mostrando mensagem caso exista alguma */
    if($sessao->has('mensagem_template')){
      $valores['template_layout']['mensagem'] = $sessao->get('mensagem_template');
      $sessao->forget('mensagem_template');
      $sessao->save();
    }

    /* Variável que guarda a mensagem da página começa inicialmente vazia */
    $mensagem = '';

    /* Carregando lista de tipos de usuários */
    $usuario = new Usuario();
    $valores['usuarios']['tipos_de_usuario'] = $usuario->enum_tipo();

    /* Carregando lista de quantidades por segmento */
    $quantidades_por_segmento = $this->criar_array_quantidades_por_segmento();
    $valores['usuarios']['quantidades_por_segmento'] = $quantidades_por_segmento;

    /* Carregando tabela de usuários */
    $valores['usuarios']['lista_de_usuarios'] = $this->mostrar_usuarios();

    /* Verificando se o usuário está logado */
    if($this->get_usuario_logado() === null){
      $mensagem = 'Você precisa entrar para poder acessar esta página.';
      unset($valores['usuarios']['tipos_de_usuario']);
      unset($valores['usuarios']['quantidades_por_segmento']);
      unset($valores['usuarios']['lista_de_usuarios']);
    }

    $valores['usuarios']['mensagem_da_pagina'] = $mensagem;
    return Inertia::render('usuarios/usuarios', $valores);
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

  private function criar_array_quantidades_por_segmento(){
    $quantidades_por_segmento['5'] = 5;
    $quantidades_por_segmento['10'] = 10;
    $quantidades_por_segmento['15'] = 15;
    $quantidades_por_segmento['20'] = 20;
    $quantidades_por_segmento['25'] = 25;
    $quantidades_por_segmento['30'] = 30;
    $quantidades_por_segmento['40'] = 40;
    $quantidades_por_segmento['50'] = 50;
    $quantidades_por_segmento['60'] = 60;
    $quantidades_por_segmento['100'] = 100;
    $quantidades_por_segmento['120'] = 120;

    return $quantidades_por_segmento;
  }

  private function mostrar_usuarios($segmento = 1){
    $usuarios_model = new UsuariosModel();
    $usuario = new Usuario();

    $requisicao = $this->get_requisicao();
    $valores = array();

    /* Verificando se o sistema irá mostrar os e-mails */
    $mostrar_email = false;
    if($this->get_usuario_logado() !== null){
      $tipo_do_usuario_logado = $this->get_usuario_logado()->get_tipo();
      switch($tipo_do_usuario_logado){
        case 'dono':
        case 'administrador':
        case 'moderador':
          $mostrar_email = true;
          break;
      }
    }
    $valores['mostrar_email'] = $mostrar_email;

    /* Preparando os filtros */
    $filtros = array();
    $filtro_nome_de_usuario = trim($requisicao->get('filtro_nome_de_usuario') ?? '');
    if($filtro_nome_de_usuario !== ''){
      $filtros['nome_de_usuario'] = $filtro_nome_de_usuario;
    }
    $valores['filtro_nome_de_usuario'] = $filtro_nome_de_usuario;

    $filtro_email_do_usuario = trim($requisicao->get('filtro_email_do_usuario') ?? '');
    if($filtro_email_do_usuario !== '' && $mostrar_email){
      $filtros['email'] = $filtro_email_do_usuario;
    }
    $valores['filtro_email_do_usuario'] = $filtro_email_do_usuario;

    $filtro_tipo_de_usuario = $requisicao->get('filtro_tipo_de_usuario');
    $array_tipos_de_usuario = $usuario->enum_tipo();
    if(isset($array_tipos_de_usuario[$filtro_tipo_de_usuario])){
      $filtros['tipo'] = $filtro_tipo_de_usuario;
    }else{
      $filtro_tipo_de_usuario = 'todos';
    }
    $valores['filtro_tipo_de_usuario'] = $filtro_tipo_de_usuario;

    /* Preparando a ordenação */
    $ordenacao = $requisicao->get('ordenacao');
    $valores['ordem_do_nome_de_usuario'] = 'Nome de Usuário';
    $valores['ordem_do_email'] = 'E-mail';
    $valores['ordem_do_momento_do_cadastro'] = 'Cadastrado em';
    $valores['ordem_do_tipo'] = 'Tipo';
    switch($ordenacao){
      case 'padrao':
        break;
      case 'nome_de_usuario_em_ordem_alfabetica':
        $valores['ordem_do_nome_de_usuario'] = 'Nome de Usuário ▲';
        break;
      case 'nome_de_usuario_em_ordem_alfabetica_inversa':
        $valores['ordem_do_nome_de_usuario'] = 'Nome de Usuário ▼';
        break;
      case 'email_em_ordem_alfabetica':
        if($mostrar_email){
          $valores['ordem_do_email'] = 'E-mail ▲';
        }else{
          $ordenacao = 'padrao';
        }
        break;
      case 'email_em_ordem_alfabetica_inversa':
        if($mostrar_email){
          $valores['ordem_do_email'] = 'E-mail ▼';
        }else{
          $ordenacao = 'padrao';
        }
        break;
      case 'momento_do_cadastro_em_ordem_cronologica':
        $valores['ordem_do_momento_do_cadastro'] = 'Cadastrado em ▲';
        break;
      case 'momento_do_cadastro_em_ordem_cronologica_inversa':
        $valores['ordem_do_momento_do_cadastro'] = 'Cadastrado em ▼';
        break;
      case 'tipo_em_ordem_alfabetica':
        $valores['ordem_do_tipo'] = 'Tipo ▲';
        break;
      case 'tipo_em_ordem_alfabetica_inversa':
        $valores['ordem_do_tipo'] = 'Tipo ▼';
        break;
      default:
        $ordenacao = 'padrao';
        break;
    }
    $valores['ordenacao'] = $ordenacao;

    /* Preparando a quantidade por segmento */
    $quantidade_por_segmento = (int) $requisicao->get('quantidade_por_segmento');
    $quantidades_por_segmento = $this->criar_array_quantidades_por_segmento();
    if(in_array($quantidade_por_segmento, $quantidades_por_segmento)){
      $valores['quantidade_por_segmento'] = $quantidade_por_segmento;
    }else{
      $quantidade_por_segmento = self::QUANTIDADE_PADRAO_POR_SEGMENTO;
      $valores['quantidade_por_segmento'] = 'padrao';
    }

    $descartar = $quantidade_por_segmento * $segmento - $quantidade_por_segmento;
    $descartar = max($descartar, 0);

    /* Preparando o resultado */
    $usuarios = $usuarios_model->selecionar_usuarios($filtros, $ordenacao, $quantidade_por_segmento,
      $descartar);
    $array_usuarios = array();
    foreach($usuarios as $usuario){
      $array_usuario = array();

      $array_usuario['id_do_usuario'] = $usuario->get_pk_usuario();
      $array_usuario['nome_de_usuario'] = $usuario->get_nome_de_usuario();

      $array_usuario['email'] = '---';
      if($mostrar_email){
        $array_usuario['email'] = $usuario->get_email();
      }

      $momento_do_cadastro = $usuario->get_momento_do_cadastro();
      $momento_do_cadastro = $this->colocar_no_fuso_horario_do_usuario_logado($momento_do_cadastro);
      $momento_do_cadastro = $this->converter_para_horario_data_do_html($momento_do_cadastro);
      $array_usuario['momento_do_cadastro'] = $momento_do_cadastro;

      $array_usuario['tipo'] = $usuario->get_tipo();

      $array_usuarios[] = $array_usuario;
    }

    $valores['usuarios'] = $array_usuarios;

    return $valores;
  }

  public function mostrar_usuarios_ajax(){
    $this->conferir_se_o_usuario_esta_logado();
    if($this->get_usuario_logado() === null){
      $mensagem = 'Você precisa entrar para poder consultar mais usuários.';
      $retorno['mensagem_de_falha'] = $mensagem;
      echo json_encode($retorno);
      die;
    }

    $requisicao = $this->get_requisicao();
    $segmento = $requisicao->get('segmento');
    if(!is_numeric($segmento) or $segmento <= 0 or floor($segmento) != $segmento){
      $segmento = 1;
    }

    $retorno['lista_de_usuarios'] = $this->mostrar_usuarios($segmento);
    echo json_encode($retorno);
    die;
  }

}
