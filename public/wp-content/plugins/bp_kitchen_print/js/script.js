"use strict";

document.addEventListener('DOMContentLoaded', function () {
  const observer = lozad('.lozad', {
    rootMargin: '10px 0px',
    threshold: 0.1,
    loaded: function (el) {
      el.classList.add('loaded');
      // console.log('Image loaded:', el.src);
    }
  });
  observer.observe();
  console.log('Lozad observer initialized for', document.querySelectorAll('.lozad').length, 'images');
});

const nameKitchen = kitchenName;

listenMainButtons();
listenInnerButtons();

function listenMainButtons() {
  // const where = document.querySelector('.kitchen_card_ajax_block_inner').dataset.where;
  // console.log('listenMainButtons');
  const buttons = document.querySelectorAll('.kitchen_card_button');
  buttons.forEach(button => {
    button.addEventListener('click', async function () {
      console.log('listenMainButtons button click');
      buttons.forEach(button => {
        button.id = '';
      });
      button.id = 'selected-button';
      let value = button.value;
      const ajaxBlockButtons = document.querySelector('.kitchen_card_ajax_block_inner');
      if (ajaxBlockButtons) {
        ajaxBlockButtons.remove();
      }
      let result = await ajaxRequest(value, 'main', nameKitchen);
      console.log(result);
      renderResult(result, value);
    });
  });
}

async function listenInnerButtons() {
  
  const where = document.querySelector('.kitchen_card_ajax_block_inner').dataset.where;
  const buttons = document.querySelectorAll('.kitchen_card_inner_block');
  buttons.forEach(button => {
    button.addEventListener('click', async function () {
      console.log('listenInnerButtons button click');
      buttons.forEach(button => {
        button.classList.remove('selected-inner-button');
      });
      button.classList.add('selected-inner-button');
      let value = button.dataset.materialId;
      console.log(value);
      console.log(where);
      console.log(nameKitchen);
      let result = await ajaxRequest(value, where, nameKitchen);
      console.log(result);
      renderResult(result);
    });
  });
}

async function ajaxRequest(value, where, nameKitchen) {
  // console.log(value);
  const type = (typeof kitchenType !== 'undefined') ? kitchenType : 'kitchen';
  let data = {
    value: value,
    where: where,
    nameKitchen: nameKitchen,
    type: type
  };
  // console.log(data);
  let url = '/wp-content/plugins/bp_kitchen_print/ajax_kitchen.php';
  const kitchenCardContent = document.querySelector('.kitchen_card_facade_div');
  loadingIndicator(kitchenCardContent);
  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data)
    });
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    const result = await response.json();
    return result;
  } catch (error) {
    console.error('Error:', error);
  }
}

function renderResult(result, where) {
  if (result.main) {
    // Render the main buttons (material types)
    const ajaxBlockButtons = createTagHtml('div', 'kitchen_card_ajax_block_inner', '', '', '', ['where', where + '-list']);
    const ajaxBlock = document.querySelector('.kitchen_card_ajax_block');
    ajaxBlock.insertAdjacentElement('afterend', ajaxBlockButtons);
    // console.log(result.main);
    let i = 0;
    result.main.forEach(item => {
      let id = item.id ? item.id : item;
      let name = item.name ? item.name : item;
      const ajaxButton = createTagHtml('div', 'kitchen_card_inner_block', name, '', '', ['material-id', id]);
      if (i === 0) {
        ajaxButton.classList.add('selected-inner-button');
      }
      ajaxBlockButtons.appendChild(ajaxButton);
      i++;
    });
    listenInnerButtons();
  }
  // Render the list items
  if (result.list && Array.isArray(result.list)) {
    // console.log('List items:', result.list);
    const kitchenCardContent = document.querySelector('.kitchen_card_facade_div');
    kitchenCardContent.style.height = 'auto';
    kitchenCardContent.style.display = 'grid';
    kitchenCardContent.innerHTML = '';

    result.list.forEach((item) => {
      // const facadeName = item.name;
      const convertedItem = convertItem(item);
      const itemObj = createTagHtml('div', 'facades_list_item');
      const itemLink = createTagLink(convertedItem.image, '', '', '', '', ['lightbox', 'image-3']);
      const itemName = createTagHtml('span', 'list_item_name', convertedItem.name);
      const itemDescription = convertedItem.description ? createTagHtml('span', 'list_item_description', convertedItem.description) : '';

      const itemImage = createTagHtml('img', 'lozad', convertedItem.thumb, '', '', ['src', convertedItem.thumb]);
      const itemNew = convertedItem.new ? createTagHtml('img', 'new-span', '/wp-content/uploads/2020/10/new.png') : '';
      const itemSilver = convertedItem.silver ? createTagHtml('img', 'silver-span', '/wp-content/uploads/2020/07/bacter.png') : '';
      const itemAntibac = convertedItem.antibac ? createTagHtml('img', 'antibac-span', '/wp-content/uploads/2020/07/bacter.png') : '';

      massAppendChild(
        itemLink,
        itemImage,
        itemNew,
        itemSilver,
        itemAntibac
      );
      massAppendChild(
        itemObj,
        itemLink,
        itemName,
        itemDescription
      );
      kitchenCardContent.appendChild(itemObj);
      // const facadeLink = item.link;
    });
  }
}

function convertItem(item) {
  // console.log(item);
  const sourceItem = item && typeof item === 'object' ? item : {};
  const newItem = {
    image: convertImageItem(sourceItem),
    thumb: convertThumbItem(sourceItem),
    name: convertNameItem(sourceItem),
    description: convertDescriptionItem(sourceItem),
    new: convertNewItem(sourceItem),
    silver: convertSilverItem(sourceItem),
    antibac: convertAntibacItem(sourceItem),
  };
  // console.log(newItem);
  return newItem;
}

function convertImageItem(item) {
  if (!item || typeof item !== 'object') {
    return '';
  }
  if (item.image) {
    return 'https://geosideal.ru/wp-content/uploads/facades_list/textures/' + item.image;
  } else if (item.imageLink) {
    return 'https://geosideal.ru/' + item.imageLink;
  } else if (item.imageHref) {
    return 'https://geosideal.ru/' + item.imageHref;
  } else if (item.image_href) {
    return 'https://geosideal.ru/' + item.image_href;
  }
  return '';
}

function convertThumbItem(item) {
  if (item.image) {
    return 'https://geosideal.ru/wp-content/uploads/facades_list/thumbnails/' + item.image;
  } else if (item.thumbLink) {
    return 'https://geosideal.ru' + item.thumbLink;
  } else if (item.thumbHref) {
    return 'https://geosideal.ru' + item.thumbHref;
  } else if (item.thumb_href) {
    return 'https://geosideal.ru' + item.thumb_href;
  }
  return '';
}

function convertNameItem(item) {
  if (item.name) {
    return item.name;
  } else if (item.tabletopName) {
    return item.tabletopName;
  } else if (item.modelName) {
    return item.modelName;
  } else if (item.model_name) {
    return item.model_name;
  }
  return '';
}

function convertDescriptionItem(item) {
  if (item.description) {
    return item.description;
  }
  return '';
}

function convertNewItem(item) {
  if (item.newfacade) {
    return item.new;
  } else if (item.timestamp) {
    let now = (Date.now()) / 1000;
    let diff = now - item.timestamp;
    if (diff < 182 * 24 * 60 * 60) {
      return true;
    }
  }
  return '';
}

function convertSilverItem(item) {
  if (item.silver) {
    return item.silver;
  }
  return '';
}

function convertAntibacItem(item) {
  if (item.antibac) {
    return item.antibac;
  }
  return '';
}