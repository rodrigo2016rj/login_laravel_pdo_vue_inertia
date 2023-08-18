<?php

namespace App\Http\Controllers;

use App\Models\EditarTipoDeUsuarioModel;
use App\Models\Entidades\Usuario;
use Inertia\Inertia;

final class EditarTipoDeUsuarioController extends TemplateLayoutController{

  public function carregar_pagina($redirecionar_com_id = false){
    if($redirecionar_com_id === 'pagina_inicial'){
      //Redireciona para a página inicial caso seja necessário.
      header('Location: /pagina_inicial');
      die;
    }elseif($redirecionar_com_id !== false){
      //Redireciona para si mesmo, motivo: limpar a requisição.
      header("Location: /editar_tipo_de_usuario?id=$redirecionar_com_id");
      die;
    }

    $this->conferir_se_o_usuario_esta_logado();
    $valores = $this->get_valores();
    $sessao = session();

    /* Especificando a página do sistema para os links e outras tags */
    $valores['template_layout']['pagina_do_sistema'] = 'editar_tipo_de_usuario';

    /* Mostrando mensagem caso exista alguma */
    if($sessao->has('mensagem_template')){
      $valores['template_layout']['mensagem'] = $sessao->get('mensagem_template');
      $sessao->forget('mensagem_template');
      $sessao->save();
    }

    /* Variável que guarda a mensagem da página começa inicialmente vazia */
    $mensagem = '';

    /* $mostrar_formulario a princípio é true */
    $mostrar_formulario = true;

    /* Informando os tipos de usuário */
    $usuario = new Usuario();
    $tipos_de_usuario = $usuario->enum_tipo();
    $valores['editar_tipo_de_usuario']['tipos_de_usuario'] = $tipos_de_usuario;

    /* Colocando valores iniciais nas variáveis para não ficarem undefined no Vue */
    $valores['editar_tipo_de_usuario']['nome_de_usuario'] = '';
    $valores['editar_tipo_de_usuario']['tipo'] = '';
    $valores['editar_tipo_de_usuario']['id'] = '';

    $editar_tipo_de_usuario_model = new EditarTipoDeUsuarioModel();

    /* Validando o ID de usuário informado na URL */
    $requisicao = $this->get_requisicao();
    $pk_usuario = $requisicao->get('id');
    if(!is_numeric($pk_usuario) or $pk_usuario <= 0 or floor($pk_usuario) != $pk_usuario){
      $mensagem = 'ID inválido, o ID do usuário precisa ser um número natural maior que zero.';
      $mostrar_formulario = false;
    }else{
      $valores['template_layout']['id_referencia'] = $pk_usuario;

      /* Consultando e mostrando informações do usuário */
      $array_resultado = $editar_tipo_de_usuario_model->selecionar_usuario($pk_usuario);
      if(isset($array_resultado['mensagem_do_model'])){
        $mensagem = $array_resultado['mensagem_do_model'];
        $mostrar_formulario = false;
      }else{
        $usuario = $array_resultado[0];

        $valores['editar_tipo_de_usuario']['id'] = $usuario->get_pk_usuario();
        $valores['editar_tipo_de_usuario']['nome_de_usuario'] = $usuario->get_nome_de_usuario();
        $valores['editar_tipo_de_usuario']['tipo'] = $usuario->get_tipo();
      }
    }

    /* Recolocando valores preenchidos previamente pelo usuário no formulário */
    if($sessao->has('backup_do_formulario_da_pagina_editar_tipo_de_usuario')){
      $backup = $sessao->get('backup_do_formulario_da_pagina_editar_tipo_de_usuario');
      $valores['editar_tipo_de_usuario']['tipo'] = $backup['tipo'];
      $sessao->forget('backup_do_formulario_da_pagina_editar_tipo_de_usuario');
      $sessao->save();
    }

    /* Verificando se o usuário está logado */
    if($this->get_usuario_logado() === null){
      $mensagem = 'Você precisa entrar para poder acessar esta página.';
      unset($valores['editar_tipo_de_usuario']['tipos_de_usuario']);
      unset($valores['editar_tipo_de_usuario']['nome_de_usuario']);
      unset($valores['editar_tipo_de_usuario']['tipo']);
      unset($valores['editar_tipo_de_usuario']['id']);
      $mostrar_formulario = false;
    }elseif($mostrar_formulario){
      /* Verificando se o usuário logado pode editar o tipo do outro usuário */
      $tipo_do_usuario_logado = $this->get_usuario_logado()->get_tipo();
      $tipo_do_usuario_alvo = $usuario->get_tipo();

      $niveis = $usuario->niveis_dos_tipos_de_usuario();
      $nivel_do_tipo_do_usuario_logado = 0;
      $nivel_do_tipo_do_usuario_alvo = 0;
      foreach($niveis as $chave => $valor){
        if(in_array($tipo_do_usuario_logado, $valor)){
          $nivel_do_tipo_do_usuario_logado = $chave;
        }
        if(in_array($tipo_do_usuario_alvo, $valor)){
          $nivel_do_tipo_do_usuario_alvo = $chave;
        }
      }

      $tipo_do_usuario_logado_na_frase = $tipo_do_usuario_logado;
      if(isset($tipos_de_usuario[$tipo_do_usuario_logado])){
        $tipo_do_usuario_logado_na_frase = mb_strtolower($tipos_de_usuario[$tipo_do_usuario_logado]);
      }
      $tipo_do_usuario_alvo_na_frase = $tipo_do_usuario_alvo;
      if(isset($tipos_de_usuario[$tipo_do_usuario_alvo])){
        $tipo_do_usuario_alvo_na_frase = mb_strtolower($tipos_de_usuario[$tipo_do_usuario_alvo]);
      }

      if($nivel_do_tipo_do_usuario_logado <= $nivel_do_tipo_do_usuario_alvo){
        $mensagem = 'Você não tem permissão para editar o tipo deste usuário.';
        $mostrar_formulario = false;
      }elseif($nivel_do_tipo_do_usuario_logado === 1){
        $mensagem = "Como um usuário do tipo $tipo_do_usuario_logado_na_frase não pode promover";
        $mensagem .= " um usuário do tipo $tipo_do_usuario_alvo_na_frase, você não pode acessar";
        $mensagem .= ' esta página.';
        $mostrar_formulario = false;
      }

      if(!$mostrar_formulario){
        $valores['editar_tipo_de_usuario']['tipos_de_usuario'] = array();
        $valores['editar_tipo_de_usuario']['nome_de_usuario'] = '---';
        $valores['editar_tipo_de_usuario']['tipo'] = '---';
        $valores['editar_tipo_de_usuario']['id'] = '---';
      }
    }else{
      $valores['editar_tipo_de_usuario']['tipos_de_usuario'] = array();
      $valores['editar_tipo_de_usuario']['nome_de_usuario'] = '---';
      $valores['editar_tipo_de_usuario']['tipo'] = '---';
      $valores['editar_tipo_de_usuario']['id'] = '---';
    }

    /* Se houver mensagem na sessão, deve ser mostrada */
    if($sessao->has('mensagem_da_pagina_editar_tipo_de_usuario')){
      $mensagem = $sessao->get('mensagem_da_pagina_editar_tipo_de_usuario');
      $sessao->forget('mensagem_da_pagina_editar_tipo_de_usuario');
      $sessao->save();
    }

    $valores['editar_tipo_de_usuario']['mostrar_formulario'] = $mostrar_formulario;
    $valores['editar_tipo_de_usuario']['mensagem_da_pagina'] = $mensagem;
    return Inertia::render('editar_tipo_de_usuario/editar_tipo_de_usuario', $valores);
  }

  public function entrar(){
    $this->entrar_padronizado();
    $requisicao = $this->get_requisicao();
    $pk_usuario = $requisicao->get('id');
    if(is_numeric($pk_usuario) and $pk_usuario > 0 and floor($pk_usuario) == $pk_usuario){
      $this->carregar_pagina($pk_usuario);
    }else{
      $this->carregar_pagina('pagina_inicial');
    }
    die;
  }

  public function sair(){
    $this->sair_padronizado();
    $requisicao = $this->get_requisicao();
    $pk_usuario = $requisicao->get('id');
    if(is_numeric($pk_usuario) and $pk_usuario > 0 and floor($pk_usuario) == $pk_usuario){
      $this->carregar_pagina($pk_usuario);
    }else{
      $this->carregar_pagina('pagina_inicial');
    }
    die;
  }

  public function editar(){
    $editar_tipo_de_usuario_model = new EditarTipoDeUsuarioModel();
    $usuario = new Usuario();

    $this->conferir_se_o_usuario_esta_logado();
    $sessao = session();

    /* Obtendo valores do formulário */
    $requisicao = $this->get_requisicao();
    $pk_usuario = $requisicao->post('pk_usuario');
    $tipo = $requisicao->post('tipo');

    /* Fazendo backup do formulário */
    $backup_do_formulario['pk_usuario'] = $pk_usuario;
    $backup_do_formulario['tipo'] = $tipo;
    $sessao->put('backup_do_formulario_da_pagina_editar_tipo_de_usuario', $backup_do_formulario);
    $sessao->save();

    /* Verificando se o usuário está logado */
    if($this->get_usuario_logado() === null){
      $mensagem = 'O tipo do usuário não foi editado.';
      $mensagem .= ' Você precisa entrar para poder editar.';
      $sessao->put('mensagem_da_pagina_editar_tipo_de_usuario', $mensagem);
      $sessao->save();
      $this->carregar_pagina($pk_usuario);
      die;
    }

    /* Validações */
    if(!is_numeric($pk_usuario) or $pk_usuario <= 0 or floor($pk_usuario) != $pk_usuario){
      $mensagem = 'O tipo do usuário não foi editado.';
      $mensagem .= ' O ID do usuário precisa ser um número natural maior que zero.';
      $sessao->put('mensagem_da_pagina_editar_tipo_de_usuario', $mensagem);
      $sessao->save();
      $this->carregar_pagina($pk_usuario);
      die;
    }
    $array_resultado = $editar_tipo_de_usuario_model->selecionar_usuario($pk_usuario);
    if(isset($array_resultado['mensagem_do_model'])){
      $mensagem = 'O tipo do usuário não foi editado.';
      $mensagem .= " {$array_resultado['mensagem_do_model']}";
      $sessao->put('mensagem_da_pagina_editar_tipo_de_usuario', $mensagem);
      $sessao->save();
      $this->carregar_pagina($pk_usuario);
      die;
    }else{
      $usuario = $array_resultado[0];
    }

    $tipos_de_usuario = $usuario->enum_tipo();
    if(!isset($tipos_de_usuario[$tipo])){
      $mensagem = 'O tipo do usuário não foi editado.';
      $mensagem .= ' O tipo escolhido não é válido.';
      $sessao->put('mensagem_da_pagina_editar_tipo_de_usuario', $mensagem);
      $sessao->save();
      $this->carregar_pagina($pk_usuario);
      die;
    }

    $tipo_do_usuario_logado = $this->get_usuario_logado()->get_tipo();
    $tipo_do_usuario_alvo = $usuario->get_tipo();
    $niveis = $usuario->niveis_dos_tipos_de_usuario();
    $nivel_do_tipo_do_usuario_logado = 0;
    $nivel_do_tipo_do_usuario_alvo = 0;
    $nivel_do_tipo_escolhido = 0;
    foreach($niveis as $chave => $valor){
      if(in_array($tipo_do_usuario_logado, $valor)){
        $nivel_do_tipo_do_usuario_logado = $chave;
      }
      if(in_array($tipo_do_usuario_alvo, $valor)){
        $nivel_do_tipo_do_usuario_alvo = $chave;
      }
      if(in_array($tipo, $valor)){
        $nivel_do_tipo_escolhido = $chave;
      }
    }

    $tipo_do_usuario_alvo_na_frase = $tipo_do_usuario_alvo;
    if(isset($tipos_de_usuario[$tipo_do_usuario_alvo])){
      $tipo_do_usuario_alvo_na_frase = mb_strtolower($tipos_de_usuario[$tipo_do_usuario_alvo]);
    }
    $tipo_na_frase = $tipo;
    if(isset($tipos_de_usuario[$tipo])){
      $tipo_na_frase = mb_strtolower($tipos_de_usuario[$tipo]);
    }

    if($nivel_do_tipo_do_usuario_logado <= $nivel_do_tipo_do_usuario_alvo){
      $mensagem = 'O tipo do usuário não foi editado.';
      $mensagem .= " Você não tem permissão para editar um usuário do tipo $tipo_do_usuario_alvo_na_frase.";
      $sessao->put('mensagem_da_pagina_editar_tipo_de_usuario', $mensagem);
      $sessao->save();
      $this->carregar_pagina($pk_usuario);
      die;
    }
    if($nivel_do_tipo_do_usuario_logado <= $nivel_do_tipo_escolhido){
      $mensagem = 'O tipo do usuário não foi editado.';
      $mensagem .= " Você não tem permissão para atribuir o tipo $tipo_na_frase a um usuário.";
      $sessao->put('mensagem_da_pagina_editar_tipo_de_usuario', $mensagem);
      $sessao->save();
      $this->carregar_pagina($pk_usuario);
      die;
    }

    /* Editar usuário no banco de dados */
    $usuario->set_pk_usuario($pk_usuario);
    $usuario->set_tipo($tipo);
    $array_resultado = $editar_tipo_de_usuario_model->editar_tipo_de_usuario($usuario);
    if(isset($array_resultado['mensagem_do_model'])){
      $mensagem = 'O tipo do usuário não foi editado.';
      $mensagem .= " {$array_resultado['mensagem_do_model']}";
      $sessao->put('mensagem_da_pagina_editar_tipo_de_usuario', $mensagem);
      $sessao->save();
      $this->carregar_pagina($pk_usuario);
      die;
    }else{
      $mensagem = 'O tipo do usuário foi editado com sucesso.';
      $sessao->put('mensagem_da_pagina_editar_tipo_de_usuario', $mensagem);
      $sessao->forget('backup_do_formulario_da_pagina_editar_tipo_de_usuario');
      $sessao->save();
      $this->carregar_pagina($pk_usuario);
      die;
    }
  }

}
