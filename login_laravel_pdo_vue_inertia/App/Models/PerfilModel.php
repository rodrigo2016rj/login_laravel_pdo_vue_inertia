<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\Entidades\Usuario;
use PDO;

final class PerfilModel{

  public function selecionar_usuario($pk_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
SELECT pk_usuario, nome_de_usuario, email, momento_do_cadastro, 
tipo, sexo, exibir_sexo_no_perfil, exibir_email_no_perfil 
FROM usuario 
WHERE pk_usuario = :pk_usuario
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':pk_usuario', $pk_usuario, PDO::PARAM_INT);

    $pdo_statement->execute();
    $array_resultado = $pdo_statement->fetchAll(PDO::FETCH_CLASS, Usuario::class);

    if(count($array_resultado) === 0){
      $mensagem_do_model = "Nenhum usuário com ID $pk_usuario foi encontrado no banco de dados";
      $mensagem_do_model .= ' do sistema.';
      $array_resultado['mensagem_do_model'] = $mensagem_do_model;
    }

    return $array_resultado;
  }

}
