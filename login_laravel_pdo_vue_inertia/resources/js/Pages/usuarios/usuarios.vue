<script>
  import TemplateLayout from "../template_layout.vue";
  import {Head} from "@inertiajs/vue3";
  
  export default{
    props: [
      "template_layout",
      "usuarios"
    ],
    components: {
      TemplateLayout,
      Head
    },
    data(){
      return{
        /* Propriedades obtidas dos controllers precisam ser recolocadas para prevenir o cache do inertia */
        vue_template_layout: this.template_layout,
        vue_usuarios: this.usuarios,
        
        /* Propriedades novas e seus valores iniciais */
        endereco_do_arquivo_css: "/css/" + this.template_layout.visual_escolhido + "/usuarios.css",
        
        durante_requisicao_ajax: false,
        segmento: 1,
        etapa_da_acao_carregar_mais_registros: "esperando_acao",
        
        /* Guardando backup de algumas propriedades */
        backup_filtro_nome_de_usuario: typeof this.usuarios.lista_de_usuarios != "undefined" ? this.usuarios.lista_de_usuarios.filtro_nome_de_usuario : null,
        backup_filtro_email_do_usuario: typeof this.usuarios.lista_de_usuarios != "undefined" ? this.usuarios.lista_de_usuarios.filtro_email_do_usuario : null,
        backup_filtro_tipo_de_usuario: typeof this.usuarios.lista_de_usuarios != "undefined" ? this.usuarios.lista_de_usuarios.filtro_tipo_de_usuario : null,
        backup_quantidade_por_segmento: typeof this.usuarios.lista_de_usuarios != "undefined" ? this.usuarios.lista_de_usuarios.quantidade_por_segmento : null
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
      remover_foco_do_botao(evento){
        evento.currentTarget.blur();
      },
      enviar_formulario_de_filtro(usar_valores_da_ultima_busca = false){
        let filtro_nome_de_usuario = this.vue_usuarios.lista_de_usuarios.filtro_nome_de_usuario;
        let filtro_email_do_usuario = this.vue_usuarios.lista_de_usuarios.filtro_email_do_usuario;
        let filtro_tipo_de_usuario = this.vue_usuarios.lista_de_usuarios.filtro_tipo_de_usuario;
        let quantidade_por_segmento = this.vue_usuarios.lista_de_usuarios.quantidade_por_segmento;
        let ordenacao = this.vue_usuarios.lista_de_usuarios.ordenacao;
        
        if(usar_valores_da_ultima_busca){
          /* Estes valores devem ser aqueles utilizados na última busca ao invés do que foi digitado após */
          filtro_nome_de_usuario = this.backup_filtro_nome_de_usuario;
          filtro_email_do_usuario = this.backup_filtro_email_do_usuario;
          filtro_tipo_de_usuario = this.backup_filtro_tipo_de_usuario;
          quantidade_por_segmento = this.backup_quantidade_por_segmento;
        }
        
        var elemento_formulario = document.createElement("form");
        elemento_formulario.setAttribute("class", "tag_oculta");
        elemento_formulario.setAttribute("method", "get");
        elemento_formulario.setAttribute("action", "/usuarios");
        
        let redirecionar = true;
        
        if(filtro_nome_de_usuario != ""){
          redirecionar = false;
          var elemento_campo = document.createElement("input");
          elemento_campo.setAttribute("name", "filtro_nome_de_usuario");
          elemento_campo.setAttribute("value", filtro_nome_de_usuario);
          elemento_formulario.appendChild(elemento_campo);
        }
        
        if(filtro_email_do_usuario != "" && this.vue_usuarios.lista_de_usuarios.mostrar_email){
          redirecionar = false;
          var elemento_campo = document.createElement("input");
          elemento_campo.setAttribute("name", "filtro_email_do_usuario");
          elemento_campo.setAttribute("value", filtro_email_do_usuario);
          elemento_formulario.appendChild(elemento_campo);
        }
        
        if(filtro_tipo_de_usuario != "todos"){
          redirecionar = false;
          var elemento_campo = document.createElement("input");
          elemento_campo.setAttribute("name", "filtro_tipo_de_usuario");
          elemento_campo.setAttribute("value", filtro_tipo_de_usuario);
          elemento_formulario.appendChild(elemento_campo);
        }
        
        if(quantidade_por_segmento != "padrao"){
          redirecionar = false;
          var elemento_campo = document.createElement("input");
          elemento_campo.setAttribute("name", "quantidade_por_segmento");
          elemento_campo.setAttribute("value", quantidade_por_segmento);
          elemento_formulario.appendChild(elemento_campo);
        }
        
        if(ordenacao != "padrao"){
          redirecionar = false;
          var elemento_campo = document.createElement("input");
          elemento_campo.setAttribute("name", "ordenacao");
          elemento_campo.setAttribute("value", ordenacao);
          elemento_formulario.appendChild(elemento_campo);
        }
        
        if(redirecionar){
          window.location.href = "/usuarios";
        }else{
          document.body.appendChild(elemento_formulario);
          elemento_formulario.submit();
        }
      },
      buscar(evento){
        evento.preventDefault();
        evento.currentTarget.blur();
        this.enviar_formulario_de_filtro();
      },
      limpar(evento){
        evento.preventDefault();
        evento.currentTarget.blur();
        window.location.href = "/usuarios";
      },
      ordenar_por_nome_de_usuario(evento){
        switch(this.vue_usuarios.lista_de_usuarios.ordenacao){
          case "nome_de_usuario_em_ordem_alfabetica":
            this.vue_usuarios.lista_de_usuarios.ordenacao = "nome_de_usuario_em_ordem_alfabetica_inversa";
            break;
          case "nome_de_usuario_em_ordem_alfabetica_inversa":
            this.vue_usuarios.lista_de_usuarios.ordenacao = "padrao";
            break;
          default:
            this.vue_usuarios.lista_de_usuarios.ordenacao = "nome_de_usuario_em_ordem_alfabetica";
            break;
        }
        this.enviar_formulario_de_filtro(true);
      },
      ordenar_por_email(evento){
        switch(this.vue_usuarios.lista_de_usuarios.ordenacao){
          case "email_em_ordem_alfabetica":
            this.vue_usuarios.lista_de_usuarios.ordenacao = "email_em_ordem_alfabetica_inversa";
            break;
          case "email_em_ordem_alfabetica_inversa":
            this.vue_usuarios.lista_de_usuarios.ordenacao = "padrao";
            break;
          default:
            this.vue_usuarios.lista_de_usuarios.ordenacao = "email_em_ordem_alfabetica";
            break;
        }
        this.enviar_formulario_de_filtro(true);
      },
      ordenar_por_momento_do_cadastro(evento){
        switch(this.vue_usuarios.lista_de_usuarios.ordenacao){
          case "momento_do_cadastro_em_ordem_cronologica":
            this.vue_usuarios.lista_de_usuarios.ordenacao = "momento_do_cadastro_em_ordem_cronologica_inversa";
            break;
          case "momento_do_cadastro_em_ordem_cronologica_inversa":
            this.vue_usuarios.lista_de_usuarios.ordenacao = "padrao";
            break;
          default:
            this.vue_usuarios.lista_de_usuarios.ordenacao = "momento_do_cadastro_em_ordem_cronologica";
            break;
        }
        this.enviar_formulario_de_filtro(true);
      },
      ordenar_por_tipo_de_usuario(evento){
        switch(this.vue_usuarios.lista_de_usuarios.ordenacao){
          case "tipo_em_ordem_alfabetica":
            this.vue_usuarios.lista_de_usuarios.ordenacao = "tipo_em_ordem_alfabetica_inversa";
            break;
          case "tipo_em_ordem_alfabetica_inversa":
            this.vue_usuarios.lista_de_usuarios.ordenacao = "padrao";
            break;
          default:
            this.vue_usuarios.lista_de_usuarios.ordenacao = "tipo_em_ordem_alfabetica";
            break;
        }
        this.enviar_formulario_de_filtro(true);
      },
      carregar_mais_registros(evento, usar_valores_da_ultima_busca = false){
        evento.preventDefault();
        
        let filtro_nome_de_usuario = this.vue_usuarios.lista_de_usuarios.filtro_nome_de_usuario;
        let filtro_email_do_usuario = this.vue_usuarios.lista_de_usuarios.filtro_email_do_usuario;
        let filtro_tipo_de_usuario = this.vue_usuarios.lista_de_usuarios.filtro_tipo_de_usuario;
        let quantidade_por_segmento = this.vue_usuarios.lista_de_usuarios.quantidade_por_segmento;
        let ordenacao = this.vue_usuarios.lista_de_usuarios.ordenacao;
        
        if(usar_valores_da_ultima_busca){
          /* Estes valores devem ser aqueles utilizados na última busca ao invés do que foi digitado após */
          filtro_nome_de_usuario = this.backup_filtro_nome_de_usuario;
          filtro_email_do_usuario = this.backup_filtro_email_do_usuario;
          filtro_tipo_de_usuario = this.backup_filtro_tipo_de_usuario;
          quantidade_por_segmento = this.backup_quantidade_por_segmento;
        }
        
        if(!this.durante_requisicao_ajax){
          this.segmento++;
          this.etapa_da_acao_carregar_mais_registros = "esperando_resposta";
          this.durante_requisicao_ajax = true;
          
          /* Requisição ajax */
          let conexao_ajax = null;
          if(window.XMLHttpRequest){
            conexao_ajax = new XMLHttpRequest();
          }else if(window.ActiveXObject){
            conexao_ajax = new ActiveXObject("Microsoft.XMLHTTP");
          }
          const tipo = "GET";
          let url_mais = "";
          url_mais += "?filtro_nome_de_usuario=" + filtro_nome_de_usuario;
          url_mais += "&filtro_email_do_usuario=" + filtro_email_do_usuario;
          url_mais += "&filtro_tipo_de_usuario=" + filtro_tipo_de_usuario;
          url_mais += "&quantidade_por_segmento=" + quantidade_por_segmento;
          url_mais += "&ordenacao=" + ordenacao;
          url_mais += "&segmento=" + this.segmento;
          let url = "/usuarios/mostrar_usuarios_ajax" + url_mais;
          let dados_post = null;
          let resposta = null;
          conexao_ajax.onreadystatechange = function(){
            if(conexao_ajax.readyState == 4){
              if(conexao_ajax.status == 200){
                resposta = JSON.parse(conexao_ajax.responseText);
                this.etapa_da_acao_carregar_mais_registros = "esperando_acao";
                if(typeof resposta.mensagem_de_falha != "undefined"){
                  alert(resposta.mensagem_de_falha);
                  window.location.href = "usuarios";
                }else{
                  if(resposta.lista_de_usuarios.usuarios.length > 0){
                    this.vue_usuarios.lista_de_usuarios.usuarios = this.vue_usuarios.lista_de_usuarios.usuarios.concat(resposta.lista_de_usuarios.usuarios);
                  }else{
                    this.etapa_da_acao_carregar_mais_registros = "totalmente_carregada";
                  }
                }
              }
              this.durante_requisicao_ajax = false;
            }
          }.bind(this);
          conexao_ajax.open(tipo, url, true);
          conexao_ajax.setRequestHeader("Content-Type", "application/json");
          conexao_ajax.setRequestHeader("X-CSRF-TOKEN", this.vue_template_layout.chave_anti_csrf);
          conexao_ajax.send(JSON.stringify(dados_post));
        }
      }
    }
  }
</script>

<template>
  <TemplateLayout :template_layout="vue_template_layout">
    <template #conteudo>
      <div id="div_mensagem" :class="vue_usuarios.mensagem_da_pagina ? '' : 'tag_oculta'">
        <span id="span_mensagem">{{vue_usuarios.mensagem_da_pagina}}</span>
      </div>
      <div v-if="vue_template_layout.usuario_esta_logado" id="div_usuarios">
        <h1 id="h1_titulo_da_pagina">
          <span>Usuários</span>
        </h1>
        <form id="form_opcoes_de_filtro" method="get" action="/usuarios">
          <h2 id="h2_titulo_filtros">
            <span>Buscar</span>
          </h2>
          <div id="div_filtro_nome_de_usuario">
            <div id="div_label_filtro_nome_de_usuario">
              <label id="label_filtro_nome_de_usuario" for="campo_filtro_nome_de_usuario">
                <span>Nome de usuário</span>
              </label>
            </div>
            <div id="div_campo_filtro_nome_de_usuario">
              <input type="text" id="campo_filtro_nome_de_usuario" name="filtro_nome_de_usuario" 
                     v-model="vue_usuarios.lista_de_usuarios.filtro_nome_de_usuario" autocomplete="off"/>
            </div>
          </div>
          <div v-if="vue_usuarios.lista_de_usuarios.mostrar_email" id="div_filtro_email_do_usuario">
            <div id="div_label_filtro_email_do_usuario">
              <label id="label_filtro_email_do_usuario" for="campo_filtro_email_do_usuario">
                <span>E-mail</span>
              </label>
            </div>
            <div id="div_campo_filtro_email_do_usuario">
              <input type="text" id="campo_filtro_email_do_usuario" name="filtro_email_do_usuario" 
                     v-model="vue_usuarios.lista_de_usuarios.filtro_email_do_usuario" autocomplete="off"/>
            </div>
          </div>
          <div id="div_filtro_tipo_de_usuario">
            <div id="div_label_filtro_tipo_de_usuario">
              <label id="label_filtro_tipo_de_usuario" for="caixa_de_selecao_filtro_tipo_de_usuario">
                <span>Tipo de usuário</span>
              </label>
            </div>
            <div id="div_caixa_de_selecao_filtro_tipo_de_usuario">
              <select id="caixa_de_selecao_filtro_tipo_de_usuario" name="filtro_tipo_de_usuario" 
                      v-model="vue_usuarios.lista_de_usuarios.filtro_tipo_de_usuario">
                <option value="todos">Selecione</option>
                <option v-for="(valor, chave) in vue_usuarios.tipos_de_usuario" :value="chave">{{valor}}</option>
              </select>
            </div>
          </div>
          <div id="div_quantidade_por_segmento">
            <div id="div_label_quantidade_por_segmento">
              <label id="label_quantidade_por_segmento" for="caixa_de_selecao_quantidade_por_segmento">
                <span>Quantidade por segmento</span>
              </label>
            </div>
            <div id="div_caixa_de_selecao_quantidade_por_segmento">
              <select id="caixa_de_selecao_quantidade_por_segmento" name="quantidade_por_segmento" 
                      v-model="vue_usuarios.lista_de_usuarios.quantidade_por_segmento">
                <option value="padrao">Selecione</option>
                <option v-for="(valor, chave) in vue_usuarios.quantidades_por_segmento" :value="chave">{{valor}}</option>
              </select>
            </div>
          </div>
          <div id="div_botoes">
            <input type="submit" id="botao_buscar" value="Buscar" @click="buscar" @mouseleave="remover_foco_do_botao"/>
            <span>&nbsp;</span>
            <input type="reset" id="botao_limpar" value="Limpar" @click="limpar" @mouseleave="remover_foco_do_botao"/>
          </div>
        </form>
        <div id="div_local_da_tabela_usuarios">
          <h2 id="h2_titulo_tabela_de_usuarios">
            <span>Tabela</span>
          </h2>
          <table id="tabela_usuarios">
            <thead>
              <tr>
                <th class="th_nome_de_usuario" @click="ordenar_por_nome_de_usuario" @mousedown.prevent>
                  <span class="span_ordenacao">{{vue_usuarios.lista_de_usuarios.ordem_do_nome_de_usuario}}</span>
                </th>
                <th v-if="vue_usuarios.lista_de_usuarios.mostrar_email" class="th_email" @click="ordenar_por_email" 
                    @mousedown.prevent>
                  <span class="span_ordenacao">{{vue_usuarios.lista_de_usuarios.ordem_do_email}}</span>
                </th>
                <th class="th_momento_do_cadastro" @click="ordenar_por_momento_do_cadastro" @mousedown.prevent>
                  <span class="span_ordenacao">{{vue_usuarios.lista_de_usuarios.ordem_do_momento_do_cadastro}}</span>
                </th>
                <th class="th_tipo" @click="ordenar_por_tipo_de_usuario" @mousedown.prevent>
                  <span class="span_ordenacao">{{vue_usuarios.lista_de_usuarios.ordem_do_tipo}}</span>
                </th>
              </tr>
            </thead>
            <tbody>
              <template v-for="(usuario, chave) in vue_usuarios.lista_de_usuarios.usuarios">
                <tr :class="(chave + 1) % 2 !== 0 ? 'impar' : 'par'">
                  <td class="td_tudo" colspan="4">
                    <span>Nome: </span>
                    <a class="link_nome_de_usuario" 
                       :href="'perfil?id=' + usuario.id_do_usuario">{{usuario.nome_de_usuario}}</a>
                    <br/>
                    <template v-if="vue_usuarios.lista_de_usuarios.mostrar_email">
                      <span>E-mail: </span>
                      <span class="span_email">{{usuario.email}}</span>
                      <br/>
                    </template>
                    <span>Cadastrado em: </span>
                    <span class="span_momento_do_cadastro">{{usuario.momento_do_cadastro}}</span>
                    <br/>
                    <span>Tipo: </span>
                    <span class="span_tipo">{{usuario.tipo}}</span>
                  </td>
                  <td class="td_nome_de_usuario">
                    <a class="link_nome_de_usuario" 
                       :href="'perfil?id=' + usuario.id_do_usuario">{{usuario.nome_de_usuario}}</a>
                  </td>
                  <td v-if="vue_usuarios.lista_de_usuarios.mostrar_email" class="td_email">
                    <span class="span_email">{{usuario.email}}</span>
                  </td>
                  <td class="td_momento_do_cadastro">
                    <span class="span_momento_do_cadastro">{{usuario.momento_do_cadastro}}</span>
                  </td>
                  <td class="td_tipo">
                    <span class="span_tipo">{{usuario.tipo}}</span>
                  </td>
                </tr>
              </template>
              <tr v-if="vue_usuarios.lista_de_usuarios.usuarios.length === 0" class="impar">
                <td id="td_mensagem_da_tabela" colspan="4">
                  <span id="span_mensagem_quando_nao_ha_usuarios">Nenhum usuário foi encontrado, limpe os filtros ou busque por outras informações.</span>
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <th class="th_nome_de_usuario">
                  <span>Nome de Usuário</span>
                </th>
                <th v-if="vue_usuarios.lista_de_usuarios.mostrar_email" class="th_email">
                  <span>E-mail</span>
                </th>
                <th class="th_momento_do_cadastro">
                  <span>Cadastrado em</span>
                </th>
                <th class="th_tipo">
                  <span>Tipo</span>
                </th>
              </tr>
            </tfoot>
          </table>
          <div v-if="etapa_da_acao_carregar_mais_registros === 'esperando_acao'" 
               id="div_link_carregar_mais_registros">
            <a id="link_carregar_mais_registros" href="" 
               @click="carregar_mais_registros($event, true)">Carregar mais registros</a>
          </div>
          <div v-if="etapa_da_acao_carregar_mais_registros === 'esperando_resposta'" 
               id="div_mensagem_de_carregamento_da_tabela">
            <span id="span_mensagem_de_carregamento_da_tabela">Carregando...</span>
          </div>
          <div v-if="etapa_da_acao_carregar_mais_registros === 'totalmente_carregada'" 
               id="div_mensagem_de_final_da_tabela">
            <span id="span_mensagem_de_final_da_tabela">A tabela chegou ao fim.</span>
          </div>
        </div>
      </div>
    </template>
  </TemplateLayout>
  <Head>
    <title>Usuários</title>
    <link :href="endereco_do_arquivo_css" type="text/css" rel="stylesheet"/>
  </Head>
</template>
