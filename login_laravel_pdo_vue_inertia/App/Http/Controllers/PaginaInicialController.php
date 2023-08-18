<?php

namespace App\Http\Controllers;

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

}
