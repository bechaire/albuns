// import é ecma pattern, preferir
// require é node
import '../styles/app.scss';

//import 'bootstrap/scss/bootstrap.scss'; //ou aqui, no lugar do @import no app.scss
import bootstrap from 'bootstrap/dist/js/bootstrap.bundle';

//npm install fontawesome-free-6.2.1 --save-dev
//npm run dev

//import 'fontawesome-free-6.2.1/js/all.js';

document.addEventListener('DOMContentLoaded', function () {
    //inicialização de elementos que usam a biblioteca popper.js
    document.querySelectorAll('[data-bs-toggle]').forEach(function(el){
        let initialize = ['Tooltip', 'Popover'];
        initialize.forEach(init=>{
            if (el.getAttribute('data-bs-toggle') == init.toLowerCase()) {
                new bootstrap[init](el);
            }
        });
    });
});
