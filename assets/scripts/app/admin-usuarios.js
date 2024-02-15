import DataTable from 'datatables.net-bs5';
import language from '../data/datatables-ptbr.json';

export function hidatateListOfUsers() {
    let config = {
        language,
        ajax: '/admin/usuarios',
        pageLength: 25,
        lengthMenu: [25, 50, 75, 100],
        order: [[0, 'desc'], [2, 'asc'], [1, 'asc']],
        columns: [
            { data: 'ativo', width: "8%", className: 'dt-center', searchable: false, render: renderAtivo  },
            { data: 'nome', width: "50%", className: 'dt-head-center dt-body-left', render: renderNome },
            { data: 'usuario', className: 'dt-center' },
            { data: null, width: "10%", className: 'dt-center', searchable: false, render: renderAdmin },
            { data: null, width: "7%", className: 'dt-center', searchable: false, orderable: false, render: renderOpcoes },
        ],
        createdRow: processaLinha,
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