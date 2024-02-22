import { disableInput, humanFileSize, removeOptionByValue } from "./utils";

export function manipulaFormularioAlbum() {
    let formAlbum = document.querySelector('form.album-fotos');
    let containerUpload = document.querySelector('.container-upload');

    ajustaCamposFormularioManutencaoAlbum(formAlbum);
    personalizaContainerUpload(containerUpload);
}

function ajustaCamposFormularioManutencaoAlbum(formAlbum) {
    if (!formAlbum) return;

    let albumNovo = formAlbum.classList.contains('criando');
    let oStatus = formAlbum.elements['album[status]'];

    if (albumNovo) {
        disableInput(oStatus, 'C', 'C');
        return;
    }

    removeOptionByValue(oStatus, 'C');
}

function personalizaContainerUpload(containerUpload) {
    if (!containerUpload) return;

    let input = containerUpload.querySelector('input[type=file]');
    let button = containerUpload.querySelector('button');

    let uploadInfo = {
        enviando: false,
        idxArquivoAtual: 0,
        bytesEnviados: 0,
        bytesTotais: 0,
        input,
        button
    };

    input.addEventListener('change', () => {
        atualizaInformacoesUpload(uploadInfo);
    });

    button.addEventListener('click', (event) => {
        event.preventDefault();
        enviarArquivos(uploadInfo);
    });

}

function atualizaInformacoesUpload(uploadInfo) {
    //arquivos ordenados por nome
    uploadInfo.arquivos = [...uploadInfo.input.files].sort((a, b) => a.name.localeCompare(b.name));
    //bytes totais a serem enviados
    uploadInfo.bytesTotais = uploadInfo.arquivos.reduce((acumulador, arquivo) => acumulador + arquivo.size, 0);
    //atualiza visão da barra de status
    updateProgressBar(uploadInfo);
}

function updateProgressBar(data) {
    let container = document.querySelector('.progressbar-container');

    if (!data.arquivos.length) {
        container.style.display = 'none';
        return;
    }
    container.style.display = 'block';

    let percentualAtual = (!data.enviando) ? 0 : Math.ceil(((data.idxArquivoAtual + 1) / data.arquivos.length) * 100);
    let totalMegabytes = humanFileSize(data.bytesTotais).replace(' ', '');

    let classProgresso = '';
    let textoProgresso = `&nbsp;&nbsp;Serão enviados ${data.arquivos.length} arquivos, num total de ${totalMegabytes}`;
    if (data.enviando) {
        classProgresso = 'bg-danger progress-bar-striped progress-bar-animated';
        textoProgresso = `Enviando imagem ${data.idxArquivoAtual + 1} de ${data.arquivos.length} (${percentualAtual}% = 75MB de ${totalMegabytes})`;
    }

    container.innerHTML = `
    <div class="progress bg-secondary mt-1" role="progressbar" aria-label="Status do upload das imagens" aria-valuenow="${percentualAtual}" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar py-1 overflow-visible ${classProgresso}" style="width: ${percentualAtual}%">
            ${textoProgresso}
        </div>
    </div>
    `;
}

function enviarArquivos(uploadInfo) {
    if (!uploadInfo.input.files.length) {
        return;
    }

    uploadInfo.input.disabled = true;
    uploadInfo.button.disabled = true;
}