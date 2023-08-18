<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\Entidades\Usuario;
use PDOException;
use PDO;

final class CadastreSeModel{

  public function verificar_disponibilidade_de_nome_de_usuario($nome_de_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
SELECT nome_de_usuario
FROM usuario
WHERE REPLACE(REPLACE(REPLACE(nome_de_usuario, '_', ''), '-', ''), '.', '') = 
REPLACE(REPLACE(REPLACE(:nome_de_usuario, '_', ''), '-', ''), '.', '')
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':nome_de_usuario', $nome_de_usuario, PDO::PARAM_STR);
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

  public function verificar_disponibilidade_de_email($email){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
SELECT email
FROM usuario
WHERE email = :email
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':email', $email, PDO::PARAM_STR);

    $pdo_statement->execute();
    $array_resultado = $pdo_statement->fetchAll(PDO::FETCH_CLASS, Usuario::class);

    if(count($array_resultado) > 0){
      $mensagem_do_model = 'O e-mail escolhido já foi utilizado em outro cadastro de usuário.';
      $mensagem_do_model .= ' Por favor, utilize outro e-mail no momento do cadastro.';
      $array_resultado['mensagem_do_model'] = $mensagem_do_model;
    }

    return $array_resultado;
  }

  public function cadastrar_usuario($usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
INSERT INTO usuario (nome_de_usuario, email, senha, momento_do_cadastro, sexo) 
VALUES (:nome_de_usuario, :email, :senha, :momento_do_cadastro, :sexo)
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':nome_de_usuario', $usuario->get_nome_de_usuario(), PDO::PARAM_STR);
    $pdo_statement->bindValue(':email', $usuario->get_email(), PDO::PARAM_STR);
    $pdo_statement->bindValue(':senha', $usuario->get_senha(), PDO::PARAM_STR);
    $pdo_statement->bindValue(':momento_do_cadastro', $usuario->get_momento_do_cadastro(), PDO::PARAM_STR);
    $pdo_statement->bindValue(':sexo', $usuario->get_sexo(), PDO::PARAM_STR);

    $array_resultado = array();
    try{
      $pdo_statement->execute();
    }catch(PDOException $excecao){
      $codigo_da_excecao = $excecao->errorInfo[1];
      switch($codigo_da_excecao){
        case 1062:
          $mensagem_do_model = 'Já existe um usuário cadastrado com uma ou mais destas informações.';
          $array_resultado['mensagem_do_model'] = $mensagem_do_model;
          break;
        default:
          $array_resultado['mensagem_do_model'] = $excecao->getMessage();
          break;
      }
    }

    return $array_resultado;
  }

}
