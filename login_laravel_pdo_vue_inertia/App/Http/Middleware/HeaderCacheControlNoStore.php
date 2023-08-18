<?php

namespace App\Http\Middleware;

use Closure;

class HeaderCacheControlNoStore{

  public function handle($requisicao, Closure $proximo){
    $resposta = $proximo($requisicao);
    $resposta->headers->set('Cache-Control', 'no-store');
    return $resposta;
  }

}
