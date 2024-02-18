import DataTable from 'datatables.net-bs5';
import language from '../data/datatables-ptbr.json';
import Swal from "sweetalert2";
import { slugify } from './utils';

export function hidatateListOfAlbuns() {
    let config = {
        language,
        ajax: '/admin/albuns',
        pageLength: 25,
        lengthMenu: [25, 75, 250, 1000],
        order: [[0, 'desc'], [2, 'asc'], [1, 'asc']],
        columns: [
            { data: 'data', className: 'dt-center', render: renderDate },
            { data: 'instituicao', className: 'dt-center' },
            { data: 'titulo', className: 'dt-head-center dt-body-left', render: renderTitulo },
            { data: 'acessos', className: 'dt-center', searchable: false },
            { data: null, className: 'dt-center', searchable: false, orderable: false, render: renderOpcoes },
        ],
        initComplete: adicionaFiltroRemovidos,
        createdRow: processaLinha,
    }
    new DataTable('#datatable-albuns', config);
}

function renderDate(data, type, row) {
    let dateObj = new Date(data);
    let dateBR = dateObj.toLocaleDateString();
    let monthBR = dateObj.toLocaleDateString('pt-BR', { month: 'long' });

    //também busca pelo status
    if (type == 'display') {
        return dateBR;
    }

    //se estiver filtrando... busca pela data em ambos os formatos d/m/Y Y-m-d
    //também adiciono o caractere "不" em todos os registros não marcados como excluídos
    //depois, filtro para listar todos os registros que tiverem "不" (não [excluídos])
    //adiciono um botão, e quando clicado, busco por todos os registros independente deste marcador
    //isse teve que ser feito assim pois a busca no campo é aproximada e portanto se eu usasse a palavra "listar" ou algo assim, na busca por essa palavra tudo seria exibido, inclusive o que não divesse essa palavra visível na tabela 
    return data + ' ' + dateBR + ' ' + monthBR + ' ' + (row.status != 'X' ? '不' : '');
}

function renderTitulo(data, type, row) {
    if (type != 'display') {
        //remove espaços iniciais e remove aspas que puderem existir no texto
        let result = data.trim().replaceAll(/["']/g, '');
        //remove acentos para que a ordenação ou busca não precise ser exata
        result = result.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        //o retorno é o texto com acentos removidos e com acento, então a busca será feita por todo o resultado (com ou sem acento)
        return result + ' ' + data;
    }
    return `<a href="/album/${row.id}-${slugify(row.titulo)}" target="_blank" class="text-decoration-none text-dark">${data}</a>
        <span class="badge text-bg-light cursor-help" title="Quantidade de fotos"> ${row.qtdfotos}</span>
    `;
}

function renderOpcoes(data, type, row) {
    if (type != 'display') {
        return data;
    }

    if (row.status == 'X') {
        return `<a type="button" class="btn btn-secondary btn-sm w-100 text-start" href="/admin/album/${row.id}">Editar</a>`;
    }

    return `<div class="btn-group">
      <a type="button" class="btn btn-secondary btn-sm" href="/admin/album/${row.id}">Editar</a>
      <a type="button" class="btn btn-secondary btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="visually-hidden">Toggle Dropdown</span>
      </a>
      <ul class="dropdown-menu">
        ${row.status == 'A' ? `
            <li><a class="dropdown-item" href="/album/${row.id}" target="_blank"><i class="bi bi-eye"></i> Ver publicação</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item btn-desativar" href="#"><i class="bi bi-eye-slash"></i> Ocultar/Desativar</a></li>
            <li><hr class="dropdown-divider"></li>
        ` : ''}
        <li><a class="dropdown-item bg-warning-subtle btn-excluir" href="#"><i class="bi bi-trash"></i> Excluir álbum</a></li>
      </ul>
    </div>`;
}

function adicionaFiltroRemovidos(settings) {
    this.api().column(0).search('不').draw();
    let checkSwitch = `<div class="form-check form-switch d-inline-block ms-3 cursor-pointer">
        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="switchCheckExcluidos">
        <label class="form-check-label cursor-pointer" for="switchCheckExcluidos">Exibir Excluídos</label>
    </div>`;

    document.querySelector("#datatable-albuns_length").insertAdjacentHTML('beforeend', checkSwitch);

    document.querySelector('#switchCheckExcluidos').addEventListener('change', e => {
        if (e.target.checked) {
            this.api().column(0).search('').draw();
            return;
        }

        this.api().column(0).search('不').draw();
    });
}

function processaLinha(row, data, index) {
    row.querySelector('.btn-desativar')?.addEventListener('click', (event) => {
        event.preventDefault();
        desativarAlbum(data);
    });

    row.querySelector('.btn-excluir')?.addEventListener('click', (event) => {
        event.preventDefault();
        excluirAlbum(data);
    });

    let classStatus = ({ I: "inativo", C: "criado", X: "excluido" })[data.status];
    if (classStatus) {
        row.classList.add('album-' + classStatus);
    };
}

function desativarAlbum(registro) {
    console.log('desativar: ' + registro.titulo)
}

function excluirAlbum(registro) {
    Swal.fire({
        title: `Excluir o álbum "${registro.id}"?`,
        html: `<p class="text-justify">Ao excluir o álbum <strong>"${registro.titulo}"</strong>, ele entrará na fila de exclusão e será removido dentro de alguns dias, caso se arrependa, clique no seletor "Exibir Excluídos" e reative o álbum clicando em "Editar"</p>`,
        icon: "error",
        showCancelButton: true,
        cancelButtonText: "cancelar",
        // confirmButtonColor: "#3085d6",
        // cancelButtonColor: "#d33",
        confirmButtonText: "Sim, quero remover"
    }).then(result => {
        if (result.isConfirmed) {
            
        }
    });
}
