console.log('Core module js loaded');
document.addEventListener('livewire:init', () => {
    Livewire.hook('exception', () => {
        console.log('Exception occurred');
    })
    Livewire.hook('request', ({ fail }) => {
        fail(({status, content, preventDefault}) => {
            console.log(content)
            if (![413, 419, 422].includes(status)) {
                Livewire.dispatch('showErrorModal', {
                    payload: {
                        status: status,
                        title: `Server Error`,
                        message: `The server returned a ${status} error. Please contact the system admin for assistance.`,
                        content: `The server returned a ${status} error. Please contact the system admin for assistance.`,
                    }
                })
                preventDefault()
            }
        })
    })
})