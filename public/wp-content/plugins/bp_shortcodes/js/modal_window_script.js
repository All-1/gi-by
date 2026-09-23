function getTimeAgo(timestamp) {
  if (!timestamp) {
    timestamp = (Date.now()) / 1000;
  }
  let now = (Date.now()) / 1000;
  let diff = now - timestamp;

  let minute = 60;
  let hour = 60 * minute;
  let day = 24 * hour;
  let week = 7 * day;
  let month = 30 * day;
  let year = 365 * day;

  if (diff < minute) {
    return 'сейчас';
  } else if (diff < hour) {
    return Math.floor(diff / minute) + 'м назад';
  } else if (diff < day) {
    return Math.floor(diff / hour) + 'ч назад';
  } else if (diff < week) {
    return Math.floor(diff / day) + 'д назад';
  } else if (diff < month) {
    return Math.floor(diff / week) + 'н назад';
  } else if (diff < year) {
    return Math.floor(diff / month) + 'ме назад';
  } else {
    return Math.floor(diff / year) + 'г назад';
  }
}

function formatTimestamp(timestamp) {
  if (!timestamp) {
    timestamp = (Date.now()) / 1000;
  }
  const date = new Date(timestamp * 1000);
  const months = ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'];
  const days = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

  const addLeadingZero = (number) => {
    return number < 10 ? '0' + number : number;
  };

  return `${days[date.getDay()]}, ${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()} в ${addLeadingZero(date.getHours())}:${addLeadingZero(date.getMinutes())}`;
}

function checkContainer(container) {
  if (container) {
    //console.log(`Элемент ${Array.from(container.classList)} найден`);
    return;
  }

}
// Скачивание большого количества документов одним архивом.
function downloadZip(filesLinks, messageFromServer) {
  // Создаем новый экземпляр JSZip
  var zip = new JSZip();

  // Проходимся по массиву ссылок на файлы и добавляем каждый файл в архив
  for (let index = 0; index < filesLinks.length; index++) {
    let filePath = filesLinks[index];

    // Получаем имя файла из пути
    let fileName = filePath.substring(filePath.lastIndexOf("/") + 1);

    // Добавляем файл в архив
    zip.file(fileName, fetch(filePath).then(response => response.blob()));
  }

  // Генерируем архив
  zip.generateAsync({ type: "blob" })
    .then(function (content) {
      // Создаем ссылку для скачивания
      var url = window.URL.createObjectURL(content);

      // Создаем элемент <a> для скачивания архива
      var a = document.createElement('a');
      a.href = url;
      a.download = 'contract-' + messageFromServer.serialNumber +
        '_dialog-' + messageFromServer.idDialog +
        '_type-' + messageFromServer.typeDialog +
        '_author-' + messageFromServer.firstNameAuthor + '-' + messageFromServer.lastNameAuthor +
        '_archive.zip'; // Имя файла архива
      a.click();

      // Очищаем ссылку после скачивания
      window.URL.revokeObjectURL(url);
    });
}
/*Функция создания DOM элементов*/
// Функция createTagHtml создаёт HTML элемент с указанными атрибутами
function removeElements(...dataElements) {
  if (dataElements.length > 0) {
    dataElements.forEach(dataElement => {
      dataElement.remove();
    });
  }
}

function createTagHtml(htmlTag, htmlClass, content, id, type, ...dataAttributes) {
  // Создаём HTML элемент с указанным тегом
  let element = document.createElement(htmlTag);
  // Если тег - 'input', 'textarea' или 'select', добавляем атрибут 'name' с значением равным id
  if (htmlTag === 'input' || htmlTag === 'textarea' || htmlTag === 'select') {
    element.name = id;
  }
  // Если указан htmlClass, добавляем его в классы элемента
  if (htmlClass) {
    element.classList.add(htmlClass);
  }
  // Если указан content, добавляем его как текстовое содержимое элемента
  if (content) {
    element.textContent = content;
  }
  // Если указан id, добавляем его как идентификатор элемента
  if (id) {
    element.id = id;
    // Если тег - 'label', устанавливаем атрибут 'for' равный id и меняем id элемента 
    if (htmlTag == 'label') {
      element.setAttribute('for', id);
      element.id = id + '_label';
    }
  }
  // Если указан type, добавляем тип и атрибут 'name' равный id
  if (type) {
    element.setAttribute('type', type);
    element.setAttribute('name', id);
    // Если тип - 'file', устанавливаем атрибут 'multiple' в значение true
    if (type === 'file') {
      element.multiple = true;
    }
  }
  // Если указаны dataAttributes, добавляем их как data-атрибуты
  if (dataAttributes.length > 0) {
    dataAttributes.forEach(dataAttribute => {
      const [key, value] = dataAttribute;
      element.setAttribute(`data-${key}`, value);
    });
  }
  // Возвращаем созданный элемент
  return element;
}

/*Функция упаковки DOM элементов*/
function massAppendChild(parent, ...childs) {
  childs.forEach(child => {
    parent.appendChild(child);
  });
}

function checkType(where) {
  let what;
  switch (where) {
    case 'Консультация':
      what = 'consultation';
      break;
    case 'Заказ':
      what = 'order';
      break;
    case 'Рекламация':
      what = 'complaint';
      break;
    case 'Ответ':
      what = 'reply';
      break;
  }
  return what;
}

function checkExtensionFiles(avatarFile, extension) {
  switch (extension) {
    case "txt":
      avatarFile.classList.add('txt_file');
      break;
    case "docx":
    case "doc":
      avatarFile.classList.add('doc_file');
      break;
    case "pdf":
      avatarFile.classList.add('pdf_file');
      break;
    case "jpg":
    case "jpeg":
    case "png":
      avatarFile.classList.add('img_file');
      break;
    case "psd":
      avatarFile.classList.add('psd_file');
      break;
    case "ppt":
    case "pptx":
      avatarFile.classList.add('ppt_file');
      break;
    case "xls":
      avatarFile.classList.add('xls_file');
      break;
    case "xlsx":
      avatarFile.classList.add('xlsx_file');
      break;
    case "scn":
      avatarFile.classList.add('kitchen_file');
      break;
    default:
      avatarFile.classList.add('default_file');
  };
}
/*
Обработка загрузки файлов
*/

let fileUploadsData = {};

function listenFileUploads(inputFile, key, handleChangeCallback) {
  let handleChange = function () {
    if (!fileUploadsData[key]) {
      fileUploadsData[key] = [];
    }
    let newFiles = inputFile.files;
    for (let i = 0; i < newFiles.length; i++) {
      console.log('Выбранный файл:', newFiles[i]);
      fileUploadsData[key].push(newFiles[i]);
    };
    //А вот и наш Call Back
    handleChangeCallback(fileUploadsData[key]);
  };
  // Принудительно вызываем обработчик, если файлы уже выбраны (например, при повторном выборе)
  if (inputFile.files.length > 0) {
    handleChange();
  };
};

//Данная функция ждёт когда мы вытащим в локальную область видимости все файлы
async function trackInputChange(
  searchArea,
  inputFile,
  afterBlock,
  key,
  trackChange
) {
  inputFile.removeEventListener('change', trackChange);
  return new Promise((resolve) => {
    listenFileUploads(inputFile, key, (files) => {
      resolve(files);
    });
  })
    .then(uploadsFiles => {
      //Обнуляем предыдущий conteiner с файлами
      let avatarFiles = searchArea.querySelector('.dialog_files');
      let filesFromServer;
      if (avatarFiles) {
        filesFromServer = avatarFiles.querySelectorAll('.files_from_server');
      }
      let boxWithAvatarFiles;
      if (filesFromServer && filesFromServer.length > 0) {
        boxWithAvatarFiles = showUploadsFiles(uploadsFiles);

        filesFromServer.forEach((file) => {
          // Добавляем файлы в начало boxWithAvatarFiles
          boxWithAvatarFiles.insertBefore(file, boxWithAvatarFiles.firstChild);
        });

        if (avatarFiles) {
          avatarFiles.remove();
        }
      } else {
        if (avatarFiles) {
          avatarFiles.remove();
        }
        boxWithAvatarFiles = showUploadsFiles(uploadsFiles);
      }

      // Заново показываем файлы пользователю.
      if (boxWithAvatarFiles) {
        afterBlock.parentNode.insertBefore(boxWithAvatarFiles, afterBlock);
        //console.log(afterBlock);
      }

      //Заново показываем файлы пользователю.
      afterBlock.parentNode.insertBefore(boxWithAvatarFiles, afterBlock);
      inputFile.addEventListener('change', trackChange);
      return uploadsFiles;
    })
    .catch(error => {
      console.error('Ошибка в startAsyncFileUploads:', error);
    });
}

//Выдыхай, дальше проще.
function showUploadsFiles(uploadsFiles) {
  let boxUploadsFiles;
  if (uploadsFiles) {
    boxUploadsFiles = createTagHtml("div", "dialog_files");
    for (let index = uploadsFiles.length - 1; index >= 0; index--) {
      let file = uploadsFiles[index];
      let fileNameParts = file.name.split('.');
      let extension = fileNameParts.pop();
      let name = fileNameParts.join('.');
      let avatarFile = createTagHtml('a', 'dialog_files_link');
      let fileName = createTagHtml('span', 'dialog_name', name);
      avatarFile.appendChild(fileName);
      let deleteSpan = createTagHtml('span', 'delete_dialog_files');
      deletedFileUploads(deleteSpan, avatarFile, uploadsFiles, index);
      checkExtensionFiles(avatarFile, extension);
      avatarFile.classList.add('added_file_now');
      boxUploadsFiles.appendChild(avatarFile);
    }
    return boxUploadsFiles;
  } else {
    console.log('А нет ничего в uploadsFiles');
  }
}

function deletedFileUploads(deleteSpan, fileLink, filesArray, fileindex) {
  fileLink.addEventListener('click', function (event) {
    event.preventDefault();
  });
  fileLink.appendChild(deleteSpan);
  // Удаляем старый слушатель, если он существует
  if (fileLink.deleteSpanClickHandler) {
    deleteSpan.removeEventListener('click', fileLink.deleteSpanClickHandler);
  }

  fileLink.deleteSpanClickHandler = function () {
    filesArray.splice(fileindex, 1);
    fileLink.classList.add('deleted_dialog_files');
    console.log(filesArray.length);
  };

  deleteSpan.addEventListener('click', fileLink.deleteSpanClickHandler);
}
/*Конец обработки загрузки файлов*/

/*Отправка данных на сервер*/
function startSubmit(textarea, uploadsFileslocal, type, sn, boxTopic) {
  if (type === "Заказ" || type === "Рекламация") {
    let flagCheckbox = checkCheckbox(textarea);
    if (flagCheckbox) {
      submitTopicLogic(textarea, uploadsFileslocal, type, sn);
      boxTopic.remove();
    } else {
      //console.log('А хуй тебе или ты думал после такой ассинхрощины я тебя пущу без checkbox?');
      let boxNotice = boxTopic.querySelector('.create_topic_box_notice');
      console.log(boxNotice);
      boxNotice.classList.add('action-notice');
    }
  } else {
    submitTopicLogic(textarea, uploadsFileslocal, type, sn);
    boxTopic.remove();
  }
}

function checkCheckbox(textarea) {
  let parentTextarea = textarea.parentNode;
  let searchArea = parentTextarea.parentNode;
  let checkboxes = searchArea.querySelectorAll('input[type="checkbox"]');

  let allChecked = Array.from(checkboxes).every(function (checkbox) {
    return checkbox.checked;
  });
  return allChecked;
}

function submitTopicLogic(textarea, uploadsFileslocal, type, sn, idDialog, oldFilesSave, idMessage, status) {
  let serverEndPoint = '/wp-content/plugins/bp_shortcodes/modal_window_send_server.php';
  let methodTopic = 'POST';
  let formData = collectFormData(type, sn, textarea, uploadsFileslocal, idDialog, oldFilesSave, idMessage, status);
  // Отправляем данные на сервер с использованием fetch
  //console.log(serverEndPoint, methodTopic, formData);
  sendFetch(serverEndPoint, methodTopic, formData, textarea);
  // uploadsFileslocal = {};
  // fileUploadsData = {};
}

function collectFormData(type, sn, textarea, uploadsFiles, idDialog, oldFilesSave, idMessage, status) {
  let formData = new FormData();
  formData.append("title", type);
  formData.append("serialNumber", sn);
  console.log(type);
  console.log(sn);
  if (textarea) {
    formData.append("message", textarea.value);
    console.log(textarea.value);
  }
  if (idDialog) {
    formData.append("idDialog", idDialog);
    console.log(idDialog);
  }
  if (oldFilesSave) {
    formData.append("oldFilesSave", oldFilesSave);
    console.log(oldFilesSave);
  }
  if (idMessage) {
    formData.append("idMessage", idMessage);
    console.log(idMessage);
  }
  if (status) {
    formData.append("status", status);
    console.log(status);
  }
  // Добавляем файлы в форму, если они есть
  if (uploadsFiles && uploadsFiles.length > 0) {
    uploadsFiles.forEach(file => {
      let renamedFile = new File([file], transliterate(file.name), { type: file.type });
      formData.append("fileInput[]", renamedFile, renamedFile.name);
      console.log(renamedFile);
    });
  }
  console.log(formData);
  return formData;
}

function sendFetch(endPoint, method, formData, textarea) {
  console.log('Отправляем на сервер:');
  console.log(formData); // Выводим данные перед отправкой

  fetch(endPoint, {
    method: method,
    body: formData,
  })
    .then(response => {
      if (!response) {
        throw new Error('Ошибка запроса: ответ от сервера не существует');
      }
      return response.json();

    })
    .then(data => {
      console.log('Ответ сервера:', data);
      // const jsonData = JSON.parse(data);
      showTopic(data);
      if (data.idMessage > 1) {
        let findOnPageWindow = document.querySelector('[data-contract-window-sn="' + data.serialNumber + '"]');
        controlPrevMessage(data, findOnPageWindow, 'sendFetch');
        console.log('data.idMessage > 1 :', data.idMessage > 1);
      }
      if (data.statusBeforeMessage === 'readed') {
        let beforeMessage = document.querySelector('[data-id-message="' + data.beforeMessageId + '"]');
        let messageStatusOld = beforeMessage.querySelector('.unreaded_message');
        if (messageStatusOld) {
          messageStatusOld.remove();
          let blockBeforeStatus = beforeMessage.querySelector('.date_message');
          let messageStatus = createTagHtml('div', data.statusBeforeMessage + '_message');
          blockBeforeStatus.insertAdjacentElement('afterend', messageStatus, "sendFetch");
        }
      }
      //reciveTopic(data);
    })
    .catch(error => {
      console.error('Ошибка при выполнении запроса:', error);
    });
}

// Конец отправки данных на сервер.

// Определение функции сохранения
function saveMessage(parent, newText, newFiles, deleteFiles) {
  // Здесь вы можете добавить код для отправки данных на сервер
  console.log(`Сохранение данных у клиента: ID ${parent.id}, Текст: ${newText}`);

  updateContent(parent, newText);
  parent.classList.remove('editable-message')
  parent.classList.add('redacted-message');
  // Обработка новых файлов
  if (newFiles && newFiles.length > 0) {
    newFiles.forEach(newFile => console.log(`Новый файл: ${newFile.name}`));
  }

  // Обработка удаленных файлов
  if (deleteFiles && deleteFiles.length > 0) {
    for (let i = 0; i < deleteFiles.length; i++) {
      let fileUrl = deleteFiles[i].getAttribute('href');
      deleteFiles[i].remove();
      if (fileUrl) {
        console.log(`Удален файл по ссылке: ${fileUrl}`);
      } else {
        console.log('Ссылка не найдена или не содержит атрибут href.');
      }
    }
  }
}

function replaceTitle(titleContainer, newTitleContainer) {
  // Очистить содержимое titleContainerElement
  titleContainer.innerHTML = '';
  // Добавить все дочерние элементы из oldTitleContainerElement
  newTitleContainer.childNodes.forEach(function (childNode) {
    titleContainer.appendChild(childNode.cloneNode(true));
  });
}

function closeMessage(parent, oldText) {
  // Здесь вы можете добавить код для отправки данных на сервер
  // console.log(`Сохранение данных на сервер: ID ${parent.id}, Текст: ${oldText}`);

  updateContent(parent, oldText);
  let deletedFiles = parent.querySelectorAll('.deleted_dialog_files');
  // Обработка новых файлов
  for (var i = 0; i < deletedFiles.length; i++) {
    deletedFiles[i].classList.remove('deleted_dialog_files');
  }
}

function removeDeleteSpan(filesContainer) {
  // console.log('Функция выполнилась')
  if (filesContainer) {
    let deleteSpans = filesContainer.querySelectorAll('.delete_dialog_files');
    deleteSpans.forEach(function (deleteSpan) {
      deleteSpan.remove();
    });
  }
}

function createRedactButton(parentContainer, afterContainer, parentMessage) {
  let redactButton = createTagHtml('div', 'message_redact');
  let redactSpan = createTagHtml('span', 'message_redact_link', 'Редактировать')
  redactButton.appendChild(redactSpan);
  if (afterContainer) {
    parentContainer.insertBefore(redactButton, afterContainer.nextSibling);
  } else {
    parentContainer.appendChild(redactButton);
  }
  // Повторно добавляем обработчик события
  redactButton.addEventListener('click', function () {
    handleRedactClick(redactButton, parentMessage);
  });
}



function updateContent(parent, newText) {
  let contentElement = parent.querySelector('.dialog_message_content');
  let contentElementContainer = document.createElement('p');
  contentElementContainer.textContent = newText;
  if (contentElement.textContent) {
    contentElement.textContent = '';
  }

  contentElement.appendChild(contentElementContainer);
}

// Определение функции обработки события ОТВЕТИТЬ 
function handleReplyClick(replyButton, parentMessage) {
  //Находим Родительский элемент в котором находятся все данные
  let reactContainer = replyButton.closest('.message_react');
  let parentContainer = reactContainer.closest('.dialog_message');

  reactContainer.style.display = 'none';
  // Поиск ID элемента и создание нового id
  parentMessage.idMessage = parseInt(parentMessage.idMessage);
  let newIdMessage = parentMessage.idMessage + 1;

  let preMainParentConteiner; let мainParentConteiner; let replyParentConteiner; let dialogBox; let idMessage;
  //Находим родительский контейнер если Это главное сообщение
  if (parentContainer.closest('.dialog_message_main')) {
    preMainParentConteiner = parentContainer.closest('.dialog_message_main');
    мainParentConteiner = preMainParentConteiner.closest('.dialog_main_message');
    dialogBox = мainParentConteiner.closest('.dialog');
  } else {
    replyParentConteiner = parentContainer.closest('.dialog_message_reply');
    dialogBox = replyParentConteiner.closest('.dialog');
  }
  idMessage = parseInt(idMessage);
  // console.log(idMessage);
  //Создание всех элементов на странице для ответа
  let dialogMessageContainer = createTagHtml('div', 'dialog_message_reply', '', 'reply_conteiner', '', ['id-message', parentMessage.idMessage + 1]);


  //Второй уровень вложености контейнера ответа
  let dialogIconAuthor = createTagHtml('div', 'dialog_icon_author');
  let dialogMessageReply = createTagHtml('div', 'dialog_message');
  dialogMessageReply.setAttribute('data-message-id', newIdMessage);

  //Третий уровень вложености ответа
  let dialogMessageContent = createTagHtml('div', 'dialog_message_content');
  let replyFilesMainConteiner = createTagHtml('div', 'dialog_files_uploads_main')
  let replyFilesControl = createTagHtml('div', 'reply_control');
  let clearFixElement = createTagHtml('div', 'clearfix', '');

  //Создание элементов управления
  let textareaElement = createTagHtml('textarea', 'message_content_redact', '', 'redact_textarea_' + newIdMessage);
  let saveElement = createTagHtml('div', 'message_content_redact_save', 'Ответить', 'redact_submit_' + newIdMessage);
  let closeElement = createTagHtml('div', 'message_content_redact_close', 'Отмена', 'redact_close_' + newIdMessage);
  let inputReplyFile = createTagHtml('input', 'redact_files_uploads', '', 'redact_files_uploads_' + newIdMessage, 'file');
  let labelFileUploads = createTagHtml('label', '', 'Загрузить файлы', 'redact_files_uploads_' + newIdMessage);


  //Упаковываем все элементы
  dialogBox.appendChild(dialogMessageContainer);
  massAppendChild(
    dialogMessageContainer,
    dialogIconAuthor, dialogMessageReply
  );
  massAppendChild(
    dialogMessageReply,
    dialogMessageContent, replyFilesMainConteiner, replyFilesControl, saveElement, closeElement, clearFixElement
  );
  dialogMessageContent.appendChild(textareaElement);
  replyFilesMainConteiner.appendChild(replyFilesControl);
  massAppendChild(
    replyFilesControl,
    inputReplyFile, labelFileUploads
  );
  textareaElement.focus();
  textareaElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
  //Слушаем элемент Close
  closeElement.addEventListener('click', function () {
    removeElements(
      dialogMessageContainer,
      saveElement,
      closeElement,
      inputReplyFile,
      labelFileUploads,
      textareaElement
    );
    fileUploadsData = {};
    reactContainer.style.display = 'block';
  })

  let uploadsFilesReply = {};
  let location = parentMessage.serialNumber + '_' + parentMessage.idDialog + '_' + parentMessage.IdMessage + '_redact';

  //console.log('Размер', textareaElement.closest('.dialog_message_reply'));
  function submitClick() {
    submitTopicLogic(textareaElement, uploadsFilesReply, parentMessage.typeDialog, parentMessage.serialNumber, parentMessage.idDialog);
    saveMessage(dialogMessageReply, textareaElement.value, uploadsFilesReply);
    let contactContent = document.querySelector('[data-sn-contract="' + parentMessage.serialNumber + '"]');
    let findOnPageDialog = contactContent.querySelector('[data-id-dialog="' + parentMessage.idDialog + '"]');
    parentMessage.idMessage = parseInt(parentMessage.idMessage, 10);
    let prevMessage = findOnPageDialog.querySelector('[data-id-message="' + parentMessage.idMessage + '"]');
    //console.log('Предыдущий ID', parentMessage.idMessage + '  Предыдущий Элемент', prevMessage);
    let prevMessageReact = prevMessage.querySelector('.message_react');
    fileUploadsData = {};
    dialogMessageContainer.id = '';

  };

  inputReplyFile.addEventListener('change', async function () {
    try {
      uploadsFilesReply = await trackInputChange(
        dialogMessageContainer,
        inputReplyFile,
        replyFilesControl,
        location,
        trackInputChange
      );
      // Удаляем предыдущий обработчик перед добавлением нового
      saveElement.removeEventListener("click", submitClick);
      // Добавляем новый обработчик только после успешного изменения файла
      saveElement.addEventListener("click", submitClick);
      dialogMessageContainer.id = '';
    } catch (error) {
      console.error('Ошибка в обработке файла:', error);
    }
  });
  //Слушаем элемент Save
  saveElement.addEventListener('click', submitClick);
}

// Определение функции обработки события редактирования
function handleRedactClick(redactButton, messageFromServer) {
  // Находим все интересующие соседние элементы
  let contactContent = document.querySelector('[data-sn-contract="' + messageFromServer.serialNumber + '"]');
  findOnPageDialog = contactContent.querySelector('[data-id-dialog="' + messageFromServer.idDialog + '"]');
  let mainContainer = findOnPageDialog.querySelector('[data-id-message="' + messageFromServer.idMessage + '"]');
  mainContainer.classList.add('editable-message');

  let parentContainer = redactButton.closest('.dialog_message');

  checkContainer(parentContainer);

  //Поиск всех элементов в родительском блоке '.dialog_message'
  let titleContainerElement = parentContainer.querySelector('.dialog_message_data');
  let redactFilesControl = parentContainer.querySelector('.dialog_files_uploads_main');
  let filesContainerElement;
  if (redactFilesControl) {
    filesContainerElement = redactFilesControl.querySelector('.dialog_files');
  } else {
    redactFilesControl = createTagHtml('div', 'dialog_files_uploads_main');
    filesContainerElement = createTagHtml('div', 'dialog_files');
    redactFilesControl.appendChild(filesContainerElement);
    parentContainer.appendChild(redactFilesControl);
  }
  //console.log(redactFilesControl)
  let contentElement = parentContainer.querySelector('.dialog_message_content');

  //console.log(filesContainerElement);
  // Сохранение всех старых элементов
  let oldTitleContainerElement = titleContainerElement.cloneNode(true);
  let oldfilesForServer = [];
  // Проверяем, найден ли соседний элемент
  if (contentElement) {
    let childElement = contentElement.children[0];
    let contentText = childElement.textContent;  // Добавляем эту строку для определения contentText

    if (filesContainerElement) {
      // Вырезаем последний дочерний элемент с классом "dialog_archive_link"
      let archiveLink = filesContainerElement.querySelector('.dialog_archive_link');
      if (archiveLink) {
        filesContainerElement.removeChild(archiveLink);
      } else {
        console.log('Соседний элемент не найден или не имеет класса "dialog_archive_link"');
      }

      // Вставляем в каждый "dialog_files_link" новый элемент span.delete_dialog_files
      let fileLinks = filesContainerElement.querySelectorAll('.dialog_files_link');


      fileLinks.forEach(function (fileLink) {
        fileLink.classList.add('.files_from_server');
        let deleteSpan = createTagHtml('span', 'delete_dialog_files');
        oldfilesForServer.push(fileLink.href); // или любое другое значение, которое вы хотите добавить
        deletedFileUploads(deleteSpan, fileLink, oldfilesForServer);
      });
    }

    // Создаём заголовок "РЕДАКТИРОВАТЬ"
    let titleElement = createTagHtml('span', 'author_message', 'Редактировать');
    let clearFixElement = createTagHtml('div', 'clearfix', '');
    let textareaElement = createTagHtml('textarea', 'message_content_redact', contentText, 'redact_textarea_' + messageFromServer.idMessage);
    let redactControl = createTagHtml('div', 'redact_control');
    let redactFilesUplodsButton = createTagHtml('div', 'dialog_files_uploads');
    let saveElement = createTagHtml('div', 'message_content_redact_save', 'Ответить', 'redact_submit_' + messageFromServer.idMessage);
    let closeElement = createTagHtml('div', 'message_content_redact_close', 'Отмена', 'redact_close_' + messageFromServer.idMessage);
    let inputRedactFile = createTagHtml('input', 'redact_files_uploads', '', 'redact_files_uploads_' + messageFromServer.idMessage, 'file');
    let labelFileUploads = createTagHtml('label', '', 'Загрузить файлы', 'redact_files_uploads_' + messageFromServer.idMessage);

    // Заменяем содержимое блока "dialog_main_message_content" на textarea
    contentElement.innerHTML = '';
    contentElement.appendChild(textareaElement);
    textareaElement.focus();
    textareaElement.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Заменяем содержимое блока 'dialog_main_message_data'
    titleContainerElement.innerHTML = '';
    massAppendChild(
      titleContainerElement,
      titleElement, clearFixElement
    );
    // Удаляем кнопку редактировать
    redactButton.remove();
    // Добавляем кнопку сохранить, отменить и загрузить файлы
    massAppendChild(
      parentContainer,
      redactControl
    )
    massAppendChild(
      redactFilesControl,
      redactFilesUplodsButton
    );
    massAppendChild(
      redactFilesUplodsButton,
      inputRedactFile, labelFileUploads
    );
    massAppendChild(
      redactControl,
      saveElement, closeElement
    );
    let deletedFiles;
    if (filesContainerElement) {
      deletedFiles = filesContainerElement.querySelectorAll('.deleted_dialog_files');
    }

    //Слушаем элемент Save
    let uploadsFilesRadact = [];
    let location = messageFromServer.serialNumber + '_' + messageFromServer.idDialog + '_' + messageFromServer.IdMessage + '_redact';

    function submitClick() {
      submitTopicLogic(textareaElement, uploadsFilesRadact, messageFromServer.typeDialog, messageFromServer.serialNumber, messageFromServer.idDialog, oldfilesForServer, messageFromServer.idMessage);
      saveMessage(mainContainer, textareaElement.value, uploadsFilesRadact, deletedFiles);
      removeElements(redactControl, inputRedactFile, labelFileUploads)
      createRedactButton(parentContainer, redactFilesControl, messageFromServer);
      replaceTitle(titleContainerElement, oldTitleContainerElement);
      fileUploadsData = {};
    };

    function closeClick() {
      let addedFiledNow;
      let filesContainerElement = redactFilesControl.querySelector('.dialog_files');
      console.log(filesContainerElement);
      if (redactFilesControl) {
        addedFiledNow = redactFilesControl.querySelectorAll('.added_file_now');
        console.log(addedFiledNow);
      }
      closeMessage(parentContainer, contentText);
      if (addedFiledNow) {
        addedFiledNow.forEach(file => {
          file.remove();
          console.log(addedFiledNow);
        });
      }
      removeElements(
        saveElement,
        closeElement,
        inputRedactFile,
        labelFileUploads,
        textareaElement,
        redactControl,
        redactFilesUplodsButton
      );
      removeDeleteSpan(filesContainerElement);
      createRedactButton(parentContainer, redactFilesControl, messageFromServer);
      replaceTitle(titleContainerElement, oldTitleContainerElement);
      fileUploadsData = {};
    }

    saveElement.addEventListener('click', submitClick);
    //Слушаем элемент Close
    closeElement.addEventListener('click', closeClick);
    inputRedactFile.addEventListener('change', async function () {
      try {
        uploadsFilesRadact = await trackInputChange(
          redactFilesControl,
          inputRedactFile,
          redactFilesUplodsButton,
          location,
          trackInputChange
        );
        // Удаляем предыдущий обработчик перед добавлением нового
        saveElement.removeEventListener("click", submitClick);
        closeElement.removeEventListener('click', closeClick);
        // Добавляем новый обработчик только после успешного изменения файла
        saveElement.addEventListener("click", submitClick);
        closeElement.addEventListener('click', closeClick);

      } catch (error) {
        console.error('Ошибка в обработке файла:', error);
      }
    });
    //Слушаем элемент Save


  } else {
    console.log('Соседний элемент не найден или не имеет класса "dialog_main_message_content"');
  }
}

/*Начало Long Poling для Window*/
function getDataForWindow(sn, getServer, lastData) {
  let urlPHP = '/wp-content/plugins/bp_contracts/modal_window_getdata.php';
  let param;

  if (lastData) {
    param = `?sn=${encodeURIComponent(sn)}&getServer=${encodeURIComponent(getServer)}&dateLastMessage=${encodeURIComponent(lastData)}`;
  } else {
    param = `?sn=${encodeURIComponent(sn)}&getServer=${encodeURIComponent(getServer)}`;
  }
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 600000);

  console.log(urlPHP + param);

  fetch(urlPHP + param, { signal: controller.signal })
    .then(response => {
      clearTimeout(timeoutId); // Очистить таймаут при успешном ответе
      // Проверяем статус ответа
      if (!response.ok) {
        throw new Error(`Ошибка HTTP: ${response.status}`);
      }

      // Получаем текст ответа
      return response.text();
    })
    .then(data => {

      // Пробуем распарсить ответ как JSON
      try {
        const jsonData = JSON.parse(data);
        console.log('Ответ сервера (JSON):', jsonData);
        getServer = getServer + 1;
        let findOnPageWindow = document.querySelector('[data-contract-window-sn="' + sn + '"]');
        if (findOnPageWindow) {
          if (jsonData !== false) {
            Object.keys(jsonData).forEach(function (key) {
              showTopic(jsonData[key]);
            });
            let allDates = getAllDates(jsonData);
            let newestDate = new Date(Math.max(...allDates));
            let objectWithNewestDate = findObjectByDate(jsonData, newestDate);


            controlPrevMessage(jsonData, findOnPageWindow);

            getDataForWindow(objectWithNewestDate.serialNumber, getServer, newestDate);
          } else {
            getDataForWindow(sn, getServer);
          }

        } else {
          console.log('Закончили');

          return;
        }


      } catch (error) {
        console.error('Ошибка при разборе JSON:', error);
      }
    })
    .catch(error => {
      // Обрабатываем ошибку от сервера
      console.error('Произошла ошибка при запросе данных:', error.message);
    });
}

/* Прошу простить того кто будет читать этот код, это полное дерьмо, а не функция, писал её на нервах, голова очень плохо соображала и я наворотил откровенной хуйни*/
function controlPrevMessage(data, parent, sendFetch) {
  if (!sendFetch) {
    Object.keys(data).forEach(function (key) {
      let findOnPageDialogBox = parent.querySelector('[data-id-dialog-box="' + data[key].idDialog + '"]');
      let findOnPageDialog = parent.querySelector('[data-id-dialog="' + data[key].idDialog + '"]');
      let findReplyContainer = findOnPageDialog.querySelector('#reply_conteiner');
      if (findReplyContainer) {
        return;
      }
      if (!findOnPageDialog) {
        //console.log('Привет');
        return;
      }
      let findMainMessage = findOnPageDialog.querySelector('.dialog_main_message');
      let findReplyMessages = findOnPageDialog.querySelectorAll('[data-id-message].dialog_message_reply');
      //console.log(findReplyMessages.length);
      let findPrevMessage = findOnPageDialog.querySelector('.message_view_prev');
      let findUnreadedMessage = findOnPageDialogBox.querySelector('.unreaded_message');
      let findMarkerUnreadedMessage = findOnPageDialogBox.querySelector('.marker-unreaded-message-dialog');
      let idAuthor;
      if (findUnreadedMessage) {
        //console.log(findUnreadedMessage);
        idAuthor = parseInt(findUnreadedMessage.getAttribute('data-id-author'));
      }



      let findLabelDialog = findOnPageDialogBox.querySelector('.dialog_label');
      let viewPrevMessages = createTagHtml('span', 'message_view_prev', 'Предыдущие сообщения');
      let arrowViewPrevMessages = createTagHtml('span', 'message_view_prev_arrowup');


      let findСontrolAllMessages = findOnPageDialogBox.querySelector('.control-show-all-messages');

      //console.log(findReplyContainer);
      let controlShowAllMessages = createTagHtml('div', 'control-show-all-messages', findReplyMessages.length);
      let arrowShowAllMessages = createTagHtml('span', 'arrow_show_all_messages');

      if (findMainMessage && findReplyMessages.length > 0 && !findСontrolAllMessages) {
        findOnPageDialog.insertAdjacentElement('afterend', controlShowAllMessages);
        controlShowAllMessages.appendChild(arrowShowAllMessages);
      }
      if (findUnreadedMessage && idAuthor !== data[key].IdUser && !findMarkerUnreadedMessage) {
        let markerUnreadedMessage = createTagHtml('div', 'marker-unreaded-message-dialog');

        findLabelDialog.appendChild(markerUnreadedMessage);
        //console.log('Зашли 1');
      }

      if (findСontrolAllMessages) {
        let counterNewMessages = parseInt(findСontrolAllMessages.innerText);
        if (counterNewMessages < findReplyMessages.length) {
          findСontrolAllMessages.innerText = findReplyContainer ?
            findReplyMessages.length - 1 :
            findReplyMessages.length;
          findСontrolAllMessages.appendChild(arrowShowAllMessages);
          if (findСontrolAllMessages.id === 'dialog-open') {
            arrowShowAllMessages.style.transform = 'rotate(180deg)';
          }
          findСontrolAllMessages.appendChild(arrowShowAllMessages);
        }
      }
      let flagOpenDialog = findOnPageDialogBox.querySelector('#dialog-open');
      let flagViewPrevMessages = findOnPageDialogBox.querySelector('#show-all-messages-dialog');
      if (flagOpenDialog) {
        controlShowAllMessages = flagOpenDialog;
        controlShowAllMessages.removeEventListener('click', hideDialog);
        controlShowAllMessages.addEventListener('click', hideDialog);
        // console.log('flagOpenDialog');
        if (findReplyMessages.length < 2) {
          viewPrevMessages.style.display = 'none';
        } else {
          viewPrevMessages.style.display = 'block';
        }
      } else {
        viewPrevMessages.style.display = 'none';
      }
      if (findMainMessage && findReplyMessages.length > 0 && !flagOpenDialog) {
        for (var i = 0; i < findReplyMessages.length; i++) {
          if (!flagOpenDialog) {
            findReplyMessages[i].style.display = 'none';
          }
        }
        controlShowAllMessages.addEventListener('click', showDialog);
      }

      if (findMainMessage && findReplyMessages.length > 2) {
        if (!findPrevMessage) {
          viewPrevMessages.appendChild(arrowViewPrevMessages);
          findMainMessage.insertAdjacentElement('afterend', viewPrevMessages);
          viewPrevMessages.removeEventListener('click', hidePrevMessages);
          viewPrevMessages.removeEventListener('click', showPrevMessages);
          viewPrevMessages.addEventListener('click', hidePrevMessages)
        }
        if (!flagOpenDialog) {
          for (var i = 0; i < findReplyMessages.length - 2; i++) {
            findReplyMessages[i].style.display = 'none';
            //console.log(findReplyMessages[i]);
          }
        }
        if (!flagViewPrevMessages) {
          //console.log(flagViewPrevMessages);
          for (var i = 0; i < findReplyMessages.length - 3; i++) {
            findReplyMessages[i].style.display = 'none';
          }
        }
      }
      function showDialog() {
        let findReplyMessages = findOnPageDialog.querySelectorAll('[data-id-message].dialog_message_reply');
        let findСontrolAllMessages = findOnPageDialogBox.querySelector('.control-show-all-messages');
        let arrowShowAllMessages = findСontrolAllMessages.querySelector('.arrow_show_all_messages');
        //let viewPrevMessages = findOnPageDialog.querySelector('.message_view_prev');
        if (findReplyMessages.length <= 2) {
          for (var i = 0; i < findReplyMessages.length; i++) {
            findReplyMessages[i].style.display = 'grid';
          };
        } else {
          for (var i = findReplyMessages.length - 1; i > findReplyMessages.length - 3; i--) {
            findReplyMessages[i].style.display = 'grid';
          };
        }
        let lastChildReplyMessages = findReplyMessages[findReplyMessages.length - 1];
        lastChildReplyMessages.scrollIntoView({ behavior: 'smooth', block: 'center' });
        viewPrevMessages.style.display = 'block';
        arrowShowAllMessages.style.transform = 'rotate(180deg)';
        controlShowAllMessages.style.top = '-1px';
        controlShowAllMessages.style.bottom = 'auto';
        controlShowAllMessages.setAttribute('id', 'dialog-open');
        controlShowAllMessages.removeEventListener('click', showDialog);
        controlShowAllMessages.addEventListener('click', hideDialog);
        viewPrevMessages.addEventListener('click', showPrevMessages);
      }
      function hideDialog() {
        let findReplyMessages = findOnPageDialog.querySelectorAll('[data-id-message].dialog_message_reply');
        let findСontrolAllMessages = findOnPageDialogBox.querySelector('.control-show-all-messages');
        let arrowShowAllMessages = findСontrolAllMessages.querySelector('.arrow_show_all_messages');
        //let viewPrevMessages = findOnPageDialog.querySelector('.message_view_prev');
        for (var i = 0; i < findReplyMessages.length; i++) {
          findReplyMessages[i].style.display = 'none';
        };
        viewPrevMessages.style.display = 'none';
        viewPrevMessages.id = '';
        viewPrevMessages.textContent = 'Предыдущие сообщения';
        arrowViewPrevMessages.style.transform = 'rotate(0deg)';
        viewPrevMessages.appendChild(arrowViewPrevMessages);
        //console.log(viewPrevMessages.style.display);
        arrowShowAllMessages.style.transform = 'rotate(0deg)';
        controlShowAllMessages.style.top = 'auto';
        controlShowAllMessages.style.bottom = '-1px';
        controlShowAllMessages.id = '';
        controlShowAllMessages.removeEventListener('click', hideDialog);
        controlShowAllMessages.addEventListener('click', showDialog);
        viewPrevMessages.removeEventListener('click', hidePrevMessages);
        viewPrevMessages.addEventListener('click', showPrevMessages)
      }
      function showPrevMessages() {
        let findReplyMessages = findOnPageDialog.querySelectorAll('[data-id-message].dialog_message_reply');
        for (var i = 0; i < findReplyMessages.length; i++) {
          findReplyMessages[i].style.display = 'grid';
        };
        let lastChildReplyMessages = findReplyMessages[findReplyMessages.length - 1];
        lastChildReplyMessages.scrollIntoView({ behavior: 'smooth', block: 'center' });
        viewPrevMessages.id = 'show-all-messages-dialog';
        viewPrevMessages.textContent = 'Свернуть сообщения';
        viewPrevMessages.appendChild(arrowViewPrevMessages);
        arrowViewPrevMessages.style.transform = 'rotate(180deg)';
        viewPrevMessages.removeEventListener('click', showPrevMessages);
        viewPrevMessages.addEventListener('click', hidePrevMessages)
      }
      function hidePrevMessages() {
        let findReplyMessages = findOnPageDialog.querySelectorAll('[data-id-message].dialog_message_reply');
        for (var i = 0; i < findReplyMessages.length - 2; i++) {
          findReplyMessages[i].style.display = 'none';
        }
        viewPrevMessages.textContent = 'Предыдущие сообщения';
        viewPrevMessages.id = '';
        viewPrevMessages.appendChild(arrowViewPrevMessages);
        arrowViewPrevMessages.style.transform = 'rotate(0deg)';
        viewPrevMessages.removeEventListener('click', hidePrevMessages);
        viewPrevMessages.addEventListener('click', showPrevMessages)
      };
    });
  } else if (sendFetch && data.idMessage === 2) {
    let findOnPageDialogBox = parent.querySelector('[data-id-dialog-box="' + data.idDialog + '"]');
    let findOnPageDialog = parent.querySelector('[data-id-dialog="' + data.idDialog + '"]');
    let findСontrolAllMessages = findOnPageDialogBox.querySelector('.control-show-all-messages');
    if (!findСontrolAllMessages) {
      let controlShowAllMessages = createTagHtml('div', 'control-show-all-messages', '1');
      let arrowShowAllMessages = createTagHtml('span', 'arrow_show_all_messages');
      findOnPageDialog.insertAdjacentElement('afterend', controlShowAllMessages);
      controlShowAllMessages.appendChild(arrowShowAllMessages);
      controlShowAllMessages.setAttribute('id', 'dialog-open');
      arrowShowAllMessages.style.transform = 'rotate(180deg)';
      controlShowAllMessages.style.top = '-1px';
      controlShowAllMessages.style.bottom = 'auto';
      //console.log('Зашли 2');
    }
  }// else if (sendFetch) {
  //   let findOnPageDialogBox = parent.querySelector('[data-id-dialog-box="' + data.idDialog + '"]');
  //   let findUnreadedMessage = findOnPageDialogBox.querySelector('.unreaded_message');
  //   if (!findUnreadedMessage) {
  //     let findUnreadedMessageMarker = findOnPageDialogBox.querySelector('.marker-unreaded-message-dialog');
  //     //console.log(findUnreadedMessageMarker);
  //     //console.log('findUnreadedMessage');
  //     findUnreadedMessageMarker.remove();
  //     //console.log(findUnreadedMessageMarker);
  //   }
  // }
}

function getAllDates(data) {
  let allDates = [];
  for (let key in data) {
    let date, date2;
    if (data[key].dateSend) {
      date = data[key].dateSend;
    }
    if (data[key].dateLastReaded !== '0000-00-00 00:00:00') {
      date2 = data[key].dateLastReaded;
    }

    if (Object.hasOwnProperty.call(data, key) && date) {
      let objectDate = new Date(date);
      allDates.push(objectDate);
    }
    if (Object.hasOwnProperty.call(data, key) && date2) {
      let objectDate = new Date(date2);
      allDates.push(objectDate);
    }
  }
  return allDates;
}

function findObjectByDate(data, targetDate) {
  for (let key in data) {
    let date, date2;
    if (data[key].dateSend) {
      date = data[key].dateSend;
    }
    if (data[key].dateLastReaded !== '0000-00-00 00:00:00') {
      date2 = data[key].dateLastReaded;
    }
    if (Object.hasOwnProperty.call(data, key) && date) {
      let objectDate = new Date(date);
      if (objectDate.getTime() === targetDate.getTime()) {
        return data[key];
      }
    }
    if (Object.hasOwnProperty.call(data, key) && date2) {
      let objectDate = new Date(date2);
      if (objectDate.getTime() === targetDate.getTime()) {
        return data[key];
      }
    }
  }
  return null; // Если объект не найден
}


/*Конец Long Poling для Window*/


//ПОКА НЕИСПОЛЬЗУЕМЫЙ СКРИПТ ОБРАБОТКИ ДАННЫХ НА AJAX
function loadinfo(obj) {
  let type = jQuery(obj).data('type');
  let name = jQuery(obj).data('name');
  jQuery('#modal_window').css('display', 'block');
  jQuery('html').css('overflow', 'hidden');
  jQuery('#modal_content').html('');
  jQuery('#loadingicon').css('display', 'block');

  /*
  jQuery.ajax({
    url: '/wp-content/plugins/bp_dealer_files/write_designers_files_content.php',
    type: 'POST',
    data: {type:type, name:name},
    success: function(data){ 
      jQuery('#modal_content').html(data);
      jQuery('#loadingicon').css('display','none');
    } 
  });
  */
};
