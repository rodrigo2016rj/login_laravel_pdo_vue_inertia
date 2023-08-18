<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\Entidades\Usuario;
use PDO;

final class TemplateLayoutModel{

  public function seleciona_usuario_pelo_nome_de_usuario($nome_de_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
SELECT pk_usuario, nome_de_usuario, email, senha, momento_do_cadastro, fuso_horario, visual, tipo, 
sexo, exibir_sexo_no_perfil, exibir_email_no_perfil 
FROM usuario 
WHERE nome_de_usuario = :nome_de_usuario
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':nome_de_usuario', $nome_de_usuario, PDO::PARAM_STR);

    $pdo_statement->execute();
    $array_resultado = $pdo_statement->fetchAll(PDO::FETCH_CLASS, Usuario::class);

    if(count($array_resultado) == 0){
      $mensagem_do_model = "O usuário \"$nome_de_usuario\" não foi encontrado no";
      $mensagem_do_model .= ' banco de dados do sistema.';
      $array_resultado['mensagem_do_model'] = $mensagem_do_model;
    }

    return $array_resultado;
  }

  public function seleciona_senha_do_usuario_pelo_nome_de_usuario($nome_de_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
SELECT senha 
FROM usuario 
WHERE nome_de_usuario = :nome_de_usuario
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':nome_de_usuario', $nome_de_usuario, PDO::PARAM_STR);

    $pdo_statement->execute();
    $array_resultado = $pdo_statement->fetchAll(PDO::FETCH_CLASS, Usuario::class);

    if(count($array_resultado) == 0){
      $mensagem_do_model = 'Não existe um usuário cadastrado com esse nome de usuário.';
      $array_resultado['mensagem_do_model'] = $mensagem_do_model;
    }

    return $array_resultado;
  }

}
