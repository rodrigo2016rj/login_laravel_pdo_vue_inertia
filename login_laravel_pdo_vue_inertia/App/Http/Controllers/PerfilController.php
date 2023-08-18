<?php

namespace App\Http\Controllers;

use App\Models\PerfilModel;
use App\Models\Entidades\Usuario;
use Inertia\Inertia;

final class PerfilController extends TemplateLayoutController{

  public function carregar_pagina($redirecionar_com_id = false){
    if($redirecionar_com_id === 'pagina_inicial'){
      //Redireciona para a página inicial caso seja necessário.
      header('Location: /pagina_inicial');
      die;
    }elseif($redirecionar_com_id !== false){
      //Redireciona para si mesmo, motivo: limpar a requisição.
      header("Location: /perfil?id=$redirecionar_com_id");
      die;
    }

    $this->conferir_se_o_usuario_esta_logado();
    $valores = $this->get_valores();
    $sessao = session();

    /* Especificando a página do sistema para os links e outras tags */
    $valores['template_layout']['pagina_do_sistema'] = 'perfil';

    /* Mostrando mensagem caso exista alguma */
    if($sessao->has('mensagem_template')){
      $valores['template_layout']['mensagem'] = $sessao->get('mensagem_template');
      $sessao->forget('mensagem_template');
      $sessao->save();
    }

    /* Variável que guarda a mensagem da página começa inicialmente vazia */
    $mensagem = '';

    /* Colocando valores iniciais nas variáveis para não ficarem undefined no Vue */
    $valores['perfil']['usuario']['id'] = '';
    $valores['perfil']['usuario']['nome_de_usuario'] = '';
    $valores['perfil']['usuario']['email'] = '';
    $valores['perfil']['usuario']['momento_do_cadastro'] = '';
    $valores['perfil']['usuario']['tipo'] = '';
    $valores['perfil']['usuario']['sexo'] = '';
    $valores['perfil']['usuario']['exibir_sexo_no_perfil'] = '';
    $valores['perfil']['usuario']['exibir_email_no_perfil'] = '';

    /* $mostrar_link_editar_tipo_de_usuario a princípio é true */
    $mostrar_link_editar_tipo_de_usuario = true;

    $perfil_model = new PerfilModel();

    /* Validando o ID de usuário informado na URL */
    $requisicao = $this->get_requisicao();
    $pk_usuario = $requisicao->get('id');
    $usuario = new Usuario();
    if(!is_numeric($pk_usuario) or $pk_usuario <= 0 or floor($pk_usuario) != $pk_usuario){
      $mensagem = 'ID inválido, o ID do usuário precisa ser um número natural maior que zero.';
      $mostrar_link_editar_tipo_de_usuario = false;
    }else{
      $valores['template_layout']['id_referencia'] = $pk_usuario;

      /* Consultando e mostrando informações do usuário */
      $array_resultado = $perfil_model->selecionar_usuario($pk_usuario);
      if(isset($array_resultado['mensagem_do_model'])){
        $mensagem = $array_resultado['mensagem_do_model'];
        $mostrar_link_editar_tipo_de_usuario = false;
      }else{
        $usuario = $array_resultado[0];

        $array_usuario['nome_de_usuario'] = $usuario->get_nome_de_usuario();
        $array_usuario['id'] = $usuario->get_pk_usuario();
        $array_usuario['tipo'] = $usuario->get_tipo();

        $array_usuario['exibir_sexo_no_perfil'] = false;
        $array_usuario['sexo'] = '---';
        if($usuario->get_exibir_sexo_no_perfil() == 'sim'){
          $array_usuario['exibir_sexo_no_perfil'] = true;
          $array_usuario['sexo'] = $usuario->get_sexo();
        }

        $array_usuario['exibir_email_no_perfil'] = false;
        $array_usuario['email'] = '---';
        if($usuario->get_exibir_email_no_perfil() == 'sim'){
          $array_usuario['exibir_email_no_perfil'] = true;
          $array_usuario['email'] = $usuario->get_email();
        }

        $momento_do_cadastro = $usuario->get_momento_do_cadastro();
        $momento_do_cadastro = $this->colocar_no_fuso_horario_do_usuario_logado($momento_do_cadastro);
        $momento_do_cadastro = $this->converter_para_horario_data_do_html($momento_do_cadastro);
        $array_usuario['momento_do_cadastro'] = $momento_do_cadastro;

        $valores['perfil']['usuario'] = $array_usuario;
      }
    }

    /* Verificando se o usuário está logado */
    if($this->get_usuario_logado() === null){
      $mensagem = 'Você precisa entrar para poder acessar esta página.';
      $valores['perfil']['usuario']['nome_de_usuario'] = '---';
      unset($valores['perfil']['usuario']['email']);
      unset($valores['perfil']['usuario']['momento_do_cadastro']);
      unset($valores['perfil']['usuario']['tipo']);
      unset($valores['perfil']['usuario']['sexo']);
      unset($valores['perfil']['usuario']['exibir_sexo_no_perfil']);
      unset($valores['perfil']['usuario']['exibir_email_no_perfil']);
      $mostrar_link_editar_tipo_de_usuario = false;
    }else{
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

      if($nivel_do_tipo_do_usuario_logado <= $nivel_do_tipo_do_usuario_alvo or
        $nivel_do_tipo_do_usuario_logado <= 1){
        $mostrar_link_editar_tipo_de_usuario = false;
      }
    }

    /* Se houver mensagem na sessão, deve ser mostrada */
    if($sessao->has('mensagem_da_pagina_perfil')){
      $mensagem = $sessao->get('mensagem_da_pagina_perfil');
      $sessao->forget('mensagem_da_pagina_perfil');
      $sessao->save();
    }

    $valores['perfil']['mostrar_link_editar_tipo_de_usuario'] = $mostrar_link_editar_tipo_de_usuario;
    $valores['perfil']['mensagem_da_pagina'] = $mensagem;
    return Inertia::render('perfil/perfil', $valores);
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

}
