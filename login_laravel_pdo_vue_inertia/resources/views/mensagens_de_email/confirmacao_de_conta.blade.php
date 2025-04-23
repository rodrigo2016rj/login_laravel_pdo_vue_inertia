<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html" charset="UTF-8"/>
  </head>
  <div>
    <span>Alguém, provavelmente você, se cadastrou no Sistema Login Laravel PDO Vue Inertia,
      com este e-mail ({{$email}}) e IP {{$ip}}.</span>
    <br/><br/>
    <span>Se você não fez este cadastro, ignore esta mensagem.</span>
    <br/><br/>
    <span>Acesse o link abaixo para confirmar sua conta:</span>
    <br/>
    <a href="http://localhost/pagina_inicial/confirmar_conta?id_do_usuario={{$pk_usuario}}&chave={{$chave}}">Confirmar Conta</a>
    <br/><br/>
    <span>--</span>
    <br/>
    <span>Sistema</span>
  </div>
</html>
