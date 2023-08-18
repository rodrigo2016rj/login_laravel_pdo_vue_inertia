<script>
  import TemplateLayout from "../template_layout.vue";
  import {Head} from "@inertiajs/vue3";
  
  export default{
    props: [
      "template_layout",
      "configuracoes"
    ],
    components: {
      TemplateLayout,
      Head
    },
    data(){
      return{
        /* Propriedades obtidas dos controllers precisam ser recolocadas para prevenir o cache do inertia */
        vue_template_layout: this.template_layout,
        vue_configuracoes: this.configuracoes,
        
        /* Propriedades novas e seus valores iniciais */
        endereco_do_arquivo_css: "/css/" + this.template_layout.visual_escolhido + "/configuracoes.css",
        aba_selecionada: this.configuracoes.aba_inicial,
        contador_ajax: 0,
        mensagem_da_acao_escolher_fuso_horario: "",
        mensagem_da_acao_escolher_visual: "",
        mensagem_da_acao_editar_nome_de_usuario: "",
        mensagem_da_acao_exibir_nao_exibir_sexo_no_perfil: "",
        mensagem_da_acao_exibir_nao_exibir_email_no_perfil: "",
        senha_atual: "",
        nova_senha: "",
        nova_senha_novamente: "",
        mensagem_da_acao_mudar_senha: ""
      }
    },
    mounted(){
      const div_app_template = document.getElementById("div_app_template");
      const div_mensagem = document.getElementById("div_mensagem");
      const span_mensagem = document.getElementById("span_mensagem");
      
      /* O resize_observer é importante no Vue para detectar quando as larguras e alturas deixarão de ser automáticas. */
      const resize_observer = new ResizeObserver(function(){
        /* Enquanto as larguras e alturas forem automáticas não há como fazer ajustes, por isso retorna. */
        var estilo_computado = window.getComputedStyle(div_app_template);
        if(estilo_computado.height === "auto"){
          return;
        }
        
        /* Ajustando a div_mensagem para que cada linha de texto tenha mais ou menos mesmo tamanho. */
        switch(this.vue_template_layout.visual_escolhido){
          case "tema_sofisticado":
            var estilo_computado = window.getComputedStyle(div_mensagem);
            const largura_da_div_mensagem = parseInt(estilo_computado.width, 10);
            
            /* Elementos inline continuam com largura e altura automáticas, o uso de offsetWidth é necessário. */
            const largura_do_span_mensagem = span_mensagem.offsetWidth;
            const texto_da_mensagem = span_mensagem.innerText;
            
            let nova_largura = "0px";
            if(largura_do_span_mensagem > largura_da_div_mensagem){
              const quantidade_de_linhas = Math.ceil(largura_do_span_mensagem / largura_da_div_mensagem);
              nova_largura = Math.ceil(texto_da_mensagem.length / quantidade_de_linhas) + "ex";
            }else{
              nova_largura = largura_do_span_mensagem + 1 + "px";
            }
            div_mensagem.style.whiteSpace = "normal";
            div_mensagem.style.maxWidth = nova_largura;
          break;
        }
        
        /* Removendo o observer. */
        resize_observer.unobserve(div_app_template);
      }.bind(this));
      
      resize_observer.observe(div_app_template);
    },
    methods: {
      selecionar_aba(aba){
        this.aba_selecionada = aba;
      },
      remover_foco_do_botao(evento){
        evento.currentTarget.blur();
      },
      escolher_fuso_horario(evento){
        this.vue_configuracoes.fuso_horario = evento.currentTarget.value;
        
        this.contador_ajax++;
        this.mensagem_da_acao_escolher_fuso_horario = "Salvando...";
        
        const numero_desta_acao_ajax = this.contador_ajax;
        
        /* Requisição ajax */
        let conexao_ajax = null;
        if(window.XMLHttpRequest){
          conexao_ajax = new XMLHttpRequest();
        }else if(window.ActiveXObject){
          conexao_ajax = new ActiveXObject("Microsoft.XMLHTTP");
        }
        const tipo = "POST";
        let url_mais = "";
        let url = "/configuracoes/escolher_fuso_horario_ajax" + url_mais;
        let dados_post = {fuso_horario: this.vue_configuracoes.fuso_horario};
        let resposta = null;
        conexao_ajax.onreadystatechange = function(){
          if(conexao_ajax.readyState == 4){
            if(conexao_ajax.status == 200){
              resposta = JSON.parse(conexao_ajax.responseText);
              if(numero_desta_acao_ajax >= this.contador_ajax){
                if(typeof resposta.mensagem_de_falha != "undefined"){
                  this.mensagem_da_acao_escolher_fuso_horario = resposta.mensagem_de_falha;
                }else{
                  this.mensagem_da_acao_escolher_fuso_horario = "Salvo!";
                }
              }
            }else{
              window.location.href = "configuracoes";
            }
          }
        }.bind(this);
        conexao_ajax.open(tipo, url, true);
        conexao_ajax.setRequestHeader("Content-Type", "application/json");
        conexao_ajax.setRequestHeader("X-CSRF-TOKEN", this.vue_template_layout.chave_anti_csrf);
        conexao_ajax.send(JSON.stringify(dados_post));
      },
      escolher_visual(evento){
        this.vue_configuracoes.visual = evento.currentTarget.value;
        
        this.contador_ajax++;
        this.mensagem_da_acao_escolher_visual = "Salvando...";
        
        const numero_desta_acao_ajax = this.contador_ajax;
        
        /* Requisição ajax */
        let conexao_ajax = null;
        if(window.XMLHttpRequest){
          conexao_ajax = new XMLHttpRequest();
        }else if(window.ActiveXObject){
          conexao_ajax = new ActiveXObject("Microsoft.XMLHTTP");
        }
        const tipo = "POST";
        let url_mais = "";
        let url = "/configuracoes/escolher_visual_ajax" + url_mais;
        let dados_post = {visual: this.vue_configuracoes.visual};
        let resposta = null;
        conexao_ajax.onreadystatechange = function(){
          if(conexao_ajax.readyState == 4){
            if(conexao_ajax.status == 200){
              resposta = JSON.parse(conexao_ajax.responseText);
              if(numero_desta_acao_ajax >= this.contador_ajax){
                if(typeof resposta.mensagem_de_falha != "undefined"){
                  this.mensagem_da_acao_escolher_visual = resposta.mensagem_de_falha;
                }else{
                  this.mensagem_da_acao_escolher_visual = "Salvo!";
                }
              }
            }else{
              window.location.href = "configuracoes";
            }
          }
        }.bind(this);
        conexao_ajax.open(tipo, url, true);
        conexao_ajax.setRequestHeader("Content-Type", "application/json");
        conexao_ajax.setRequestHeader("X-CSRF-TOKEN", this.vue_template_layout.chave_anti_csrf);
        conexao_ajax.send(JSON.stringify(dados_post));
      },
      editar_nome_de_usuario(evento){
        this.remover_foco_do_botao(evento);
        
        this.contador_ajax++;
        this.mensagem_da_acao_editar_nome_de_usuario = "Salvando...";
        
        const numero_desta_acao_ajax = this.contador_ajax;
        
        /* Requisição ajax */
        let conexao_ajax = null;
        if(window.XMLHttpRequest){
          conexao_ajax = new XMLHttpRequest();
        }else if(window.ActiveXObject){
          conexao_ajax = new ActiveXObject("Microsoft.XMLHTTP");
        }
        const tipo = "POST";
        let url_mais = "";
        let url = "/configuracoes/editar_nome_de_usuario_ajax" + url_mais;
        let dados_post = {nome_de_usuario: this.vue_configuracoes.nome_de_usuario};
        let resposta = null;
        conexao_ajax.onreadystatechange = function(){
          if(conexao_ajax.readyState == 4){
            if(conexao_ajax.status == 200){
              resposta = JSON.parse(conexao_ajax.responseText);
              if(numero_desta_acao_ajax >= this.contador_ajax){
                if(typeof resposta.mensagem_de_falha != "undefined"){
                  this.mensagem_da_acao_editar_nome_de_usuario = resposta.mensagem_de_falha;
                }else{
                  this.mensagem_da_acao_editar_nome_de_usuario = "Salvo!";
                  this.vue_configuracoes.nome_de_usuario = resposta.nome_de_usuario;
                }
              }
            }else{
              window.location.href = "configuracoes";
            }
          }
        }.bind(this);
        conexao_ajax.open(tipo, url, true);
        conexao_ajax.setRequestHeader("Content-Type", "application/json");
        conexao_ajax.setRequestHeader("X-CSRF-TOKEN", this.vue_template_layout.chave_anti_csrf);
        conexao_ajax.send(JSON.stringify(dados_post));
      },
      exibir_nao_exibir_sexo_no_perfil(evento){
        this.contador_ajax++;
        this.mensagem_da_acao_exibir_nao_exibir_sexo_no_perfil = "Salvando...";
        
        const numero_desta_acao_ajax = this.contador_ajax;
        
        if(evento.currentTarget.checked){
          this.vue_configuracoes.exibir_sexo_no_perfil = "sim";
        }else{
          this.vue_configuracoes.exibir_sexo_no_perfil = "nao";
        }
        
        /* Requisição ajax */
        let conexao_ajax = null;
        if(window.XMLHttpRequest){
          conexao_ajax = new XMLHttpRequest();
        }else if(window.ActiveXObject){
          conexao_ajax = new ActiveXObject("Microsoft.XMLHTTP");
        }
        const tipo = "POST";
        let url_mais = "";
        let url = "/configuracoes/exibir_nao_exibir_sexo_no_perfil_ajax" + url_mais;
        let dados_post = {opcao_escolhida: this.vue_configuracoes.exibir_sexo_no_perfil};
        let resposta = null;
        conexao_ajax.onreadystatechange = function(){
          if(conexao_ajax.readyState == 4){
            if(conexao_ajax.status == 200){
              resposta = JSON.parse(conexao_ajax.responseText);
              if(numero_desta_acao_ajax >= this.contador_ajax){
                if(typeof resposta.mensagem_de_falha != "undefined"){
                  this.mensagem_da_acao_exibir_nao_exibir_sexo_no_perfil = resposta.mensagem_de_falha;
                }else{
                  this.mensagem_da_acao_exibir_nao_exibir_sexo_no_perfil = "Salvo!";
                }
              }
            }else{
              window.location.href = "configuracoes";
            }
          }
        }.bind(this);
        conexao_ajax.open(tipo, url, true);
        conexao_ajax.setRequestHeader("Content-Type", "application/json");
        conexao_ajax.setRequestHeader("X-CSRF-TOKEN", this.vue_template_layout.chave_anti_csrf);
        conexao_ajax.send(JSON.stringify(dados_post));
      },
      exibir_nao_exibir_email_no_perfil(evento){
        this.contador_ajax++;
        this.mensagem_da_acao_exibir_nao_exibir_email_no_perfil = "Salvando...";
        
        const numero_desta_acao_ajax = this.contador_ajax;
        
        if(evento.currentTarget.checked){
          this.vue_configuracoes.exibir_email_no_perfil = "sim";
        }else{
          this.vue_configuracoes.exibir_email_no_perfil = "nao";
        }
        
        /* Requisição ajax */
        let conexao_ajax = null;
        if(window.XMLHttpRequest){
          conexao_ajax = new XMLHttpRequest();
        }else if(window.ActiveXObject){
          conexao_ajax = new ActiveXObject("Microsoft.XMLHTTP");
        }
        const tipo = "POST";
        let url_mais = "";
        let url = "/configuracoes/exibir_nao_exibir_email_no_perfil_ajax" + url_mais;
        let dados_post = {opcao_escolhida: this.vue_configuracoes.exibir_email_no_perfil};
        let resposta = null;
        conexao_ajax.onreadystatechange = function(){
          if(conexao_ajax.readyState == 4){
            if(conexao_ajax.status == 200){
              resposta = JSON.parse(conexao_ajax.responseText);
              if(numero_desta_acao_ajax >= this.contador_ajax){
                if(typeof resposta.mensagem_de_falha != "undefined"){
                  this.mensagem_da_acao_exibir_nao_exibir_email_no_perfil = resposta.mensagem_de_falha;
                }else{
                  this.mensagem_da_acao_exibir_nao_exibir_email_no_perfil = "Salvo!";
                }
              }
            }else{
              window.location.href = "configuracoes";
            }
          }
        }.bind(this);
        conexao_ajax.open(tipo, url, true);
        conexao_ajax.setRequestHeader("Content-Type", "application/json");
        conexao_ajax.setRequestHeader("X-CSRF-TOKEN", this.vue_template_layout.chave_anti_csrf);
        conexao_ajax.send(JSON.stringify(dados_post));
      },
      mudar_senha(evento){
        this.remover_foco_do_botao(evento);
        
        this.contador_ajax++;
        this.mensagem_da_acao_mudar_senha = "Salvando...";
        
        const numero_desta_acao_ajax = this.contador_ajax;
        
        /* Requisição ajax */
        let conexao_ajax = null;
        if(window.XMLHttpRequest){
          conexao_ajax = new XMLHttpRequest();
        }else if(window.ActiveXObject){
          conexao_ajax = new ActiveXObject("Microsoft.XMLHTTP");
        }
        const tipo = "POST";
        let url_mais = "";
        let url = "/configuracoes/mudar_senha_ajax" + url_mais;
        let dados_post = {senha_atual: this.senha_atual, nova_senha: this.nova_senha, 
                          nova_senha_novamente: this.nova_senha_novamente};
        let resposta = null;
        conexao_ajax.onreadystatechange = function(){
          if(conexao_ajax.readyState == 4){
            if(conexao_ajax.status == 200){
              resposta = JSON.parse(conexao_ajax.responseText);
              if(numero_desta_acao_ajax >= this.contador_ajax){
                if(typeof resposta.mensagem_de_falha != "undefined"){
                  this.mensagem_da_acao_mudar_senha = resposta.mensagem_de_falha;
                }else{
                  this.mensagem_da_acao_mudar_senha = "Salvo!";
                  this.senha_atual = "";
                  this.nova_senha = "";
                  this.nova_senha_novamente = "";
                }
              }
            }else{
              window.location.href = "configuracoes";
            }
          }
        }.bind(this);
        conexao_ajax.open(tipo, url, true);
        conexao_ajax.setRequestHeader("Content-Type", "application/json");
        conexao_ajax.setRequestHeader("X-CSRF-TOKEN", this.vue_template_layout.chave_anti_csrf);
        conexao_ajax.send(JSON.stringify(dados_post));
      }
    }
  }
</script>

<template>
  <TemplateLayout :template_layout="template_layout">
    <template #conteudo>
      <div id="div_mensagem" :class="vue_configuracoes.mensagem_da_pagina ? '' : 'tag_oculta'">
        <span id="span_mensagem">{{vue_configuracoes.mensagem_da_pagina}}</span>
      </div>
      <div v-if="vue_template_layout.usuario_esta_logado && vue_configuracoes.mostrar_configuracoes" id="div_configuracoes">
        <h1 id="h1_titulo_da_pagina">
          <span>Configurações</span>
        </h1>
        <div id="div_local_das_abas">
          <div id="div_rotulo_das_abas">
            <div id="div_rotulo_da_aba_preferencias" @click="selecionar_aba('aba_preferencias')" 
                 :class="['rotulo_da_aba', aba_selecionada === 'aba_preferencias' ? 'rotulo_da_aba_selecionada' : '']">
              <span id="span_rotulo_da_aba_preferencias">Preferências</span>
            </div>
            <div id="div_rotulo_da_aba_perfil" @click="selecionar_aba('aba_perfil')" 
                 :class="['rotulo_da_aba', aba_selecionada === 'aba_perfil' ? 'rotulo_da_aba_selecionada' : '']">
              <span id="span_rotulo_da_aba_perfil">Perfil</span>
            </div>
            <div id="div_rotulo_da_aba_seguranca" @click="selecionar_aba('aba_seguranca')" 
                 :class="['rotulo_da_aba', aba_selecionada === 'aba_seguranca' ? 'rotulo_da_aba_selecionada' : '']">
              <span id="span_rotulo_da_aba_seguranca">Segurança</span>
            </div>
          </div>
          <div v-if="aba_selecionada === 'aba_preferencias'" id="div_aba_preferencias" class="conteudo_da_aba">
            <h2 id="h2_aba_preferencias">
              <span>Editar preferências</span>
            </h2>
            <div id="div_escolher_fuso_horario">
              <div id="div_rotulo_fuso_horario">
                <span id="span_rotulo_fuso_horario">Escolha um fuso horário:</span>
                <span id="span_escolher_fuso_horario" 
                      class="mensagem_da_operacao">{{mensagem_da_acao_escolher_fuso_horario}}</span>
              </div>
              <div id="div_caixa_de_selecao_fuso_horario">
                <select id="caixa_de_selecao_fuso_horario" name="fuso_horario" :value="vue_configuracoes.fuso_horario" 
                        @change="escolher_fuso_horario">
                  <option v-for="(valor, chave) in vue_configuracoes.fuso_horarios" :value="chave">{{valor}}</option>
                </select>
              </div>
            </div>
            <div id="div_escolher_visual">
              <div id="div_rotulo_visual">
                <span id="span_rotulo_visual">Escolha um visual:</span>
                <span id="span_escolher_visual" 
                      class="mensagem_da_operacao">{{mensagem_da_acao_escolher_visual}}</span>
              </div>
              <div id="div_caixa_de_selecao_visual">
                <select id="caixa_de_selecao_visual" name="visual" :value="vue_configuracoes.visual" 
                        @change="escolher_visual">
                  <option v-for="(valor, chave) in vue_configuracoes.visuais" :value="chave">{{valor}}</option>
                </select>
              </div>
            </div>
          </div>
          <div v-if="aba_selecionada === 'aba_perfil'" id="div_aba_perfil" class="conteudo_da_aba">
            <h2 id="h2_aba_perfil">
              <span>Editar perfil</span>
            </h2>
            <div id="div_editar_nome_de_usuario">
              <div id="div_rotulo_editar_nome_de_usuario">
                <span id="span_rotulo_editar_nome_de_usuario">Editar nome de usuário:</span>
                <span id="span_editar_nome_de_usuario" 
                      class="mensagem_da_operacao">{{mensagem_da_acao_editar_nome_de_usuario}}</span>
              </div>
              <div id="div_campo_editar_nome_de_usuario">
                <input type="text" id="campo_editar_nome_de_usuario" name="nome_de_usuario" 
                       v-model="vue_configuracoes.nome_de_usuario" placeholder="nome de usuário"/>
                <span>&nbsp;</span>
                <button id="botao_salvar_nome_de_usuario" @click="editar_nome_de_usuario" 
                        @mouseleave="remover_foco_do_botao">Salvar</button>
              </div>
            </div>
            <div id="div_exibir_nao_exibir_sexo">
              <div id="div_rotulo_exibir_nao_exibir_sexo">
                <span id="span_rotulo_exibir_nao_exibir_sexo">Exibir informação do sexo no perfil?</span>
                <span id="span_exibir_nao_exibir_sexo" 
                      class="mensagem_da_operacao">{{mensagem_da_acao_exibir_nao_exibir_sexo_no_perfil}}</span>
              </div>
              <div id="div_caixa_de_checagem_exibir_nao_exibir_sexo">
                <input v-if="vue_configuracoes.exibir_sexo_no_perfil == 'sim'" type="checkbox"
                       id="caixa_de_checagem_exibir_nao_exibir_sexo" checked="checked" 
                       name="exibir_sexo_no_perfil" @change="exibir_nao_exibir_sexo_no_perfil"/>
                <input v-else type="checkbox" id="caixa_de_checagem_exibir_nao_exibir_sexo" 
                       name="exibir_sexo_no_perfil" @change="exibir_nao_exibir_sexo_no_perfil"/>
                <span>&nbsp;</span>
                <label for="caixa_de_checagem_exibir_nao_exibir_sexo" @mousedown.prevent>Exibir</label>
              </div>
            </div>
            <div id="div_exibir_nao_exibir_email">
              <div id="div_rotulo_exibir_nao_exibir_email">
                <span id="span_rotulo_exibir_nao_exibir_email">Exibir endereço de e-mail no perfil?</span>
                <span id="span_exibir_nao_exibir_email" 
                      class="mensagem_da_operacao">{{mensagem_da_acao_exibir_nao_exibir_email_no_perfil}}</span>
              </div>
              <div id="div_caixa_de_checagem_exibir_nao_exibir_email">
                <input v-if="vue_configuracoes.exibir_email_no_perfil === 'sim'" type="checkbox" 
                       id="caixa_de_checagem_exibir_nao_exibir_email" checked="checked" 
                       name="exibir_email_no_perfil" @change="exibir_nao_exibir_email_no_perfil"/>
                <input v-else type="checkbox" id="caixa_de_checagem_exibir_nao_exibir_email" 
                       name="exibir_email_no_perfil" @change="exibir_nao_exibir_email_no_perfil"/>
                <span>&nbsp;</span>
                <label for="caixa_de_checagem_exibir_nao_exibir_email" @mousedown.prevent>Exibir</label>
              </div>
            </div>
          </div>
          <div v-if="aba_selecionada === 'aba_seguranca'" id="div_aba_seguranca" class="conteudo_da_aba">
            <h2 id="h2_aba_seguranca">
              <span>Opções de segurança</span>
            </h2>
            <div id="div_mudar_senha">
              <div id="div_rotulo_mudar_senha">
                <span id="span_rotulo_mudar_senha">Mudar senha</span>
              </div>
              <div id="div_rotulo_digite_sua_senha_atual">
                <span>Digite a sua senha atual:</span>
              </div>
              <div id="div_campo_senha_atual">
                <input id="campo_senha_atual" name="senha_atual" type="password" 
                       placeholder="senha atual" v-model="senha_atual"/>
              </div>
              <div id="div_rotulo_digite_a_nova_senha">
                <span>Digite a nova senha:</span>
              </div>
              <div id="div_campo_nova_senha">
                <input id="campo_nova_senha" name="nova_senha" type="password" 
                       placeholder="nova senha" v-model="nova_senha"/>
              </div>
              <div id="div_rotulo_digite_a_nova_senha_novamente">
                <span>Digite a nova senha novamente:</span>
              </div>
              <div id="div_campo_nova_senha_novamente">
                <input id="campo_nova_senha_novamente" name="nova_senha_novamente" type="password" 
                       placeholder="nova senha novamente" v-model="nova_senha_novamente"/>
              </div>
              <div id="div_botao_mudar_senha">
                <button type="button" id="botao_mudar_senha" @click="mudar_senha" 
                        @mouseleave="remover_foco_do_botao">Mudar Senha</button>
                <span>&nbsp;</span>
                <span id="span_mudar_senha" class="mensagem_da_operacao">{{mensagem_da_acao_mudar_senha}}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </TemplateLayout>
  <Head>
    <title>Configurações</title>
    <link :href="endereco_do_arquivo_css" type="text/css" rel="stylesheet"/>
  </Head>
</template>
