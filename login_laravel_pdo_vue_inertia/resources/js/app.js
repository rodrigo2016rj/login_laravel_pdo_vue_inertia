import {createApp, h} from "vue";
import {createInertiaApp} from "@inertiajs/vue3";

/* Coloque uma tag title no seu inertia.blade.php */
const titulo_do_sistema = document.getElementsByTagName("title")[0].innerText;

createInertiaApp({
  title: (title) => `${titulo_do_sistema} - ${title}`, //Título do sistema mais título da página
  resolve: name => {
    const pages = import.meta.glob("./Pages/**/*.vue", {eager: true});
    return pages[`./Pages/${name}.vue`];
  },
  setup({el, App, props, plugin}){
    el.id = "div_app_template";
    delete el.dataset.page;
    return createApp({render: () => h(App, props)}).use(plugin).mount(el);
  }
});
