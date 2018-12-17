class Limiter {

    constructor() {
        this._target = "data-limit"
    }

    async gottaFindEmAll() {
        const elements = document.querySelectorAll(`[${this._target}]`)
        
        elements.forEach( (element) => {

            let thisLimit = element.dataset.limit
            this.hideEmAll(element, thisLimit)
        })
    }

    async hideEmAll(parent, limit)
    {
        if (parent.childElementCount > limit ) {
            const elements = parent.querySelectorAll('li')
            const button = parent.querySelector('[data-toggle-arrow]')

            parent.classList.add('collapse__toggled')
            button.classList.remove('js-toggle__button__hidden')

            elements.forEach( (element, key) => {
                if (key >= limit) {
                    element.classList.toggle('js-list__item__hidden')
                }
            })

            this.addButtonListener(button, parent)
        } 
    }

    addButtonListener(button, list) {
        button.addEventListener('click', (e) => {
            e.preventDefault()

            if( list.classList.contains('collapse__toggled') ) {
                list.querySelectorAll('li').forEach( element => {
                    element.classList.remove('js-list__item__hidden')
                })
            } else {
                const elements = list.querySelectorAll('li')

                elements.forEach( (element, key) => {
                    if (key > list.dataset.limit) {
                        element.classList.toggle('js-list__item__hidden')
                    }
                })
            }

            list.classList.toggle('collapse__toggled')
        });
    }


}

document.addEventListener('DOMContentLoaded', () => {
    new Limiter().gottaFindEmAll()
})
// Uncomment ^ for production and limiter testing.