import DataTable from 'datatables.net-bs5';
import language from '../data/datatables-ptbr.json';
import Swal from 'sweetalert2';

export function hidratarListaDeUsuarios() {
    let config = {
        language,
        ajax: '/admin/usuarios',
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, 'desc'], [1, 'asc']],
        columns: [
            { data: 'ativo', width: "8%", className: 'dt-center', searchable: false, render: renderAtivo  },
            { data: 'nome', width: "50%", className: 'dt-head-center dt-body-left', render: renderNome },
            { data: 'usuario', className: 'dt-center' },
            { data: null, width: "10%", className: 'dt-center', searchable: false, render: renderAdmin },
            { data: null, width: "7%", className: 'dt-center', searchable: false, orderable: false, render: renderOpcoes },
        ],
        createdRow: processaLinha,
        initComplete: personalizaGrid,
    }
    new DataTable('#datatable-usuarios', config);
}

function processaLinha(row, data, index) {
    if (data.ativo != 'S') {
        row.classList.add('usuario-inativo');
    };
}

function renderNome(data, type, row) {
    if (type != 'display') {
        //remove espaços iniciais e remove aspas que puderem existir no texto
        let result = data.trim().replaceAll(/["']/g, '');
        //remove acentos para que a ordenação ou busca não precise ser exata
        result = result.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        //o retorno é o texto com acentos removidos e com acento, então a busca será feita por todo o resultado (com ou sem acento)
        return result + ' ' + data;
    }
    return data;
}

function renderOpcoes(data, type, row) {
    return `<a type="button" class="btn btn-secondary btn-sm" href="/admin/usuarios/${row.id}">Editar</a>`;
}

function renderAtivo(data, type, row) {
    return data == 'S' ? 'Sim' : 'Não';
}

function renderAdmin(data, type, row) {
    return (data.roles.indexOf('ROLE_ADMIN') >= 0) ? 'Sim' : 'Não';
}

function personalizaGrid(settings) {
    adicionaBotaoNovoUsuario();
    adicionaBotaoLimparCache();
}

function adicionaBotaoNovoUsuario() {
    let addButton = `<a href="/admin/usuarios/new" class="btn btn-primary opacity-75 btn-sm align-top ms-3">
        <i class="bi bi-shield-plus"></i> Adicionar Usuário
    </a>`;

    document.querySelector("#datatable-usuarios_length").insertAdjacentHTML('beforeend', addButton);
}

function adicionaBotaoLimparCache() {
    let addButton = `<a href="/admin/albuns/purge" id="btn-purge-cache" class="btn btn-primary opacity-75 btn-sm align-top ms-3">
        <i class="bi bi-eraser"></i> Limpar cache
    </a>`;

    document.querySelector("#datatable-usuarios_length").insertAdjacentHTML('beforeend', addButton);

    document.querySelector("#btn-purge-cache").addEventListener('click', event=>{
        event.preventDefault();
        Swal.fire({
            title: 'Leia com atenção',
            icon: 'warning',
            html: `Apesar de segura, use esta funcionalidade com cautela, limpar o cache de TODAS as fotos geradas <b>vai economizar espaço em disco</b> mas forcará que os próximos acessos consumam <i>momentaneamente</i> recursos de processamento para regearar as imagens públicas`,
            cancelButtonText: 'Cancelar',
            showCancelButton: true,
            confirmButtonText: 'Sim, quero LIMPAR',
            focusCancel: true,
            showLoaderOnConfirm: true,
            preConfirm: async ()=> {
                await fetch(event.target.href, {
                    method: 'POST'
                })
                .then(result=>result.json())
                .then(json=>{
                    if (json.status == 'success') {
                        Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: 'As imagens do cache foram excluídas',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                })
                .catch(error=>{
                    Swal.fire({
                        position: "top-end",
                        icon: "error",
                        title: `Falha ao limpar o cache, contate a equipe de TI (${error.message})`,
                        showConfirmButton: false,
                        timer: 1500
                    });
                })
            },
            allowOutsideClick: ()=>Swal.isLoading()
        });
    })
}