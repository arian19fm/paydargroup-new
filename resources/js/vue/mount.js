// Isolated Vue component mounting.
//
// Vue is NOT a global app. A Blade view opts in by rendering a mount target:
//
//     <div data-vue-component="example-counter" data-props='{"start": 3}'></div>
//
// Each component is registered below as a *lazy* import. Vue and the
// component chunk are only downloaded when a matching target exists, so pages
// without widgets pay nothing. Props are read from the JSON in `data-props`
// (server-rendered, so all data stays SSR-driven).
//
// Rules (docs/FRONTEND.md):
//  - one widget = one mount target = one .vue file in resources/js/components
//  - no router, no Pinia, no shared global state
//  - the server-rendered markup inside the target should be a sensible
//    non-JS fallback where the widget is content, or empty where it is a tool
//
// Register components here. Keys are the values used in data-vue-component.
const registry = {
    // 'example-counter': () => import('../components/ExampleCounter.vue'),
};

export async function mountVueComponents(root = document) {
    const targets = root.querySelectorAll('[data-vue-component]');

    if (targets.length === 0) {
        return;
    }

    const { createApp } = await import('vue');

    for (const el of targets) {
        const name = el.dataset.vueComponent;
        const loader = registry[name];

        if (!loader) {
            console.warn(`[vue] no component registered for "${name}"`);
            continue;
        }

        let props = {};
        if (el.dataset.props) {
            try {
                props = JSON.parse(el.dataset.props);
            } catch (error) {
                console.error(`[vue] invalid data-props JSON on "${name}"`, error);
            }
        }

        const module = await loader();
        createApp(module.default, props).mount(el);
    }
}
