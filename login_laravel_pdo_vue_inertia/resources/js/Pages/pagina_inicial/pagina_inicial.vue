<script>
  import TemplateLayout from "../template_layout.vue";
  import {Head} from "@inertiajs/vue3";
  
  export default{
    props: [
      "template_layout",
      "pagina_inicial"
    ],
    components: {
      TemplateLayout,
      Head
    },
    data(){
      return{
        /* Propriedades obtidas dos controllers precisam ser recolocadas para prevenir o cache do inertia */
        vue_template_layout: this.template_layout,
        vue_pagina_inicial: this.pagina_inicial,
        
        /* Propriedades novas e seus valores iniciais */
        endereco_do_arquivo_css: "/css/" + this.template_layout.visual_escolhido + "/pagina_inicial.css"
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
  }
</script>

<template>
  <TemplateLayout :template_layout="vue_template_layout">
    <template #conteudo>
      <div id="div_mensagem" :class="vue_pagina_inicial.mensagem_da_pagina ? '' : 'tag_oculta'">
        <span id="span_mensagem">{{vue_pagina_inicial.mensagem_da_pagina}}</span>
      </div>
      <div id="div_pagina_inicial">
        <h1 id="h1_titulo_da_pagina">
          <span>Página Inicial</span>
        </h1>
        <div id="div_sobre_este_sistema">
          <p>
            Fiz este sistema para demonstrar uma das minhas formas de implementar: formulário de cadastro, 
            formulário de login, tabela com navegação contínua, página de configurações, abas, validações 
            e níveis de permissão. Para definir um usuário como sendo do tipo "dono", cadastre um usuário 
            e utilize um UPDATE via MySQL para alterar o valor da coluna tipo.
          </p>
          <p>
            Este sistema tem dois menus, um no topo esquerdo e outro no topo direito, ambos dentro da 
            div_cabecalho_template. No menu do topo esquerdo há um link para a página inicial (esta 
            página) e ao entrar no sistema (login) irá aparecer um link para a página de usuários que só 
            pode ser acessada por usuários cadastrados. No menu do topo direito há inicialmente os links: 
            Cadastre-se e Entrar. Após entrar (login), irão aparecer no menu do topo direito, os links: 
            Perfil, Configurações e Sair.
          </p>
        </div>
      </div>
    </template>
  </TemplateLayout>
  <Head>
    <title>Página Inicial</title>
    <link :href="endereco_do_arquivo_css" type="text/css" rel="stylesheet"/>
  </Head>
</template>
