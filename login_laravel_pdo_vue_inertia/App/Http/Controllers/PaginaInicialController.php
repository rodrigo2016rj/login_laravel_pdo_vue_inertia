<?php

namespace App\Http\Controllers;

use App\Models\PaginaInicialModel;
use App\Models\Entidades\Usuario;
use Inertia\Inertia;

final class PaginaInicialController extends TemplateLayoutController{

  public function carregar_pagina($redirecionar = false){
    if($redirecionar){
      //Redireciona para si mesmo, motivo: limpar a requisição.
      header('Location: /pagina_inicial');
      die;
    }

    $this->conferir_se_o_usuario_esta_logado();
    $valores = $this->get_valores();
    $sessao = session();

    /* Especificando a página do sistema para os links e outras tags */
    $valores['template_layout']['pagina_do_sistema'] = 'pagina_inicial';

    /* Mostrando mensagem caso exista alguma */
    if($sessao->has('mensagem_template')){
      $valores['template_layout']['mensagem'] = $sessao->get('mensagem_template');
      $sessao->forget('mensagem_template');
      $sessao->save();
    }

    /* Variável que guarda a mensagem da página começa inicialmente vazia */
    $mensagem = '';

    /* Se houver mensagem na sessão, deve ser mostrada */
    if($sessao->has('mensagem_da_pagina_inicial')){
      $mensagem = $sessao->get('mensagem_da_pagina_inicial');
      $sessao->forget('mensagem_da_pagina_inicial');
      $sessao->save();
    }

    $valores['pagina_inicial']['mensagem_da_pagina'] = $mensagem;

    return Inertia::render('pagina_inicial/pagina_inicial', $valores);
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

  public function confirmar_conta(){
    $sessao = session();
    $pagina_inicial_model = new PaginaInicialModel();
    $usuario = new Usuario();

    /* Obtendo valores do formulário */
    $requisicao = $this->get_requisicao();
    $chave = $requisicao->get('chave');
    $pk_usuario = $requisicao->get('id_do_usuario');

    /* Validações */
    if(!is_numeric($pk_usuario) or $pk_usuario <= 0 or floor($pk_usuario) != $pk_usuario){
      $mensagem = 'A conta não foi confirmada.';
      $mensagem .= ' O ID do usuário precisa ser um número natural maior que zero.';
      $sessao->put('mensagem_da_pagina_inicial', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
    $array_resultado = $pagina_inicial_model->selecionar_usuario($pk_usuario);
    if(isset($array_resultado['mensagem_do_model'])){
      $mensagem = 'A conta não foi confirmada.';
      $mensagem .= " {$array_resultado['mensagem_do_model']}";
      $sessao->put('mensagem_da_pagina_inicial', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }else{
      $usuario = $array_resultado[0];
    }

    if($usuario->get_conta_confirmada() === 'sim'){
      $mensagem = 'A conta já havia sido confirmada.';
      $mensagem .= ' Para fazer login clique no link "Entrar" no menu do topo direito.';
      $sessao->put('mensagem_da_pagina_inicial', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }

    if($chave === '' or $chave !== $usuario->get_chave_para_operacoes_via_link()){
      $mensagem = 'A conta não foi confirmada.';
      $mensagem .= ' A chave contida na URL não é válida.';
      $sessao->put('mensagem_da_pagina_inicial', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }

    /* Confirmando conta */
    $array_resultado = $pagina_inicial_model->confirmar_conta($usuario);
    if(isset($array_resultado['mensagem_do_model'])){
      $mensagem = 'A conta não foi confirmada.';
      $mensagem .= " {$array_resultado['mensagem_do_model']}";
      $sessao->put('mensagem_da_pagina_inicial', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }else{
      $mensagem = 'A conta foi confirmada com sucesso.';
      $mensagem .= ' Para fazer login clique no link "Entrar" no menu do topo direito.';
      $sessao->put('mensagem_da_pagina_inicial', $mensagem);
      $sessao->save();
      $this->carregar_pagina(true);
      die;
    }
  }

}
