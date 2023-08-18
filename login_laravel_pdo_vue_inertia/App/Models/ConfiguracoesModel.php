<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\Entidades\Usuario;
use PDO;
use PDOException;

final class ConfiguracoesModel{

  public function selecionar_usuario($pk_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
SELECT pk_usuario, visual, fuso_horario, nome_de_usuario, exibir_sexo_no_perfil, 
exibir_email_no_perfil 
FROM usuario 
WHERE pk_usuario = :pk_usuario 
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':pk_usuario', $pk_usuario, PDO::PARAM_INT);

    $pdo_statement->execute();
    $array_resultado = $pdo_statement->fetchAll(PDO::FETCH_CLASS, Usuario::class);

    if(count($array_resultado) === 0){
      $mensagem_do_model = 'Este usuário não se encontra mais no banco de dados do sistema.';
      $array_resultado['mensagem_do_model'] = $mensagem_do_model;
    }

    return $array_resultado;
  }

  public function salvar_fuso_horario($fuso_horario, $pk_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
UPDATE usuario 
SET fuso_horario = :fuso_horario 
WHERE pk_usuario = :pk_usuario
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':fuso_horario', $fuso_horario, PDO::PARAM_STR);
    $pdo_statement->bindValue(':pk_usuario', $pk_usuario, PDO::PARAM_INT);

    $pdo_statement->execute();

    $array_resultado = array();
    return $array_resultado;
  }

  public function salvar_visual($visual, $pk_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
UPDATE usuario 
SET visual = :visual 
WHERE pk_usuario = :pk_usuario
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':visual', $visual, PDO::PARAM_STR);
    $pdo_statement->bindValue(':pk_usuario', $pk_usuario, PDO::PARAM_INT);

    $pdo_statement->execute();

    $array_resultado = array();
    return $array_resultado;
  }

  public function verificar_disponibilidade_de_nome_de_usuario($nome_de_usuario, $pk_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
SELECT nome_de_usuario
FROM usuario
WHERE REPLACE(REPLACE(REPLACE(nome_de_usuario, '_', ''), '-', ''), '.', '') = 
REPLACE(REPLACE(REPLACE(:nome_de_usuario, '_', ''), '-', ''), '.', '') 
AND pk_usuario <> :pk_usuario
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':nome_de_usuario', $nome_de_usuario, PDO::PARAM_STR);
    $pdo_statement->bindValue(':pk_usuario', $pk_usuario, PDO::PARAM_INT);
    $pdo_statement->execute();
    $array_resultado = $pdo_statement->fetchAll(PDO::FETCH_CLASS, Usuario::class);

    if(count($array_resultado) === 1){
      $usuario_encontrado = $array_resultado[0];
      if($usuario_encontrado->get_nome_de_usuario() === $nome_de_usuario){
        $mensagem_do_model = 'O nome de usuário escolhido já foi utilizado em outro cadastro.';
        $mensagem_do_model .= ' Por favor, escolha outro nome de usuário.';
        $array_resultado['mensagem_do_model'] = $mensagem_do_model;
      }else{
        $nome_similar = $usuario_encontrado->get_nome_de_usuario();
        $mensagem_do_model = 'O nome de usuário escolhido é muito similar a um nome de usuário já';
        $mensagem_do_model .= " cadastrado (nome \"$nome_similar\"). Por favor, escolha outro";
        $mensagem_do_model .= ' nome de usuário.';
        $array_resultado['mensagem_do_model'] = $mensagem_do_model;
      }
    }elseif(count($array_resultado) > 1){
      $mensagem_do_model = 'O nome de usuário escolhido é muito similar a outros nomes de usuários';
      $mensagem_do_model .= ' já cadastrados. Por favor, escolha outro nome de usuário.';
      $array_resultado['mensagem_do_model'] = $mensagem_do_model;
    }

    return $array_resultado;
  }

  public function salvar_nome_de_usuario($nome_de_usuario, $pk_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
UPDATE usuario 
SET nome_de_usuario = :nome_de_usuario 
WHERE pk_usuario = :pk_usuario
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':nome_de_usuario', $nome_de_usuario, PDO::PARAM_STR);
    $pdo_statement->bindValue(':pk_usuario', $pk_usuario, PDO::PARAM_INT);

    $array_resultado = array();
    try{
      $pdo_statement->execute();
    }catch(PDOException $excecao){
      $codigo_da_excecao = $excecao->errorInfo[1];
      switch($codigo_da_excecao){
        case 1062:
          $mensagem_do_model = 'O nome de usuário escolhido já foi utilizado em outro cadastro.';
          $mensagem_do_model .= ' Por favor, escolha outro nome de usuário.';
          $array_resultado['mensagem_do_model'] = $mensagem_do_model;
          break;
        default:
          $array_resultado['mensagem_do_model'] = $excecao->getMessage();
          break;
      }
    }

    return $array_resultado;
  }

  public function salvar_escolha_de_exibicao_de_sexo_no_perfil($opcao_escolhida, $pk_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
UPDATE usuario 
SET exibir_sexo_no_perfil = :exibir_sexo_no_perfil 
WHERE pk_usuario = :pk_usuario
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':exibir_sexo_no_perfil', $opcao_escolhida, PDO::PARAM_STR);
    $pdo_statement->bindValue(':pk_usuario', $pk_usuario, PDO::PARAM_INT);

    $pdo_statement->execute();

    $array_resultado = array();
    return $array_resultado;
  }

  public function salvar_escolha_de_exibicao_de_email_no_perfil($opcao_escolhida, $pk_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
UPDATE usuario 
SET exibir_email_no_perfil = :exibir_email_no_perfil 
WHERE pk_usuario = :pk_usuario
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':exibir_email_no_perfil', $opcao_escolhida, PDO::PARAM_STR);
    $pdo_statement->bindValue(':pk_usuario', $pk_usuario, PDO::PARAM_INT);

    $pdo_statement->execute();

    $array_resultado = array();
    return $array_resultado;
  }

  public function salvar_nova_senha($senha, $pk_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
UPDATE usuario 
SET senha = :senha 
WHERE pk_usuario = :pk_usuario
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':senha', $senha, PDO::PARAM_STR);
    $pdo_statement->bindValue(':pk_usuario', $pk_usuario, PDO::PARAM_INT);

    $pdo_statement->execute();

    $array_resultado = array();
    return $array_resultado;
  }

}
