<script>
  import TemplateLayout from "../template_layout.vue";
  import {Head} from "@inertiajs/vue3";
  
  export default{
    props: [
      "template_layout",
      "editar_tipo_de_usuario"
    ],
    components: {
      TemplateLayout,
      Head
    },
    data(){
      return{
        /* Propriedades obtidas dos controllers precisam ser recolocadas para prevenir o cache do inertia */
        vue_template_layout: this.template_layout,
        vue_editar_tipo_de_usuario: this.editar_tipo_de_usuario,
        
        /* Propriedades novas e seus valores iniciais */
        endereco_do_arquivo_css: "/css/" + this.template_layout.visual_escolhido + "/editar_tipo_de_usuario.css",
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
  <TemplateLayout :template_layout="template_layout">
    <template #conteudo>
      <div id="div_mensagem" :class="vue_editar_tipo_de_usuario.mensagem_da_pagina ? '' : 'tag_oculta'">
        <span id="span_mensagem">{{vue_editar_tipo_de_usuario.mensagem_da_pagina}}</span>
      </div>
      <div v-if="vue_template_layout.usuario_esta_logado && vue_editar_tipo_de_usuario.mostrar_formulario" 
           id="div_editar_tipo_de_usuario">
        <h1 id="h1_titulo_da_pagina">
          <span>Editar tipo do usuário {{vue_editar_tipo_de_usuario.nome_de_usuario}}</span>
        </h1>
        <form id="form_editar_tipo_de_usuario" method="post" action="/editar_tipo_de_usuario/editar">
          <div id="div_tipo_de_usuario">
            <div id="div_label_tipo_de_usuario">
              <label id="label_tipo_de_usuario" for="caixa_de_selecao_tipo_de_usuario">
                <span>Tipo de usuário</span>
              </label>
            </div>
            <div id="div_caixa_de_selecao_tipo_de_usuario">
              <select id="caixa_de_selecao_tipo_de_usuario" name="tipo" :value="vue_editar_tipo_de_usuario.tipo" 
                      autocomplete="off">
                <option v-for="(valor, chave) in vue_editar_tipo_de_usuario.tipos_de_usuario" :value="chave">{{valor}}</option>
              </select>
            </div>
          </div>
          <div id="div_botao_editar">
            <input type="hidden" name="_token" :value="vue_template_layout.chave_anti_csrf"/>
            <input type="hidden" id="campo_id_do_usuario" name="pk_usuario" :value="vue_editar_tipo_de_usuario.id"/>
            <input type="submit" id="botao_editar" @click="remover_foco_do_botao" @mouseleave="remover_foco_do_botao" 
                   value="Editar"/>
          </div>
        </form>
      </div>
    </template>
  </TemplateLayout>
  <Head>
    <title>Editar tipo de usuário</title>
    <link :href="endereco_do_arquivo_css" type="text/css" rel="stylesheet"/>
  </Head>
</template>
