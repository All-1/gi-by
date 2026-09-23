'use strict';

let buttonItems = document.querySelectorAll('.button_item');
let contentItems = document.querySelectorAll('.content_items');
buttonItems.forEach(button => {
  button.addEventListener('click', async () => {
    let value = button.value;
    buttonItems.forEach(button => {
      button.classList.remove('active');
    });
    button.classList.add('active');
    let contentItem = document.querySelector('.content_item');
    let result = await ajaxRequest(value);
    contentItem.innerHTML = '';
    renderResult(result, contentItem);

  });
});

deleteImage();
deleteCommode();
updateCommode();

function deleteImage() {
  let contentItem = document.querySelector('.content_item');
  let imageBlock = contentItem.querySelector('.image');
  let image = imageBlock.querySelector('img');
  let deleteImage = contentItem.querySelector('.delete_image');
  let input = createTagHtml('input', 'file', '', '', 'file', ['name', 'newFiles[]'], ['multiple', 'false']);
  deleteImage.addEventListener('click', () => {
    image.remove();
    deleteImage.remove();
    imageBlock.appendChild(input);
  });
}

function renderResult(result, contentItem) {
  console.log(result);
  let item = result[0];
  let visualisation = result.visualisation;
  let thumbsFolder = result.thumbs_folder;

  let divFiles = createTagHtml('div', 'files');
  if (visualisation.length > 0) {
    for (let file of visualisation) {
      let divFile = createTagHtml('div', 'file');
      let img = createTagHtml('img', '', file.url_thumb, '', '', ['src', file.url_thumb]);
      let input = createTagHtml('input', 'checkbox', '', '', '', ['type', 'checkbox'], ['name', 'deleteGallery[]'], ['value', file.id]);
      let inputName = createTagHtml('input', '', '', '', '', ['type', 'text'], ['name', 'nameVisualisation[' + file.id + ']'], ['value', file.name_visual]);
      let inputRank = createTagHtml('input', '', '', '', '', ['type', 'text'], ['name', 'rankVisualisation[' + file.id + ']'], ['value', file.rank_visual]);
      massAppendChild(divFile, img, input, inputName, inputRank);
      divFiles.appendChild(divFile);
    }
  }
  let divUploadGallery = createTagHtml('div', 'upload_gallery');
  let inputUploadGallery = createTagHtml('input', '', '', '', 'file', ['name', 'new_gallery[]']);
  let h2UploadGallery = createTagHtml('h2', '', 'Добавить новые фотографии в галерею:', '', '', ['style', 'margin-bottom: 10px;']);

  let form = createTagHtml('form', '', '', '', '', ['method', 'POST']);
  let divImage = createTagHtml('div', 'image');
  let deleteImageBlock = createTagHtml('div', 'delete_image');
  let cross = createTagHtml('div', 'cross');
  deleteImageBlock.appendChild(cross);
  divImage.appendChild(deleteImageBlock);
  let img = createTagHtml('img', '', item.avatar, '', '', ['src', item.avatar]);

  let divContentItemForm = createTagHtml('div', 'content_item_form');
  let labelName = createTagHtml('label', '', 'НАЗВАНИЕ:', '', '', ['for', 'name']);
  let labelDescription = createTagHtml('label', '', 'ОПИСАНИЕ:', '', '', ['for', 'descr']);
  let labelCost = createTagHtml('label', '', 'ЦЕНА:', '', '', ['for', 'cost']);
  let labelSales = createTagHtml('label', '', 'СКИДКА:', '', '', ['for', 'sales']);
  let textareaName = createTagHtml('textarea', '', item.name, '', '', ['name', 'name']);
  let textareaDescription = createTagHtml('textarea', '', item.description, '', '', ['name', 'descr']);
  let textareaCost = createTagHtml('textarea', '', item.cost, '', '', ['name', 'cost']);
  let textareaSales = createTagHtml('textarea', '', item.sales, '', '', ['name', 'sales']);
  let inputCommodeNameEng = createTagHtml('input', '', '', '', '', ['type', 'hidden'], ['name', 'commodeNameEng'], ['value', item.name_eng]);
  let inputId = createTagHtml('input', '', '', '', '', ['type', 'hidden'], ['name', 'id'], ['value', item.newid]);
  let buttonSubmit = createTagHtml('button', 'submit', 'ВНЕСТИ ПРАВКИ', '', '', ['name', 'moderate'], ['value', item.newid]);
  let buttonDeleteCommode = createTagHtml('button', 'deleteCommode', 'УДАЛИТЬ КОМОД', '', '', ['name', 'deleteCommode'], ['value', item.newid]);
  let divButtons = createTagHtml('div', 'buttons');
  let divClearBoth = createTagHtml('div', '', '', '', '', ['style', 'clear:both']);

  divImage.appendChild(img);
  massAppendChild(
    divContentItemForm,
    labelName, textareaName,
    labelDescription, textareaDescription,
    labelCost, textareaCost,
    labelSales, textareaSales,
    inputCommodeNameEng,
    inputId
  );

  massAppendChild(divButtons, buttonSubmit, buttonDeleteCommode);
  massAppendChild(divUploadGallery, h2UploadGallery, inputUploadGallery);
  massAppendChild(form, divImage, divContentItemForm, divFiles, divUploadGallery, divButtons);
  massAppendChild(contentItem, form, divClearBoth);
  deleteImage();
  updateCommode();
  deleteCommode();
}

async function ajaxRequest(value) {
  console.log(value);
  let data = {
    value: value,
  };
  console.log(data);
  let url = '/wp-content/plugins/bp_related_products/ajax.php';
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  });
  let result = await response.json();
  return result;
}


async function updateCommode() {
  let contentBlock = document.querySelector('.content_item');
  let form = contentBlock.querySelector('form');
  let buttonSubmit = form.querySelector('button.submit');
  let contentItem = document.querySelector('.content_item');
  buttonSubmit.addEventListener('click', async (e) => {
    e.preventDefault();
    let formData = new FormData(form);
    console.log(formData);
    let url = '/wp-content/plugins/bp_related_products/inc/ajax_update.php';
    const response = await fetch(url, {
      method: 'POST',
      body: formData,
    });
    let text = await response.text();
    let result;
    try {
      result = JSON.parse(text);
      contentItem.innerHTML = '';
      renderResult(result, contentItem);
    } catch (e) {
      console.error('JSON Parse Error:', e);
      console.error('Raw Response:', text);
      return;
    }
    console.log(result);
  });
}
async function deleteCommode() {
  let contentBlock = document.querySelector('.content_item');
  let form = contentBlock.querySelector('form');
  let buttonDeleteCommode = form.querySelector('button.deleteCommode');
  buttonDeleteCommode.addEventListener('click', async (e) => {
    e.preventDefault();
    let formData = new FormData(form);
    console.log(formData);
    let url = '/wp-content/plugins/bp_related_products/inc/ajax_delete.php';
    const response = await fetch(url, {
      method: 'POST',
      body: formData,
    });
    let text = await response.text();
    let result;
    try {
      result = JSON.parse(text);
      console.log(result);
      window.location.reload();
    } catch (e) {
      console.error('JSON Parse Error:', e);
      console.error('Raw Response:', text);
      return;
    }
  });
}
