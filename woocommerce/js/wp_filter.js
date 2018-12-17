class Filter {

    constructor() {
        this.setters()
        this.addClickers()
        this._filters = []
    }

    setters() {
        this._elements = document.querySelectorAll('[data-filter]')
        this._target_field = document.querySelector('.js-filter-page-filter-form').dataset.filterTarget
        this._wordpress_filter = document.querySelector('.js-filter-page-filter-form').dataset.wordpressFilter
        this._limit_target = "data-limit"
    }

    addClickers() {
        try {
            try {
                this._elements.forEach(($element) => {
                    this.addClicker($element)
                })
            } catch ( e ) {
                reject(e)
            }

        } catch ( e ) {
            reject(e)
        }
    }

    addClicker($element) {
        $element.addEventListener('change', () => {
            try {
                let _checked = document.querySelectorAll('[data-filter]:checked')

                this.ajaxRequest(_checked)

            } catch ( e ) {
                reject(e)
            }
        })
    }

    fetchAllFilters() {
        this._filters = document.querySelector('[data-filter-attributes-list]').dataset.filterAttributesList
    }

    async ajaxRequest($checked) {
        let data = {filter: []}

        this.fetchAllFilters()

        $checked.forEach( el => {
            data.filter.push({
                filter: el.dataset.filter,
                filtrate: el.dataset.filtrate,
            })
        })

        data.helpers = {
            wp_filter: this._wordpress_filter,
            filters: this._filters,
        }

        const meta = {
            action: 'filtered_callback',
            data: data
        }

        await jQuery.post(djcee_ajax_object.ajax_url, meta, response => {
            if( response ) {
                this.fillTheContent(response.data)
                this.fixTheFilters(response.data)
                this.showACountOfProducts(response.data)
            }
        })
    }

    fillTheContent(content) {
        if( content ) {
            document.querySelector(`.${this._target_field}`).innerHTML = ''
            content.posts.data.forEach( element => {
                document.querySelector(`.${this._target_field}`).insertAdjacentHTML( 'beforeend', element )
            })
        }
    }

    showACountOfProducts( content) {
        if(content) {
            let span = document.querySelector('.js__target__count')
            let string = span.dataset.string
            span.innerHTML = `${content.posts.count} ${string}`
        }
    }

    fixTheFilters(filter) {

        let existing_filters = []

        filter.filters.forEach( filterList => {
            filterList.forEach( filter => {
                if( !existing_filters[filter.taxonomy]) {
                    existing_filters[filter.taxonomy] = []
                }
                existing_filters[filter.taxonomy].push(filter.slug)
            })
        })

        for(let key in existing_filters)
        {
            let $container = document.querySelector(`[data-filter="${key}"]`).parentNode.parentNode

            $container.querySelectorAll('li').forEach( el => {
                el.classList.add('js-list__item__hidden')
            })

            existing_filters[key].forEach( active => {
                $container.querySelector(`[data-filtrate="${active}"]`).parentNode.classList.remove('js-list__item__hidden')
            })
        }
    }

    log(msg) {
        console.log(msg)
    }
}

new Filter()