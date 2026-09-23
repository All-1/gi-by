"use strict";

function updateLastSN(where, sn = undefined) {
  let newWhere = choseWhereForWindow(where);
  let nameProperty = newWhere + 'SN';
  lastSN[nameProperty] = sn;
}

function isFloat(value) {
  return typeof value === 'number' &&
    !isNaN(value) &&
    !Number.isInteger(value);
}

function invokeDataForWindow(where, sn) {
  let newWhere = choseWhereForWindow(where);
  updateLastSN(where, sn);
  if (sn) {
    socket.send('getPotencialParticipiant::: ' + sn);
    socket.send('getDataFor' + newWhere + '::: ' + sn);
  }
}

function chooseAccountDetails(data, currId) {
  if (currId === 0) {
    return data.accountDetailsEUR;
  } else if (currId === 1) {
    return data.accountDetailsBYN;
  } else if (currId === 2) {
    return data.accountDetailsRUB;
  } else if (currId === 3) {
    return data.accountDetailsRUB2;
  }
}

function checkCurrId(data, currId) {
  if (data.accountDetailsRUB2 !== undefined && data.accountDetailsRUB2 !== '-' && data.accountDetailsRUB2 !== '') {
    return 3;
  } else {
    return currId;
  } 
}

function chooseCurrency(currId) {
  if (currId === 0) {
    return 'бел.руб.';
  } else if (currId === 1) {
    return 'евро';
  } else if (currId === 2) {
    return 'рос.руб.';
  } else if (currId === 3) {
    return 'рос.руб.';
  }
}

function choseWhereForWindow(where) {
  let newWhere = switchWhere(where).slice(0, -1);
  newWhere = newWhere === 'Order' ? 'Contract' : newWhere;
  return newWhere;
}

function switchWhere(where) {
  if (where.length === 0) return ''; // Если строка пустая, возвращаем пустую строку

  // Преобразуем первую букву в заглавную, оставляя остальную часть строки без изменений
  let whereForCase = where.charAt(0).toUpperCase() + where.slice(1);
  return whereForCase;
}

function toCapsCase(where) {
  if (where.length === 0) return ''; // Если строка пустая, возвращаем пустую строку

  // Преобразуем первую букву в заглавную, оставляя остальную часть строки без изменений
  let whereForCase = where.charAt(0).toUpperCase() + where.slice(1);
  return whereForCase;
}

function clearBlocks(...blocks) {
  blocks.forEach(block => {
    if (block) {
      block.innerHTML = ''; // Или используйте textContent = '' при необходимости
    }
  });
}

function switchCommand(buttonID) {
  // Удаляем тире
  let noHyphens = buttonID.replace(/-/g, ' ');
  // Преобразуем первую букву каждой новой строки в заглавную
  let formattedString = noHyphens.split(' ').map((word, index) => {
    // Пропускаем первое слово, так как его первая буква не должна быть заглавной
    if (index === 0) {
      return word;
    }
    return word.charAt(0).toUpperCase() + word.slice(1);
  }).join('');
  return formattedString;
}

function eventListenerCallContractWindow(data, contractLink, user, where) {
  contractLink.addEventListener("click", function (event) {
    event.preventDefault(); // Отмена действия по умолчанию (перехода по ссылке)
    callContractModalWindow(data.serialNumber, data.nameContract, user, where);
    document.documentElement.classList.add('no-scrollbar');
  });
}

function clickRedactTitle(title, container, listenerFunction) {
  let inputForTitle = createTagHtml('input', 'contract-title-input', '', '', '');
  let cancelRedaction = createTagHtml('div', 'close-redact-title');
  let saveRadaction = createTagHtml('div', 'accept-redact-title');
  let containerControlRedact = createTagHtml('div', 'control-redact-title');
  inputForTitle.value = title.textContent;
  inputForTitle.name = 'title-contract';
  let contractH1 = title;
  container.insertBefore(inputForTitle, container.firstChild);
  container.insertBefore(containerControlRedact, container.firstChild);
  massAppendChild(
    containerControlRedact,
    cancelRedaction, saveRadaction
  )

  inputForTitle.focus();

  title.remove();
  title.removeEventListener('click', listenerFunction);
  saveRadaction.addEventListener('click', listenerSave);
  cancelRedaction.addEventListener('click', listenerCancel);
  function listenerSave() {
    clickSaveTitle(contractH1, inputForTitle, container, containerControlRedact);
  }
  function listenerCancel() {
    clickCancelRedaction(contractH1, inputForTitle, container, containerControlRedact)
  }
}

function clickCancelRedaction(title, inputForTitle, container, containerControl) {
  removeCertainBlock('.notice-redact-title', container);
  inputForTitle.remove();
  containerControl.remove();
  container.insertBefore(title, container.firstChild);
  title.addEventListener("click", listenerRedactTitle);
  function listenerRedactTitle() {
    clickRedactTitle(title, container, listenerRedactTitle);
  }
}
function clickSaveTitle(title, inputForTitle, container, containerControl) {

  let oldTitle = title.textContent;
  let newTitle = title;
  if (oldTitle !== inputForTitle.value && inputForTitle.value) {
    newTitle.textContent = inputForTitle.value;
    removeCertainBlock('.notice-redact-title', container);

    inputForTitle.remove();
    containerControl.remove();

    container.insertBefore(newTitle, container.firstChild);
    newTitle.addEventListener("click", listenerRedactTitle);
    function listenerRedactTitle() {
      clickRedactTitle(newTitle, container, listenerRedactTitle);
    }
    // noticeBlock.remove();
    let sn = container.getAttribute('data-contract-sn');
    let formData = {
      'nameContract': inputForTitle.value,
      'serialNumber': sn
    };
    let jsonData = JSON.stringify(formData);
    socket.send('changeNameContract::: ' + jsonData);
  } else {
    let noticeBlock = createTagHtml('div', 'notice-redact-title', '* Нет изменений. Внесите или отмените.');
    container.appendChild(noticeBlock);
  }
}

function removeCertainBlock(classOrId, container) {
  let findNoticeBlock = container.querySelector(classOrId);
  if (findNoticeBlock) {
    findNoticeBlock.remove();
  }
}

function checkRole(role) {
  if (role === 'dealer' || role === 'designer_dealer') {
    return false;
  }
  return true;
}

function definePositionUser(role) {
  let position;
  switch (role) {
    case "distributor":
      position = "Дистрибьютер";
      break;
    case "free_dealer":
    case "dealer":
      position = "Дилер";
      break;
    case "designer_dealer":
      position = "Дизайнер салона";
      break;
    case "consultant":
      position = "Консультант";
      break;
    case "manager":
      position = "Менеджер";
      break;
    case "complaint_handler":
      position = "Специалист по качеству";
      break;
    case "administrator":
      position = "Админ";
      break;
    case "specialist":
      position = "Узкий специалист";
      break;
    case "bookkeeper":
      position = "Бухгалтер";
      break;
    case "shipment_manager":
      position = "Менеджер по отгрузке";
      break;
    case "sales_manager":
      position = "Менеджер по продажам";
      break;
  }
  return position;
}

function changeUserRole(potencialParticipiant) {
  potencialParticipiant.forEach(user => {
    user.roleShow = definePositionUser(user.role);
  });
  return potencialParticipiant;
}
function partyVerification($userRole) {
  let userParty = '';
  switch ($userRole) {
    case "manager":
    case "consultant":
    case "complaint_handler":
    case "bookkeeper":
    case "shipment_manager":
    case "specialist":
    case "administrator":
      userParty = 'factory_worker';
      break;
    case "distributor":
    case "free_dealer":
    case "dealer":
    case "designer_dealer":
      userParty = 'contractor';
      break;
  }
  return userParty;
}

function changeTypeInArray(participants) {

  for (let key in participants) {
    if (participants.hasOwnProperty(key)) {
      participants[key].id = parseInt(participants[key].id); // Или parseFloat для десятичных чисел
    }
  }
  return participants;
}
function checkIncludesId(participiant, userId) {

  if (participiant) {
    if (participiant.length > 0) {
      for (let key in participiant) {
        if (participiant.hasOwnProperty(key)) {
          // participiant.id = parseInt(participiant.id); // Или parseFloat для десятичных чисел
          if (parseInt(userId) === parseInt(participiant[key].id)) {
            return true;
          }
        }
      }
      return false;
    }
  }
}

function checkIncludesIdTwoLayer(dialogpDidntRead, userId) {
  let flag = false;
  if (dialogpDidntRead) {
    if (dialogpDidntRead.length > 0) {
      dialogpDidntRead.forEach(didntReadMessage => {
        if (didntReadMessage.length > 0) {
          didntReadMessage.forEach(user => {
            if (userId === user.id) {
              flag = true;
            }
          });
        }
      });
      return flag;
    }
  }
}

function checkContraryParticipiant(userWhose, userArray) {
  let contraryParticipiant = [];
  userArray.forEach(user => {
    if (user.whose !== userWhose) {
      contraryParticipiant.push(user);
    }
  });

  return contraryParticipiant;
}

function checkReaded(contraryParticipiant, contraryDidntRead) {
  if (contraryDidntRead.length === contraryParticipiant.length) {
    return 2;
  } else if (contraryDidntRead.length !== 0) {
    return 1;
  } else {
    return 0;
  }
}

function transliterate(text) {
  // const translit = {
  //   'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
  //   'е': 'e', 'ё': 'e', 'ж': 'zh', 'з': 'z', 'и': 'i',
  //   'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
  //   'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
  //   'у': 'u', 'ф': 'f', 'х': 'kh', 'ц': 'ts', 'ч': 'ch',
  //   'ш': 'sh', 'щ': 'sch', 'ы': 'y', 'э': 'e', 'ю': 'yu',
  //   'я': 'ya', 'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G',
  //   'Д': 'D', 'Е': 'E', 'Ё': 'E', 'Ж': 'Zh', 'З': 'Z',
  //   'И': 'I', 'Й': 'Y', 'К': 'K', 'Л': 'L', 'М': 'M',
  //   'Н': 'N', 'О': 'O', 'П': 'P', 'Р': 'R', 'С': 'S',
  //   'Т': 'T', 'У': 'U', 'Ф': 'F', 'Х': 'Kh', 'Ц': 'Ts',
  //   'Ч': 'Ch', 'Ш': 'Sh', 'Щ': 'Sch', 'Э': 'E', 'Ю': 'Yu',
  //   'Я': 'Ya', 'ь': '', 'ъ': '', ' ': '_'
  // };
  const translit = {
    ' ': '_'
  };

  let result = '';
  for (let i = 0; i < text.length; i++) {
    const char = text[i];
    const translitChar = translit[char];
    result += translitChar !== undefined ? translitChar : char;
  }

  return result;
}

function translitType(type) {
  switch (type) {
    case 'Консультация':
      type = 'Consultation';
      break;
    case 'Заказ':
      type = 'Order';
      break;
    case 'Рекламация':
      type = 'Complaint';
      break;
  }
  return type;
}

function whereUpdate(where) {
  let whereUpdate;
  switch (where) {
    case 'contracts':
      whereUpdate = 'data-sn-contract-table';
      break;
    case 'orders':
      whereUpdate = 'data-nf-order-table';
      break;
    case 'order-window':
      whereUpdate = 'data-nf-order-window';
      break;
    case 'contractors':
      whereUpdate = 'data-id-user-contractors';
      break;
    case 'invoices':
      whereUpdate = 'data-nf-invoices';
      break;
  }
  return whereUpdate;
}

function setDynamicProperty(where) {
  let whereUpdate;
  switch (where) {
    case 'contracts':
      whereUpdate = 'serialNumber';
      break;
    case 'order-window':
    case 'orders':
      whereUpdate = 'id';
      break;
    case 'contractors':
      whereUpdate = 'idUser';
      break;
  }
  return whereUpdate;
}

function checkUpdateTable(data, where) {
  let allSerialNumbers = [];
  let dynamicProperty = setDynamicProperty(where);
  Object.keys(data).forEach(function (key) {
    allSerialNumbers.push(data[key][dynamicProperty]);
  });
  let variable = whereUpdate(where);
  let rowContracts = document.querySelectorAll('[' + variable + ']');
  let flag = false;
  for (let i = 0; i < rowContracts.length; i++) {
    let rowContract = rowContracts[i];
    let serialNumber = rowContract.getAttribute(variable);
    if (!allSerialNumbers.includes(serialNumber)) {
      rowContract.parentNode.removeChild(rowContract);
      flag = true;
    }
    if (rowContracts.length < allSerialNumbers.length) {
      flag = true;
    }
  }
  return flag;
}


function isDivisibleByFive(number) {
  return number % 5 === 0;
}

function nearestMultipleOfFive(number) {
  if (number < 5) {
    return 1;
  } else {
    return Math.floor(number / 5) * 5;
  }
}

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

function converTStoDate(timestamp) {
  // Получение компонентов даты
  if (timestamp > 0) {
    let date = new Date(timestamp * 1000);
    let day = ('0' + date.getUTCDate()).slice(-2);
    let month = ('0' + (date.getMonth() + 1)).slice(-2);
    let year = date.getUTCFullYear();
    // Форматирование в виде "10 мая 2000 г."
    const formattedDate = `${day}/${month}/${year}`;
    return formattedDate;
  } else {
    return '-';
  }
}

function formatDate(dateString) {
  if (dateString !== '0000-00-00 00:00:00') {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Месяцы в JavaScript начинаются с 0
    const year = date.getFullYear();

    return `${day}/${month}/${year}`;
  } else {
    return '-';
  }
}

function convertStatusToUserStatus(status) {
  let statusForUser;
  switch (status) {
    case 'reception':
      statusForUser = 'Обработка';
      break;
    case 'proforma':
      statusForUser = 'Проформа';
      break;
    case 'confirm':
      statusForUser = 'Подтверждён';
      break;
    case 'production':
      statusForUser = 'Производство';
      break;
    case 'package':
      statusForUser = 'Упакован';
      break;
    case 'part-shipment':
      statusForUser = 'Отгружен частично';
      break;
    case 'shipment':
      statusForUser = 'Отгружен';
      break;
  }
  return statusForUser;
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

function formatDateObject(date) {
  const months = ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'];
  const days = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
  const addLeadingZero = (number) => {
    return number < 10 ? '0' + number : number;
  };
  return `${days[date.getDay()]}, ${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()} в ${addLeadingZero(date.getHours())}:${addLeadingZero(date.getMinutes())}`;
}

function checkContainer(container) {
  if (container) {
    return;
  }

}
function downloadZip(filesLinks, messageFromServer) {
  var zip = new JSZip();

  for (let index = 0; index < filesLinks.length; index++) {
    // Получаем оригинальный путь файла
    let filePath = filesLinks[index];

    // Заменяем начало URL
    filePath = serverUrl + filePath;
    // Получаем имя файла из пути
    let fileName = filePath.substring(filePath.lastIndexOf("/") + 1);

    // Добавляем файл в архив
    zip.file(fileName, fetch(filePath).then(response => response.blob()));
  }

  zip.generateAsync({ type: "blob" })
    .then(function (content) {
      var url = window.URL.createObjectURL(content);
      var a = document.createElement('a');
      a.href = url;
      a.download = 'contract-' + messageFromServer.serialNumber +
        '_dialog-' + messageFromServer.idDialog +
        '_type-' + messageFromServer.typeDialog +
        '_author-' + messageFromServer.firstNameAuthor + '-' + messageFromServer.lastNameAuthor +
        '_archive.zip';
      a.click();
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

function createTagLink(href, content, id, type, ...dataAttributes) {
  let element = createTagHtml('a', '', content, id, type, ...dataAttributes);
  element.href = href;
  return element;
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
  if (content && htmlTag !== 'img') {
    element.innerHTML = content;
  } else if (content && htmlTag === 'img') {
    element.src = content;
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
  if (dataAttributes.length >= 1) {
    dataAttributes.forEach(dataAttribute => {

      const [key, value] = dataAttribute;

      if (key === 'name') {
        element.name = value;
      } else if (key === 'method') {
        element.method = value;
      } else if (key === 'value') {
        element.value = value;
      } else if (key === 'style') {
        element.style.cssText = value;
      } else if (key === 'type') {
        element.setAttribute('type', value);
      } else if (key === 'multiple' && value === 'true') {
        element.multiple = true;
      } else if (key === 'multiple' && value === 'false') {
        element.multiple = false;
      } else {
        element.setAttribute(`data-${key}`, value);
      }

    });
  }
  // Возвращаем созданный элемент
  return element;
}

/*Функция упаковки DOM элементов*/
function massAppendChild(parent, ...childs) {
  childs.forEach(child => {
    if (child && parent) {
      parent.appendChild(child);
    }
  });
}

function checkType(where) {
  let what;
  switch (where) {
    case 'Консультация':
    case 'Consultation':
      what = 'consultation';
      break;
    case 'Заказ':
    case 'Order':
      what = 'order';
      break;
    case 'Рекламация':
    case 'Complaint':
      what = 'complaint';
      break;
    case 'Ответ':
      what = 'reply';
      break;
  }
  return what;
}

function checkExtensionFiles(avatarFile, fileNameHidden, extension) {
  let classForFile;
  switch (extension) {
    case "txt":
      classForFile = 'txt_file';
      break;
    case "docx":
    case "doc":
      classForFile = 'doc_file';
      break;
    case "pdf":
      classForFile = 'pdf_file';
      break;
    case "jpg":
    case "jpeg":
    case "png":
      classForFile = 'img_file';
      break;
    case "psd":
      classForFile = 'psd_file';
      break;
    case "ppt":
    case "pptx":
      classForFile = 'ppt_file';
      break;
    case "xls":
      classForFile = 'xls_file';
      break;
    case "xlsx":
      classForFile = 'xlsx_file';
      break;
    case "scn":
    case "cat":
      classForFile = 'kitchen_file';
      break;
    case "zip":
      classForFile = 'zip_file';
      break;
    case "rar":
      classForFile = 'rar_file';
      break;
    default:
      classForFile = 'default_file';
  };
  avatarFile.classList.add(classForFile);
  fileNameHidden.classList.add(classForFile);
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
    boxUploadsFiles.classList.add('dialog_files_now');
    for (let index = uploadsFiles.length - 1; index >= 0; index--) {
      let file = uploadsFiles[index];
      let fileNameParts = file.name.split('.');
      let extension = fileNameParts.pop();
      let name = fileNameParts.join('.');
      let avatarFile = createTagHtml('a', 'dialog_files_link');
      let fileName = createTagHtml('span', 'dialog_name', name);
      let fileNameHidden = createTagHtml('span', 'dialog_name_hidden', name);
      massAppendChild(avatarFile, fileName, fileNameHidden);
      let deleteSpan = createTagHtml('span', 'delete_dialog_files');
      deletedFileUploads(deleteSpan, avatarFile, uploadsFiles, index);
      checkExtensionFiles(avatarFile, fileNameHidden, extension);
      avatarFile.classList.add('added_file_now');
      boxUploadsFiles.appendChild(avatarFile);
    }
    return boxUploadsFiles;
  } else {
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
  };

  deleteSpan.addEventListener('click', fileLink.deleteSpanClickHandler);
}
/*Конец обработки загрузки файлов*/

/*Отправка данных на сервер*/
function startSubmit(textarea, uploadsFileslocal, type, sn, boxTopic) {
  if (type === "Order") {
    let flagCheckbox = checkCheckbox(textarea);

    if (flagCheckbox) {
      submitTopicLogic(textarea, uploadsFileslocal, type, sn);
      boxTopic.remove();
    } else {
      let boxNotice = boxTopic.querySelector('.create_topic_box_notice');
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

async function collectFormData(type, sn, textarea, uploadsFiles, idDialog, oldFilesSave, idMessage, status) {
  let formData = {
    typeDialog: type,
    serialNumber: sn,
    messageBody: textarea ? textarea.value : '',
    idDialog: idDialog || '',
    oldFilesSave: oldFilesSave || '',
    idMessage: idMessage || '',
    status: status || '',
    files: [],
    filesExist: true,
  };
  if (uploadsFiles && uploadsFiles.length > 0) {
    formData.filesExist = true;
    for (const file of uploadsFiles) {
      let renamedFile = new File([file], file.name, { type: file.type });
      let arrayBuffer = await readFileAsArrayBuffer(renamedFile);
      let base64Content = arrayBufferToBase64(arrayBuffer);
      formData.files.push({
        file: renamedFile,
        fileName: renamedFile.name,
        type: renamedFile.type,
        size: renamedFile.size,
        content: base64Content
      });
    }
  }
  if (idMessage === 1) {
    flagSecondMessageDialog = true;
  } else {
    flagSecondMessageDialog = false;
  }
  return formData;
}

function arrayBufferToBase64(arrayBuffer) {
  let binary = '';
  const bytes = new Uint8Array(arrayBuffer);
  const len = bytes.byteLength;
  for (let i = 0; i < len; i++) {
    binary += String.fromCharCode(bytes[i]);
  }
  return btoa(binary);
}

function readFileAsArrayBuffer(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = reject;
    reader.readAsArrayBuffer(file);
  });
}

// WORK WITH TABLE ORDERS
function createRowTableOrders(where, data, user) {

  let trOrder = createTagHtml("div", "tr_div_table_orders", '', '', '');
  trOrder.setAttribute('data-nf-' + where, parseInt(data.id));
  let tdOrderNumberFactory = createTagHtml("div", "td_div_table_order");
  let tdOrderNumberClient = createTagHtml("div", "td_div_table_order", data.clientName);
  let tdOrderStatus = createTagHtml("div", "td_div_table_order", '', '', '', ['order-status', data.status]);
  let tdOrderReceptionDate = createTagHtml("div", "td_div_table_order", formatDate(data.receptionDate), '', '', ['order-reception-date', data.receptionDateTS]);
  let tdOrderDateInvoice = createTagHtml("div", "td_div_table_order", '', '', '', ['order-invoice-date', data.invoiceDateTS]);
  // Build invoice date text and optional invoice link with hover behavior
  const invoiceDateSpan = createTagHtml('span', 'invoice-date-text', formatDate(data.invoiceDate));
  tdOrderDateInvoice.appendChild(invoiceDateSpan);
  invoiceDateHover(data.invoiceId, tdOrderDateInvoice, invoiceDateSpan, user.role);


  let tdOrderDateOutput = createTagHtml("div", "td_div_table_order", formatDate(data.requiredDate), '', '', ['order-output-date', data.requiredDateTS]);
  let tdOrderDateShipment = createTagHtml("div", "td_div_table_order", formatDate(data.shipmentDate), '', '', ['order-shipment-date', data.shipmentDateTS]);
  massAppendChild(
    trOrder,
    tdOrderNumberFactory, tdOrderNumberClient,
    tdOrderStatus, tdOrderReceptionDate, tdOrderDateInvoice,
    tdOrderDateOutput, tdOrderDateShipment
  );
  if (where === 'order-table') {
    let tdOrderPoint = createTagHtml("div", "td_div_table_order", data.namePoint);
    trOrder.appendChild(tdOrderPoint);
  }
  tdOrderStatus.classList.add('td-status-date');
  tdOrderNumberClient.classList.add('number-client');
  tdOrderNumberClient.classList.add('number-client');
  tdOrderStatus.setAttribute("data-order-status-date", data.orderStatusDateTS);

  tdOrderStatus = controlStatusOrder(tdOrderStatus, data.status, data.orderStatusDate);

  let orderNumber = createTagHtml('span', 'order-number', data.orderNumber);
  tdOrderNumberFactory.appendChild(orderNumber);
  tdOrderNumberFactory.classList.add('td_table_order_number');

  return trOrder;
}

function controlStatusOrder(parent, status, orderStatusDate) {
  if (orderStatusDate !== '') {
    let orderStatusText = createTagHtml("span", "order_status", status);
    let statusDate = createTagHtml("span", "order_status_date", formatDate(orderStatusDate));
    statusDate.classList.add('hidden-status-date');
    massAppendChild(
      parent,
      statusDate, orderStatusText
    );
    parent.classList.add('status-with-inner-contains');
  } else {
    parent.innerHTML = status;
  }
  return parent;
}

function simpleSendData(data, method) {
  let jsonData = JSON.stringify(data);
  console.log("method: ", method);
  console.log("jsonData: ", jsonData);
  socket.send(method + '::: ' + jsonData);
}

function newLine(textarea) {
  textarea.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      textarea.value = textarea.value + '\n';
    }
  });
}

function newLineHandler(textarea) {
  textarea.value = textarea.value.replace(/\n/g, '<br>');
}

function decodeMessage(text) {
  return text.replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#039;/g, "'")
    .replace(/&amp;/g, '&');
}

function removeCharacters(text) {
  // First decode HTML entities to actual characters
  let decoded = text.replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#039;/g, "'")
    .replace(/&amp;/g, '&');

  // Then strip out any HTML tags
  return decoded.replace(/<[^>]*>/g, ' ');
}


function invoiceDateHover(invoiceId, tdOrderDateInvoice, invoiceDateSpan, role) {
  if (invoiceId && parseInt(invoiceId) > 0) {
    const invoiceLink = createTagHtml('a', 'сompound_link', 'Состав');
    const invoiceDownload = createTagHtml('a', 'invoice_download', 'Скачать');
    invoiceLink.classList.add('hidden-link');
    invoiceDownload.classList.add('hidden-link');
    // Click to download/print invoice like on invoices page
    invoiceLink.addEventListener('click', function (event) {
      event.preventDefault();
      window.open(`/orders/?invoiceId=${invoiceId}`);
    });
    invoiceDownload.addEventListener('click', function (event) {
      event.preventDefault();
      simpleSendData(invoiceId, 'printInvoice');
    });
    if (checkRole(role)) {
      invoiceDateSpan.classList.add('hidden-invoice-date-text');
      massAppendChild(tdOrderDateInvoice,
        invoiceDownload, invoiceLink
      );
      // tdOrderDateInvoice.appendChild(invoiceLink);
    }
  }
}




async function startAudioRecording() {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    const mediaRecorder = new MediaRecorder(stream);
    const audioChunks = [];

    mediaRecorder.ondataavailable = (event) => {
      if (event.data.size > 0) {
        audioChunks.push(event.data);
      }
    };

    mediaRecorder.start();
    return { mediaRecorder, audioChunks };
  } catch (error) {
    console.error('Error starting audio recording:', error);
    throw error;
  }
}

async function stopAudioRecording(mediaRecorder, audioChunks) {
  if (!mediaRecorder || mediaRecorder.state === 'inactive') {
    throw new Error('No active recording to stop');
  }

  return new Promise((resolve) => {
    mediaRecorder.onstop = () => {
      // Stop all tracks in the stream
      mediaRecorder.stream.getTracks().forEach(track => track.stop());

      // Create blob from collected chunks
      const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
      const audioUrl = URL.createObjectURL(audioBlob);
      resolve({ audioBlob, audioUrl });
    };

    try {
      mediaRecorder.stop();
    } catch (error) {
      console.error('Error stopping recording:', error);
      mediaRecorder.stream.getTracks().forEach(track => track.stop());
      resolve({ audioBlob: null, audioUrl: null });
    }
  });
}

function createAudioPlayer(audioUrl) {
  return new Promise((resolve) => {
    const audioPlayer = document.createElement('audio');
    audioPlayer.controls = true;
    audioPlayer.src = audioUrl;

    audioPlayer.onloadedmetadata = () => {
      resolve(audioPlayer);
    };

    audioPlayer.onerror = (error) => {
      console.error('Audio player error:', error);
      resolve(audioPlayer);
    };
  });
}

function loadingIndicator(parent) {
  const loadingIndicator = createTagHtml('div', 'loading-visible', '', 'loading-indicator');
  const spinner = createTagHtml('div', 'spinner');
  const loadingText = createTagHtml('p', '', 'Loading...');
  massAppendChild(loadingIndicator, spinner, loadingText);
  let height = parent.clientHeight;
  loadingIndicator.style.height = 300 + 'px';
  parent.innerHTML = '';
  parent.appendChild(loadingIndicator);
  parent.style.display = 'block';
  parent.style.height = height + 'px';
  // return loadingIndicator;
}