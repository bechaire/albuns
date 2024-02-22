import { disableInput, humanFileSize, removeOptionByValue } from "./utils";

export function manipulaFormularioAlbum() {
    let formAlbum = document.querySelector('form.album-fotos');
    let containerUpload = document.querySelector('.container-upload');

    personalizaSelectStatus(formAlbum);
    personalizaContainerUpload(containerUpload);
}

function personalizaSelectStatus(formAlbum) {
    if (!formAlbum) return;
    
    let albumNovo = formAlbum.classList.contains('criando');
    let oStatus = formAlbum.elements['album[status]'];

    if (albumNovo) {
        disableInput(oStatus, 'C', 'C');
        return;
    }

    removeOptionByValue(oStatus, 'C');
}

function personalizaContainerUpload(container) {
    let progressInfo = {};

    progressInfo = {
        arquivos: [{size:2},{size:2},{size:2},{size:4},{size:4},{size:4},{size:4}],
        enviando: false,
        idxArquivoAtual: 5,
    };
    updateProgressBar(progressInfo);
}

function updateProgressBar(data) {
    let container = document.querySelector('.progressbar-container');

    if (!data?.arquivos.length) {
        container.style.display = 'none';
        return;
    }
    container.style.display = 'block';

    let percentualAtual = (!data.enviando) ? 0 : Math.ceil(((data.idxArquivoAtual+1) / data.arquivos.length) * 100);
    let bytesTotal = data.arquivos.reduce((acumulador, arquivo)=>acumulador+arquivo.size, 0);
    let totalMegabytes = humanFileSize(bytesTotal).replace(' ', '');

    let classProgresso = '';
    let textoProgresso = `&nbsp;&nbsp;Serão enviados ${data.arquivos.length} arquivos, num total de ${totalMegabytes}`;
    if (data.enviando) {
        classProgresso = 'bg-danger progress-bar-striped progress-bar-animated';
        textoProgresso = `Enviando imagem ${data.idxArquivoAtual+1} de ${data.arquivos.length} (${percentualAtual}% = 75MB de ${totalMegabytes})`;
    }

    container.innerHTML = `
    <div class="progress bg-secondary mt-1" role="progressbar" aria-label="Status do upload das imagens" aria-valuenow="${percentualAtual}" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar py-1 overflow-visible ${classProgresso}" style="width: ${percentualAtual}%">
            ${textoProgresso}
        </div>
    </div>
    `;
}