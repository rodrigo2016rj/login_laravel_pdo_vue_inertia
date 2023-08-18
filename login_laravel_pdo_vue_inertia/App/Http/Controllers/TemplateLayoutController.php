<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\TemplateLayoutModel;
use DateTime;
use DateTimeZone;
use Exception;

class TemplateLayoutController extends Controller{
  /* Armazena informações do array que guarda os valores da view. */
  private $valores;

  /* Armazena informações do usuário logado. */
  private $usuario_logado;

  /* Armazena o objeto da requisição. */
  private $requisicao;

  public function __construct(Request $requisicao){
    $this->requisicao = $requisicao;
  }

  protected final function get_valores(){
    //Só use este método após chamar o conferir_se_o_usuario_esta_logado
    return $this->valores;
  }

  protected final function get_usuario_logado(){
    //Só use este método após chamar o conferir_se_o_usuario_esta_logado
    return $this->usuario_logado;
  }

  protected final function get_requisicao(){
    return $this->requisicao;
  }

  /** ---------------------------------------------------------------------------------------------
    Confere se o usuário está logado e melhora o array valores. */
  protected final function conferir_se_o_usuario_esta_logado(){
    $template_layout_model = new TemplateLayoutModel();

    $sessao = session();

    $this->valores['template_layout']['visual_escolhido'] = 'tema_inicial';
    $this->valores['template_layout']['chave_anti_csrf'] = csrf_token();
    $this->valores['template_layout']['pagina_do_sistema'] = '';
    $this->valores['template_layout']['id_referencia'] = '';
    $this->valores['template_layout']['mensagem'] = '';
    $this->valores['template_layout']['usuario_esta_logado'] = false;
    $this->valores['template_layout']['id_do_usuario_logado'] = null;

    $this->usuario_logado = null;
    if($sessao->has('nome_de_usuario')){
      $nome_de_usuario = $sessao->get('nome_de_usuario');
      $array_resultado = $template_layout_model->seleciona_usuario_pelo_nome_de_usuario($nome_de_usuario);

      if(isset($array_resultado['mensagem_do_model'])){
        //Aqui eu posso colocar para guardar um registro de tentativa suspeita de login por exemplo.
      }else{
        $this->usuario_logado = $array_resultado[0];

        $this->valores['template_layout']['usuario_esta_logado'] = true;

        $this->valores['template_layout']['id_do_usuario_logado'] = $this->usuario_logado->get_pk_usuario();

        $visual_escolhido = $this->usuario_logado->get_visual();
        $visuais_permitidos = $this->criar_array_de_temas_visuais();
        if(in_array($visual_escolhido, array_keys($visuais_permitidos))){
          $this->valores['template_layout']['visual_escolhido'] = $visual_escolhido;
        }
      }
    }
  }

  /** ---------------------------------------------------------------------------------------------
    Função padrão para o usuário entrar na conta (login). */
  protected final function entrar_padronizado(){
    $template_layout_model = new TemplateLayoutModel();

    $sessao = session();

    /* Obtendo valores do formulário */
    $requisicao = $this->requisicao;
    $nome_de_usuario = trim($requisicao->post('nome_de_usuario') ?? '');
    $senha = $requisicao->post('senha');

    if($nome_de_usuario === ''){
      $mensagem = 'O campo nome de usuário precisa ser preenchido.';
      $sessao->put('mensagem_template', $mensagem);
      $sessao->save();
      return false;
    }
    if($senha === null or $senha === ''){
      $mensagem = 'O campo senha precisa ser preenchido.';
      $sessao->put('mensagem_template', $mensagem);
      $sessao->save();
      return false;
    }

    $array_resultado = $template_layout_model->seleciona_senha_do_usuario_pelo_nome_de_usuario($nome_de_usuario);

    if(isset($array_resultado['mensagem_do_model'])){
      $sessao->put('mensagem_template', $array_resultado['mensagem_do_model']);
      $sessao->save();
      return false;
    }else{
      $usuario = $array_resultado[0];
      if(password_verify($senha, $usuario->get_senha())){
        $sessao->put('nome_de_usuario', $nome_de_usuario);
        $sessao->save();
      }else{
        $mensagem = 'A senha digitada não está correta.';
        $sessao->put('mensagem_template', $mensagem);
        $sessao->save();
        return false;
      }
    }

    return true;
  }

  /** ---------------------------------------------------------------------------------------------
    Função padrão para o usuário sair da conta (logout). */
  protected final function sair_padronizado(){
    $sessao = session();
    $sessao->forget('nome_de_usuario');
    $sessao->save();
  }

  /** ---------------------------------------------------------------------------------------------
    Criptografa a senha do usuário. */
  protected final function criptografar_senha_do_usuario($senha){
    $senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);

    return $senha_criptografada;
  }

  /** ---------------------------------------------------------------------------------------------
    Acrescenta quebras de linha no padrão XHTML. */
  protected function acrescentar_quebras_de_linha_xhtml($texto){
    //Armazena em array todos os padrões de quebra de linha de sistemas operacionais diferentes
    $tipos_de_quebras_de_sistemas_operacionais = array("\r\n", "\r", "\n");
    //Substitui quebras de linha presentes na string por: termina parágrafo </p> começa parágrafo <p>
    $texto_modificado = str_replace($tipos_de_quebras_de_sistemas_operacionais, '</p><p>', $texto);
    //Substitui parágrafo vazio por: quebra de linha <br/>
    $texto_resultante = str_replace('<p></p>', '<br/>', $texto_modificado);
    //Retorna o texto resultante dentro da tag <p></p>
    return "<p>$texto_resultante</p>";
  }

  /** ---------------------------------------------------------------------------------------------
    Converte dd/MM/yyyy para: yyyy-MM-dd */
  protected function converter_para_data_do_sql($data){
    if(!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)){
      //Caso não venha no formato certo, retorna a string sem conversão.
      return $data;
    }
    $dia = substr($data, 0, 2);
    $mes = substr($data, 3, 2);
    $ano = substr($data, 6, 4);
    return "$ano-$mes-$dia";
  }

  /** ---------------------------------------------------------------------------------------------
    Converte xxhyy para: xx:yy:zz */
  protected function converter_para_horario_do_sql($horario){
    if(!preg_match('/^\d{2}h\d{2}$/', $horario)){
      //Caso não venha no formato certo, retorna a string sem conversão.
      return $horario;
    }
    $horas = substr($horario, 0, 2);
    $minutos = substr($horario, 3, 2);
    return "$horas:$minutos:00";
  }

  /** ---------------------------------------------------------------------------------------------
    Converte xxhyy dd/MM/yyyy para: yyyy-MM-dd xx:yy:zz */
  protected function converter_para_horario_data_do_sql($string){
    if(!preg_match('/^\d{2}h\d{2} \d{2}\/\d{2}\/\d{4}$/', $string)){
      //Caso não venha no formato certo, retorna a string sem conversão.
      return $string;
    }
    $horas = substr($string, 0, 2);
    $minutos = substr($string, 3, 2);
    $dia = substr($string, 6, 2);
    $mes = substr($string, 9, 2);
    $ano = substr($string, 12, 4);
    return "$ano-$mes-$dia $horas:$minutos:00";
  }

  /** ---------------------------------------------------------------------------------------------
    Converte yyyy-MM-dd para: dd/MM/yyyy */
  protected function converter_para_data_do_html($data){
    if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)){
      //Caso não venha no formato certo, retorna a string sem conversão.
      return $data;
    }
    $ano = substr($data, 0, 4);
    $mes = substr($data, 5, 2);
    $dia = substr($data, 8, 2);
    return "$dia/$mes/$ano";
  }

  /** ---------------------------------------------------------------------------------------------
    Converte xx:yy:zz para: xxhyy */
  protected function converter_para_horario_do_html($horario){
    if(!preg_match('/^\d{2}:\d{2}:\d{2}$/', $horario)){
      //Caso não venha no formato certo, retorna a string sem conversão.
      return $horario;
    }
    $horas = substr($horario, 0, 2);
    $minutos = substr($horario, 3, 2);
    return $horas.'h'.$minutos;
  }

  /** ---------------------------------------------------------------------------------------------
    Converte yyyy-MM-dd xx:yy:zz para: dd/MM/yyyy às xxhyy */
  protected function converter_para_horario_data_do_html($string){
    if(!preg_match('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $string)){
      //Caso não venha no formato certo, retorna a string sem conversão.
      return $string;
    }
    $ano = substr($string, 0, 4);
    $mes = substr($string, 5, 2);
    $dia = substr($string, 8, 2);
    $horas = substr($string, 11, 2);
    $minutos = substr($string, 14, 2);
    return "$dia/$mes/$ano às ".$horas.'h'.$minutos;
  }

  /** ---------------------------------------------------------------------------------------------
    Coloca no fuso horário do usuário logado */
  protected function colocar_no_fuso_horario_do_usuario_logado($string){
    if(!preg_match('/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/', $string)){
      //Caso não venha no formato certo, retorna a string do jeito que veio.
      return $string;
    }
    if($this->usuario_logado === null){
      //Caso não tenha usuário logado, retorna a string do jeito que veio.
      return $string;
    }
    try{
      $sem_fuso_horario = new DateTimeZone('GMT');
      $objeto_date_time = new DateTime($string, $sem_fuso_horario);

      $fuso_horario_do_usuario = $this->usuario_logado->get_fuso_horario();
      $fuso_horario_do_usuario = new DateTimeZone($fuso_horario_do_usuario);

      $objeto_date_time->setTimeZone($fuso_horario_do_usuario);

      $string = $objeto_date_time->format('Y-m-d H:i:s');
    }catch(Exception $excecao){
      //Aqui posso registrar que há um fuso horário errado na coluna fuso_horario do banco de dados.
    }

    return $string;
  }

  /** ---------------------------------------------------------------------------------------------
    Cria array com os quatro fuso horários do Brasil mais os outros aceitos no PHP pelo local. */
  protected function criar_array_de_fuso_horarios(){
    $fuso_horarios = array();

    $fuso_horarios['-0500'] = 'Horário do Acre';
    $fuso_horarios['-0400'] = 'Horário do Amazonas';
    $fuso_horarios['-0300'] = 'Horário de Brasília';
    $fuso_horarios['-0200'] = 'Horário de Fernando de Noronha';

    $timezones = timezone_identifiers_list();
    foreach($timezones as $timezone){
      $fuso_horario = str_replace('Africa/', 'África/', $timezone);
      $fuso_horario = str_replace('America/', 'América/', $fuso_horario);
      $fuso_horario = str_replace('Asia/', 'Ásia/', $fuso_horario);
      $fuso_horario = str_replace('_', ' ', $fuso_horario);
      $fuso_horario = str_replace('/', ' / ', $fuso_horario);
      $fuso_horarios[$timezone] = $fuso_horario;
    }
    $fuso_horarios['America/Sao_Paulo'] = 'América / São Paulo';
    $fuso_horarios['America/Belem'] = 'América / Belém';

    return $fuso_horarios;
  }

  /** ---------------------------------------------------------------------------------------------
    Cria array com os temas visuais já criados na pasta css deste sistema. */
  protected function criar_array_de_temas_visuais(){
    $visuais['tema_inicial'] = 'Tema Inicial';
    $visuais['tema_sofisticado'] = 'Tema Sofisticado';
    return $visuais;
  }

}
