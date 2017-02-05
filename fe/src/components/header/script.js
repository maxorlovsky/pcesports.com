Vue.component('header-component', {
    template: '#header-template',
    data: function() {
        return {
            menu: {}
        };
    },
    created: function() {
        return this.fetchData();
    },
    methods: {
        fetchData: function() {
            let self = this;
            
            axios.get('https://api.pcesports.com/wp/wp-json/pce-api/menu')
            .then((links) => {
                let returnMenu = [];

                for (let value of links.data) {
                    let menuItem = {
                        'title': value.title,
                        'url': value.url.replace('http://', ''),
                        'css_classes': value.classes,
                        'target': value.target,
                        'slug': value.post_name,
                        'sublinks': []
                    };
                    /*'title'         => $v['title'],
                                'url'           => str_replace('http://', '', $v['url']),
                                'css_classes'   => implode(' ', $v['classes']),
                                'target'        => $v['target'],
                                'slug'          => $v['post_name'],
                                'sublinks'      => array(),*/

                    returnMenu.push(menuItem);
                }
                
                self.menu = returnMenu;
            });
        }
    }
});