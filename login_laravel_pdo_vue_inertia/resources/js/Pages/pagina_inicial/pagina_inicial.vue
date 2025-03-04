<script>
  import TemplateLayout from "../template_layout.vue";
  import {Head} from "@inertiajs/vue3";
  
  export default{
    props: [
      "template_layout"
    ],
    components: {
      TemplateLayout,
      Head
    },
    data(){
      return{
        /* Propriedades obtidas dos controllers precisam ser recolocadas para prevenir o cache do inertia */
        vue_template_layout: this.template_layout,
        
        /* Propriedades novas e seus valores iniciais */
        endereco_do_arquivo_css: "/css/" + this.template_layout.visual_escolhido + "/pagina_inicial.css"
      }
    }
  }
</script>

<template>
  <TemplateLayout :template_layout="vue_template_layout">
    <template #conteudo>
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
