import Swal from "sweetalert2";
import bootstrap from 'bootstrap/dist/js/bootstrap.bundle';
import { adicionaOpcaoMover, disableInput, humanFileSize, removeOptionByValue } from "./utils";

export function manipulaFormularioAlbum() {
    // formulário de edição do álbum de fotos
    let formAlbum = document.querySelector('form.album-fotos');
    // container onde está o campo de upload de novas fotos
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

    oStatus.addEventListener('change', event=>{
        if (event.target.value == 'X') {
            Swal.fire({
                title: 'Atenção, o álbum será removido',
                text: 'O álbum será marcado para remoção e será excluído dentro de alguns dias, use o seletor "Exibir Excluídos" na listagem de álbuns para vê-lo e, se necessário, reverter esta marcação'
            });
        }
    });
}

function resetUploadInfo(uploadInfo) {
    uploadInfo.input.disabled = false;
    uploadInfo.button.disabled = false;
    uploadInfo.input.value = '';
    uploadInfo.enviando = false;
    uploadInfo.idxArquivoAtual = 0;
    uploadInfo.bytesEnviados = 0;
}

// o container contem o formulário para envio de fotos (input + botão)
// e também a barra de status que é atualizada a cada nova ação realizada com as fotos
// e, de forma automática, a cada novo upload realizado
function personalizaContainerUpload(containerUpload) {
    if (!containerUpload) return;

    let form = containerUpload.querySelector('form');
    let input = containerUpload.querySelector('input[type=file]');
    let button = containerUpload.querySelector('button');

    let uploadInfo = {
        enviando: false,
        enviosSimultaneos: 5,
        idxArquivoAtual: 0,
        bytesEnviados: 0,
        bytesTotais: 0,
        qtdFotosExistentes: 0,
        form,
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

    loadImages(uploadInfo);
}

// prepara os arquivos para serem enviados ao site, criando um vetor ordenado pelo nome dos arquivos
// cria um campo com o total em bytes a ser enviado ao site
function atualizaInformacoesUpload(uploadInfo) {
    //arquivos ordenados por nome
    uploadInfo.arquivos = [...uploadInfo.input.files].sort((a, b) => a.name.localeCompare(b.name));
    //bytes totais a serem enviados
    uploadInfo.bytesTotais = uploadInfo.arquivos.reduce((acumulador, arquivo) => acumulador + arquivo.size, 0);
    //atualiza visão da barra de status
    updateProgressBar(uploadInfo);
}

// atualiza a progressbar com a situação atual dos arquivos a serem enviados e já enviados
function updateProgressBar(data) {
    let container = document.querySelector('.progressbar-container');

    if (!data.input.files.length) {
        container.style.display = 'none';
        return;
    }
    container.style.display = 'block';

    let percentualAtual = (!data.enviando) ? 0 : Math.ceil((data.bytesEnviados / data.bytesTotais) * 100);
    if (percentualAtual > 100) {
        percentualAtual = 100;
    }
    let hBytesEnviar = humanFileSize(data.bytesEnviados).replace(' ', '');
    let hBytesEnviados = humanFileSize(data.bytesTotais).replace(' ', '');

    let classProgresso = '';
    let textoProgresso = `&nbsp;&nbsp;Serão enviados ${data.input.files.length} arquivos, num total de ${hBytesEnviados}`;
    if (data.enviando) {
        classProgresso = 'bg-danger progress-bar-striped progress-bar-animated';
        textoProgresso = `Enviando imagem ${data.idxArquivoAtual + 1} de ${data.input.files.length} (${percentualAtual}% = ${hBytesEnviar} de ${hBytesEnviados})`;
    }
    if (percentualAtual == 100) {
        classProgresso = 'bg-success';
        textoProgresso = `Foram enviadas ${data.input.files.length} imagens, num total de ${hBytesEnviados}`;
    }

    container.innerHTML = `
    <div class="progress bg-secondary mt-1" role="progressbar" aria-label="Status do upload das imagens" aria-valuenow="${percentualAtual}" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar py-1 overflow-visible ${classProgresso}" style="width: ${percentualAtual}%">
            ${textoProgresso}
        </div>
    </div>
    `;
}

// ação realizada pelo click do botão, prepara o envio dos arquivos baseado nos parâmetros recebidos
function enviarArquivos(uploadInfo) {
    if (!uploadInfo.input.files.length) {
        return;
    }

    uploadInfo.input.disabled = true;
    uploadInfo.button.disabled = true;

    var i;
    for (i = 0; i < uploadInfo.arquivos.length; i++) {
        let posicao = i + 1 + uploadInfo.qtdFotosExistentes;
        uploadInfo.arquivos[i].posicao = posicao;
        uploadInfo.arquivos[i].enviado = false;
    }

    uploadInfo.enviando = true;
    updateProgressBar(uploadInfo);

    for (i = 0; i < uploadInfo.enviosSimultaneos; i++) {
        realizaPost(uploadInfo);
    }
}

// realiza o post, arquivo por arquivo, do que estiver pendente na fila
// ao terminar o upload, invoca o próximo arquivo até a fila limpar
function realizaPost(uploadInfo) {
    let arquivo = uploadInfo.arquivos.shift();
    if (!arquivo) {
        if (uploadInfo.input.files.length == uploadInfo.idxArquivoAtual) {
            Swal.fire({
                title: 'Arquivos enviados',
                icon: 'success'
            }).then(result => {
                loadImages(uploadInfo);
                resetUploadInfo(uploadInfo);
            });
        }
        return;
    }

    let failMessage = (fileName, message) => {
        Swal.fire({
            title: `Falha ao enviar o arquivo ${fileName}`,
            html: `<p>Ocorreu uma falha ao enviar o arquivo <strong>"${fileName}"</strong>. ${message}</p>`,
            icon: "error"
        })
    };

    const formData = new FormData();
    formData.append('foto', arquivo);
    formData.append('posicao', arquivo.posicao);
    fetch(uploadInfo.form.action, {
        method: 'POST',
        body: formData,
    }).then(response => {
        if (response.ok) {
            uploadInfo.idxArquivoAtual++;
            uploadInfo.bytesEnviados += arquivo.size;
            updateProgressBar(uploadInfo);
            response.json().then(json => {
                console.log(json.message);
            });
            realizaPost(uploadInfo);
            return;
        }
        response.json().then(json => {
            failMessage(arquivo.name, json.message)
        })
    }).catch(error => {
        console.log(error)
        error.text().then(message => {
            failMessage(arquivo.name, message)
        })
    });
}

// ao abrir a página e ao término dos uploads, atualiza a grade de imagens do álbum
function loadImages(uploadInfo) {
    fetch(uploadInfo.form.action)
        .then(result => result.json())
        .then(json => {
            uploadInfo.qtdFotosExistentes = json.fotos.length;
            criaGradeFotos(json, uploadInfo)
        });
}

// cria a grade das fotos do álbum, adicionando os botões de edição para cada imagem
function criaGradeFotos(data, uploadInfo) {
    let container = document.querySelector('.container-fotos');
    let html = '';
    data.fotos.forEach(foto => {
        let opcoes = foto.opcoes ? JSON.parse(foto.opcoes) : { flipv: 0, fliph: 0, rotate: 0 };
        html += `<div class="p-1 mb-2 box-foto text-center position-relative" data-idalbum="${data.id}" data-idfoto="${foto.id}" data-visivel="${foto.visivel}" data-destaque="${foto.destaque}" data-ordem="${foto.ordem}" data-flipv="${opcoes.flipv}" data-fliph="${opcoes.fliph}" data-rotate="${opcoes.rotate}">
            <img src="${data.path_miniaturas}/${foto.identificador}.jpg" data-src="${data.path_normais}/${foto.identificador}.jpg" class="img-thumbnail">
        </div>`;
    });
    container.classList.add('p-1','d-flex','justify-content-between','justify-items-center','align-items-center','flex-wrap','align-items-center');
    container.innerHTML = html;
    
    // para todas as imagens, adiciona o recurso de mover as imagens
    adicionaOpcaoMover('.container-fotos', '.box-foto', () => {
        let imagensReposicionadas = consultaFotosReposicionadas();
        imagensReposicionadas.forEach(objeto=>{
            atualizaRegistroArquivoFoto(objeto, uploadInfo.form.action);
        });
    });

    inserirOpcoesEdicao(uploadInfo);
}

/**
 * AO MOVER UMA FOTO DE LUGAR, ESTA FUNÇÃO É INVOCADA QUE RETORNA OS OBJETOS QUE TIVERAM
 * SEUS LOCAIS ALTERADOS NA GRADE EXIBIDA, ESTA ORDEM DEVE SER REFLETIDA NO ÁLBUM PUBLICADO
 * @returns array
 */
function consultaFotosReposicionadas() {
    let itens = document.querySelectorAll('.box-foto');
    let objetosAlterados = [];
    for(let i=0; i<itens.length; i++) {
        if (i+1 != itens[i].dataset.ordem) {
            itens[i].dataset.ordem_nova = i+1;
            objetosAlterados.push( itens[i] );
        }
    }
    return objetosAlterados;
}

function inserirOpcoesEdicao(uploadInfo) {
    let boxFoto = document.querySelectorAll('.box-foto');
    let icons = {
        flipV:      'bi bi-arrow-down-up',
        flipH:      'bi bi-arrow-left-right',
        destacar:   'bi bi-star',
        destacado:  'bi bi-star-fill',
        bloquear:   'bi bi-ban',
        desbloquear:'bi bi-ban-fill',
        rotateL:    'bi bi-arrow-counterclockwise',
        rotateR:    'bi bi-arrow-clockwise',
        rotate180:  'bi bi-arrow-repeat',
        excluir:    'bi bi-trash3',
        ver:        'bi bi-eye',
    };

    boxFoto.forEach(itemFoto => {
        let opcoesLeft = document.createElement('div');
        let opcoesRight = document.createElement('div');

        let opcoesClassesPadrao = ['opcoes','position-absolute','d-flex','flex-column','bg-dark','bg-opacity-50','p-2','rounded'];
        opcoesLeft.classList.add(...opcoesClassesPadrao,'ms-4','top-50','start-0','translate-middle-y');
        opcoesRight.classList.add(...opcoesClassesPadrao,'me-4','top-50','end-0','translate-middle-y');

        opcoesLeft.innerHTML = `
            <a href="#" title="Foto destaque do álbum" class="option" data-option="destacar">
                <i class="${itemFoto.dataset.destaque == 'S' ? icons.destacado : icons.destacar}"></i>
            </a>
            <a href="#" title="Ver grande" class="option" data-option="ver">
                <i class="${icons.ver}"></i>
            </a>
            <a href="#" title="Bloquear foto / ocultar" class="option" data-option="bloquear">
                <i class="${itemFoto.dataset.visivel == 'S' ? icons.bloquear : icons.desbloquear}"></i>
            </a>
            <a href="#" title="Excluir" class="option" data-option="remover">
                <i class="${icons.excluir}"></i>
            </a>
        `;

        opcoesRight.innerHTML = `
            <a href="#" title="Girar para a esquerda" class="option" data-option="girar90e">
                <i class="${icons.rotateL}"></i>
            </a>
            <a href="#" title="Girar para a direita" class="option" data-option="girar90d">
                <i class="${icons.rotateR}"></i>
            </a>
            <a href="#" title="Girar 180 graus" class="option" data-option="girar180">
                <i class="${icons.rotate180}"></i>
            </a>
            <a href="#" title="Espelhar verticalmente" class="option" data-option="espelharV">
                <i class="${icons.flipV}"></i>
            </a>
            <a href="#" title="Espelhar horizontalmente" class="option" data-option="espelharH">
                <i class="${icons.flipH}"></i>
            </a>
        `;

        itemFoto.appendChild(opcoesLeft);
        itemFoto.appendChild(opcoesRight);
    });

    let removerFotoDestaque = () => {
        boxFoto.forEach(foto => {
            foto.dataset.destaque = 'N';
            foto.querySelector('[data-option=destacar] i').setAttribute('class', icons.destacar);
        });
    }

    let showModal = (titulo, conteudo) => {
        const oModal = document.querySelector('#modalAdmin');
        const modal = new bootstrap.Modal(oModal);
        oModal.querySelector('.modal-title').innerHTML = titulo;
        oModal.querySelector('.modal-body').innerHTML = conteudo;
        modal.show();
    }

    boxFoto.forEach(foto => {
        foto.querySelectorAll('.option').forEach(option => {
            option.addEventListener('click', event => {
                event.preventDefault();
                switch(option.dataset.option) {
                    case 'destacar':
                        if (foto.dataset.visivel != 'S') return;
                        removerFotoDestaque();
                        foto.dataset.destaque = 'S';
                        foto.querySelector('[data-option=destacar] i').setAttribute('class', icons.destacado);
                        break;
                    case 'ver' :
                        showModal('', `
                            <img src="${foto.querySelector('img').dataset.src}" class="img-thumbnail d-block mx-auto">
                        `)
                        return;
                    case 'bloquear':
                        foto.dataset.visivel = (foto.dataset.visivel=='S' ? 'N' : 'S'); 
                        let iconeBloqueio = (foto.dataset.visivel=='S' ? icons.bloquear : icons.desbloquear);
                        foto.querySelector('[data-option=bloquear] i').setAttribute('class', iconeBloqueio);
                        foto.dataset.destaque = 'N';
                        foto.querySelector('[data-option=destacar] i').setAttribute('class', icons.destacar);
                    break;
                    case 'remover':
                        Swal.fire({
                            title: 'Confirma a remoção desta imagem?',
                            html: 'Não será possível desfazer esta ação!<br>' +
                                   '<img src="'+foto.querySelector('img').src+'" class="mt-2 img-thumbnail">',
                            cancelButtonText: 'Cancelar',
                            showCancelButton: true,
                            confirmButtonText: 'Sim, quero REMOVER',
                            focusCancel: true
                        }).then(confirm=>{
                            if (confirm.isConfirmed) {
                                foto.dataset.excluir = 'S';
                                atualizaRegistroArquivoFoto(foto, uploadInfo.form.action);
                            }
                        });
                        return;
                    case 'girar90e':
                        foto.dataset.rotate = parseInt(foto.dataset.rotate) + 270;
                        if (foto.dataset.rotate==360) {
                            foto.dataset.rotate = 0;
                        } else if (foto.dataset.rotate>360) {
                            foto.dataset.rotate -= 360;
                        }
                        break;
                    case 'girar90d':
                        foto.dataset.rotate = parseInt(foto.dataset.rotate) + 90; 
                        if (foto.dataset.rotate>=360) {
                            foto.dataset.rotate = 0;
                        }
                        break;
                    case 'girar180':
                        foto.dataset.rotate = parseInt(foto.dataset.rotate) + 180; 
                        if (foto.dataset.rotate==360) {
                            foto.dataset.rotate = 0;
                        } else if (foto.dataset.rotate>360) {
                            foto.dataset.rotate -= 360;
                        }
                        break;
                    case 'espelharV':
                        foto.dataset.flipv = (foto.dataset.flipv == 1 ? 0 : 1);
                        break;
                    case 'espelharH':
                        foto.dataset.fliph = (foto.dataset.fliph == 1 ? 0 : 1);
                        break;
                }
                atualizaRegistroArquivoFoto(foto, uploadInfo.form.action);
            });
        })
    });
}

function atualizaRegistroArquivoFoto(objeto, action) {
    const opcoes = JSON.stringify(objeto.dataset);
    const method = objeto.dataset.excluir == 'S' ? 'DELETE' : 'PATCH';
    
    const formData = new FormData();
    formData.append('opcoes', opcoes);
    formData.append('_method', method);

    fetch(action, {
        method: 'POST',
        body: formData
    })
    .then(result=>result.json())
    .then(json=>{
        if (json.status=='success') {
            if (json.excluir == 'S') {
                objeto.parentNode.removeChild(objeto);
                return;
            }

            objeto.dataset.ordem = json.ordem;
            objeto.querySelector('img').src = `${json.path_miniaturas}/${json.identificador}.jpg`;
            objeto.querySelector('img').dataset.src = `${json.path_normais}/${json.identificador}.jpg`;

            return;
        }

        if (json.status && json.message) {
            Swal.fire({
                title: 'Falha ao realizar ação',
                icon: json.status,
                html: json.message,
            });
        }
    });

    //depois de fazer o que precisa fazer, atualiza o dataset, troca a imagem se necessário
}