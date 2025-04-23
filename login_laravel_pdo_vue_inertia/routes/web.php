<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaginaInicialController;
use App\Http\Controllers\CadastreSeController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\EditarTipoDeUsuarioController;
use App\Http\Controllers\ConfiguracoesController;

/* Página Padrão */
Route::get('/', [PaginaInicialController::class, 'carregar_pagina']);

/* Página Inicial */
Route::get('/pagina_inicial', [PaginaInicialController::class, 'carregar_pagina']);
Route::post('/pagina_inicial/entrar', [PaginaInicialController::class, 'entrar']);
Route::get('/pagina_inicial/sair', [PaginaInicialController::class, 'sair']);
Route::get('/pagina_inicial/confirmar_conta', [PaginaInicialController::class, 'confirmar_conta']);

/* Cadastre-se */
Route::get('/cadastre-se', [CadastreSeController::class, 'carregar_pagina']);
Route::post('/cadastre-se/entrar', [CadastreSeController::class, 'entrar']);
Route::get('/cadastre-se/sair', [CadastreSeController::class, 'sair']);
Route::post('/cadastre-se/cadastrar', [CadastreSeController::class, 'cadastrar']);

/* Usuários */
Route::get('/usuarios', [UsuariosController::class, 'carregar_pagina']);
Route::post('/usuarios/entrar', [UsuariosController::class, 'entrar']);
Route::get('/usuarios/sair', [UsuariosController::class, 'sair']);
Route::get('/usuarios/mostrar_usuarios_ajax', [UsuariosController::class, 'mostrar_usuarios_ajax']);

/* Perfil */
Route::get('/perfil', [PerfilController::class, 'carregar_pagina']);
Route::post('/perfil/entrar', [PerfilController::class, 'entrar']);
Route::get('/perfil/sair', [PerfilController::class, 'sair']);

/* Editar tipo de usuário */
Route::get('/editar_tipo_de_usuario', [EditarTipoDeUsuarioController::class, 'carregar_pagina']);
Route::post('/editar_tipo_de_usuario/entrar', [EditarTipoDeUsuarioController::class, 'entrar']);
Route::get('/editar_tipo_de_usuario/sair', [EditarTipoDeUsuarioController::class, 'sair']);
Route::post('/editar_tipo_de_usuario/editar', [EditarTipoDeUsuarioController::class, 'editar']);

/* Configurações */
Route::get('/configuracoes', [ConfiguracoesController::class, 'carregar_pagina']);
Route::post('/configuracoes/entrar', [ConfiguracoesController::class, 'entrar']);
Route::get('/configuracoes/sair', [ConfiguracoesController::class, 'sair']);
Route::post('/configuracoes/escolher_fuso_horario_ajax', [ConfiguracoesController::class, 'escolher_fuso_horario_ajax']);
Route::post('/configuracoes/escolher_visual_ajax', [ConfiguracoesController::class, 'escolher_visual_ajax']);
Route::post('/configuracoes/editar_nome_de_usuario_ajax', [ConfiguracoesController::class, 'editar_nome_de_usuario_ajax']);
Route::post('/configuracoes/exibir_nao_exibir_sexo_no_perfil_ajax', [ConfiguracoesController::class, 'exibir_nao_exibir_sexo_no_perfil_ajax']);
Route::post('/configuracoes/exibir_nao_exibir_email_no_perfil_ajax', [ConfiguracoesController::class, 'exibir_nao_exibir_email_no_perfil_ajax']);
Route::post('/configuracoes/mudar_senha_ajax', [ConfiguracoesController::class, 'mudar_senha_ajax']);
