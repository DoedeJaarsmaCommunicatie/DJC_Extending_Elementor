const startfiltering = () => {
    let elements = document.querySelectorAll('[data-filter]')
    elements.forEach( el => {
        el.addEventListener('change', () => {
            let _checkedAll = document.querySelectorAll('[data-filter]:checked')
            let urlParams = new URLSearchParams()
            _checkedAll.forEach( checked => {
                urlParams.append( checked.dataset.filter, checked.dataset.filtrate )
            })
            window.location.search = urlParams.toString()
        })
    })
}
startfiltering()