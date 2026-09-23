"use strict";

function stickyHeader() {
  const header = document.querySelector('.header_modal_content');
  const contractContent = document.querySelector('.contracts_content');
  const modalWindow = document.querySelector('.modal_window_contracts');
  if (header) {
    const sticky = header.offsetTop;
    // Добавляем throttling для функции прокрутки
    modalWindow.addEventListener('scroll', throttle(() => {
      if (modalWindow.scrollTop > sticky) {
        header.classList.add('fixed-header-windows');
        contractContent.classList.add('contract-content-when-fix');
      } else {
        header.classList.remove('fixed-header-windows');
        contractContent.classList.remove('contract-content-when-fix');
        // stickyHeader();
      }
    }, 300)); // 100 миллисекунд — это интервал для throttling (можно регулировать)
  }
}

// Функция throttle: ограничивает частоту вызова функции
function throttle(func, limit) {
  let lastFunc;
  let lastRan;
  return function () {
    const context = this;
    const args = arguments;
    if (!lastRan) {
      func.apply(context, args);
      lastRan = Date.now();
    } else {
      clearTimeout(lastFunc);
      lastFunc = setTimeout(function () {
        if (Date.now() - lastRan >= limit) {
          func.apply(context, args);
          lastRan = Date.now();
        }
      }, limit - (Date.now() - lastRan));
    }
  };
}

function contractForWindow(data, user) {
  if (!data) {
    return;
  }
  try {
    let dataDialogues;
    let potencialParticipiant;
    if (data) {
      dataDialogues = data.dataDialogues.sort((a, b) => {
        return Number(a.idDialog) - Number(b.idDialog);
      });
      console.log('dataDialogues пришла');
      console.log(dataDialogues);
      potencialParticipiant = changeUserRole(Object.values(data.potencialParticipiant));
    } else {
      console.error('Нет dataDialogues:');
      return;
    }
    let findOnPageWindow = document.querySelector('[data-contract-window-sn="' + data.serialNumber + '"]');
    if (findOnPageWindow) {
      changeNameContract(findOnPageWindow, data);
      dataDialogues.forEach(dialog => {
        dialog.idParticipant = Object.values(changeTypeInArray(dialog.idParticipant));
        dialog.idDialog = parseInt(dialog.idDialog);
        dialog.serialNumber = parseInt(dialog.serialNumber);
        if (dialog.messages) {
          let didntReadMessages = [];
          dialog.messages.forEach(message => {
            message.didntRead = Object.values(changeTypeInArray(message.didntRead));
            didntReadMessages.push(message.didntRead);
          });
          dialog.messages.forEach(message => {
            message.serialNumber = data.serialNumber;
            message.typeDialog = dialog.typeDialog;
            message.idDialog = parseInt(message.idDialog, 10);
            message.idMessage = parseInt(message.idMessage, 10);
            message.idAuthor = parseInt(message.idAuthor, 10);
            message.serialNumber = parseInt(message.serialNumber, 10);
            message.idParticipant = dialog.idParticipant;
            message.didntReadMessages = didntReadMessages;
            showTopic(message, findOnPageWindow, user, dialog.messages);
          });
          controlPrevMessage(dialog.messages, findOnPageWindow, flagSecondMessageDialog);
        }
        if (checkIncludesId(dialog.idParticipant, userId)) {
          controlParticipiant(dialog, potencialParticipiant);
        } else {
          delBoxControlParticipiant(dialog);
          if (user.position !== 'Админ') {
            addMeParticipiants(dialog);
          }
        }
      });
      let modalContent = document.getElementById('modal_content');
      let findBoxOrders = modalContent.querySelector('.contract_box_orders');
      if (data.dataOrders.length !== 0) {
        orderOnWindow(data.dataOrders, findBoxOrders, user);
      } else {
        if (findBoxOrders) {
          findBoxOrders.remove();
        }
      }
    }
    stickyHeader();
  } catch (error) {
    console.error('Ошибка при парсинге JSON:', error);
  }
}

function showContract(sn) {
  socket.send('getDataForContract::: ' + sn);
}

function orderOnWindow(data, findBoxOrders, user) {
  //You must rebuild this chain of function
  const where = 'order-window';
  const flagUpdate = checkUpdateTable(data, where);
  let modalContent = document.getElementById('modal_content');
  let contractDialogs = modalContent.querySelector('.contract_content');
  if (findBoxOrders) {
    findBoxOrders.remove();
  }
  let boxOrders = createTagHtml("div", "contract_box_orders");
  let innerBoxOrders = createTagHtml("div", "contract_inner_box_orders");
  let paramForHeader = [
    ['orders_number_factory', '№ заказа'],
    ['orders_number_client', 'Имя'],
    ['orders_status', 'Состояние'],
    ['orders_accepted', 'Принят'],
    ['orders_invoice', 'Счёт'],
    ['orders_completion', 'Сдача'],
    ['orders_shipment', 'Отгрузка'],
  ];
  let headerOrders = createHeader(where, paramForHeader);
  modalContent.insertBefore(boxOrders, contractDialogs);
  boxOrders.appendChild(innerBoxOrders);
  innerBoxOrders.appendChild(headerOrders);
  data.forEach(order => {
    updateOrdersWindow(order, where, innerBoxOrders, user);
  })
}

function updateOrdersWindow(data, where, box, user) {
  //You must rebuild this chain of function
  let targetOrderOnPage = document.querySelector('[data-nf-' + where + '="' + data.id + '"]');
  if (!targetOrderOnPage) {
    let rowOrder = createRowTableOrders(where, data, user);
    let tdOrderNumberFactory = rowOrder.querySelector('.td_table_order_number');
    box.appendChild(rowOrder);
  } else {
    let trOrder = createRowTableOrders(where, data, user);
    targetOrderOnPage.insertAdjacentElement("afterend", trOrder);
    targetOrderOnPage.remove();
  }
}

function createHeader(where, paramForHeader) {
  let header = createTagHtml("div", "tr_header_orders");
  header.classList.add(where);
  paramForHeader.forEach(element => {
    let td = createTagHtml("div", "td_div_header_orders", element[1]);
    td.classList.add(element[0]);
    header.appendChild(td);
  })
  return header;
}

function delBoxControlParticipiant(dialog) {
  let contactContent = document.querySelector('[data-sn-contract="' + dialog.serialNumber + '"]');
  let findOnPageDialogBox = contactContent.querySelector('[data-id-dialog-box="' + String(dialog.idDialog) + '"]');
  if (findOnPageDialogBox) {
    let findBoxControlDialog = findOnPageDialogBox.querySelector('[data-id-dialog-control="' + String(dialog.idDialog) + '"]');
    if (findBoxControlDialog) {
      findBoxControlDialog.remove();
    }
  }
}
//Update Title Contract


//Функция основного модального окна.
function callContractModalWindow(sn, name, user, where) {
  if (!user) {
    user = userParam;
  }

  invokeDataForWindow(where, sn);
  let snForUser = String(sn).padStart(6, "0");
  //Создаём полностью с нуля всё модальное окно.

  let modalWindow = createTagHtml("div", "modal_window_contracts", '', '', '', ['contract-window-sn', sn]);
  modalWindow.setAttribute("data-contract-window-sn", sn);

  let modalContent = createTagHtml("div", "", "", "modal_content");

  let headerModalContent = createTagHtml("div", "header_modal_content");
  let toStart = createTagHtml("div", "tostart", "", "tostart-" + where);
  let toStartArrow = createTagHtml("img");
  toStartArrow.src = "/wp-content/themes/wp-diary/images/icons/arrow-back.svg";

  let contractTitle = createTagHtml("div", "contract-title", '', '', '', ['contract-sn', sn]);
  let contractH1 = createTagHtml("h1", "h1-contract", name);
  let contractNumber = createTagHtml("span", "contract-number", "№" + snForUser);

  let clearFix = createTagHtml("div", "clearfix");

  let contractContent = createTagHtml("div", "contracts_content", '', '', '', ['sn-contract', sn]);
  //Упаковываем все элементы друг в друга. Всё разделено по блокам
  document.body.appendChild(modalWindow);

  modalWindow.appendChild(modalContent);
  massAppendChild(modalContent,
    headerModalContent, contractContent);

  toStart.appendChild(toStartArrow);
  contractH1.appendChild(contractNumber);
  massAppendChild(
    contractTitle,
    contractH1, contractNumber
  );

  if (user.whose === 'contractor') {
    let divDropDown = createTagHtml("div", "dropdown-button", "Создать");
    let controlModalPanel = createTagHtml("div", "dropdown-list");
    let consultationModal = createTagHtml("span", "control_modal_button", "Консультация", "consultation_modal");
    let orderModal = createTagHtml("span", "control_modal_button", "Заказ", "order_modal");
    let complaintModal = createTagHtml("span", "control_modal_button", "Рекламация", "complaint_modal");
    massAppendChild(
      headerModalContent,
      toStart, divDropDown, contractTitle, clearFix
    );
    divDropDown.appendChild(controlModalPanel);
    massAppendChild(
      controlModalPanel,
      consultationModal, orderModal, complaintModal
    );
    contractH1.classList.add("title-for-contractor");
    contractH1.addEventListener("click", listenerRedactTitle);
    consultationModal.addEventListener("click", function () {
      handleModalClick(sn, headerModalContent, "Консультация:");
      fileUploadsData = {}; //Глобальный объект с 
    });

    orderModal.addEventListener("click", function () {
      handleModalClick(sn, headerModalContent, "Заказ:");
      fileUploadsData = {};
    });

    complaintModal.addEventListener("click", function () {
      handleModalClick(sn, headerModalContent, "Рекламация:");
      fileUploadsData = {};
    });
  } else {
    massAppendChild(
      headerModalContent,
      toStart, contractTitle, clearFix
    );
  }

  toStart.addEventListener("click", function (event) {
    modalWindow.remove();
    fileUploadsData = {};
    closeObjectRelationship(sn, where, 'close');
    updateLastSN(where);
    // startGetForContract('start');
    document.documentElement.classList.remove('no-scrollbar');
  });
  function listenerRedactTitle() {
    clickRedactTitle(contractH1, contractTitle, listenerRedactTitle);
  }
}

function closeObjectRelationship(sn, where, command) {
  let whereForCase = toCapsCase(where);
  let socketCommand = command + whereForCase;
  let jsonData = JSON.stringify(sn);
  socket.send(socketCommand + '::: ' + jsonData);
}

function handleModalClick(sn, beforeBlock, type) {
  let findModalCreateBox = document.querySelector('.modal_create_box');
  if (findModalCreateBox) {
    findModalCreateBox.remove();
  }
  let modalCreateBox = createTagHtml("div", "modal_create_box");
  beforeBlock.insertAdjacentElement("afterend", modalCreateBox);
  callCreateTopic(sn, modalCreateBox, type);
}

function callCreateTopic(sn, where, type) {
  type = type.replace(new RegExp(':', 'g'), '');
  let typeDialogHTML = checkType(type);

  let location = 'create_' + typeDialogHTML + '_file' + "_" + sn;
  let createTopic = createTagHtml("div", "create_topic");
  let topicH4 = createTagHtml("h4", "", type + ':');
  let createTopicBox = createTagHtml("div", "create_topic_box");
  let clearFix = createTagHtml("div", "clearfix");

  let mainContainerFiles = createTagHtml('div', 'dialog_files_uploads_main');
  let filesConteinerTopic = createTagHtml('div', 'dialog_files_uploads');
  let clearFixFilesConteiner = createTagHtml("div", "clearfix");

  let topicTextarea = createTagHtml("textarea", "create_topic_message", "", 'create_' + typeDialogHTML + '_message');
  topicTextarea.placeholder = "Что хотите узнать?";

  topicTextarea.setAttribute('lang', 'ru');
  topicTextarea.spellcheck = true;


  let topicControl = createTagHtml("div", "create_topic_control");

  let inputTopicFile = createTagHtml("input", "create_topic_file", "", 'create_' + typeDialogHTML + '_file', "file");
  let labelTopicFile = createTagHtml("label", "", "Загрузите файлы", 'create_' + typeDialogHTML + '_file');
  let submitTopic = createTagHtml("button", "create_topic_submit", "Отправить", "create_" + typeDialogHTML + "_submit");
  let topicEsc = createTagHtml("div", "create_topic_button");

  let closeTopic = createTagHtml("button", "create_topic_close", "Закрыть");

  submitTopic.setAttribute("data-consultation-for-contract", sn);

  newLine(topicTextarea);
  //Упаковка
  where.appendChild(createTopic);
  massAppendChild(
    createTopic,
    topicH4, createTopicBox
  );
  massAppendChild(createTopicBox,
    topicTextarea, mainContainerFiles, topicControl
  );
  massAppendChild(mainContainerFiles,
    filesConteinerTopic, clearFixFilesConteiner
  );
  massAppendChild(filesConteinerTopic,
    inputTopicFile, labelTopicFile
  )
  massAppendChild(
    topicControl,
    submitTopic, topicEsc
  );
  topicEsc.appendChild(closeTopic);



  if (type === "Заказ") {
    checkboxForTopic(createTopic, type);
  }
  createTopic.appendChild(clearFix);
  let uploadsFilesTopic = {};
  topicTextarea.focus();
  topicTextarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
  // Вызов асинхронной функции и обработка результата
  //Прежде чем тут что-то трогать иди изучай как происходит вся асинхронная работа. Тут гордиев узел. Надо развязать.

  type = translitType(type);
  function submitClick() {
    startSubmit(topicTextarea, uploadsFilesTopic, type, sn, createTopic);
  };

  submitTopic.addEventListener("click", submitClick);
  inputTopicFile.addEventListener('change', async function () {
    try {
      uploadsFilesTopic = await trackInputChange(
        createTopic,
        inputTopicFile,
        filesConteinerTopic,
        location,
        trackInputChange
      );
      // Удаляем предыдущий обработчик перед добавлением нового
      submitTopic.removeEventListener("click", submitClick);
      // Добавляем новый обработчик только после успешного изменения файла
      submitTopic.addEventListener("click", submitClick);
    } catch (error) {
      console.error('Ошибка в обработке файла:', error);
    }
  });

  closeTopic.addEventListener("click", function (event) {
    createTopic.remove();
    uploadsFilesTopic = {};
    fileUploadsData = {};
  });
}



function controlParticipiant(dialog, potencialParticipiant) {
  let contactContent = document.querySelector('[data-sn-contract="' + dialog.serialNumber + '"]');
  let findOnPageDialogBox = contactContent.querySelector('[data-id-dialog-box="' + String(dialog.idDialog) + '"]');
  if (findOnPageDialogBox) {
    let findBlockAddMe = findOnPageDialogBox.querySelector('#add-me-dialog');
    if (findBlockAddMe) {
      findBlockAddMe.remove();
    }
    let findBoxControlDialog = findOnPageDialogBox.querySelector('[data-id-dialog-control="' + dialog.idDialog + '"]');

    let boxControlDialog = createTagHtml("div", "control-dialog-participiant", '', '', '', ['id-dialog-control', dialog.idDialog]);
    let addNewParticipiant = createTagHtml("div", "add-participiant", '+ Добавить участников', '', '', ['add-participiant-dialog', dialog.idDialog]);
    let quiteDialog = createTagHtml("div", "quite-dialog", 'Покинуть диалог', '', '', ['quite-dialog', dialog.idDialog]);
    let windowPotencialParcipiant = displayPotencialParcipiants(potencialParticipiant, dialog);
    quiteDialog.addEventListener('click', controlQuitDialog);
    function controlQuitDialog() {
      if (socket.readyState === WebSocket.OPEN) {
        let formData = dataForInteractionDialog(userId, dialog.idDialog, dialog.serialNumber);
        boxControlDialog.remove();
        socket.send('quitDialog::: ' + formData);
      } else {
        console.warn('socket is closed');
      }
    }
    if (!findBoxControlDialog) {
      findOnPageDialogBox.appendChild(boxControlDialog);
      addNewParticipiant.appendChild(windowPotencialParcipiant);
      massAppendChild(
        boxControlDialog,
        addNewParticipiant, quiteDialog
      );
    } else {
      let findwWindowPotencialParcipiant = document.querySelector('#window-participiant-' + dialog.idDialog);
      if (findwWindowPotencialParcipiant) {
        findwWindowPotencialParcipiant.innerHTML = '';
        let newContent = displayPotencialParcipiants(potencialParticipiant, dialog);
        while (newContent.firstChild) {
          findwWindowPotencialParcipiant.appendChild(newContent.firstChild);
        }
      }
    }
  }
}

function addMeParticipiants(dialog) {
  let contactContent = document.querySelector('[data-sn-contract="' + dialog.serialNumber + '"]');
  let findOnPageDialogBox = contactContent.querySelector('[data-id-dialog-box="' + String(dialog.idDialog) + '"]');
  let boxControlDialog = createTagHtml("div", "control-dialog-participiant", '', 'add-me-dialog', '', ['id-dialog-control', dialog.idDialog]);
  let addMeParticipiant = createTagHtml("div", "add-participiant", 'Добавиться в диалог', '', '', ['add-participiant-dialog', dialog.idDialog]);
  if (findOnPageDialogBox) {
    findOnPageDialogBox.appendChild(boxControlDialog);
  }
  boxControlDialog.appendChild(addMeParticipiant);
  addMeParticipiant.addEventListener('click', function () {
    if (!checkIncludesId(dialog.idParticipant, userId)) {
      let formData = dataForInteractionDialog(userId, dialog.idDialog, dialog.serialNumber);
      socket.send('addMeParticipiant::: ' + formData);
    }
    boxControlDialog.remove();
    // flagOnline.className = "my-participiant-participiant";
  });
}

function displayPotencialParcipiants(potencialParticipiant, dialog) {
  let windowPotencialParcipiant = createTagHtml("div", "window-participiant", '', 'window-participiant-' + dialog.idDialog);

  for (const user of potencialParticipiant) {
    if (checkIncludesId(dialog.idParticipant, userId)) {
      let participiant = checkIncludesId(dialog.idParticipant, user.id) ? 'participiant' : 'potencial-participiant';
      let boxPotencialParticipant = createTagHtml("div", "my-participiant", '', '', '', ['id-participant', user.id]);
      let flagOnline = createTagHtml("div", "my-participiant-" + participiant, '', 'participiant' + user.id);
      let nameParticipiant = createTagHtml("div", "my-participiant-name", user.displayName);
      let noticeUserRole = createTagHtml("div", "notice-user-role", '(' + user.roleShow + ')');
      massAppendChild(
        boxPotencialParticipant,
        flagOnline, nameParticipiant, noticeUserRole
      );
      windowPotencialParcipiant.appendChild(boxPotencialParticipant);
      boxPotencialParticipant.addEventListener('click', function () {
        if (!checkIncludesId(dialog.idParticipant, user.id)) {
          let formData = dataForInteractionDialog(user.id, dialog.idDialog, dialog.serialNumber);
          if (socket.readyState === WebSocket.OPEN) {
            socket.send('addParticipiant::: ' + formData);
          } else {
            console.warn('socket is closed');
          }
        }
        // flagOnline.className = "my-participiant-participiant";
      });
    }
    continue;
  }
  return windowPotencialParcipiant;
}


function checkMessageWhose(idAuthor, participants) {
  let whoseMessage;
  participants.forEach(user => {
    if (user.id === idAuthor) {
      whoseMessage = user.whose;
    }
  });
  return whoseMessage;
}

function showTopic(messageFromServer, findOnPageWindow, user, messages) {
  let contactContent = document.querySelector('[data-sn-contract="' + messageFromServer.serialNumber + '"]');
  let dialog, dialogBox, mainMessage, messageBox;
  let typeDialogHTML = checkType(messageFromServer.typeDialog);
  console.log(messageFromServer.serialNumber);
  console.log(messageFromServer.idDialog);
  const didntRead = messageFromServer.didntRead;
  const didntReadMessages = messageFromServer.didntReadMessages;
  const participants = messageFromServer.idParticipant;
  const idAuthor = messageFromServer.idAuthor;
  const contraryParticipants = checkContraryParticipiant(whose, participants);
  const contraryDidntRead = checkContraryParticipiant(whose, didntRead);
  //Флаги
  const flagIncludes = checkIncludesId(participants, userId);
  const flagDidntRead = checkIncludesId(didntRead, userId);
  const flagAuthor = idAuthor === userId;
  const whoseAuthor = checkMessageWhose(idAuthor, participants);
  const didntReadDialog = checkIncludesIdTwoLayer(didntReadMessages, userId);

  const dataFlaged = {
    'flagIncludes': checkIncludesId(participants, userId),
    'flagDidntRead': checkIncludesId(didntRead, userId),
    'flagReadedContrary': checkReaded(contraryParticipants, contraryDidntRead),
    'flagAuthor': idAuthor === userId,
    'whoseAuthor': checkMessageWhose(idAuthor, participants),
    'whoseMessage': whose === whoseAuthor,
    'didntReadDialog': checkIncludesIdTwoLayer(didntReadMessages, userId),
  };
  let findOnPageDialog = contactContent.querySelector('[data-id-dialog="' + messageFromServer.idDialog + '"]');
  let findOnPageDialogBox = contactContent.querySelector('[data-id-dialog-box="' + messageFromServer.idDialog + '"]');

  let findOnPageMessage;
  let replyMessageBox;
  if (findOnPageDialog) {
    findOnPageMessage = findOnPageDialog.querySelector('[data-id-message="' + messageFromServer.idMessage + '"]');
    replyMessageBox = findOnPageDialog.querySelector('#reply_conteiner');
    if (findOnPageMessage) {
      if (findOnPageMessage.id === 'reply_conteiner') {
        findOnPageMessage.setAttribute('data-id-message', messageFromServer.idMessage + 1);
      }
    }
  }
  let iconDialog = createTagHtml('div', 'dialog_icon_author');
  if (messageFromServer.idMessage === 1) {
    if (!findOnPageDialog) {
      dialog = createTagHtml("div", "dialog", '', '', '', ['id-dialog', messageFromServer.idDialog]);
      dialogBox = createTagHtml("div", "dialog-box", '', '', '', ['id-dialog-box', messageFromServer.idDialog]);
      let firstChild = contactContent.firstChild;
      if (firstChild) {
        contactContent.insertBefore(dialogBox, firstChild);
      } else {
        contactContent.appendChild(dialogBox);
      }
      dialogBox.appendChild(dialog);
    } else {
      dialogBox = findOnPageDialogBox;
      dialog = findOnPageDialog;
    }

    dialog.classList.add("dialog_" + typeDialogHTML);

    let labelDialog = createTagHtml('div', 'dialog_label');
    labelDialog.classList.add(typeDialogHTML + '_label');
    let arrowDown = createTagHtml('div', 'dialog_arrow_down');
    let imgArrowDown = createTagHtml('img');
    imgArrowDown.setAttribute('src', '/wp-content/themes/wp-diary/images/arrow_down.svg');
    messageBox = createTagHtml('div', 'dialog_message_main');

    if (!findOnPageMessage) {
      mainMessage = createTagHtml("div", "dialog_main_message", '', '', '', ['id-message', messageFromServer.idMessage]);
      dialog.appendChild(mainMessage);
    } else {
      mainMessage = findOnPageMessage;
      mainMessage.innerHTML = '';
    }
    massAppendChild(
      mainMessage,
      messageBox
    );
    iconDialog.appendChild(labelDialog);
    arrowDown.appendChild(imgArrowDown);
  } else {
    dialog = document.querySelector('[data-id-dialog="' + messageFromServer.idDialog + '"]');
    if (!findOnPageMessage) {
      messageBox = createTagHtml('div', 'dialog_message_reply', '', '', '', ['id-message', messageFromServer.idMessage]);
      dialog.appendChild(messageBox);
    } else {
      // messageBox = findOnPageMessage;
      // messageBox.innerHTML = '';
      if (findOnPageMessage.id) {
        if (findOnPageMessage.id === 'reply_conteiner') {
          messageBox = createTagHtml('div', 'dialog_message_reply', '', '', '', ['id-message', messageFromServer.idMessage]);
          dialog.insertBefore(messageBox, findOnPageMessage);
        }
      } else {
        messageBox = findOnPageMessage;
        if (!flagDidntRead) {
          let messageStatus = messageBox.querySelector('.unreaded_message');
          let messageStatusText = messageBox.querySelector('.message_status_text');
          if (messageStatus && messageStatusText) {
            removeElements(messageStatus, messageStatusText);
          }
        }
        if (replyMessageBox) {
          return;
        } else {
          messageBox.innerHTML = '';
        }
      }
    }
  }

  let messageBody = createTagHtml('div', 'dialog_message');
  if (messageFromServer.idMessage === 1) {
    messageBody.classList.add('dialog_main_message_body');
  }

  let messageDataNew = createHeaderMessage(messageFromServer, dataFlaged, didntRead, participants);

  let messageContent = createTagHtml('div', 'dialog_message_content');

  let decodedMessage = decodeMessage(messageFromServer.messageBody);
  let messageContentTagP = createTagHtml('p', '', decodedMessage);
  
  massAppendChild(
    messageBox,
    iconDialog, messageBody
  );
  massAppendChild(
    messageBody,
    messageDataNew, messageContent
  );

  if (messageFromServer.files !== 'null' && messageFromServer.files !== '[]' && messageFromServer.files !== "") {
    let redactFilesControl = createTagHtml('div', 'dialog_files_uploads_main');
    let boxMessageFiles = createTagHtml('div', 'dialog_files');
    let jsonObjectFiles = JSON.parse(messageFromServer.files);
    let filesLinks = [];
    //let zipArchive = new JSZip();
    for (let index = jsonObjectFiles.length - 1; index >= 0; index--) {
      console.log(jsonObjectFiles[index]);
      
      let filePathArray = decodeURIComponent(jsonObjectFiles[index]);
      // Проверка на массив и выбор первого элемента
      let filePath = Array.isArray(filePathArray) ? filePathArray[0] : filePathArray;
      filesLinks.push(filePath);
      // Дальше ваш код
      let fileName = filePath.split('/').pop(); // получаем имя файла из пути
      let avatarFile = createTagHtml('a', 'dialog_files_link');
      let fileNameParts = fileName.split('.');
      let extension = fileNameParts.pop();
      let name = fileNameParts.join('.');
      let fileNameElement = createTagHtml('span', 'dialog_name', name);
      let fileNameHidden = createTagHtml('span', 'dialog_name_hidden', name);
      avatarFile.setAttribute('href', serverUrl + filePath);
      avatarFile.target = '_blank';
      avatarFile.classList.add('files_from_server');
      massAppendChild(avatarFile, fileNameElement, fileNameHidden);
      checkExtensionFiles(avatarFile, fileNameHidden, extension);
      boxMessageFiles.appendChild(avatarFile);
    }
    if (filesLinks.length > 1) {
      let archiveLink = createTagHtml('a', 'dialog_archive_link', 'Скачать всё');
      archiveLink.removeEventListener('click', clickArchive);
      archiveLink.addEventListener('click', clickArchive);
      function clickArchive() {
        downloadZip(filesLinks, messageFromServer);
      }
      boxMessageFiles.appendChild(archiveLink);
    }
    messageBody.appendChild(redactFilesControl);
    redactFilesControl.appendChild(boxMessageFiles);
  }


  messageContent.appendChild(messageContentTagP);
  

  if (flagAuthor && flagIncludes) {
    let messageAddAnswer = createTagHtml('div', 'message_react');
    let addAnswerButton = createTagHtml('span', 'message_react_reply', 'Добавить');
    addAnswerButton.addEventListener('click', function () {
      handleReplyClick(addAnswerButton, messageFromServer, user);
    });
    messageAddAnswer.appendChild(addAnswerButton);
    messageBody.appendChild(messageAddAnswer);

    if (dataFlaged.didntReadDialog) {
      let messageReactReaded = createButtonReaded(messageFromServer, contactContent);
      messageAddAnswer.appendChild(messageReactReaded);
      // let audioMessage = createMessageControls(messageAddAnswer);
    }
  }
  if (!flagAuthor && flagIncludes) {
    let messageReactBox = createTagHtml('div', 'message_react');
    let messageReactReply = createTagHtml('span', 'message_react_reply', 'Ответить');
    let messageReactReaded = createButtonReaded(messageFromServer, contactContent);
    messageReactReply.addEventListener('click', function () {
      handleReplyClick(messageReactReply, messageFromServer, user);
    });
    massAppendChild(
      messageReactBox,
      messageReactReply, messageReactReaded
    )
    messageBody.appendChild(messageReactBox);
    if (!didntReadDialog) {
      messageReactReaded.remove();
    }
    // let audioMessage = createMessageControls(messageReactBox);
  }
  let unansweredMessagesReact = dialog.querySelectorAll('.message_react');
  if (unansweredMessagesReact.length > 1) {
    unansweredMessagesReact.forEach(function (unansweredMessageReact) {
      let unansweredMessage = unansweredMessageReact.closest('[data-id-message]');
      let idUnansweredMessage = parseInt(unansweredMessage.getAttribute('data-id-message'));
      if (idUnansweredMessage < messageFromServer.idMessage) {
        unansweredMessageReact.remove();
      }
    })
  }
}

function createButtonReaded(messageFromServer, contactContent) {
  let messageReactReaded = createTagHtml('span', 'message_react_readed', 'Прочитано');
  messageReactReaded.addEventListener('click', function () {
    sendToServerReaded(messageFromServer.serialNumber, messageFromServer.idDialog, messageFromServer.idMessage);
    let findDialogBox = contactContent.querySelector('[data-id-dialog-box="' + messageFromServer.idDialog + '"]');
    let findUnreadedMarkerDialog = findDialogBox.querySelector('.marker-unreaded-message-dialog');
    messageReactReaded.remove();
    findUnreadedMarkerDialog.remove();
  });
  return messageReactReaded;
}


function displayDidintRead(didntRead, participants) {
  let unvisibleDidntReadBlock = createTagHtml('div', 'block_users_unreaded_message');
  if (didntRead.length > 0) {
    participants.forEach(userContrary => {
      let userContraryParticipant = createTagHtml('div', 'user_contrary_participant', userContrary.displayName);
      let boxUserContraryParticipant = createTagHtml('div', 'box_user_contrary_participant');
      // Изначально предполагаем, что пользователь не прочитал сообщение
      let userDidntReadHTML = createTagHtml('div', 'marker_user_unreaded_message', '', 'user-unreaded-' + userContrary.id);
      // Проверяем, прочитал ли пользователь сообщение
      didntRead.forEach(userDidntRead => {
        if (userDidntRead.id === userContrary.id) {
          userDidntReadHTML = createTagHtml('div', 'marker_user_readed_message', '', 'user-readed-' + userDidntRead.id);
        }
      });
      // Добавляем HTML для пользователя, который (не) прочитал сообщение
      boxUserContraryParticipant.appendChild(userDidntReadHTML);
      // Добавляем HTML для участника
      boxUserContraryParticipant.appendChild(userContraryParticipant);
      // Добавляем блок участника в основной блок
      unvisibleDidntReadBlock.appendChild(boxUserContraryParticipant);
    });
  }
  return unvisibleDidntReadBlock;
}

function checkboxForTopic(where, type) {
  let checkboxTextOne, checkboxTextTwo, checkboxTextThree, checkboxTextFour, checkboxTextFive;
  let checkboxIdOne, checkboxIdTwo, checkboxIdThree, checkboxIdFour, checkboxIdFive;
  switch (type) {
    case "Заказ":
      checkboxTextOne = "Схемы встройки";
      checkboxTextTwo = "Расчёт погонажа";
      checkboxTextThree = "Чертежи НСТ";
      checkboxTextFour = "Перенумерация";
      checkboxTextFive = "Завершения";
      checkboxIdOne = "scheme";
      checkboxIdTwo = "molding";
      checkboxIdThree = "blueprints";
      checkboxIdFour = "renumbering";
      checkboxIdFive = "completion";
      break;
    case "Рекламация":
      checkboxTextOne = "Чекбок 1";
      checkboxTextTwo = "Чекбок 2";
      checkboxTextThree = "Чекбок 3";
      checkboxTextFour = "Чекбок 4";
      checkboxTextFive = "Чекбок 5";
      checkboxIdOne = "checkbox-1";
      checkboxIdTwo = "checkbox-2";
      checkboxIdThree = "checkbox-3";
      checkboxIdFour = "checkbox-4";
      checkboxIdFive = "checkbox-5";
      break;
  }

  let checkboxesBlock = createTagHtml("div", "create_input_box");
  let checkboxBlockOne = createTagHtml("div", "create_topic_box_input");
  let checkboxBlockTwo = createTagHtml("div", "create_topic_box_input");
  let checkboxBlockThree = createTagHtml("div", "create_topic_box_input");
  let checkboxBlockFour = createTagHtml("div", "create_topic_box_input");
  let checkboxBlockFive = createTagHtml("div", "create_topic_box_input");
  let checkboxOne = createTagHtml("input", "create_topic_checkbox", "",
    checkboxIdOne, "checkbox");
  let checkboxTwo = createTagHtml("input", "create_topic_checkbox", "",
    checkboxIdTwo, "checkbox");
  let checkboxThree = createTagHtml("input", "create_topic_checkbox", "",
    checkboxIdThree, "checkbox");
  let checkboxFour = createTagHtml("input", "create_topic_checkbox", "",
    checkboxIdFour, "checkbox");
  let checkboxFive = createTagHtml("input", "create_topic_checkbox", "",
    checkboxIdFive, "checkbox");
  let labelCheckboxOne = createTagHtml("label", "", checkboxTextOne, checkboxIdOne);
  let labelCheckboxTwo = createTagHtml("label", "", checkboxTextTwo, checkboxIdTwo);
  let labelCheckboxThree = createTagHtml("label", "", checkboxTextThree, checkboxIdThree);
  let labelCheckboxFour = createTagHtml("label", "", checkboxTextFour, checkboxIdFour);
  let labelCheckboxFive = createTagHtml("label", "", checkboxTextFive, checkboxIdFive);
  let topicNotice = createTagHtml("div", "create_topic_box_notice", "*Вы не сможете создать тему если все эти пункты не будут заполнены");

  where.appendChild(checkboxesBlock);
  massAppendChild(
    checkboxesBlock,
    checkboxBlockOne, checkboxBlockTwo, checkboxBlockThree, checkboxBlockFour, checkboxBlockFive, topicNotice
  );
  massAppendChild(checkboxBlockOne, checkboxOne, labelCheckboxOne);
  massAppendChild(checkboxBlockTwo, checkboxTwo, labelCheckboxTwo);
  massAppendChild(checkboxBlockThree, checkboxThree, labelCheckboxThree);
  massAppendChild(checkboxBlockFour, checkboxFour, labelCheckboxFour);
  massAppendChild(checkboxBlockFive, checkboxFive, labelCheckboxFive);
}

function changeNameContract(window, data) {
  let contractTitle = window.querySelector('[data-contract-sn="' + data.serialNumber + '"]');
  if (contractTitle) {
    let contractSN = contractTitle.getAttribute('data-contract-sn');
    let contractTitleH1 = contractTitle.querySelector('.h1-contract');
    let currentContractName = contractTitleH1.textContent;

    const checkSN = parseInt(contractSN) === parseInt(data.serialNumber);
    const checkName = currentContractName !== data.nameContract;
    if (checkSN && checkName) {
      contractTitleH1.textContent = data.nameContract;
    }
  }
}

// Определение функции сохранения
function saveMessage(parent, newText, newFiles, user, deleteFiles) {
  // Здесь вы можете добавить код для отправки данных на сервер

  updateContent(parent, newText, user);

  parent.classList.remove('editable-message')
  parent.classList.add('redacted-message');
  // Обработка новых файлов
  if (newFiles && newFiles.length > 0) {
  }

  // Обработка удаленных файлов
  if (deleteFiles && deleteFiles.length > 0) {
    for (let i = 0; i < deleteFiles.length; i++) {
      let fileUrl = deleteFiles[i].getAttribute('href');
      deleteFiles[i].remove();
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

  updateContent(parent, oldText);
  let deletedFiles = parent.querySelectorAll('.deleted_dialog_files');
  // Обработка новых файлов
  for (var i = 0; i < deletedFiles.length; i++) {
    deletedFiles[i].classList.remove('deleted_dialog_files');
  }
}

function removeDeleteSpan(filesContainer) {
  if (filesContainer) {
    let deleteSpans = filesContainer.querySelectorAll('.delete_dialog_files');
    deleteSpans.forEach(function (deleteSpan) {
      deleteSpan.remove();
    });
  }
}

function createRedactButton(parentContainer, afterContainer, parentMessage, user) {
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
    handleRedactClick(redactButton, parentMessage, user);
  });
}

function updateContent(parent, newText, user) {
  let contentElement = parent.querySelector('.dialog_message_content');
  let overalParent = contentElement.parentElement;
  const data = {
    'firstNameAuthor': user.firstname,
    'lastNameAuthor': user.lastname,
    'idAuthor': user.idUser,
    'dateSendTimestamp': Math.floor(Date.now() / 1000),
  };
  const dataFlaged = { 'flagAuthor': true };

  let contentElementContainer = document.createElement('p');
  let dialogMessageData = createHeaderMessage(data, dataFlaged);

  let messageAddAnswer = createTagHtml('div', 'message_react');
  let addAnswerButton = createTagHtml('span', 'message_react_reply', 'Добавить');

  let buttonsDeleteFiles = overalParent.querySelectorAll('.delete_dialog_files');



  contentElementContainer.innerHTML = newText;
  if (contentElement.textContent) {
    contentElement.textContent = '';
    dialogMessageData.innerHTML = '';
  }

  messageAddAnswer.appendChild(addAnswerButton);
  contentElement.appendChild(contentElementContainer);
  overalParent.appendChild(messageAddAnswer);

  overalParent.prepend(dialogMessageData);
  hiddenAfterUpdate(overalParent);

  if (buttonsDeleteFiles) {
    buttonsDeleteFiles.forEach((item) => {
      item.remove();
    });
  }
}

function hiddenAfterUpdate(element) {
  let parent = element.parentElement;
  let mainParent = parent.parentElement;

  let messageViewPrev = mainParent.querySelector('.message_view_prev');
  let seriasReplies = mainParent.querySelectorAll('.dialog_message_reply');

  if (messageViewPrev && !messageViewPrev.id) {
    seriasReplies.forEach((item, index) => {
      if (index < seriasReplies.length - 2) {
        item.style.display = 'none';
      }
    });
  }
}

// Определение функции обработки события ОТВЕТИТЬ 
function handleReplyClick(replyButton, parentMessage, user) {
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

  newLine(textareaElement);
  textareaElement.setAttribute('lang', 'ru');
  textareaElement.spellcheck = true;
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

  function submitClick() {
    let oldfiles;
    newLineHandler(textareaElement);
    saveMessage(dialogMessageReply, textareaElement.value, uploadsFilesReply, user);
    submitTopicLogic(textareaElement, uploadsFilesReply, parentMessage.typeDialog, parentMessage.serialNumber, parentMessage.idDialog, oldfiles, parentMessage.idMessage);
    textareaElement.remove();
    saveElement.remove();
    closeElement.remove();
    replyFilesControl.remove();
    let contactContent = document.querySelector('[data-sn-contract="' + parentMessage.serialNumber + '"]');
    let findOnPageDialog = contactContent.querySelector('[data-id-dialog="' + parentMessage.idDialog + '"]');
    parentMessage.idMessage = parseInt(parentMessage.idMessage, 10);
    let prevMessage = findOnPageDialog.querySelector('[data-id-message="' + parentMessage.idMessage + '"]');
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

function controlUnreadedMarker(data, findLabelDialog) {
  let didntRead = data.didntRead;
  let findMarkerUnreadedMessage = findLabelDialog.querySelector('.marker-unreaded-message-dialog');
  if (checkIncludesId(didntRead, userId) && data.idAuthor !== userId && !findMarkerUnreadedMessage) {
    let markerUnreadedMessage = createTagHtml('div', 'marker-unreaded-message-dialog');
    findLabelDialog.appendChild(markerUnreadedMessage);
  }
}

function controlPrevMessage(data, parent, sendFetch) {
  Object.keys(data).forEach(function (key) {
    let findOnPageDialogBox = parent.querySelector('[data-id-dialog-box="' + data[key].idDialog + '"]');
    let findOnPageDialog = parent.querySelector('[data-id-dialog="' + data[key].idDialog + '"]');
    let findReplyContainer = findOnPageDialog.querySelector('#reply_conteiner');
    let findLabelDialog = findOnPageDialogBox.querySelector('.dialog_label');
    controlUnreadedMarker(data[key], findLabelDialog);
    if (findReplyContainer) {
      return;
    }
    if (!findOnPageDialog) {
      return;
    }
    let findMainMessage = findOnPageDialog.querySelector('.dialog_main_message');
    let findReplyMessages = findOnPageDialog.querySelectorAll('[data-id-message].dialog_message_reply');
    let findPrevMessage = findOnPageDialog.querySelector('.message_view_prev');
    let findСontrolAllMessages = findOnPageDialogBox.querySelector('.control-show-all-messages');
    let viewPrevMessages = createTagHtml('span', 'message_view_prev', 'Предыдущие сообщения');
    let arrowViewPrevMessages = createTagHtml('span', 'message_view_prev_arrowup');
    let controlShowAllMessages = createTagHtml('div', 'control-show-all-messages', data.length - 1);
    let arrowShowAllMessages = createTagHtml('span', 'arrow_show_all_messages');
    if (!sendFetch) {
      if (findMainMessage && data.length > 1 && !findСontrolAllMessages) {
        findOnPageDialog.insertAdjacentElement('afterend', controlShowAllMessages);
        controlShowAllMessages.appendChild(arrowShowAllMessages);
      }
      if (findСontrolAllMessages) {
        let counterNewMessages = parseInt(findСontrolAllMessages.innerText);
        if (counterNewMessages < data.length - 1) {
          findСontrolAllMessages.innerText = data.length - 1;
          findСontrolAllMessages.appendChild(arrowShowAllMessages);
          if (findСontrolAllMessages.id === 'dialog-open') {
            arrowShowAllMessages.style.transform = 'rotate(180deg)';
          }
          findСontrolAllMessages.appendChild(arrowShowAllMessages);
        }
      }
      let flagOpenDialog = findOnPageDialogBox.querySelector('#dialog-open');
      let flagViewPrevMessages = findOnPageDialogBox.querySelector('#show-all-messages-dialog');
      if (findMainMessage && data.length > 3) {
        if (!findPrevMessage) {
          viewPrevMessages.appendChild(arrowViewPrevMessages);
          findMainMessage.insertAdjacentElement('afterend', viewPrevMessages);
          viewPrevMessages.removeEventListener('click', function () {
            hidePrevMessages(viewPrevMessages, arrowViewPrevMessages);
          });
          viewPrevMessages.removeEventListener('click', function () {
            showPrevMessages(viewPrevMessages, arrowViewPrevMessages);
          });
          viewPrevMessages.addEventListener('click', function () {
            hidePrevMessages(viewPrevMessages, arrowViewPrevMessages);
          });

        }
        if (!flagOpenDialog) {
          for (var i = 0; i < data.length - 2; i++) {
            findReplyMessages[i].style.display = 'none';
          }
        }
        if (!flagViewPrevMessages) {
          for (var i = 0; i < data.length - 3; i++) {
            findReplyMessages[i].style.display = 'none';
          }
        }
      }
      if (findMainMessage && findReplyMessages.length > 0) {
        for (var i = 0; i < findReplyMessages.length; i++) {
          if (!flagOpenDialog) {
            findReplyMessages[i].style.display = 'none';
          }
        }
        if (flagOpenDialog) {
          controlShowAllMessages = flagOpenDialog;
          controlShowAllMessages.removeEventListener('click', function () {
            hideDialog(viewPrevMessages, controlShowAllMessages, arrowViewPrevMessages);
          });
          controlShowAllMessages.addEventListener('click', function () {
            hideDialog(viewPrevMessages, controlShowAllMessages, arrowViewPrevMessages);
          });
          if (data.length < 3) {
            viewPrevMessages.style.display = 'none';
          } else {
            viewPrevMessages.style.display = 'block';
          }
        } else {
          viewPrevMessages.style.display = 'none';
          controlShowAllMessages.addEventListener('click', function () {
            showDialog(viewPrevMessages, controlShowAllMessages, arrowViewPrevMessages);
          });
        }
      }
    } else if (sendFetch && data.length === 2) {
      if (!findСontrolAllMessages) {
        let controlShowAllMessages = createTagHtml('div', 'control-show-all-messages', '1');
        let arrowShowAllMessages = createTagHtml('span', 'arrow_show_all_messages');
        findOnPageDialog.insertAdjacentElement('afterend', controlShowAllMessages);
        controlShowAllMessages.appendChild(arrowShowAllMessages);
        controlShowAllMessages.setAttribute('id', 'dialog-open');
        arrowShowAllMessages.style.transform = 'rotate(180deg)';
        controlShowAllMessages.style.top = '-1px';
        controlShowAllMessages.style.bottom = 'auto';
        controlShowAllMessages.addEventListener('click', function () {
          hideDialog(viewPrevMessages, controlShowAllMessages, arrowViewPrevMessages);
        });
      }
    }
    function showDialog(viewPrevMessages, controlShowAllMessages, arrowViewPrevMessages) {
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
      controlShowAllMessages.removeEventListener('click', function () {
        showDialog(viewPrevMessages, controlShowAllMessages, arrowViewPrevMessages);
      });
      controlShowAllMessages.addEventListener('click', function () {
        hideDialog(viewPrevMessages, controlShowAllMessages, arrowViewPrevMessages);
      });
      viewPrevMessages.addEventListener('click', function () {
        showPrevMessages(viewPrevMessages, arrowViewPrevMessages);
      });
    }
    function hideDialog(viewPrevMessages, controlShowAllMessages, arrowViewPrevMessages) {
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
      arrowShowAllMessages.style.transform = 'rotate(0deg)';
      controlShowAllMessages.style.top = 'auto';
      controlShowAllMessages.style.bottom = '-1px';
      controlShowAllMessages.id = '';
      controlShowAllMessages.removeEventListener('click', function () {
        hideDialog(viewPrevMessages, controlShowAllMessages, arrowViewPrevMessages);
      });
      controlShowAllMessages.addEventListener('click', function () {
        showDialog(viewPrevMessages, controlShowAllMessages, arrowViewPrevMessages);
      });
      viewPrevMessages.removeEventListener('click', function () {
        hidePrevMessages(viewPrevMessages, arrowViewPrevMessages);
      });
      viewPrevMessages.addEventListener('click', function () {
        showPrevMessages(viewPrevMessages, arrowViewPrevMessages);
      });
    }
    function showPrevMessages(viewPrevMessages, arrowViewPrevMessages) {
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
      viewPrevMessages.removeEventListener('click', function () {
        showPrevMessages(viewPrevMessages, arrowViewPrevMessages);
      });
      viewPrevMessages.addEventListener('click', function () {
        hidePrevMessages(viewPrevMessages, arrowViewPrevMessages);
      });
    }
    function hidePrevMessages(viewPrevMessages, arrowViewPrevMessages) {
      let findReplyMessages = findOnPageDialog.querySelectorAll('[data-id-message].dialog_message_reply');
      for (var i = 0; i < findReplyMessages.length - 2; i++) {
        findReplyMessages[i].style.display = 'none';
      }
      viewPrevMessages.textContent = 'Предыдущие сообщения';
      viewPrevMessages.id = '';
      viewPrevMessages.appendChild(arrowViewPrevMessages);
      arrowViewPrevMessages.style.transform = 'rotate(0deg)';
      viewPrevMessages.removeEventListener('click', function () {
        hidePrevMessages(viewPrevMessages, arrowViewPrevMessages);
      });
      viewPrevMessages.addEventListener('click', function () {
        showPrevMessages(viewPrevMessages, arrowViewPrevMessages);
      });
    };
  });
}

function sendToServerReaded(serialNumber, idDialog, idMessage) {
  let formData = {
    "serialNumber": serialNumber,
    "idDialog": idDialog,
    "idMessage": idMessage,
  }
  let jsonData = JSON.stringify(formData);
  socket.send('readedMessage::: ' + jsonData);
}

function dataForInteractionDialog(idUser, idDialog, serialNumber) {
  let formData = {
    'idDialog': idDialog,
    'idUser': idUser,
    'serialNumber': serialNumber,
  }
  formData = JSON.stringify(formData);
  return formData;
}

function submitTopicLogic(textarea, uploadsFileslocal, type, sn, idDialog, oldFilesSave, idMessage, status) {
  newLineHandler(textarea);
  collectFormData(type, sn, textarea, uploadsFileslocal, idDialog, oldFilesSave, idMessage, status)
    .then(formData => {
      // sendFilesViaWebSocket(formData, socket);
      console.log('newMessage отправляем на сервер:');
      console.log(formData);
      socket.send('newMessage::: ' + JSON.stringify(formData));
      // Обнуляем переменные, если это нужно
      uploadsFileslocal = [];
      fileUploadsData = {};
    })
    .catch(error => {
      console.error('Ошибка при сборе данных формы:', error);
    });
}

function createHeaderMessage(data, dataFlaged, didntRead, participants) {
  let messageData = createTagHtml('div', 'dialog_message_data');
  let messageAuthor = createTagHtml('span', 'author_message', data.firstNameAuthor + ' ' + data.lastNameAuthor);
  if (!dataFlaged.flagAuthor) {
    messageAuthor.classList.add('author_message_manager');
  }
  let redditBullShit = createTagHtml('div', 'reddit-bulshit', '•');
  let messageDate = createTagHtml('span', 'date_message', getTimeAgo(data.dateSendTimestamp));
  let hiddenMessageDate = createTagHtml('div', 'hidden_date_message', formatTimestamp(data.dateSendTimestamp));

  let messageStatusBoxNew = dataFlaged ? createStatusBox(data.idAuthor, dataFlaged, didntRead, participants) : null;

  let clearFix = createTagHtml('div', 'clearfix');
  massAppendChild(
    messageData,
    messageAuthor, redditBullShit, messageDate, messageStatusBoxNew, clearFix
  );
  messageDate.appendChild(hiddenMessageDate);
  return messageData;
}



// 
function createStatusBox(idAuthor, dataFlaged, didntRead, participants) {
  let messageStatusBox = createTagHtml('div', 'unreaded_message_box');
  let firstCase = !dataFlaged.whoseMessage && dataFlaged.flagDidntRead && dataFlaged.flagIncludes;
  let secondCase = dataFlaged.whoseMessage && dataFlaged.flagDidntRead && dataFlaged.flagIncludes;
  let thirdCase = dataFlaged.flagAuthor && dataFlaged.flagReadedContrary === 2 && dataFlaged.flagIncludes;
  let forthCase = dataFlaged.flagAuthor && dataFlaged.flagReadedContrary === 1 && dataFlaged.flagIncludes;
  let fifthCase = dataFlaged.flagAuthor && Object.keys(dataFlaged).length === 1;

  let messageStatus;
  let messageStatusText;
  let unvisibleDidntReadBlock;

  if (firstCase || secondCase || thirdCase || fifthCase) {
    messageStatusText = createTagHtml('div', 'message_status_text', 'Не прочитано');
    messageStatus = createTagHtml('div', 'unreaded_message', '', '', '', ['id-author', idAuthor]);
  } else if (forthCase && didntRead && participants) {
    messageStatusText = createTagHtml('div', 'message_status_text', 'Частично');
    messageStatus = createTagHtml('div', 'unreaded_message', '', '', '', ['id-author', idAuthor]);
    unvisibleDidntReadBlock = displayDidintRead(didntRead, participants);
  }

  if (messageStatus && messageStatusText) {
    massAppendChild(
      messageStatusBox,
      messageStatus, messageStatusText, unvisibleDidntReadBlock
    )
  }
  return messageStatusBox;
}

function createMessageControls(parentMessage) {
  const controlsContainer = createTagHtml('div', 'message-controls');
  
  // Add audio recording button
  const audioButton = createTagHtml('button', 'audio-record-button', '🎤');
  let mediaRecorder = null;
  let isRecording = false;
  let audioChunks = [];

  audioButton.addEventListener('click', async () => {
    if (!isRecording) {
      try {
        // Start recording
        const result = await startAudioRecording();
        mediaRecorder = result.mediaRecorder;
        audioChunks = result.audioChunks;
        isRecording = true;
        audioButton.classList.add('recording');
        audioButton.textContent = '⏺️';
      } catch (err) {
        console.error('Failed to start recording:', err);
      }
    } else {
      try {
        // Stop recording
        const { audioBlob, audioUrl } = await stopAudioRecording(mediaRecorder, audioChunks);
        isRecording = false;
        audioButton.classList.remove('recording');
        audioButton.textContent = '🎤';
        
        if (audioBlob && audioUrl) {
          // Create a file from the blob
          const audioFile = new File([audioBlob], 'audio-message.webm', { type: 'audio/webm' });
          
          // Add the audio player to the message
          if (parentMessage) {
            const audioPlayer = await createAudioPlayer(audioUrl);
            parentMessage.appendChild(audioPlayer);
          }

          // Create a download link
          const downloadLink = document.createElement('a');
          downloadLink.href = audioUrl;
          downloadLink.download = 'recording.webm';
          downloadLink.textContent = 'Download Recording';
          downloadLink.style.display = 'block';
          downloadLink.style.marginTop = '10px';
          
          // Append download link
          if (parentMessage) {
            parentMessage.appendChild(downloadLink);
          }
        }
      } catch (err) {
        console.error('Failed to stop recording:', err);
      }
    }
  });

  controlsContainer.appendChild(audioButton);
  parentMessage.appendChild(controlsContainer);
}
