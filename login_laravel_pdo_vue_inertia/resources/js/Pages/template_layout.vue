<script>
import {Head} from "@inertiajs/vue3";

export default{
  props: [
    "template_layout"
  ],
  components: {
    Head
  },
  data(){
    return{
      /* Propriedades obtidas dos controllers precisam ser recolocadas para prevenir o cache do inertia */
      vue_template_layout: this.template_layout,
      
      /* Propriedades novas e seus valores iniciais */
      endereco_do_arquivo_css: "/css/" + this.template_layout.visual_escolhido + "/template_layout.css",
      
      mostrar_popup_mensagem: false,
      mostrar_popup_entrar: false,
      
      rolagem_horizontal_da_janela: 0,
      rolagem_vertical_da_janela: 0
    }
  },
  created(){
    window.addEventListener("click", function(evento){
      let tag_alvo = evento.target;
      
      ciclo:
      while(true){
        if(tag_alvo === null || !tag_alvo.tagName){
          this.remover_popups();
          break;
        }
        
        switch(tag_alvo.id){
          case "div_mensagem_do_sistema_template":
          case "link_entrar_template":
          case "div_entrar_template":
            break ciclo;
          break;
        }
        
        tag_alvo = tag_alvo.parentNode;
      }
    }.bind(this));
    
    window.addEventListener("resize", function(evento){
      this.rolagem_horizontal_da_janela = window.scrollX;
      this.rolagem_vertical_da_janela = window.scrollY;
      if(this.mostrar_popup_mensagem === true){
        this.mostrar_popup_mensagem = "reposicionar";
      }
      if(this.mostrar_popup_entrar === true){
        this.mostrar_popup_entrar = "reposicionar";
      }
    }.bind(this));
    
    window.addEventListener("scroll", function(evento){
      this.rolagem_horizontal_da_janela = window.scrollX;
      this.rolagem_vertical_da_janela = window.scrollY;
    }.bind(this));
  },
  mounted(){
    const body = document.getElementsByTagName("body")[0];
    const div_app_template = document.getElementById("div_app_template");
    const div_cabecalho_template = document.getElementById("div_cabecalho_template");
    const div_tronco_template = document.getElementById("div_tronco_template");
    
    /* O resize_observer é importante no Vue para detectar quando as larguras e alturas deixarão de ser automáticas. */
    const resize_observer = new ResizeObserver(function(){
      /* Enquanto as larguras e alturas forem automáticas não há como fazer ajustes, por isso retorna. */
      var estilo_computado = window.getComputedStyle(div_app_template);
      if(estilo_computado.height === "auto"){
        return;
      }
      
      /* Ajustando altura do tronco para preencher a parte vertical visível da tela. */
      let altura_minima = window.innerHeight;
      
      var estilo_computado = window.getComputedStyle(body);
      altura_minima -= parseInt(estilo_computado.marginTop, 10);
      altura_minima -= parseInt(estilo_computado.borderTopWidth, 10);
      altura_minima -= parseInt(estilo_computado.paddingTop, 10);
      
      var estilo_computado = window.getComputedStyle(div_cabecalho_template);
      altura_minima -= parseInt(estilo_computado.marginTop, 10);
      altura_minima -= parseInt(estilo_computado.borderTopWidth, 10);
      altura_minima -= parseInt(estilo_computado.paddingTop, 10);
      altura_minima -= parseInt(estilo_computado.height, 10);
      altura_minima -= parseInt(estilo_computado.paddingBottom, 10);
      altura_minima -= parseInt(estilo_computado.borderBottomWidth, 10);
      altura_minima -= parseInt(estilo_computado.marginBottom, 10);
      
      var estilo_computado = window.getComputedStyle(div_tronco_template);
      altura_minima -= parseInt(estilo_computado.marginTop, 10);
      altura_minima -= parseInt(estilo_computado.borderTopWidth, 10);
      altura_minima -= parseInt(estilo_computado.paddingTop, 10);
      altura_minima -= parseInt(estilo_computado.paddingBottom, 10);
      altura_minima -= parseInt(estilo_computado.borderBottomWidth, 10);
      altura_minima -= parseInt(estilo_computado.marginBottom, 10);
      
      div_tronco_template.style.minHeight = altura_minima + "px";
      
      /* Exibe mensagem caso exista alguma. */
      if(this.vue_template_layout.mensagem !== ""){
        this.mostrar_popup_mensagem = true;
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
    remover_popups(){
      this.mostrar_popup_mensagem = false;
      this.mostrar_popup_entrar = false;
    },
    exibir_popup_entrar(evento){
      evento.preventDefault();
      
      if(this.mostrar_popup_entrar === true){
        this.remover_popups();
        this.mostrar_popup_entrar = "reposicionar";
      }else{
        this.remover_popups();
        this.mostrar_popup_entrar = true;
      }
    }
  },
  watch: {
    mostrar_popup_mensagem(valor_atual, valor_anterior){
      if(valor_atual === "reposicionar"){
        /* Aciona o watch novamente */
        this.mostrar_popup_mensagem = true;
        return;
      }
      
      const div_mensagem_do_sistema_template = document.getElementById("div_mensagem_do_sistema_template");
      if(valor_atual === true){
        div_mensagem_do_sistema_template.classList.remove("tag_oculta");
        
        let largura_da_div = 0;
        div_mensagem_do_sistema_template.style.right = "10px";
        div_mensagem_do_sistema_template.style.marginLeft = "10px";
        var estilo_computado = window.getComputedStyle(div_mensagem_do_sistema_template);
        largura_da_div += parseInt(estilo_computado.borderLeftWidth, 10);
        largura_da_div += parseInt(estilo_computado.paddingLeft, 10);
        largura_da_div += parseInt(estilo_computado.width, 10);
        largura_da_div += parseInt(estilo_computado.paddingRight, 10);
        largura_da_div += parseInt(estilo_computado.borderRightWidth, 10);
        
        const tag_html = document.querySelector("html");
        
        let largura_da_tag_html = 0;
        var estilo_computado = window.getComputedStyle(tag_html);
        largura_da_tag_html += parseInt(estilo_computado.width, 10);
        
        let posicao_horizontal = largura_da_tag_html / 2 - largura_da_div / 2;
        posicao_horizontal = Math.floor(posicao_horizontal) - 1;
        div_mensagem_do_sistema_template.style.top = "100px";
        div_mensagem_do_sistema_template.style.right = posicao_horizontal + "px";
        div_mensagem_do_sistema_template.style.marginLeft = posicao_horizontal + "px";
      }else{
        div_mensagem_do_sistema_template.classList.add("tag_oculta");
      }
    },
    mostrar_popup_entrar(valor_atual, valor_anterior){
      if(valor_atual === "reposicionar"){
        /* Aciona o watch novamente */
        this.mostrar_popup_entrar = true;
        return;
      }
      
      const div_entrar_template = document.getElementById("div_entrar_template");
      if(valor_atual === true){
        div_entrar_template.classList.remove("tag_oculta");
        
        let largura_da_div = 0;
        var estilo_computado = window.getComputedStyle(div_entrar_template);
        largura_da_div += parseInt(estilo_computado.borderLeftWidth, 10);
        largura_da_div += parseInt(estilo_computado.paddingLeft, 10);
        largura_da_div += parseInt(estilo_computado.width, 10);
        largura_da_div += parseInt(estilo_computado.paddingRight, 10);
        largura_da_div += parseInt(estilo_computado.borderRightWidth, 10);
        
        const tag_html = document.querySelector("html");
        
        let largura_da_tag_html = 0;
        var estilo_computado = window.getComputedStyle(tag_html);
        largura_da_tag_html += parseInt(estilo_computado.width, 10);
        
        let posicao_direita = largura_da_tag_html / 2 - largura_da_div / 2;
        
        div_entrar_template.style.top = "100px";
        div_entrar_template.style.right = posicao_direita + "px";
      }else{
        div_entrar_template.classList.add("tag_oculta");
      }
    }
  }
}
</script>

<template>
  <Head>
    <link :href="endereco_do_arquivo_css" type="text/css" rel="stylesheet"/>
  </Head>
  
  <div id="div_cabecalho_template">
    <nav id="nav_menu_01_template">
      <a id="link_pagina_inicial_template" class="opcao_do_menu_01" href="/pagina_inicial">Início</a>
      <a v-if="vue_template_layout.usuario_esta_logado" id="link_usuarios_template" class="opcao_do_menu_01" 
         href="/usuarios">Usuários</a>
    </nav>
    <nav id="nav_menu_02_template">
      <template v-if="vue_template_layout.usuario_esta_logado">
        <a id="link_perfil_template" class="opcao_do_menu_02" 
           :href="'/perfil?id=' + vue_template_layout.id_do_usuario_logado">Perfil</a>
        <a id="link_configuracoes_template" class="opcao_do_menu_02" href="/configuracoes">Configurações</a>
        <a v-if="vue_template_layout.id_referencia" id="link_sair_template" class="opcao_do_menu_02" 
           :href="'/' + vue_template_layout.pagina_do_sistema + '/sair?id=' + vue_template_layout.id_referencia">Sair</a>
        <a v-else id="link_sair_template" class="opcao_do_menu_02" 
           :href="'/' + vue_template_layout.pagina_do_sistema + '/sair'">Sair</a>
      </template>
      <template v-else>
        <a id="link_cadastrar_template" class="opcao_do_menu_02" href="/cadastre-se">Cadastre-se</a>
        <a id="link_entrar_template" class="opcao_do_menu_02" href="" @click="exibir_popup_entrar">Entrar</a>
      </template>
    </nav>
  </div>
  <div id="div_tronco_template">
    <slot name="conteudo"></slot>
  </div>
  <div id="div_rodape_template">
    <div id="div_autor_do_sistema_template">
      <span>Este sistema foi feito por Rodrigo Diniz da Silva.</span>
    </div>
    <div id="div_tecnologias_do_sistema_template">
      <span>Este sistema usa PHP, Laravel, Vue e Inertia.</span>
    </div>
  </div>
  <div id="div_mensagem_do_sistema_template" :class="mostrar_popup_mensagem ? '' : 'tag_oculta'">
    <span id="span_mensagem_do_sistema_template">{{vue_template_layout.mensagem}}</span>
  </div>
  <div id="div_entrar_template" class="tag_oculta">
    <form id="form_entrar_template" method="post" 
          :action="'/' + vue_template_layout.pagina_do_sistema + '/entrar' + (vue_template_layout.id_referencia ? '?id=' + vue_template_layout.id_referencia : '')">
      <div id="div_nome_template">
        <div id="div_label_nome_template">
          <label id="label_nome_template" for="campo_nome_template">
            <span>Nome de usuário</span>
          </label>
        </div>
        <div id="div_campo_nome_template">
          <input type="text" id="campo_nome_template" name="nome_de_usuario"/>
        </div>
      </div>
      <div id="div_senha_template">
        <div id="div_label_senha_template">
          <label id="label_senha_template" for="campo_senha_template">
            <span>Senha</span>
          </label>
        </div>
        <div id="div_campo_senha_template">
          <input type="password" id="campo_senha_template" name="senha"/>
        </div>
      </div>
      <div id="div_botao_entrar_template">
        <input type="hidden" name="_token" :value="vue_template_layout.chave_anti_csrf"/>
        <input type="submit" id="botao_entrar_template" @click="remover_foco_do_botao" 
               @mouseleave="remover_foco_do_botao" value="Entrar"/>
      </div>
    </form>
  </div>
</template>
