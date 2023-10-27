<script>
  import TemplateLayout from "../template_layout.vue";
  import {Head} from "@inertiajs/vue3";
  
  export default{
    props: [
      "template_layout",
      "perfil"
    ],
    components: {
      TemplateLayout,
      Head
    },
    data(){
      return{
        /* Propriedades obtidas dos controllers precisam ser recolocadas para prevenir o cache do inertia */
        vue_template_layout: this.template_layout,
        vue_perfil: this.perfil,
        
        /* Propriedades novas e seus valores iniciais */
        endereco_do_arquivo_css: "/css/" + this.template_layout.visual_escolhido + "/perfil.css",
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
    }
  }
</script>

<template>
  <TemplateLayout :template_layout="vue_template_layout">
    <template #conteudo>
      <div id="div_mensagem" :class="vue_perfil.mensagem_da_pagina ? '' : 'tag_oculta'">
        <span id="span_mensagem">{{vue_perfil.mensagem_da_pagina}}</span>
      </div>
      <div v-if="vue_template_layout.usuario_esta_logado && vue_perfil.usuario.nome_de_usuario" id="div_perfil">
        <h1 id="h1_titulo_da_pagina">
          <span>{{vue_perfil.usuario.nome_de_usuario}}</span>
        </h1>
        <div id="div_informacoes_do_usuario">
          <div id="div_local_do_id_do_usuario">
            <span id="span_rotulo_do_id_do_usuario">ID</span>
            <div id="div_id_do_usuario">
              <span id="span_id_do_usuario">{{vue_perfil.usuario.id}}</span>
            </div>
          </div>
          <div id="div_local_do_tipo_do_usuario">
            <span id="span_rotulo_do_tipo_do_usuario">Tipo</span>
            <span v-if="vue_perfil.mostrar_link_editar_tipo_de_usuario">&nbsp;</span>
            <a v-if="vue_perfil.mostrar_link_editar_tipo_de_usuario" id="link_editar_tipo_de_usuario" 
               :href="'editar_tipo_de_usuario?id=' + vue_perfil.usuario.id" title="Editar tipo de usuário"></a>
            <div id="div_tipo_do_usuario">
              <span id="span_tipo_do_usuario">{{vue_perfil.usuario.tipo}}</span>
            </div>
          </div>
          <div v-if="vue_perfil.usuario.exibir_sexo_no_perfil" id="div_local_do_sexo_do_usuario">
            <span id="span_rotulo_do_sexo_do_usuario">Sexo</span>
            <div id="div_sexo_do_usuario">
              <span id="span_sexo_do_usuario">{{vue_perfil.usuario.sexo}}</span>
            </div>
          </div>
          <div v-if="vue_perfil.usuario.exibir_email_no_perfil" id="div_local_do_email_do_usuario">
            <span id="span_rotulo_do_email_do_usuario">E-mail</span>
            <div id="div_email_do_usuario">
              <span id="span_email_do_usuario">{{vue_perfil.usuario.email}}</span>
            </div>
          </div>
          <div id="div_local_do_momento_do_cadastro_do_usuario">
            <span id="span_rotulo_do_momento_do_cadastro_do_usuario">Cadastrado em</span>
            <div id="div_momento_do_cadastro_do_usuario">
              <span id="span_momento_do_cadastro_do_usuario">{{vue_perfil.usuario.momento_do_cadastro}}</span>
            </div>
          </div>
        </div>
      </div>
    </template>
  </TemplateLayout>
  <Head>
    <title>Perfil {{vue_perfil.usuario.nome_de_usuario}}</title>
    <link :href="endereco_do_arquivo_css" type="text/css" rel="stylesheet"/>
  </Head>
</template>
