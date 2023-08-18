<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\Entidades\Usuario;
use PDO;

final class UsuariosModel{

  public function selecionar_usuarios($filtros, $ordenacao, $quantidade, $descartar){
    $pdo = DB::connection()->getPdo();

    $where = '';
    $parametros = array();
    foreach($filtros as $chave => $valor){
      $parametro = array();
      switch($chave){
        case 'nome_de_usuario':
          $where .= ' AND nome_de_usuario LIKE :nome_de_usuario';
          $parametro['nome'] = ':nome_de_usuario';
          $parametro['valor'] = "%$valor%";
          $parametro['tipo'] = PDO::PARAM_STR;
          $parametros[] = $parametro;
          break;
        case 'email':
          $where .= ' AND email LIKE :email';
          $parametro['nome'] = ':email';
          $parametro['valor'] = "%$valor%";
          $parametro['tipo'] = PDO::PARAM_STR;
          $parametros[] = $parametro;
          break;
        case 'tipo':
          $where .= ' AND tipo = :tipo';
          $parametro['nome'] = ':tipo';
          $parametro['valor'] = $valor;
          $parametro['tipo'] = PDO::PARAM_STR;
          $parametros[] = $parametro;
          break;
      }
    }
    if(strpos($where, ' AND') === 0){
      $where = substr($where, 4);
      $where = "WHERE$where";
    }

    $order_by = '';
    switch($ordenacao){
      case 'padrao':
        $order_by = 'ORDER BY pk_usuario DESC';
        break;
      case 'nome_de_usuario_em_ordem_alfabetica':
        $order_by = 'ORDER BY nome_de_usuario ASC';
        break;
      case 'nome_de_usuario_em_ordem_alfabetica_inversa':
        $order_by = 'ORDER BY nome_de_usuario DESC';
        break;
      case 'email_em_ordem_alfabetica':
        $order_by = 'ORDER BY email ASC';
        break;
      case 'email_em_ordem_alfabetica_inversa':
        $order_by = 'ORDER BY email DESC';
        break;
      case 'momento_do_cadastro_em_ordem_cronologica':
        $order_by = 'ORDER BY momento_do_cadastro ASC';
        $order_by .= ', pk_usuario DESC';
        break;
      case 'momento_do_cadastro_em_ordem_cronologica_inversa':
        $order_by = 'ORDER BY momento_do_cadastro DESC';
        $order_by .= ', pk_usuario DESC';
        break;
      case 'tipo_em_ordem_alfabetica':
        $order_by = 'ORDER BY CAST(tipo AS CHAR) ASC';
        $order_by .= ', pk_usuario DESC';
        break;
      case 'tipo_em_ordem_alfabetica_inversa':
        $order_by = 'ORDER BY CAST(tipo AS CHAR) DESC';
        $order_by .= ', pk_usuario DESC';
        break;
    }

    $sql = <<<"SQL"
SELECT pk_usuario, nome_de_usuario, email, momento_do_cadastro, tipo 
FROM usuario 
$where 
$order_by 
LIMIT :descartar, :quantidade
SQL;

    $pdo_statement = $pdo->prepare($sql);
    foreach($parametros as $parametro){
      $pdo_statement->bindValue($parametro['nome'], $parametro['valor'], $parametro['tipo']);
    }
    $pdo_statement->bindValue(':descartar', $descartar, PDO::PARAM_INT);
    $pdo_statement->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);

    $pdo_statement->execute();
    $array_resultado = $pdo_statement->fetchAll(PDO::FETCH_CLASS, Usuario::class);

    return $array_resultado;
  }

}
