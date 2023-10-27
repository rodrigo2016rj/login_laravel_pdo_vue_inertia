<script>
  import TemplateLayout from "../template_layout.vue";
  import {Head} from "@inertiajs/vue3";
  
  export default{
    props: [
      "template_layout",
      "cadastre_se"
    ],
    components: {
      TemplateLayout,
      Head
    },
    data(){
      return{
        /* Propriedades obtidas dos controllers precisam ser recolocadas para prevenir o cache do inertia */
        vue_template_layout: this.template_layout,
        vue_cadastre_se: this.cadastre_se,
        
        /* Propriedades novas e seus valores iniciais */
        endereco_do_arquivo_css: "/css/" + this.template_layout.visual_escolhido + "/cadastre-se.css"
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
      }
    }
  }
</script>

<template>
  <TemplateLayout :template_layout="vue_template_layout">
    <template #conteudo>
      <div id="div_mensagem" :class="vue_cadastre_se.mensagem_da_pagina ? '' : 'tag_oculta'">
        <span id="span_mensagem">{{vue_cadastre_se.mensagem_da_pagina}}</span>
      </div>
      <div id="div_cadastrar">
        <h1 id="h1_titulo_da_pagina">
          <span>Cadastre-se</span>
        </h1>
        <form id="form_cadastrar" method="post" action="/cadastre-se/cadastrar">
          <div id="div_nome">
            <div id="div_label_nome">
              <label id="label_nome" for="campo_nome">
                <span>Nome de usuário</span>
              </label>
            </div>
            <div id="div_campo_nome">
              <input type="text" id="campo_nome" name="nome_de_usuario" :value="vue_cadastre_se.nome_de_usuario" 
                     autocomplete="off"/>
            </div>
          </div>
          <div id="div_email">
            <div id="div_label_email">
              <label id="label_email" for="campo_email">
                <span>E-mail</span>
              </label>
            </div>
            <div id="div_campo_email">
              <input type="text" id="campo_email" name="email" :value="vue_cadastre_se.email" autocomplete="off"/>
            </div>
          </div>
          <div id="div_senha">
            <div id="div_label_senha">
              <label id="label_senha" for="campo_senha">
                <span>Senha</span>
              </label>
            </div>
            <div id="div_campo_senha">
              <input type="password" id="campo_senha" name="senha" autocomplete="off"/>
            </div>
          </div>
          <div id="div_senha_novamente">
            <div id="div_label_senha_novamente">
              <label id="label_senha_novamente" for="campo_senha_novamente">
                <span>Senha novamente</span>
              </label>
            </div>
            <div id="div_campo_senha_novamente">
              <input type="password" id="campo_senha_novamente" name="senha_novamente" autocomplete="off"/>
            </div>
          </div>
          <fieldset id="fieldset_sexo">
            <legend>Sexo</legend>
            <div id="div_lista_de_sexos">
            <template v-for="(sexo_valor, sexo_identificador) in vue_cadastre_se.array_sexos">
              <label class="label_com_botao_de_radio_sexo">
                <input v-if="sexo_identificador === vue_cadastre_se.sexo" type="radio" name="sexo" checked="checked" 
                       :value="sexo_identificador" autocomplete="off"/>
                <input v-else type="radio" name="sexo" :value="sexo_identificador" autocomplete="off"/>
                <span>&nbsp;</span>
                <span>{{sexo_valor}}</span>
              </label>
            </template>
            </div>
          </fieldset>
          <div id="div_botao_cadastrar">
            <input type="hidden" name="_token" :value="vue_template_layout.chave_anti_csrf"/>
            <input type="submit" id="botao_cadastrar" @click="remover_foco_do_botao" @mouseleave="remover_foco_do_botao" 
                   value="Cadastrar"/>
          </div>
        </form>
      </div>
    </template>
  </TemplateLayout>
  <Head>
    <title>Cadastre-se</title>
    <link :href="endereco_do_arquivo_css" type="text/css" rel="stylesheet"/>
  </Head>
</template>
