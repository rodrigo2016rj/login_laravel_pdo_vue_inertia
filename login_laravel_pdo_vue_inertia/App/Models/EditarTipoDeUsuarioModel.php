<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\Entidades\Usuario;
use PDO;

final class EditarTipoDeUsuarioModel{

  public function selecionar_usuario($pk_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
SELECT pk_usuario, nome_de_usuario, tipo 
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

  public function editar_tipo_de_usuario($usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
UPDATE usuario 
SET tipo = :tipo 
WHERE pk_usuario = :pk_usuario
SQL;

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':tipo', $usuario->get_tipo(), PDO::PARAM_STR);
    $pdo_statement->bindValue(':pk_usuario', $usuario->get_pk_usuario(), PDO::PARAM_INT);

    $pdo_statement->execute();

    $array_resultado = array();
    return $array_resultado;
  }

}
