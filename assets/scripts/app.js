import '../styles/app.scss';

import bootstrap from 'bootstrap/dist/js/bootstrap.bundle';

// import jQuery from 'jquery';

import { hidratarListaDeAlbuns } from './app/admin-home';
import { hidratarListaDeUsuarios } from './app/admin-usuarios';
import { manipulaFormularioAlbum } from './app/admin-albuns';

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

    hidratarListaDeAlbuns();
    hidratarListaDeUsuarios();
    manipulaFormularioAlbum();
});
