<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\Entidades\Usuario;
use PDO;

final class PaginaInicialModel{

  public function selecionar_usuario($pk_usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
SELECT pk_usuario, conta_confirmada, chave_para_operacoes_via_link 
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

  public function confirmar_conta($usuario){
    $pdo = DB::connection()->getPdo();

    $sql = <<<'SQL'
UPDATE usuario 
SET conta_confirmada = 'sim', chave_para_operacoes_via_link = '' 
WHERE pk_usuario = :pk_usuario 
AND chave_para_operacoes_via_link = :chave_para_operacoes_via_link
SQL;

    $pk_usuario = $usuario->get_pk_usuario();
    $chave = $usuario->get_chave_para_operacoes_via_link();

    $pdo_statement = $pdo->prepare($sql);
    $pdo_statement->bindValue(':pk_usuario', $pk_usuario, PDO::PARAM_INT);
    $pdo_statement->bindValue(':chave_para_operacoes_via_link', $chave, PDO::PARAM_STR);

    $pdo_statement->execute();

    $array_resultado = array();
    return $array_resultado;
  }

}
