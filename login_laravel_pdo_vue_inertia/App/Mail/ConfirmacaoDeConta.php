<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmacaoDeConta extends Mailable{

  use Queueable;
  use SerializesModels;

  private $email;
  private $ip;
  private $pk_usuario;
  private $chave;

  public function __construct($valores_do_email = array()){
    if(isset($valores_do_email['email'])){
      $this->email = $valores_do_email['email'];
    }
    if(isset($valores_do_email['ip'])){
      $this->ip = $valores_do_email['ip'];
    }
    if(isset($valores_do_email['pk_usuario'])){
      $this->pk_usuario = $valores_do_email['pk_usuario'];
    }
    if(isset($valores_do_email['chave'])){
      $this->chave = $valores_do_email['chave'];
    }
  }

  public function envelope(): Envelope{
    $assunto = 'Confirmação de Conta';
    return new Envelope(subject: $assunto);
  }

  public function content(): Content{
    $blade = 'mensagens_de_email/confirmacao_de_conta';
    $valores = [
      'email' => $this->email,
      'ip' => $this->ip,
      'pk_usuario' => $this->pk_usuario,
      'chave' => $this->chave,
    ];
    return new Content(view: $blade, with: $valores);
  }

  public function attachments(): array{
    return [];
  }

}
