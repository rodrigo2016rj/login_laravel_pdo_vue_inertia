import {createSSRApp, h} from "vue";
import {renderToString} from "@vue/server-renderer";
import {createInertiaApp} from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";

/* Coloque uma tag title no seu inertia.blade.php */
const titulo_do_sistema = document.getElementsByTagName("title")[0].innerText;

createServer((page) =>
  createInertiaApp({
    page,
    render: renderToString,
    title: (title) => `${titulo_do_sistema} - ${title}`, //Título do sistema mais título da página
    resolve: name => {
      const pages = import.meta.glob("./Pages/**/*.vue", {eager: true});
      return pages[`./Pages/${name}.vue`];
    },
    setup({App, props, plugin}){
      return createSSRApp({render: () => h(App, props)}).use(plugin);
    }
  })
);
